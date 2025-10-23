<?php
include 'config.php';

// --- 1. DETERMINE REPORT TYPE AND FILTERS ---
$report_type = isset($_GET['type']) ? $_GET['type'] : 'month'; // Default to Revenue By Month
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$selected_category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$report_title = "Revenue Report";
$report_data = [];
$categories = [];
$available_years = []; // New variable for dynamic years

// Helper function to fetch available years
function fetch_available_years($conn)
{
    $years = [];
    if (isset($conn)) {
        // Query to select distinct years from the orders table
        $query = "SELECT DISTINCT YEAR(created_at) AS order_year FROM tbl_orders ORDER BY order_year DESC";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $years[] = $row['order_year'];
            }
        }
    }
    // If no years found, default to current year to prevent empty list
    if (empty($years)) {
        $years[] = date('Y');
    }
    return $years;
}

// Helper function to fetch categories for dropdowns
function fetch_categories($conn)
{
    $categories = [];
    if (isset($conn)) {
        $query = "SELECT id, category_name FROM tbl_categories ORDER BY category_name ASC";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
    }
    return $categories;
}

// Fetch dynamic years for all relevant reports
if ($report_type === 'month' || $report_type === 'year' || $report_type === 'category_year') {
    $available_years = fetch_available_years($conn);
    // Ensure selected_year is set to a valid year if the default was not in DB
    if ($selected_year == date('Y') && !in_array(date('Y'), $available_years)) {
        $selected_year = $available_years[0];
    } elseif (!in_array($selected_year, $available_years)) {
        // If the current selected year is not found, default to the latest year
        $selected_year = $available_years[0];
    }
}

// Fetch categories if needed
if ($report_type === 'category' || $report_type === 'category_year') {
    $categories = fetch_categories($conn);
}


// --- 2. EXECUTE REPORT LOGIC BASED ON TYPE ---

switch ($report_type) {
    case 'month':
        $report_title = "Revenue By Month ({$selected_year})";
        // SQL: Calculate total revenue for each month of the selected year.
        $sql = "
            SELECT 
                SUM(CASE WHEN MONTH(O.created_at) = 1 THEN OI.subtotal ELSE 0 END) AS Jan,
                SUM(CASE WHEN MONTH(O.created_at) = 2 THEN OI.subtotal ELSE 0 END) AS Feb,
                SUM(CASE WHEN MONTH(O.created_at) = 3 THEN OI.subtotal ELSE 0 END) AS Mar,
                SUM(CASE WHEN MONTH(O.created_at) = 4 THEN OI.subtotal ELSE 0 END) AS Apr,
                SUM(CASE WHEN MONTH(O.created_at) = 5 THEN OI.subtotal ELSE 0 END) AS May,
                SUM(CASE WHEN MONTH(O.created_at) = 6 THEN OI.subtotal ELSE 0 END) AS Jun,
                SUM(CASE WHEN MONTH(O.created_at) = 7 THEN OI.subtotal ELSE 0 END) AS Jul,
                SUM(CASE WHEN MONTH(O.created_at) = 8 THEN OI.subtotal ELSE 0 END) AS Aug,
                SUM(CASE WHEN MONTH(O.created_at) = 9 THEN OI.subtotal ELSE 0 END) AS Sep,
                SUM(CASE WHEN MONTH(O.created_at) = 10 THEN OI.subtotal ELSE 0 END) AS Oct,
                SUM(CASE WHEN MONTH(O.created_at) = 11 THEN OI.subtotal ELSE 0 END) AS Nov,
                SUM(CASE WHEN MONTH(O.created_at) = 12 THEN OI.subtotal ELSE 0 END) AS `Dec`
            FROM tbl_orders O
            JOIN tbl_order_items OI ON O.id = OI.order_id
            WHERE O.payment_status = 'paid' AND YEAR(O.created_at) = $selected_year
        ";
        break;

    case 'year':
        $report_title = "Revenue By Year (Monthly Breakdown)";
        // SQL: Calculate total revenue for each month, grouped by year.
        $sql = "
            SELECT 
                YEAR(O.created_at) AS ReportYear,
                SUM(CASE WHEN MONTH(O.created_at) = 1 THEN OI.subtotal ELSE 0 END) AS Jan,
                SUM(CASE WHEN MONTH(O.created_at) = 2 THEN OI.subtotal ELSE 0 END) AS Feb,
                SUM(CASE WHEN MONTH(O.created_at) = 3 THEN OI.subtotal ELSE 0 END) AS Mar,
                SUM(CASE WHEN MONTH(O.created_at) = 4 THEN OI.subtotal ELSE 0 END) AS Apr,
                SUM(CASE WHEN MONTH(O.created_at) = 5 THEN OI.subtotal ELSE 0 END) AS May,
                SUM(CASE WHEN MONTH(O.created_at) = 6 THEN OI.subtotal ELSE 0 END) AS Jun,
                SUM(CASE WHEN MONTH(O.created_at) = 7 THEN OI.subtotal ELSE 0 END) AS Jul,
                SUM(CASE WHEN MONTH(O.created_at) = 8 THEN OI.subtotal ELSE 0 END) AS Aug,
                SUM(CASE WHEN MONTH(O.created_at) = 9 THEN OI.subtotal ELSE 0 END) AS Sep,
                SUM(CASE WHEN MONTH(O.created_at) = 10 THEN OI.subtotal ELSE 0 END) AS Oct,
                SUM(CASE WHEN MONTH(O.created_at) = 11 THEN OI.subtotal ELSE 0 END) AS Nov,
                SUM(CASE WHEN MONTH(O.created_at) = 12 THEN OI.subtotal ELSE 0 END) AS `Dec`
            FROM tbl_orders O
            JOIN tbl_order_items OI ON O.id = OI.order_id
            WHERE O.payment_status = 'paid'
            GROUP BY ReportYear
            ORDER BY ReportYear DESC
        ";
        break;

    case 'category':
        $report_title = "Revenue By Category";
        // SQL: Calculate total revenue and product count for each category.
        $sql = "
            SELECT 
                C.category_name,
                SUM(OI.subtotal) AS TotalRevenue,
                COUNT(OI.product_id) AS TotalProductsSold
            FROM tbl_order_items OI
            JOIN tbl_products P ON OI.product_id = P.id
            JOIN tbl_categories C ON P.category_id = C.id
            JOIN tbl_orders O ON OI.order_id = O.id
            WHERE O.payment_status = 'paid'
            GROUP BY C.category_name
            ORDER BY TotalRevenue DESC
        ";
        break;

    case 'category_year':
        // FIX: Filtering the query by the $selected_year is critical here.
        $report_title = "Revenue By Category and Year ({$selected_year})";

        $sql = "
            SELECT 
                YEAR(O.created_at) AS ReportYear,
                C.category_name,
                SUM(CASE WHEN MONTH(O.created_at) = 1 THEN OI.subtotal ELSE 0 END) AS Jan,
                SUM(CASE WHEN MONTH(O.created_at) = 2 THEN OI.subtotal ELSE 0 END) AS Feb,
                SUM(CASE WHEN MONTH(O.created_at) = 3 THEN OI.subtotal ELSE 0 END) AS Mar,
                SUM(CASE WHEN MONTH(O.created_at) = 4 THEN OI.subtotal ELSE 0 END) AS Apr,
                SUM(CASE WHEN MONTH(O.created_at) = 5 THEN OI.subtotal ELSE 0 END) AS May,
                SUM(CASE WHEN MONTH(O.created_at) = 6 THEN OI.subtotal ELSE 0 END) AS Jun,
                SUM(CASE WHEN MONTH(O.created_at) = 7 THEN OI.subtotal ELSE 0 END) AS Jul,
                SUM(CASE WHEN MONTH(O.created_at) = 8 THEN OI.subtotal ELSE 0 END) AS Aug,
                SUM(CASE WHEN MONTH(O.created_at) = 9 THEN OI.subtotal ELSE 0 END) AS Sep,
                SUM(CASE WHEN MONTH(O.created_at) = 10 THEN OI.subtotal ELSE 0 END) AS Oct,
                SUM(CASE WHEN MONTH(O.created_at) = 11 THEN OI.subtotal ELSE 0 END) AS Nov,
                SUM(CASE WHEN MONTH(O.created_at) = 12 THEN OI.subtotal ELSE 0 END) AS `Dec`
            FROM tbl_orders O
            JOIN tbl_order_items OI ON O.id = OI.order_id
            JOIN tbl_products P ON OI.product_id = P.id
            JOIN tbl_categories C ON P.category_id = C.id
            WHERE O.payment_status = 'paid' 
            AND C.id = $selected_category_id 
            AND YEAR(O.created_at) = $selected_year -- *** FIX APPLIED HERE ***
            GROUP BY ReportYear, C.category_name
            ORDER BY ReportYear DESC
        ";

        if (!$selected_category_id) {
            $report_title = "Revenue By Category and Year (Select Category)";
            $sql = "SELECT NULL LIMIT 0";
        }
        break;

    default:
        $sql = "SELECT NULL LIMIT 0";
}


if (isset($conn) && $sql !== "SELECT NULL LIMIT 0") {
    $result = $conn->query($sql);
    if ($result === FALSE) {
        die("SQL Error: " . $conn->error . "<br>Query: " . $sql);
    }

    if ($result) {
        if ($report_type === 'month') {
            $report_data = $result->fetch_assoc();
        } else {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
    }
}
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
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
                <p class="text-sm">Dynamic report view: **<?= $report_title ?>**</p>

                <form method="GET" action="reports.php" class="row mb-4 p-3 bg-light border-radius-lg shadow-sm">
                    <input type="hidden" name="type" value="<?= htmlspecialchars($report_type) ?>">

                    <?php if ($report_type === 'month' || $report_type === 'category_year'): ?>
                        <div class="col-md-4 mb-3">
                            <label for="reportYear" class="form-label">Select Year</label>
                            <select class="form-select form-select-lg p-2" name="year" id="reportYear" required>
                                <?php foreach ($available_years as $y): ?>
                                    <option value="<?= $y ?>" <?= $selected_year == $y ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <?php if ($report_type === 'category_year'): ?>
                        <div class="col-md-5 mb-3">
                            <label for="reportCategory" class="form-label">Select Category</label>
                            <select class="form-select form-select-lg p-2" name="category_id" id="reportCategory" required>
                                <option value="" selected>Select a Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $selected_category_id == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <?php if ($report_type !== 'year' && $report_type !== 'category'): ?>
                        <div class="col-md-3 d-flex align-items-end mb-3">
                            <button type="submit" class="btn bg-gradient-primary w-100 mb-0" id="generateReportButton">
                                <i class="material-symbols-rounded me-2">analytics</i>
                                Generate Report
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
                <div class="row">
                    <div class="col-12">
                        <div class="card my-4">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                    <h6 class="text-white text-capitalize ps-3"><?= $report_title ?></h6>
                                </div>
                            </div>
                            <div class="card-body px-0 pb-2">
                                <div class="table-responsive p-0">
                                    <?php if (empty($report_data) && ($report_type === 'month' || $report_type === 'year' || ($report_type === 'category_year' && $selected_category_id))): ?>
                                        <p class="text-secondary text-sm p-3">No *paid* order data available for the selected criteria.</p>
                                    <?php elseif ($report_type === 'category_year' && !$selected_category_id): ?>
                                        <p class="text-secondary text-sm p-3">Please select a Category and Year to generate the report.</p>
                                    <?php elseif ($report_type === 'month' && is_array($report_data)): ?>
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <?php foreach ($months as $month): ?>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7"><?= $month ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <?php foreach ($months as $month): ?>
                                                        <td class="align-middle text-sm font-weight-bold">
                                                            <?php
                                                            // FIX: The alias is 'Dec' in the SQL query, not '`Dec`' when fetching from PHP array
                                                            $alias = ($month === 'Dec') ? 'Dec' : $month;
                                                            $value = isset($report_data[$alias]) ? $report_data[$alias] : 0;
                                                            echo '$' . number_format($value, 2);
                                                            ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php elseif ($report_type === 'year' || $report_type === 'category_year'): ?>
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                        Year
                                                    </th>
                                                    <?php foreach ($months as $month): ?>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7"><?= $month ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($report_data as $row): ?>
                                                    <tr>
                                                        <td class="align-middle text-sm font-weight-bold">
                                                            <?= $row['ReportYear'] ?>
                                                        </td>
                                                        <?php foreach ($months as $month): ?>
                                                            <td class="align-middle text-sm">
                                                                <?php
                                                                // FIX: The alias is 'Dec' in the SQL query, not '`Dec`' when fetching from PHP array
                                                                $alias = ($month === 'Dec') ? 'Dec' : $month;
                                                                $value = isset($row[$alias]) ? $row[$alias] : 0;
                                                                echo '$' . number_format($value, 2);
                                                                ?>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php elseif ($report_type === 'category'): ?>
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Category Name</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Revenue (Paid)</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Products Sold</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($report_data as $row): ?>
                                                    <tr>
                                                        <td class="align-middle text-sm font-weight-bold">
                                                            <?= htmlspecialchars($row['category_name']) ?>
                                                        </td>
                                                        <td class="align-middle text-sm">
                                                            $<?= number_format($row['TotalRevenue'], 2) ?>
                                                        </td>
                                                        <td class="align-middle text-sm">
                                                            <?= $row['TotalProductsSold'] ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <p class="text-secondary text-sm p-3">Select a report type from the sidebar to begin.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed-plugin" hidden>
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