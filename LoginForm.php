<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>
    <?php
    session_start();

    if (isset($_SESSION['user'])) {
        header("Location: Dashboard.php", replace: true);
        exit();
    }
    ?>
    <div style="height: 100vh; width: 100vw;"
        class="d-flex flex-column align-items-center justify-content-center px-5 bg-light py-5">
        <h1 class="mb-5 text-dark">Login</h1>

        <?php
        $emailErr = $passwordErr = "";
        $email = $password = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (empty($_POST["email"])) {
                $emailErr = "Email is required";
            } else {
                $email = $_POST["email"];
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emailErr = "Invalid email format";
                }
            }
            if (empty($_POST["password"])) {
                $passwordErr = "Password is required";
            } else {
                $password = $_POST["password"];
                $password_regex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/";
                if (!preg_match($password_regex, $password)) {
                    $passwordErr = "Password must be at least 8 characters, and contain at least one uppercase letter, one lowercase letter, and one number.";
                }
            }
            if (empty($emailErr) && empty($passwordErr)) {

                if ($email === "admin@example.com" && $password === "Admin123") {

                    echo "<p class='text-success text-center'>Login successful!</p>";
                    $_SESSION["user"] = $email;
                    header(header: "Location:http://localhost/admin_dashboard/Dashboard.php", replace: true);
                    $email = $password = "";
                }
                echo "<p class='text-danger text-center'>Seem's Like you entered incorrect credentials.</p>";
            }
        }
        ?>

        <form method="POST" class="w-50 h-50 d-flex flex-column align-items-center justify-content-start">
            <div class="w-75 p-3">
                <Label class="text-dark mb-2">Email</Label>
                <input
                    type="text"
                    class="form-control text-black"
                    id="user-search"
                    name="email"
                    placeholder=""
                    aria-controls="example"
                    value="" />
                <div class="text-danger mt-2"><?= !empty($emailErr) ? $emailErr : "" ?></div>
            </div>
            <div class="w-75 p-3">
                <Label class="text-dark mb-2">Password</Label>
                <input
                    type="password"
                    class="form-control text-black"
                    id="user-password"
                    name="password"
                    placeholder=""
                    aria-controls="example"
                    value="" />
                <div class="text-danger mt-2"><?= !empty($passwordErr) ? $passwordErr : ""  ?></div>
            </div>
            <div class="p-3 w-100 d-flex flex-column align-items-center justify-content-center">
                <button type="submit" class="btn btn-success w-75">Login</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>

</html>