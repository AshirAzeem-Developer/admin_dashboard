 <?php
    function handleSignout(): void
    {

        session_unset();
        session_destroy();

        header('Location: http://localhost/admin_dashboard/sign-in.php', true);
        exit();
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== TRUE) {
        header("Location: http://localhost/admin_dashboard/sign-in.php");
        exit();
    }

    if (isset($_GET['action']) && $_GET['action'] === 'signout') {
        handleSignout();
    }
    // If using your original POST method:

    if (isset($_POST['signout'])) {
        handleSignout();
    }
    ?>