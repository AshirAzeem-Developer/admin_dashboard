<?php
// Path check: Go up one level (from functions/) to find config.php
include '../config.php';

// Check for the user ID in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo "Error: User ID is required for deletion.";
    exit;
}

$user_id = $_GET['id'];

// Use Prepared Statements to safely delete the user
$query = "DELETE FROM tbl_users WHERE id = ?";

$stmt = mysqli_prepare($conn, $query);

if ($stmt === false) {
    http_response_code(500);
    echo "Database error: " . mysqli_error($conn);
    exit;
}

// 'i' specifies the parameter is an integer
mysqli_stmt_bind_param($stmt, 'i', $user_id);

// Execute the statement
if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "User ID $user_id deleted successfully!";
    } else {
        http_response_code(404);
        echo "Error: User ID $user_id not found.";
    }
} else {
    http_response_code(500); // Internal Server Error
    echo "Error executing delete: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
