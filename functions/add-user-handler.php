<?php
// Path check: Go up one level (from functions/) to find config.php
include '../config.php';

// Check if all required fields are present
if (empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['email']) || empty($_POST['password'])) {
    http_response_code(400); // Bad Request
    echo "Error: Required fields are missing.";
    exit;
}

// 1. Sanitize and Get Data
$firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
$lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$designation = mysqli_real_escape_string($conn, $_POST['designation'] ?? '');
$phone_number = mysqli_real_escape_string($conn, $_POST['phone_number'] ?? '');
$password = $_POST['password'];
$created_by = $_SESSION['user_id'] ?? null; // Assuming the admin's user ID is stored in session

$target_dir = "../uploads/";
$max_file_size = 1048576;
$allowed_file_type = "pdf";
if (!is_dir($target_dir)) {
    @mkdir($target_dir, 0777, true);
}

// 2. Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 3. SQL Query to Insert Data using Prepared Statements
$query = "INSERT INTO tbl_users (firstname, lastname, email, password, designation, phone_number, created_by) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

if ($stmt === false) {
    http_response_code(500);
    echo "Database error: " . mysqli_error($conn);
    exit;
}

mysqli_stmt_bind_param($stmt, 'ssssssi', $firstname, $lastname, $email, $hashed_password, $designation, $phone_number, $created_by);

// 4. Execute and Respond
if (mysqli_stmt_execute($stmt)) {
    $currentUser = $stmt->insert_id;
    if ($currentUser === null) {
        exit;
    }
    $uploaded_file = $_FILES["fileToUpload"] ?? null;
    $upload_ok = true;

    if (!$uploaded_file || $uploaded_file["error"] == UPLOAD_ERR_NO_FILE) {
        $error = '<div class="alert alert-danger">Please select a file to upload.</div>';
        $upload_ok = false;
    } else {

        $original_filename = basename($uploaded_file["name"]);
        $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $user_dir = $target_dir . $currentUser . "/";
        if (!is_dir($user_dir)) {
            mkdir($user_dir, 0777, true);
        }
        $target_file = $user_dir . $original_filename;

        if ($uploaded_file["error"] !== UPLOAD_ERR_OK) {
            $error = '<div class="alert alert-danger">Upload failed due to a server error. Check your file size or try again.</div>';
            $upload_ok = false;
        } elseif ($uploaded_file["size"] > $max_file_size) {
            $error = '<div class="alert alert-danger">Sorry, your file is too large. Max size is 1MB.</div>';
            $upload_ok = false;
        } elseif ($file_extension !== $allowed_file_type) {
            $error = '<div class="alert alert-danger">Sorry, only **' . strtoupper($allowed_file_type) . '** files are allowed.</div>';
            $upload_ok = false;
        }
        if ($upload_ok && file_exists($target_file)) {
            $current_timestamp = time();
            $file_name_without_ext = pathinfo($original_filename, PATHINFO_FILENAME);

            $new_target_file = $user_dir . $file_name_without_ext . '-' . $current_timestamp . '.' . $file_extension;


            if (rename($target_file, $new_target_file)) {

                $warning_message = '<div class="alert alert-warning">A file named "' . $original_filename . '" already existed. It was renamed to "' . basename($new_target_file) . '".</div>';
            } else {
                $error = '<div class="alert alert-danger">Could not rename the existing file. Upload aborted due to permission issues.</div>';
                $upload_ok = false;
            }
        }
        if ($upload_ok) {
            if (move_uploaded_file($uploaded_file["tmp_name"], $target_file)) {
                mysqli_stmt_close($stmt);
                $stmt = $conn->prepare("UPDATE tbl_users SET attachment = ? WHERE id = ?");
                $stmt->bind_param('si', $target_file, $currentUser);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                mysqli_close($conn);

                // $error = '<div class="alert alert-success">The file **' . htmlspecialchars($original_filename) . '** has been successfully uploaded!</div>' . ($warning_message ?? '');

            } else {
                $error = '<div class="alert alert-danger">Sorry, there was an error moving your file (permissions or temporary file issue).</div>';
            }
        }
    }

    echo "User created successfully!";
} else {
    http_response_code(500); // Internal Server Error
    echo "Error creating user: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
