<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Product Dashboard</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="./assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <?php
    // Start session if not started in config.php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

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

    if (isset($_POST['signout'])) {
        handleSignout();
    }

    // Fetch total product count (optional, but nice for a dashboard)
    $getAllProductsCount = "SELECT COUNT(*) as product_count FROM tbl_products";
    $result = $conn->query($getAllProductsCount);
    $totalProducts = $result->fetch_assoc()['product_count'] ?? 0;

    // Fetch categories for the modals
    $categories = [];
    $categoryQuery = "SELECT id, category_name FROM tbl_categories ORDER BY category_name";
    $categoryResult = mysqli_query($conn, $categoryQuery);
    if ($categoryResult) {
        while ($row = mysqli_fetch_assoc($categoryResult)) {
            $categories[] = $row;
        }
    }

    ?>
    <?php include 'layout/sideNav.php'; ?>
    <div class="main-content position-relative max-height-vh-100 h-100 ms-auto pe-5" style="width: 82%;">
        <div class="container-fluid px-2 px-md-4">
            <?php include 'layout/header.php'; ?>
            <div class="page-header min-height-300 border-radius-xl mt-4"
                style="
            background-image: url('https://images.unsplash.com/photo-1531512073830-ba890ca4eba2?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
          ">
                >
                <span class="mask bg-gradient-dark opacity-6"></span>
            </div>
            <div class="card card-body mx-2 mx-md-2 mt-n6">
                <?php

                // Pagination Setup
                $limit = 10; // number of rows per page
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $start = ($page - 1) * $limit;

                // Fetch paginated products (join with categories for display)
                $query = "SELECT p.*, c.category_name 
                          FROM tbl_products p
                          LEFT JOIN tbl_categories c ON p.category_id = c.id
                          ORDER BY p.id DESC 
                          LIMIT $start, $limit";
                $result = mysqli_query($conn, $query);

                // Get total records
                $totalResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM tbl_products");
                $totalRow = mysqli_fetch_assoc($totalResult);
                $totalRecords = $totalRow['total'];
                $totalPages = ceil($totalRecords / $limit);

                ?>

                <div class="card my-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Product List (Total: <?= $totalRecords ?>)</h6>
                        <button type="button" class="btn bg-gradient-primary btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="material-symbols-rounded text-sm me-1">add</i> Add New Product
                        </button>
                    </div>

                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-4">#</th>
                                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Product Name</th>
                                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Price</th>
                                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Category</th>
                                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Hot/Active</th>
                                        <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0) : ?>
                                        <?php while ($row = mysqli_fetch_assoc($result)) :
                                            $attachments = json_decode($row['attachments'], true);
                                            $attachment_count = is_array($attachments) ? count($attachments) : 0;
                                        ?>
                                            <tr class="text-center">
                                                <td class="ps-4">
                                                    <p class="text-sm font-weight-bold mb-0"><?= $row['id']; ?></p>
                                                </td>
                                                <td>
                                                    <p class="text-sm mb-0"><?= htmlspecialchars($row['product_name']); ?></p>
                                                </td>
                                                <td>
                                                    <p class="text-sm mb-0"><?= '$' . number_format($row['price'], 2); ?></p>
                                                </td>
                                                <td>
                                                    <p class="text-sm mb-0"><?= htmlspecialchars($row['category_name'] ?? 'N/A'); ?></p>
                                                </td>
                                                <td>
                                                    <span class="badge badge-sm <?= $row['isHot'] ? 'bg-gradient-danger' : 'bg-gradient-secondary' ?>">
                                                        <?= $row['isHot'] ? 'HOT' : 'Standard' ?>
                                                    </span>
                                                    <span class="badge badge-sm <?= $row['isActive'] ? 'bg-gradient-success' : 'bg-gradient-light text-dark' ?>">
                                                        <?= $row['isActive'] ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <button class="btn btn-link text-info me-3 edit-btn" data-id="<?= $row['id']; ?>" title="Edit">
                                                        <i class="material-symbols-rounded">edit</i>
                                                    </button>
                                                    <button class="btn btn-link text-danger delete-btn" data-id="<?= $row['id']; ?>" title="Delete">
                                                        <i class="material-symbols-rounded">delete</i>
                                                    </button>
                                                    <?php if ($attachment_count > 0) : ?>
                                                        <button class="btn btn-link text-success view-attachments-btn"
                                                            data-attachments='<?= htmlspecialchars(json_encode($attachments), ENT_QUOTES, 'UTF-8'); ?>'
                                                            data-product-name="<?= htmlspecialchars($row['product_name']); ?>"
                                                            title="View Attachments (<?= $attachment_count ?>)">
                                                            <i class="material-symbols-rounded">folder</i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-secondary py-4">No products found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                        </div>
                        <div class="d-flex justify-content-center my-4 p-3">
                            <nav aria-label="Product pagination">
                                <ul class="pagination pagination-sm shadow-sm rounded-3">
                                    <li class="p-2 rounded-3 <?= ($page <= 1) ? 'disabled' : '' ?>">
                                        <a class="page-link bg-gradient-primary text-white rounded-3" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                            <i class="material-symbols-rounded text-sm me-1">chevron_left</i> Previous
                                        </a>
                                    </li>

                                    <?php
                                    $visiblePages = 10;
                                    $startPage = max(1, $page - floor($visiblePages / 2));
                                    $endPage = min($totalPages, $page + floor($visiblePages / 2));

                                    if ($endPage - $startPage + 1 < $visiblePages) {
                                        $startPage = max(1, $endPage - $visiblePages + 1);
                                        $endPage = min($totalPages, $startPage + $visiblePages - 1);
                                    }

                                    if ($startPage > 1) : ?>
                                        <li class="p-2 rounded-3"><a class="page-link" href="?page=1">1</a></li>
                                        <?php if ($startPage > 2) : ?><li class="p-2 rounded-3 disabled"><span class="page-link">...</span></li><?php endif; ?>
                                    <?php endif; ?>


                                    <?php for ($i = $startPage; $i <= $endPage; $i++) : ?>
                                        <li class="p-2 <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link px-5
                                                <?= ($i == $page) ? 'bg-gradient-primary text-white border-primary' : 'text-primary' ?>" href="?page=<?= $i ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($endPage < $totalPages) : ?>
                                        <?php if ($endPage < $totalPages - 1) : ?><li class="p-2 rounded-3 disabled"><span class="page-link">...</span></li><?php endif; ?>
                                        <li class="p-2 rounded-3"><a class="page-link" href="?page=<?= $totalPages ?>"><?= $totalPages ?></a></li>
                                    <?php endif; ?>

                                    <li class="p-2 rounded-3 <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-3 bg-gradient-primary text-white" href="?page=<?= $page + 1 ?>" aria-label="Next">
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
    </div>

    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editProductForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editId">

                        <div class="mb-3">
                            <label for="editProductName" class="form-label">Product Name</label>
                            <input type="text" name="product_name" id="editProductName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editPrice" class="form-label">Price ($)</label>
                            <input type="number" step="0.01" name="price" id="editPrice" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCategoryId" class="form-label">Category</label>
                            <select name="category_id" id="editCategoryId" class="form-control p-2">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat) : ?>
                                    <option value="<?= $cat['id']; ?>"><?= htmlspecialchars($cat['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="editIsHot" class="form-label">Is Hot?</label>
                                <select name="isHot" id="editIsHot" class="form-control p-2">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="editIsActive" class="form-label">Is Active?</label>
                                <select name="isActive" id="editIsActive" class="form-control p-2">
                                    <option value="0">Inactive</option>
                                    <option value="1">Active</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3" id="currentFilesDisplay">
                        </div>
                        <div class="mb-3">
                            <label for="editFiles" class="form-label">Add/Replace Attachments (Max 5 files, PNG/JPG/PDF)</label>
                            <input type="file" name="fileToUpload[]" id="editFiles" class="form-control" multiple>
                            <small class="text-muted">Uploading new files will **replace** all existing attachments.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-gradient-info">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addProductForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addProductName" class="form-label">Product Name</label>
                            <input type="text" name="product_name" id="addProductName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="addDescription" class="form-label">Description</label>
                            <textarea name="description" id="addDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="addPrice" class="form-label">Price ($)</label>
                            <input type="number" step="0.01" name="price" id="addPrice" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="addCategoryId" class="form-label">Category</label>
                            <select name="category_id" id="addCategoryId" class="form-control p-2">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat) : ?>
                                    <option value="<?= $cat['id']; ?>"><?= htmlspecialchars($cat['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addFiles" class="form-label">Select Attachments (Max 5 files, PNG/JPG/PDF)</label>
                            <input type="file" name="fileToUpload[]" id="addFiles" class="form-control" multiple>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="addIsHot" class="form-label">Is Hot?</label>
                                <select name="isHot" id="addIsHot" class="form-control p-2">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="addIsActive" class="form-label">Is Active?</label>
                                <select name="isActive" id="addIsActive" class="form-control p-2">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-gradient-primary">Create Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="viewAttachmentsModal" tabindex="-1" aria-labelledby="viewAttachmentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title" id="viewAttachmentsModalLabel">Attachments for [Product Name]</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="attachmentsList">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <script src="./assets/js/core/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="./assets/js/material-dashboard.min.js?v=3.2.0"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // --- 1. EDIT PRODUCT LOGIC ---
            const editButtons = document.querySelectorAll(".edit-btn");
            const editModal = new bootstrap.Modal(document.getElementById("editProductModal"));
            const editProductForm = document.getElementById("editProductForm");

            editButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    const productId = this.getAttribute("data-id");
                    document.getElementById("editId").value = productId;

                    fetch(`functions/get-product.php?id=${productId}`)
                        .then(res => {
                            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                            return res.json();
                        })
                        .then(data => {
                            document.getElementById("editProductName").value = data.product_name;
                            document.getElementById("editDescription").value = data.description;
                            document.getElementById("editPrice").value = data.price;
                            document.getElementById("editCategoryId").value = data.category_id;
                            document.getElementById("editIsHot").value = data.isHot ? '1' : '0';
                            document.getElementById("editIsActive").value = data.isActive ? '1' : '0';

                            const currentFilesDisplay = document.getElementById("currentFilesDisplay");
                            currentFilesDisplay.innerHTML = 'Current Attachments: ';

                            if (data.attachments && data.attachments.length > 0) {
                                let fileList = '';
                                data.attachments.forEach(file => {
                                    fileList += `<span class="badge bg-secondary me-1"><i class="material-symbols-rounded text-sm me-1">attach_file</i> ${file.split('/').pop()}</span>`;
                                });
                                currentFilesDisplay.innerHTML += fileList;
                            } else {
                                currentFilesDisplay.innerHTML += '<span class="text-muted">None uploaded</span>';
                            }

                            // Clear the file input for a new upload
                            document.getElementById("editFiles").value = "";
                            editModal.show();
                        })
                        .catch(err => {
                            console.error("Fetch error:", err);
                            alert("Failed to fetch product data. Check console for details.");
                        });
                });
            });

            // Logic for submitting the edit form
            editProductForm.addEventListener("submit", function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch("functions/update-product.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.text())
                    .then(response => {
                        alert(response);
                        editModal.hide();
                        location.reload();
                    })
                    .catch(err => {
                        console.error("Update error:", err);
                        alert("Failed to update product data.");
                    });
            });

            // --- 2. ADD PRODUCT LOGIC ---
            const addProductForm = document.getElementById("addProductForm");
            const addModal = new bootstrap.Modal(document.getElementById("addProductModal"));

            addProductForm.addEventListener("submit", function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch("functions/add-product-handler.php", {
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
                        alert("Failed to add product. Check console for details.");
                    });
            });

            // --- 3. DELETE PRODUCT LOGIC ---
            const deleteButtons = document.querySelectorAll(".delete-btn");

            deleteButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    const productId = this.getAttribute("data-id");

                    if (confirm("Are you sure you want to delete product ID: " + productId + "? This action cannot be undone and will also remove related files.")) {
                        fetch(`functions/delete-product.php?id=${productId}`, {
                                method: "GET",
                            })
                            .then(res => res.text())
                            .then(response => {
                                alert(response);
                                location.reload();
                            })
                            .catch(err => {
                                console.error("Delete error:", err);
                                alert("Failed to delete product. Check console for details.");
                            });
                    }
                });
            });

            // --- 4. VIEW ATTACHMENTS LOGIC ---
            const viewAttachmentsButtons = document.querySelectorAll(".view-attachments-btn");
            const viewAttachmentsModal = new bootstrap.Modal(document.getElementById("viewAttachmentsModal"));

            viewAttachmentsButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    const attachmentsJson = this.getAttribute("data-attachments");
                    const productName = this.getAttribute("data-product-name");
                    const attachments = JSON.parse(attachmentsJson);
                    const attachmentsList = document.getElementById('attachmentsList');

                    document.getElementById('viewAttachmentsModalLabel').textContent = `Attachments for ${productName}`;
                    attachmentsList.innerHTML = ''; // Clear previous links

                    if (attachments && attachments.length > 0) {
                        attachments.forEach((filePath, index) => {
                            const fileName = filePath.split('/').pop();
                            const listItem = document.createElement('div');
                            listItem.className = 'd-flex justify-content-between align-items-center p-2 border-bottom';
                            listItem.innerHTML = `
                                <span>${fileName}</span>
                                <a href="${filePath}" target="_blank" class="btn btn-sm btn-link text-success p-0">
                                    <i class="material-symbols-rounded text-lg">download</i>
                                </a>
                            `;
                            attachmentsList.appendChild(listItem);
                        });
                    } else {
                        attachmentsList.innerHTML = '<p class="text-center text-muted">No attachments found.</p>';
                    }

                    viewAttachmentsModal.show();
                });
            });
        });
    </script>
</body>

</html>