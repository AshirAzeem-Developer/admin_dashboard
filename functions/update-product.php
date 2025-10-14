<?php
include '../config.php'; // Adjust path as needed

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    exit("Error: User not logged in or session expired. Please log in again.");
}



if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request method.");
}

if (!isset($_POST['id'], $_POST['product_name'], $_POST['price'], $_POST['isHot'], $_POST['isActive'])) {
    exit("Required fields are missing.");
}

$productId = (int) $_POST['id'];
$product_name = trim($_POST['product_name']);
$description = trim($_POST['description'] ?? '');
$price = (float) $_POST['price'];
$category_id = empty($_POST['category_id']) ? NULL : (int) $_POST['category_id'];
$isHot = (int) $_POST['isHot'];
$isActive = (int) $_POST['isActive'];
$updatedBy = (int) $_SESSION['user_id'];

$uploadDir = '../uploads/products/';
$uploadedFiles = [];
$maxFileSize = 1024 * 1024 * 5;
$maxFiles = 5;

// --- 1. Handle File Upload (optional replacement) ---
if (!empty($_FILES['fileToUpload']['name'][0])) {
    // Fetch existing attachments for deletion
    $stmt_fetch = $conn->prepare("SELECT attachments FROM tbl_products WHERE id = ?");
    $stmt_fetch->bind_param("i", $productId);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    $oldAttachments = $result_fetch->fetch_assoc()['attachments'];
    $stmt_fetch->close();
    $oldAttachmentArray = json_decode($oldAttachments, true) ?? [];

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileCount = count(array_filter($_FILES['fileToUpload']['name']));
    if ($fileCount > $maxFiles) {
        exit("Error: You can only upload a maximum of $maxFiles files.");
    }

    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = $_FILES['fileToUpload']['name'][$i];
        $fileTmpName = $_FILES['fileToUpload']['tmp_name'][$i];
        $fileSize = $_FILES['fileToUpload']['size'][$i];
        $fileError = $_FILES['fileToUpload']['error'][$i];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileError !== 0 || $fileSize === 0) continue;
        if ($fileSize > $maxFileSize) exit("Error: File '$fileName' exceeds 5MB limit.");

        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($fileExt, $allowed)) exit("Error: File type '$fileExt' not allowed.");

        $newFileName = uniqid('', true) . "." . $fileExt;
        $fileDestination = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            $uploadedFiles[] = [
                'name' => $fileName,
                'type' => $fileExt,
                'size' => $fileSize,
                'url'  => 'uploads/products/' . $newFileName
            ];
        } else {
            exit("Error uploading file: $fileName");
        }
    }

    // Prepare JSON for tbl_products
    $attachments_json = json_encode(array_map(fn($f) => $f['url'], $uploadedFiles));

    // --- Delete old files physically ---
    foreach ($oldAttachmentArray as $fileToDelete) {
        $fullPath = '../' . $fileToDelete;
        if (file_exists($fullPath)) unlink($fullPath);
    }

    // --- Delete old attachment records ---
    $delStmt = $conn->prepare("DELETE FROM tbl_attachments WHERE product_id = ?");
    $delStmt->bind_param("i", $productId);
    $delStmt->execute();
    $delStmt->close();

    // --- Insert new attachment records ---
    if (!empty($uploadedFiles)) {
        $attachSql = "INSERT INTO tbl_attachments (product_id, file_name, file_type, file_size, file_url, is_primary, created_by, updated_by)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $attachStmt = $conn->prepare($attachSql);

        foreach ($uploadedFiles as $index => $file) {
            if (!is_array($file)) continue;
            $isPrimary = ($index === 0) ? 1 : 0;

            $attachStmt->bind_param(
                "issisiii",
                $productId,
                $file['name'],
                $file['type'],
                $file['size'],
                $file['url'],
                $isPrimary,
                $updatedBy,
                $updatedBy,
            );
            $attachStmt->execute();
        }
        $attachStmt->close();
    }
} else {
    // Keep existing attachments
    $stmt_fetch = $conn->prepare("SELECT attachments FROM tbl_products WHERE id = ?");
    $stmt_fetch->bind_param("i", $productId);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    $attachments_json = $result_fetch->fetch_assoc()['attachments'];
    $stmt_fetch->close();
}

// --- 2. Update Product Info ---
$sql = "UPDATE tbl_products 
        SET product_name = ?, description = ?, price = ?, attachments = ?, isHot = ?, isActive = ?, category_id = ?, updated_by = ?, updated_at = NOW()
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdsiiiii", $product_name, $description, $price, $attachments_json, $isHot, $isActive, $category_id, $updatedBy, $productId);

if ($stmt->execute()) {
    echo "✅ Product <b>$product_name</b> updated successfully!";
} else {
    echo "❌ Error updating product: " . $stmt->error;
}

$stmt->close();
$conn->close();
