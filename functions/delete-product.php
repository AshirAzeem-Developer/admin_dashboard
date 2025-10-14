<?php
include '../config.php'; // Adjust path as needed

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    exit("Product ID is required for deletion.");
}

$productId = (int) $_GET['id'];

$conn->begin_transaction(); // Start a transaction

// try {
//     // 1. Fetch attachment paths before deleting the record
//     $stmt_fetch = $conn->prepare("SELECT attachments FROM tbl_products WHERE id = ?");
//     $stmt_fetch->bind_param("i", $productId);
//     $stmt_fetch->execute();
//     $result_fetch = $stmt_fetch->get_result();
//     $productData = $result_fetch->fetch_assoc();
//     $stmt_fetch->close();

//     if (!$productData) {
//         throw new Exception("Product not found.");
//     }

//     $attachments = json_decode($productData['attachments'], true) ?? [];

//     // 2. Delete the record from the database
//     $stmt_delete = $conn->prepare("DELETE FROM tbl_products WHERE id = ?");
//     $stmt_delete->bind_param("i", $productId);

//     if (!$stmt_delete->execute()) {
//         throw new Exception("Database deletion failed: " . $stmt_delete->error);
//     }
//     $stmt_delete->close();

//     // 3. Delete the physical files
//     foreach ($attachments as $filePath) {
//         $fullPath = '../' . $filePath; // Path relative to this PHP script
//         if (file_exists($fullPath)) {
//             if (!unlink($fullPath)) {
//                 // Log but don't halt the transaction if file deletion fails
//                 error_log("Failed to delete file: " . $fullPath);
//             }
//         }
//     }

//     $conn->commit(); // Commit the transaction
//     echo "Product ID $productId and its associated files were successfully deleted.";
// } catch (Exception $e) {
//     $conn->rollback(); // Rollback on error
//     exit("Error deleting product: " . $e->getMessage());
// }


$stmt = $conn->prepare('UPDATE tbl_products SET isActive = 0 WHERE id = ?');
$stmt->bind_param("i", $productId);

if ($stmt->execute()) {
    echo "Product ID $productId has been successfully deactivated.";
} else {
    echo "Error deactivating product: " . $stmt->error;
}
$stmt->close();
$conn->close();
