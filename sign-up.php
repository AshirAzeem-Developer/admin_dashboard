<?php
// sign-up.php
include 'config.php';

// **SESSION CHECK: If the user is already logged in, redirect them**
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === TRUE) {
  header("Location: dashboard.php");
  exit();
}

$message = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $firstname = trim($_POST['firstname']);
  $lastname = trim($_POST['lastname']);
  $email = trim($_POST['email']);
  $designation = $_POST['designation'] ?? '';
  $password_raw = $_POST['password'] ?? '';
  $phone_number = trim($_POST['phone_number'] ?? '');
  $terms_agreed = isset($_POST['terms']);

  // ✅ Validation
  if (empty($firstname)) {
    $errors['firstname'] = "First name is required.";
  }
  if (empty($lastname)) {
    $errors['lastname'] = "Last name is required.";
  }
  if (empty($email)) {
    $errors['email'] = "Email is required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format.";
  }
  if (empty($designation)) {
    $errors['designation'] = "Please select your designation.";
  }
  if (empty($phone_number)) {
    $errors['phone_number'] = "Phone number is required.";
  } elseif (!preg_match('/^\+?[0-9]{7,15}$/', $phone_number)) {
    $errors['phone_number'] = "Invalid phone number format.";
  }
  if (empty($password_raw)) {
    $errors['password'] = "Password is required.";
  }
  if (!$terms_agreed) {
    $errors['terms'] = "You must agree to the Terms and Conditions.";
  }

  // ✅ If no validation errors, process the sign-up
  if (empty($errors)) {
    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO tbl_users (firstname, lastname, email, designation, password, phone_number) VALUES (?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
      error_log("SQL Prepare Error: " . $conn->error);
      $message = '<div class="alert alert-danger text-white">A server error occurred.</div>';
    } else {
      $stmt->bind_param("ssssss", $firstname, $lastname, $email, $designation, $password_hashed, $phone_number);

      try {
        if ($stmt->execute()) {
          $_SESSION['user_id'] = $stmt->insert_id;
          $_SESSION['user_name'] = $firstname;
          $_SESSION['designation'] = $designation === "Administrator" ? "admin" : "user";
          $_SESSION['logged_in'] = TRUE;
          header("Location: dashboard.php");
          exit();
        } else {
          $message = '<div class="alert alert-danger text-white">A server error occurred. Please try again.</div>';
        }
      } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
          $errors['email'] = "An account with this email already exists. Please sign in.";
        } else {
          $message = '<div class="alert alert-danger text-white">A general database error occurred.</div>';
        }
      } finally {
        $stmt->close();
      }
    }
  }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link
    rel="apple-touch-icon"
    sizes="76x76"
    href="./assets/img/apple-icon.png" />
  <link rel="icon" type="image/png" href="./assets/img/favicon.png" />
  <title>Material Dashboard 3 by Creative Tim</title>
  <link
    rel="stylesheet"
    type="text/css"
    href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
  <script
    src="https://kit.fontawesome.com/42d5adcbca.js"
    crossorigin="anonymous"></script>
  <link
    rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link
    id="pagestyle"
    href="./assets/css/material-dashboard.css?v=3.2.0"
    rel="stylesheet" />
</head>

<body class="">

  <main class="main-content mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div
              class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 start-0 text-center justify-content-center flex-column">
              <div
                class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center"
                style="
                                    background-image: url('./assets/img/illustrations/illustration-signup.jpg');
                                    background-size: cover;
                                "></div>
            </div>
            <div
              class="col-xl-5 col-lg-6 col-md-7 d-flex flex-column ms-auto me-auto ms-lg-auto me-lg-5">
              <div class="card card-plain">
                <div class="card-header">
                  <h4 class="font-weight-bolder">Sign Up</h4>
                  <p class="mb-0">
                    Enter your details to register
                  </p>
                </div>
                <div class="card-body">
                  <?php echo $message; ?>

                  <form role="form" method="POST" action="">
                    <div class="row">
                      <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <div class="input-group input-group-outline mb-1">
                          <input type="text" class="form-control" name="firstname"
                            value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>" />
                        </div>
                        <?php if (isset($errors['firstname'])): ?>
                          <small class="text-danger"><?php echo $errors['firstname']; ?></small>
                        <?php endif; ?>

                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <div class="input-group input-group-outline mb-1">
                          <input type="text" class="form-control" name="lastname"
                            value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" />
                        </div>
                        <?php if (isset($errors['lastname'])): ?>
                          <small class="text-danger"><?php echo $errors['lastname']; ?></small>
                        <?php endif; ?>

                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <div class="input-group input-group-outline mb-1">
                          <input type="email" class="form-control" name="email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
                        </div>
                        <?php if (isset($errors['email'])): ?>
                          <small class="text-danger"><?php echo $errors['email']; ?></small>
                        <?php endif; ?>

                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Designation</label>
                        <div class="input-group input-group-outline mb-1">
                          <select class="form-control" name="designation">
                            <option value="" disabled <?php echo empty($_POST['designation']) ? 'selected' : ''; ?>>Select your designation</option>
                            <option value="developer" <?php echo (($_POST['designation'] ?? '') == 'developer') ? 'selected' : ''; ?>>Developer</option>
                            <option value="designer" <?php echo (($_POST['designation'] ?? '') == 'designer') ? 'selected' : ''; ?>>Designer</option>
                            <option value="manager" <?php echo (($_POST['designation'] ?? '') == 'manager') ? 'selected' : ''; ?>>Manager</option>
                            <option value="intern" <?php echo (($_POST['designation'] ?? '') == 'intern') ? 'selected' : ''; ?>>Intern</option>
                            <option value="other" <?php echo (($_POST['designation'] ?? '') == 'other') ? 'selected' : ''; ?>>Other</option>
                          </select>
                        </div>
                        <?php if (isset($errors['designation'])): ?>
                          <small class="text-danger"><?php echo $errors['designation']; ?></small>
                        <?php endif; ?>

                      </div>
                    </div>

                    <label class="form-label">Password</label>
                    <div class="input-group input-group-outline mb-1">
                      <input type="password" class="form-control" name="password" />
                    </div>
                    <?php if (isset($errors['password'])): ?>
                      <small class="text-danger"><?php echo $errors['password']; ?></small>
                    <?php endif; ?>
                    <br>

                    <label class="form-label">Phone Number</label>
                    <div class="input-group input-group-outline mb-1">
                      <input type="tel" class="form-control" name="phone_number"
                        value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>" />
                    </div>
                    <?php if (isset($errors['phone_number'])): ?>
                      <small class="text-danger"><?php echo $errors['phone_number']; ?></small>
                    <?php endif; ?>

                    <div class="form-check form-check-info text-start ps-0 mb-1">
                      <input class="form-check-input" type="checkbox" value="agreed" id="flexCheckDefault" name="terms"
                        <?php echo isset($_POST['terms']) ? 'checked' : ''; ?> />
                      <label class="form-check-label" for="flexCheckDefault">
                        I agree to the
                        <a href="javascript:;" class="text-dark font-weight-bolder">Terms and Conditions</a>
                      </label>
                    </div>
                    <?php if (isset($errors['terms'])): ?>
                      <small class="text-danger"><?php echo $errors['terms']; ?></small>
                    <?php endif; ?>

                    <div class="text-center">
                      <button
                        type="submit"
                        class="btn btn-lg bg-gradient-dark btn-lg w-100 mt-4 mb-0">
                        Sign Up
                      </button>
                    </div>
                  </form>
                </div>
                <div class="card-footer text-center pt-0 px-lg-2 px-1 mt-4">
                  <p class="mb-2 text-sm mx-auto">
                    Already have an account?
                    <a
                      href="sign-in.php"
                      class="text-primary text-gradient font-weight-bold">Sign in</a>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf("Win") > -1;
    if (win && document.querySelector("#sidenav-scrollbar")) {
      var options = {
        damping: "0.5",
      };
      Scrollbar.init(document.querySelector("#sidenav-scrollbar"), options);
    }
  </script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="./assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>