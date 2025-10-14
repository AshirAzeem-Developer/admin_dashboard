<?php
include '../config.php';

$id = $_POST['id'];
$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$email = $_POST['email'];
$designation = $_POST['designation'];
$phone = $_POST['phone_number'];
$updated_by = $_SESSION['user_id'] ?? null; // Assuming the admin's user ID is stored in session
$updated_at = date('Y-m-d H:i:s');


$target_dir = "../uploads/";
$max_file_size = 1048576;
$allowed_file_type = "pdf";
if (!is_dir($target_dir)) {
    @mkdir($target_dir, 0777, true);
}

$query = "UPDATE tbl_users SET firstname='$firstname', lastname='$lastname', email='$email', designation='$designation', phone_number='$phone', updated_by='$updated_by', updated_at='$updated_at' WHERE id='$id'";

if (mysqli_query($conn, $query)) {
    $uploaded_file = $_FILES["fileToUpload"] ?? null;
    $upload_ok = true;

    $original_filename = basename($uploaded_file["name"]);
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    $user_dir = $target_dir . $id . "/";
    if (!is_dir($user_dir)) {
        mkdir($user_dir, 0777, true);
    }
    $target_file = $user_dir . $original_filename;

    if ($uploaded_file["error"] !== UPLOAD_ERR_OK) {
        $error = '<div class="alert alert-danger">Upload failed due to a server error. Check your file size or try again.</div>';
        $upload_ok = false;
        echo $error;
    } elseif ($uploaded_file["size"] > $max_file_size) {
        $error = '<div class="alert alert-danger">Sorry, your file is too large. Max size is 1MB.</div>';
        $upload_ok = false;
        echo $error;
    } elseif ($file_extension !== $allowed_file_type) {
        $error = '<div class="alert alert-danger">Sorry, only **' . strtoupper($allowed_file_type) . '** files are allowed.</div>';
        $upload_ok = false;
        echo $error;
    }
    if ($upload_ok && file_exists($target_file)) {
        $current_timestamp = time();
        $file_name_without_ext = pathinfo($original_filename, PATHINFO_FILENAME);

        $new_target_file = $user_dir . $file_name_without_ext . '-' . $current_timestamp . '.' . $file_extension;


        if (rename($target_file, $new_target_file)) {

            $warning_message = '<div class="alert alert-warning">A file named "' . $original_filename . '" already existed. It was renamed to "' . basename($new_target_file) . '".</div>';
        } else {
            $error = '<div class="alert alert-danger">Could not rename the existing file. Upload aborted due to permission issues.</div>';
            echo $error;
            $upload_ok = false;
        }
    }
    if ($upload_ok) {
        if (move_uploaded_file($uploaded_file["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE tbl_users SET attachment = ? WHERE id = ?");
            $stmt->bind_param('si', $target_file, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            $error = '<div class="alert alert-success">The file **' . htmlspecialchars($original_filename) . '** has been successfully uploaded!</div>' . ($warning_message ?? '');
            echo $error;
        } else {
            $error = '<div class="alert alert-danger">Sorry, there was an error moving your file (permissions or temporary file issue).</div>';
            echo $error;
        }
    }

    echo "User updated successfully!";
} else {
    echo "Error updating user: " . mysqli_error($conn);
}
