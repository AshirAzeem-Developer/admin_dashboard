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
  <title>Material Dashboard 3 by Creative Tim</title>
  <!--     Fonts and icons     -->
  <link
    rel="stylesheet"
    type="text/css"
    href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <!-- Nucleo Icons -->
  <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script
    src="https://kit.fontawesome.com/42d5adcbca.js"
    crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link
    rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <!-- CSS Files -->
  <link
    id="pagestyle"
    href="./assets/css/material-dashboard.css?v=3.2.0"
    rel="stylesheet" />

</head>

<body class="g-sidenav-show bg-gray-100">
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
  <?php include 'layout/sideNav.php'; ?>
  <div class="main-content position-relative max-height-vh-100 h-100 ms-auto pe-5" style="width: 82%;">
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
        <?php

        // Pagination Setup
        $limit = 10; // number of rows per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $start = ($page - 1) * $limit;

        // Fetch paginated users
        $query = "SELECT * FROM tbl_users ORDER BY id DESC LIMIT $start, $limit";
        $result = mysqli_query($conn, $query);

        // Get total records
        $totalResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_users");
        $totalRow = mysqli_fetch_assoc($totalResult);
        $totalRecords = $totalRow['total'];
        $totalPages = ceil($totalRecords / $limit);

        ?>

        <div class="card my-4">
          <div class="card-header pb-0 d-flex justify-content-between align-items-center">
            <h6>Registered Users</h6>
            <button type="button" class="btn bg-gradient-primary btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#addUserModal">
              <i class="material-symbols-rounded text-sm me-1">add</i> Add New
            </button>
          </div>

          <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive p-0">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr class="text-center">
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-4">#</th>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">First Name</th>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Last Name</th>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Email</th>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Designation</th>
                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Phone</th>

                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $i = 1;
                    while ($row = mysqli_fetch_assoc($result)): ?>
                      <tr class="text-center">
                        <td class="ps-4">
                          <p class="text-sm font-weight-bold mb-0"><?= $row['id']; ?></p>
                        </td>
                        <td>
                          <p class="text-sm mb-0"><?= htmlspecialchars($row['firstname']); ?></p>
                        </td>
                        <td>
                          <p class="text-sm mb-0"><?= htmlspecialchars($row['lastname']); ?></p>
                        </td>
                        <td>
                          <p class="text-sm mb-0"><?= htmlspecialchars($row['email']); ?></p>
                        </td>
                        <td>
                          <p class="text-sm mb-0"><?= htmlspecialchars($row['designation']); ?></p>
                        </td>
                        <td>
                          <p class="text-sm mb-0"><?= htmlspecialchars($row['phone_number']); ?></p>
                        </td>

                        <td class="text-center">
                          <button
                            class="btn btn-link text-info me-3 edit-btn"
                            data-id="<?= $row['id']; ?>"
                            title="Edit">
                            <i class="material-symbols-rounded">edit</i>
                          </button>
                          <button
                            class="btn btn-link text-danger delete-btn"
                            data-id="<?= $row['id']; ?>"
                            title="Delete">
                            <i class="material-symbols-rounded">delete</i>
                          </button>
                          <?php if (!empty($row['attachment'])): ?>
                            <a
                              href="functions/download.php?id=<?= $row['attachment']; ?>"
                              class="btn btn-link text-success download-btn">
                              <i class="material-symbols-rounded">download</i>
                            </a>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="8" class="text-center text-secondary py-4">No users found.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>

            </div>
            <!-- Pagination Controls -->
            <div class="d-flex justify-content-center my-4 p-3">
              <nav aria-label="User pagination">
                <ul class="pagination pagination-sm shadow-sm rounded-3">

                  <li class="p-2 rounded-3 <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link bg-gradient-primary text-white rounded-3"
                      href="?page=<?= $page - 1 ?>"
                      aria-label="Previous">
                      <i class="material-symbols-rounded text-sm me-1">chevron_left</i> Previous
                    </a>
                  </li>

                  <?php
                  // Logic to show a limited number of pages (e.g., current, and 2 pages before/after)
                  $visiblePages = 10;
                  $startPage = max(1, $page - floor($visiblePages / 2));
                  $endPage = min($totalPages, $page + floor($visiblePages / 2));

                  // Adjust range if it hits the start/end limits
                  if ($endPage - $startPage + 1 < $visiblePages) {
                    $startPage = max(1, $endPage - $visiblePages + 1);
                    $endPage = min($totalPages, $startPage + $visiblePages - 1);
                  }

                  if ($startPage > 1): ?>
                    <li class="p-2 rounded-3"><a class="page-link" href="?page=1">1</a></li>
                    <?php if ($startPage > 2): ?><li class="p-2 rounded-3 disabled"><span class="page-link">...</span></li><?php endif; ?>
                  <?php endif; ?>


                  <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="p-2 <?= ($i == $page) ? 'active' : '' ?>">
                      <a class="page-link px-5
                                    <?= ($i == $page) ? 'bg-gradient-primary text-white border-primary' : 'text-primary' ?>"
                        href="?page=<?= $i ?>">
                        <?= $i ?>
                      </a>
                    </li>
                  <?php endfor; ?>

                  <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?><li class="p-2 rounded-3 disabled"><span class="page-link">...</span></li><?php endif; ?>
                    <li class="p-2 rounded-3"><a class="page-link" href="?page=<?= $totalPages ?>"><?= $totalPages ?></a></li>
                  <?php endif; ?>

                  <li class="p-2 rounded-3 <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-3 bg-gradient-primary text-white"
                      href="?page=<?= $page + 1 ?>"
                      aria-label="Next">
                      Next <i class="material-symbols-rounded text-sm ms-1">chevron_right</i>
                    </a>
                  </li>
                </ul>
              </nav>
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
        <!-- End Toggle Button -->
      </div>
      <hr class="horizontal dark my-1" />
      <div class="card-body pt-sm-3 pt-0">
        <!-- Sidebar Backgrounds -->
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
        <!-- Sidenav Type -->
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
        <!-- Navbar Fixed -->
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

  <!-- Edit User Modal -->
  <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-gradient-primary text-white">
          <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editUserForm" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" name="id" id="editId">
            <div class="mb-3">
              <label for="editFirstName" class="form-label">First Name</label>
              <input type="text" name="firstname" id="editFirstName" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="editLastName" class="form-label">Last Name</label>
              <input type="text" name="lastname" id="editLastName" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="editEmail" class="form-label">Email</label>
              <input type="email" name="email" id="editEmail" class="form-control" required>
            </div>


            <div class="mb-3">
              <label for="editDesignation" class="form-label">Designation</label>
              <select name="designation" id="editDesignation" class="form-control p-2" required>
                <option value="">Select Designation (Optional)</option>
                <option value="Administrator">Administrator</option>
                <option value="Manager">Manager</option>
                <option value="Employee">Employee</option>
                <option value="Intern">Intern</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="editPhone" class="form-label">Phone</label>
              <input type="text" name="phone_number" id="editPhone" class="form-control">
            </div>
            <div class="mb-3" id="currentFileDisplay">
            </div>
            <div class="mb-3">
              <label for="fileToUpload" class="form-label">Change a PDF file (Max 1MB)</label>
              <input type="file" name="fileToUpload" id="editFile" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn bg-gradient-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Edit User Modal Closed -->

  <!-- Add User Modal Start -->
  <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-gradient-primary text-white">
          <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addUserForm" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="mb-3">
              <label for="addFirstName" class="form-label">First Name</label>
              <input type="text" name="firstname" id="addFirstName" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="addLastName" class="form-label">Last Name</label>
              <input type="text" name="lastname" id="addLastName" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="addEmail" class="form-label">Email</label>
              <input type="email" name="email" id="addEmail" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="addPassword" class="form-label">Password</label>
              <input type="password" name="password" id="addPassword" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="addDesignation" class="form-label">Designation</label>
              <select name="designation" id="addDesignation" class="form-control p-2" required>
                <option value="">Select Designation</option>

                <option value="Administrator">Administrator</option>
                <option value="Manager">Manager</option>
                <option value="Employee">Employee</option>
                <option value="Intern">Intern</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="addPhone" class="form-label">Phone</label>
              <input type="text" name="phone_number" id="addPhone" class="form-control">
            </div>
            <div class="mb-3">
              <label for="fileToUpload" class="form-label">Select a PDF file (Max 1MB)</label>
              <input type="file" name="fileToUpload" id="fileToUpload" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn bg-gradient-primary">Create User</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Add User Modal End -->

  <!--   Core JS Files   -->
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
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="./assets/js/material-dashboard.min.js?v=3.2.0"></script>
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
  <script>
    document.addEventListener("DOMContentLoaded", function() {

      //  1. EDIT USER LOGIC ---
      const editButtons = document.querySelectorAll(".edit-btn");
      const modal = new bootstrap.Modal(document.getElementById("editUserModal"));
      const editUserForm = document.getElementById("editUserForm");

      // Logic for fetching data and showing modal
      editButtons.forEach(btn => {
        btn.addEventListener("click", function() {
          const userId = this.getAttribute("data-id");
          document.getElementById("editId").value = userId;

          fetch(`functions/get-user.php?id=${userId}`)
            .then(res => {
              if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
              }
              return res.json();
            })
            .then(data => {
              document.getElementById("editFirstName").value = data.firstname;
              document.getElementById("editLastName").value = data.lastname;
              document.getElementById("editEmail").value = data.email;
              document.getElementById("editDesignation").value = data.designation;
              document.getElementById("editPhone").value = data.phone_number;

              const currentFileDisplay = document.getElementById("currentFileDisplay");
              if (data.attachment) {
                currentFileDisplay.innerHTML = `Current File: <strong>${data.attachment}</strong>`;
              } else {
                currentFileDisplay.innerHTML = 'Current File: <span class="text-muted">None uploaded</span>';
              }
              document.getElementById("editFile").value = "";
              modal.show();
            })
            .catch(err => {
              console.error("Fetch error:", err);
              alert("Failed to fetch user data. Check console for details.");
            });
        });
      });

      // Logic for submitting the edit form
      editUserForm.addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch("functions/update-user.php", {
            method: "POST",
            body: formData
          })
          .then(res => res.text())
          .then(response => {
            alert(response);
            modal.hide();
            location.reload();
          })
          .catch(err => {
            console.error("Update error:", err);
            alert("Failed to update user data.");
          });
      });

      // --- 2. ADD USER LOGIC ---
      const addUserForm = document.getElementById("addUserForm");
      const addModal = new bootstrap.Modal(document.getElementById("addUserModal"));

      addUserForm.addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch("functions/add-user-handler.php", {
            method: "POST",
            body: formData
          })
          .then(res => res.text())
          .then(response => {
            alert(response);
            addModal.hide();
            location.reload();
          })
          .catch(err => {
            console.error("Add error:", err);
            alert("Failed to add user. Check console for details.");
          });
      });

      // --- 3. DELETE USER LOGIC (FIXED POSITION) ---
      const deleteButtons = document.querySelectorAll(".delete-btn");

      deleteButtons.forEach(btn => {
        btn.addEventListener("click", function() {
          const userId = this.getAttribute("data-id");

          if (confirm("Are you sure you want to delete user ID: " + userId + "? This action cannot be undone.")) {
            // Send AJAX request to delete the user
            fetch(`functions/delete-user.php?id=${userId}`, {
                method: "GET",
              })
              .then(res => res.text())
              .then(response => {
                alert(response);
                location.reload();
              })
              .catch(err => {
                console.error("Delete error:", err);
                alert("Failed to delete user. Check console for details.");
              });
          }
        });
      });
    });
  </script>

</body>

</html>