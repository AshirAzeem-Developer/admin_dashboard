 <?php
    $isAdmin = $_SESSION["designation"] === "admin" ? true : false;
    ?>

 <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2" id="sidenav-main">
     <div class="sidenav-header">
         <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
         <a class="navbar-brand px-4 py-3 m-0" href=" https://demos.creative-tim.com/material-dashboard/pages/dashboard " target="_blank">
             <img src="./assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
             <span class="ms-1 text-sm text-dark">Creative Tim</span>
         </a>
     </div>
     <hr class="horizontal dark mt-0 mb-2">
     <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
         <ul class="navbar-nav">
             <li class="nav-item">
                 <?php
                    $current_page = basename($_SERVER['PHP_SELF']);
                    ?>
                 <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active bg-gradient-dark text-white' : 'text-dark' ?>" href="./dashboard.php">
                     <i class="material-symbols-rounded opacity-5">dashboard</i>
                     <span class="nav-link-text ms-1">Dashboard</span>
                 </a>
             </li>
             <li class="nav-item">
                 <a class="nav-link <?= $current_page === 'upload.php' ? 'active bg-gradient-dark text-white' : 'text-dark' ?>" href="./upload.php">
                     <i class="material-symbols-rounded opacity-5">file_upload</i>
                     <span class="nav-link-text ms-1">Upload</span>
                 </a>
             </li>
             <?php if ($isAdmin) : ?>
                 <li class="nav-item">
                     <a class="nav-link <?= $current_page === 'profile.php' ? 'active bg-gradient-dark text-white' : 'text-dark' ?>" href="./profile.php">
                         <i class="material-symbols-rounded opacity-5">person</i>
                         <span class="nav-link-text ms-1">Users</span>
                     </a>
                 </li>
             <?php endif; ?>
             <!-- Products Option -->
             <li class="nav-item">
                 <a class="nav-link <?= $current_page === 'products.php' ? 'active bg-gradient-dark text-white' : 'text-dark' ?>" href="./products.php">
                     <i class="material-symbols-rounded opacity-5">inventory_2</i>
                     <span class="nav-link-text ms-1">Products</span>
                 </a>
             </li>
             <!-- Reports Option -->
             <li class="nav-item">
                 <a class="nav-link <?= $current_page === 'reports.php' ? 'active bg-gradient-dark text-white' : 'text-dark' ?>" href="./reports.php">
                     <i class="material-symbols-rounded opacity-5">bar_chart</i>
                     <span class="nav-link-text ms-1">Reports</span>
                 </a>
             </li>


             <li class="nav-item mt-3">
                 <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Account pages</h6>
             </li>
             <li class="nav-item">
                 <a class="nav-link text-dark" href="profile.php">
                     <i class="material-symbols-rounded opacity-5">person</i>
                     <span class="nav-link-text ms-1">Profile</span>
                 </a>
             </li>
             <li class="nav-item">
                 <a class="nav-link text-dark" href="sign-in.php">
                     <i class="material-symbols-rounded opacity-5">login</i>
                     <span class="nav-link-text ms-1">Sign In</span>
                 </a>
             </li>
             <li class="nav-item">
                 <a class="nav-link text-dark" href="sign-up.php">
                     <i class="material-symbols-rounded opacity-5">assignment</i>
                     <span class="nav-link-text ms-1">Sign Up</span>
                 </a>
             </li>
         </ul>
     </div>
     <div class="sidenav-footer position-absolute w-100 bottom-0 ">
         <div class="mx-2">
             <?php
                $target_dir = "uploads/";
                $max_file_size = 1048576;
                $allowed_file_type = "pdf";
                $message = '';
                $upload_ok = true;


                if (!is_dir($target_dir)) {
                    if (!@mkdir($target_dir, 0777, true)) {
                        $message = '<div style="color: red;">Error: Could not create upload directory. Check permissions.</div>';
                        $upload_ok = false;
                    }
                }
                if (isset($_POST["submit"]) && $upload_ok) {


                    if (empty($_FILES["fileToUpload"]["name"])) {
                        $message = '<div style="color: red;" class="absolute">Please select a file to upload.</div>';
                        $upload_ok = false;
                    } else {
                        $uploaded_file = $_FILES["fileToUpload"];
                        if ($uploaded_file["error"] !== UPLOAD_ERR_OK) {
                            $upload_ok = false;
                            switch ($uploaded_file["error"]) {
                                case UPLOAD_ERR_INI_SIZE:
                                case UPLOAD_ERR_FORM_SIZE:
                                    $message = '<div style="color: red;">**ERROR**: The file is too large! It exceeds the server\'s maximum file size limit (check php.ini).</div>';
                                    break;
                                case UPLOAD_ERR_PARTIAL:
                                    $message = '<div style="color: red;">**ERROR**: The file was only partially uploaded. Please try again.</div>';
                                    break;
                                case UPLOAD_ERR_NO_FILE:
                                    $message = '<div style="color: red;">**ERROR**: No file was uploaded.</div>';
                                    break;
                                case UPLOAD_ERR_NO_TMP_DIR:
                                    $message = '<div style="color: red;">**ERROR**: Missing a temporary folder on the server.</div>';
                                    break;
                                case UPLOAD_ERR_CANT_WRITE:
                                    $message = '<div style="color: red;">**ERROR**: Failed to write file to disk. Check permissions.</div>';
                                    break;
                                default:
                                    $message = '<div style="color: red;">**ERROR**: An unknown upload error occurred (Code: ' . $uploaded_file["error"] . ').</div>';
                                    break;
                            }
                        }

                        if ($upload_ok) {
                            $original_filename = basename($uploaded_file["name"]);
                            $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                            $target_file = $target_dir . $original_filename;


                            if ($uploaded_file["size"] > $max_file_size) {
                                $message = '<div style="color: red;">Sorry, your file is too large. Max size is 1MB (' . ($max_file_size / 1024 / 1024) . ' MB).</div>';
                                $upload_ok = false;
                            }

                            if ($file_extension != $allowed_file_type) {
                                $message = '<div style="color: red;">Sorry, only **' . strtoupper($allowed_file_type) . '** files are allowed.</div>';
                                $upload_ok = false;
                            }

                            $final_target_file = $target_file;

                            if (file_exists($final_target_file) && $upload_ok) {
                                $current_timestamp = time();
                                $file_name_without_ext = pathinfo($original_filename, PATHINFO_FILENAME);
                                $new_target_file = $target_dir . $file_name_without_ext . '-' . $current_timestamp . '.' . $file_extension;

                                if (rename($final_target_file, $new_target_file)) {
                                    // Note the message is warning/informational, not an error
                                    $message .= '<div style="color: orange;">A file named "' . $original_filename . '" already existed. It was renamed to "' . basename($new_target_file) . '".</div>';
                                } else {
                                    $message = '<div style="color: red;">Could not rename the existing file. Upload aborted.</div>';
                                    $upload_ok = false;
                                }
                            }

                            if ($upload_ok) {

                                if (move_uploaded_file($uploaded_file["tmp_name"], $final_target_file)) {
                                    $message = '<div style="color: green;">The file **' . htmlspecialchars($original_filename) . '** has been successfully uploaded!</div>' . $message;
                                } else {
                                    // This usually means permissions issues or the file disappeared
                                    $message = '<div style="color: red;">Sorry, there was an error moving your file (permissions/path issue).</div>';
                                }

                                // header('Location: Dashboard.php', replace: true);
                                // exit();
                            }
                        }
                    }
                }
                ?>

             <a class="btn bg-gradient-dark w-100" href="https://www.creative-tim.com/product/material-dashboard-pro?ref=sidebarfree" type="button">Upgrade to pro</a>
         </div>
     </div>
 </aside>