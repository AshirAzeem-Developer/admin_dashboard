<?php
include '../config.php'; // Adjust path as needed

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID is required.']);
    exit();
}

$productId = (int) $_GET['id'];

// Prepare the SQL statement to prevent SQL Injection
$stmt = $conn->prepare("SELECT id, product_name, description, price, attachments, isHot, isActive, category_id FROM tbl_products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();

    // Decode the JSON attachments field for the frontend
    $product['attachments'] = json_decode($product['attachments'], true);

    echo json_encode($product);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found.']);
}

$stmt->close();
$conn->close();
