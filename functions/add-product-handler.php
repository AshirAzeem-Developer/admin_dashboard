<?php
include '../config.php'; // Adjust path as needed

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request method.");
}

// Check for required fields
if (!isset($_POST['product_name'], $_POST['price'], $_POST['isHot'], $_POST['isActive'])) {
    exit("Required fields are missing.");
}

$product_name = trim($_POST['product_name']);
$description = trim($_POST['description'] ?? '');
$price = (float) $_POST['price'];
$category_id = empty($_POST['category_id']) ? NULL : (int) $_POST['category_id'];
$isHot = (int) $_POST['isHot'];
$isActive = (int) $_POST['isActive'];
$createdBy = $_SESSION['user_id'] ?? NULL; // Assuming user_id is in session

$uploadDir = '../uploads/products/';
$uploadedFiles = [];
$maxFileSize = 1024 * 1024 * 5; // 5MB limit
$maxFiles = 5;

// --- File Upload Handling ---
if (!empty($_FILES['fileToUpload']['name'][0])) {
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileCount = count($_FILES['fileToUpload']['name']);
    if ($fileCount > $maxFiles) {
        exit("Error: You can only upload a maximum of $maxFiles files.");
    }

    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = $_FILES['fileToUpload']['name'][$i];
        $fileTmpName = $_FILES['fileToUpload']['tmp_name'][$i];
        $fileSize = $_FILES['fileToUpload']['size'][$i];
        $fileError = $_FILES['fileToUpload']['error'][$i];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileError !== 0) continue;

        if ($fileSize > $maxFileSize) {
            exit("Error: File '$fileName' exceeds the 5MB size limit.");
        }

        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($fileExt, $allowed)) {
            exit("Error: File type '$fileExt' not allowed. Only JPG, PNG, and PDF are permitted.");
        }

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
}

// ✅ Keep structure intact; only extract URLs for JSON storage
$attachments_json = !empty($uploadedFiles)
    ? json_encode(array_map(fn($f) => $f['url'], $uploadedFiles))
    : NULL;

// --- Database Insertion for Product ---
$sql = "INSERT INTO tbl_products (product_name, description, price, attachments, isHot, isActive, category_id, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdsiiii", $product_name, $description, $price, $attachments_json, $isHot, $isActive, $category_id, $createdBy);

if ($stmt->execute()) {
    $product_id = $stmt->insert_id;
    echo "✅ Product <b>$product_name</b> created successfully!<br>";

    // --- Insert into tbl_attachments ---
    if (!empty($uploadedFiles)) {
        $attachSql = "INSERT INTO tbl_attachments (product_id, file_name, file_type, file_size, file_url, is_primary, created_by)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
        $attachStmt = $conn->prepare($attachSql);

        foreach ($uploadedFiles as $index => $file) {
            if (!is_array($file)) continue; // Safety check
            $isPrimary = ($index === 0) ? 1 : 0;

            $attachStmt->bind_param(
                "issisii",
                $product_id,
                $file['name'],
                $file['type'],
                $file['size'],
                $file['url'],
                $isPrimary,
                $createdBy
            );
            $attachStmt->execute();
        }

        $attachStmt->close();
        echo count($uploadedFiles) . " attachment(s) added successfully.";
    }
} else {
    echo "❌ Error inserting product: " . $stmt->error;
}

$stmt->close();
$conn->close();
