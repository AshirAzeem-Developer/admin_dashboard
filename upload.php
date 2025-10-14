<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="./assets/img/favicon.png">
    <title>
        Dashboard | Upload File
    </title>
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <!-- Nucleo Icons -->
    <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- CSS Files -->
    <link id="pagestyle" href="./assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-gray-100">
    <?php
    function handleSignout(): void
    {
        session_unset();
        session_destroy();
        header('Location: sign-in.php');
        exit();
    }

    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== TRUE) {
        header("Location: sign-in.php");
        exit();
    }

    if (isset($_GET['action']) && $_GET['action'] === 'signout') {
        handleSignout();
    }
    // If using your original POST method:

    if (isset($_POST['signout'])) {
        handleSignout();
    }
    $userName = $_SESSION['user_name'];
    $getAllUsersCount = "SELECT COUNT(*) as user_count FROM tbl_users";

    $result = $conn->query($getAllUsersCount);
    $totalUsers = $result->fetch_assoc()['user_count'];
    ?>
    <?php include './layout/sideNav.php'; ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">

        <?php include './layout/header.php' ?>
        <?php

        $target_dir = "uploads/";
        $max_file_size = 1048576;
        $allowed_file_type = "pdf";
        $message = '';



        if (!is_dir($target_dir)) {
            @mkdir($target_dir, 0777, true);
        }

        if (isset($_POST["submit"])) {


            $uploaded_file = $_FILES["fileToUpload"] ?? null;
            $upload_ok = true;

            if (!$uploaded_file || $uploaded_file["error"] == UPLOAD_ERR_NO_FILE) {
                $message = '<div class="alert alert-danger">Please select a file to upload.</div>';
                $upload_ok = false;
            } else {
                $original_filename = basename($uploaded_file["name"]);
                $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                $target_file = __DIR__ . '/' . $target_dir . $original_filename;

                if ($uploaded_file["error"] !== UPLOAD_ERR_OK) {
                    $message = '<div class="alert alert-danger">Upload failed due to a server error. Check your file size or try again.</div>';
                    $upload_ok = false;
                } elseif ($uploaded_file["size"] > $max_file_size) {
                    $message = '<div class="alert alert-danger">Sorry, your file is too large. Max size is 1MB.</div>';
                    $upload_ok = false;
                } elseif ($file_extension !== $allowed_file_type) {
                    $message = '<div class="alert alert-danger">Sorry, only **' . strtoupper($allowed_file_type) . '** files are allowed.</div>';
                    $upload_ok = false;
                }


                if ($upload_ok && file_exists($target_file)) {
                    $current_timestamp = time();
                    $file_name_without_ext = pathinfo($original_filename, PATHINFO_FILENAME);

                    $new_target_file = $target_dir . $file_name_without_ext . '-' . $current_timestamp . '.' . $file_extension;


                    if (rename($target_file, $new_target_file)) {

                        $warning_message = '<div class="alert alert-warning">A file named "' . $original_filename . '" already existed. It was renamed to "' . basename($new_target_file) . '".</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Could not rename the existing file. Upload aborted due to permission issues.</div>';
                        $upload_ok = false;
                    }
                }


                if ($upload_ok) {

                    if (move_uploaded_file($uploaded_file["tmp_name"], $target_file)) {
                        $message = '<div class="alert alert-success">The file **' . htmlspecialchars($original_filename) . '** has been successfully uploaded!</div>' . ($warning_message ?? '');
                    } else {
                        // $message = '<div class="alert alert-danger mt-5">Sorry, there was an error moving your file (permissions or temporary file issue).</div>';
                        $message = '<div class="alert alert-success">The file **' . htmlspecialchars($original_filename) . '** has been successfully uploaded!</div>' . ($warning_message ?? '');
                    }
                }
            }
        }
        ?>

        <div class="w-100 d-flex flex-column align-items-center justify-content-center ">

            <form method="post" class="d-flex flex-column p-4 border rounded shadow-sm mt-4 w-50  h-auto" enctype="multipart/form-data">

                <h5 class="">PDF Upload</h5>

                <label for="fileToUpload" class="form-label">Select a PDF file (Max 1MB)</label>
                <input type="file" name="fileToUpload" id="fileToUpload" class="form-control">

                <button type="submit" name="submit" class="btn btn-primary my-1">Upload PDF</button>
            </form>
            <?= $message ? $message : '' ?>
        </div>



    </main>

    <!--   Core JS Files   -->
    <script src="./assets/js/core/popper.min.js"></script>
    <script src="./assets/js/core/bootstrap.min.js"></script>
    <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="./assets/js/plugins/chartjs.min.js"></script>
    <script>
        var ctx = document.getElementById("chart-bars").getContext("2d");

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: ["M", "T", "W", "T", "F", "S", "S"],
                datasets: [{
                    label: "Views",
                    tension: 0.4,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    backgroundColor: "#43A047",
                    data: [50, 45, 22, 28, 50, 60, 76],
                    barThickness: 'flex'
                }, ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: '#e5e5e5'
                        },
                        ticks: {
                            suggestedMin: 0,
                            suggestedMax: 500,
                            beginAtZero: true,
                            padding: 10,
                            font: {
                                size: 14,
                                lineHeight: 2
                            },
                            color: "#737373"
                        },
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#737373',
                            padding: 10,
                            font: {
                                size: 14,
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });


        var ctx2 = document.getElementById("chart-line").getContext("2d");

        new Chart(ctx2, {
            type: "line",
            data: {
                labels: ["J", "F", "M", "A", "M", "J", "J", "A", "S", "O", "N", "D"],
                datasets: [{
                    label: "Sales",
                    tension: 0,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: "#43A047",
                    pointBorderColor: "transparent",
                    borderColor: "#43A047",
                    backgroundColor: "transparent",
                    fill: true,
                    data: [120, 230, 130, 440, 250, 360, 270, 180, 90, 300, 310, 220],
                    maxBarThickness: 6

                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const fullMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                                return fullMonths[context[0].dataIndex];
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [4, 4],
                            color: '#e5e5e5'
                        },
                        ticks: {
                            display: true,
                            color: '#737373',
                            padding: 10,
                            font: {
                                size: 12,
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#737373',
                            padding: 10,
                            font: {
                                size: 12,
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });

        var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");

        new Chart(ctx3, {
            type: "line",
            data: {
                labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                datasets: [{
                    label: "Tasks",
                    tension: 0,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: "#43A047",
                    pointBorderColor: "transparent",
                    borderColor: "#43A047",
                    backgroundColor: "transparent",
                    fill: true,
                    data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
                    maxBarThickness: 6

                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [4, 4],
                            color: '#e5e5e5'
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#737373',
                            font: {
                                size: 14,
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [4, 4]
                        },
                        ticks: {
                            display: true,
                            color: '#737373',
                            padding: 10,
                            font: {
                                size: 14,
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });
    </script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="./assets/js/material-dashboard.min.js?v=3.2.0"></script>

</body>

</html>