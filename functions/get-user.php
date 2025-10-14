<?php
include '../config.php';

// Make sure an ID was passed
if (!isset($_GET['id'])) {
    echo json_encode(["error" => "Missing ID"]);
    exit;
}


$id = intval($_GET['id']); // always sanitize input

// Prepare query
$query = "SELECT * FROM tbl_users WHERE id = $id LIMIT 1";
$result = mysqli_query($conn, $query);

// Check if found
if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    header('Content-Type: application/json');
    echo json_encode($user);
} else {
    header('Content-Type: application/json');
    echo json_encode(["error" => "User not found"]);
}
