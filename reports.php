<?php include 'config.php'; ?>
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
    <title>Reports Dashboard</title>
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

<body class="g-sidenav-show bg-gray-100">

    <?php include 'layout/sideNav.php'; ?>
    <div class="main-content position-relative max-height-vh-100 h-100">
        <div class="container-fluid px-2 px-md-4">
            <?php include 'layout/header.php'; ?>
            <div
                class="page-header min-height-300 border-radius-xl mt-4"
                style="
            background-image: url('https://images.unsplash.com/photo-1531512073830-ba890ca4eba2?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
          ">
                <span class="mask bg-gradient-dark opacity-6"></span>
            </div>
            <div class="card card-body mx-2 mx-md-2 mt-n6">
                <h5 class="mb-0">Reports</h5>
                <p class="text-sm">Overview of user activities and statistics.</p>

                <div class="row mb-4 p-3 bg-light border-radius-lg shadow-sm">
                    <div class="col-md-4 mb-3">
                        <label for="reportTimePeriod" class="form-label">Time Period</label>
                        <select class="form-select form-select-lg p-2" id="reportTimePeriod" aria-label="Time Period Selection">
                            <option selected>Select Time Period</option>
                            <option value="last_7_days">Last 7 Days</option>
                            <option value="last_30_days">Last 30 Days</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_quarter">This Quarter</option>
                            <option value="last_quarter">Last Quarter</option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="reportDepartment" class="form-label">Department</label>
                        <select class="form-select form-select-lg p-2" id="reportDepartment" aria-label="Department Selection">
                            <option selected>Select Department (Optional)</option>
                            <option value="sales">Sales</option>
                            <option value="marketing">Marketing</option>
                            <option value="finance">Finance</option>
                            <option value="hr">Human Resources</option>
                            <option value="operations">Operations</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end mb-3">
                        <button class="btn bg-gradient-primary w-100 mb-0" id="generateReportButton">
                            <i class="material-symbols-rounded me-2">analytics</i>
                            Generate Report
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card my-4">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                    <h6 class="text-white text-capitalize ps-3">Generated Report View</h6>
                                </div>
                            </div>
                            <div class="card-body px-0 pb-2">
                                <div class="p-3">
                                    <p class="text-secondary text-sm">
                                        Use the drop-downs above to refine your data and click **"Generate Report"** to view the results here.
                                    </p>
                                    <div style="height: 300px; background-color: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid #dee2e6;">
                                        **[Area for charts and report data based on selected filters]**
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>

    </div>
    <div class="fixed-plugin">
        <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
            <i class="material-symbols-rounded py-2">settings</i>
        </a>
        <div class="card shadow-lg">
            <div class="card-header pb-0 pt-3">
                <div class="float-start">
                    <h5 class="mt-3 mb-0">Material UI Configurator</h5>
                    <p>See our dashboard options.</p>
                </div>
                <div class="float-end mt-4">
                    <button
                        class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                        <i class="material-symbols-rounded">clear</i>
                    </button>
                </div>
            </div>
            <hr class="horizontal dark my-1" />
            <div class="card-body pt-sm-3 pt-0">
                <div>
                    <h6 class="mb-0">Sidebar Colors</h6>
                </div>
                <a href="javascript:void(0)" class="switch-trigger background-color">
                    <div class="badge-colors my-2 text-start">
                        <span
                            class="badge filter bg-gradient-primary"
                            data-color="primary"
                            onclick="sidebarColor(this)"></span>
                        <span
                            class="badge filter bg-gradient-dark active"
                            data-color="dark"
                            onclick="sidebarColor(this)"></span>
                        <span
                            class="badge filter bg-gradient-info"
                            data-color="info"
                            onclick="sidebarColor(this)"></span>
                        <span
                            class="badge filter bg-gradient-success"
                            data-color="success"
                            onclick="sidebarColor(this)"></span>
                        <span
                            class="badge filter bg-gradient-warning"
                            data-color="warning"
                            onclick="sidebarColor(this)"></span>
                        <span
                            class="badge filter bg-gradient-danger"
                            data-color="danger"
                            onclick="sidebarColor(this)"></span>
                    </div>
                </a>
                <div class="mt-3">
                    <h6 class="mb-0">Sidenav Type</h6>
                    <p class="text-sm">Choose between different sidenav types.</p>
                </div>
                <div class="d-flex">
                    <button
                        class="btn bg-gradient-dark px-3 mb-2"
                        data-class="bg-gradient-dark"
                        onclick="sidebarType(this)">
                        Dark
                    </button>
                    <button
                        class="btn bg-gradient-dark px-3 mb-2 ms-2"
                        data-class="bg-transparent"
                        onclick="sidebarType(this)">
                        Transparent
                    </button>
                    <button
                        class="btn bg-gradient-dark px-3 mb-2 active ms-2"
                        data-class="bg-white"
                        onclick="sidebarType(this)">
                        White
                    </button>
                </div>
                <p class="text-sm d-xl-none d-block mt-2">
                    You can change the sidenav type just on desktop view.
                </p>
                <div class="mt-3 d-flex">
                    <h6 class="mb-0">Navbar Fixed</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input
                            class="form-check-input mt-1 ms-auto"
                            type="checkbox"
                            id="navbarFixed"
                            onclick="navbarFixed(this)" />
                    </div>
                </div>
                <hr class="horizontal dark my-3" />
                <div class="mt-2 d-flex">
                    <h6 class="mb-0">Light / Dark</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input
                            class="form-check-input mt-1 ms-auto"
                            type="checkbox"
                            id="dark-version"
                            onclick="darkMode(this)" />
                    </div>
                </div>
                <hr class="horizontal dark my-sm-4" />
                <a
                    class="btn bg-gradient-info w-100"
                    href="https://www.creative-tim.com/product/material-dashboard-pro">Free Download</a>
                <a
                    class="btn btn-outline-dark w-100"
                    href="https://www.creative-tim.com/learning-lab/bootstrap/overview/material-dashboard">View documentation</a>
                <div class="w-100 text-center">
                    <a
                        class="github-button"
                        href="https://github.com/creativetimofficial/material-dashboard"
                        data-icon="octicon-star"
                        data-size="large"
                        data-show-count="true"
                        aria-label="Star creativetimofficial/material-dashboard on GitHub">Star</a>
                    <h6 class="mt-3">Thank you for sharing!</h6>
                    <a
                        href="https://twitter.com/intent/tweet?text=Check%20Material%20UI%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fsoft-ui-dashboard"
                        class="btn btn-dark mb-0 me-2"
                        target="_blank">
                        <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
                    </a>
                    <a
                        href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/material-dashboard"
                        class="btn btn-dark mb-0 me-2"
                        target="_blank">
                        <i class="fab fa-facebook-square me-1" aria-hidden="true"></i>
                        Share
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="./assets/js/core/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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