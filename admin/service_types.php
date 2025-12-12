<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
// Database connection
include('includes/connection.php');

// Get user details
$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $admin_id = $user['admin_id'];
    $name = $user['admin_name'];       // adjust field names as per your DB
    $email = $user['admin_email'];     // adjust accordingly
    $role = $user['role'];     // adjust accordingly
} else {
    // Invalid user_id or deleted user
    session_destroy();
    header("Location: index.php");
    exit;
}

$stmt->close();
$conn->close();
?>


<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($name); ?> - Service Types</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="../assets/css/style.css">

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    </head>
    <body>
        
        <section class="dashboard">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-2 sidebar">
                        <div class="logo text-center">
                            <img src="../assets/images/logo_white.png" alt="">
                        </div>
                        <div class="menu_list">
                            <a href="add_work_order.php"><p class="menu"><span><i class="fa-solid fa-clipboard"></i>  Add Work Orders </span></p></a>
                            <a href="all_work_orders.php"><p class="menu"><span><i class="fa-solid fa-clipboard"></i>  All Work Orders </span></p></a>
                            <a href="wo_status.php"><p class="menu"><span><i class="fa-solid fa-circle-user"></i> Work Order Status</span></p></a>
                            <a href="service_types.php"><p class="active-menu"><span><i class="fa-solid fa-gears"></i> Service Types</span></p></a>
                            <a href="sites.php"><p class="menu"><span><i class="fa-regular fa-building"></i> Sites</span></p></a>
                            <a href="logout.php"><p class="menu"><span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span></p></a>
                        </div>

                        <div class="bottom-section">
                            <div class="user-info text-center">
                                <img src="../assets/images/user.png" alt="User" />
                                <p class="name"><?php echo htmlspecialchars($name); ?></p>
                                <p class="role"><?php echo htmlspecialchars($role); ?></p>
                            </div>
                            
                        </div>
                    </div>
                    <div class="col-sm-10 main-screen">
                        <div class="row">
                            <div class="col-sm-9">
                                <h3 class="page-title">Welcome</h3>
                                <p id="clock"></p>
                            </div>
                            <div class="col-sm-3">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <img src="../assets/images/user.png" width="60px" alt="">
                                    </div>
                                    <div class="col-sm-9 userinfor">
                                        <p class="user-title"><?php echo htmlspecialchars($name); ?></p>
                                        <p class="user-role"><?php echo htmlspecialchars($role); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card shadow outer-card">
                                    <div class="card-body">
                                        <h2 class="title greentitle text-center mb-3">Service Types</h2>
                                        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addServiceTypeModal">
                                            Add New Service Type
                                        </button>
                                        <button class="btn btn-primary mb-3" id="bulkActivate">Activate Selected</button>
                                        <button class="btn btn-warning mb-3" id="bulkDeactivate">Deactivate Selected</button>
                                        <table id="servicesTable" class="table table-striped table-sm" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Service Id (#)</th>
                                                    <th>Service Name</th>
                                                    <th>Service Short Name</th>
                                                    <th>Service Type</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            // Connect to the database
                                            include('includes/connection.php');

                                            // Fetch employee names
                                            $sql = "SELECT * FROM service_type_tbl"; // latest first
                                            $result = $conn->query($sql);
                                            ?>
                                            <tbody>
                                                <?php if ($result->num_rows > 0): ?>
                                                    <?php while ($row = $result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><input type="checkbox" class="rowCheck form-check-input" value="<?= $row['service_id']; ?>"></td>
                                                            <td><?php echo htmlspecialchars($row['service_id']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['service_short_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['service_status']); ?></td>
                                                            <td>
                                                                <?php if ($row['service_status'] === "Active"): ?>
                                                                    <button class="btn btn-danger btn-sm toggleStatus" data-id="<?php echo $row['service_id']; ?>" data-status="Inactive">
                                                                        Make Inactive
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button class="btn btn-success btn-sm toggleStatus" data-id="<?php echo $row['service_id']; ?>" data-status="Active">
                                                                        Make Active
                                                                    </button>
                                                                <?php endif; ?>

                                                                <button class="btn btn-warning btn-sm editService"
                                                                    data-id="<?= $row['service_id']; ?>"
                                                                    data-name="<?= htmlspecialchars($row['service_name']); ?>"
                                                                    data-short="<?= htmlspecialchars($row['service_short_name']); ?>"
                                                                    data-type="<?= htmlspecialchars($row['service_type']); ?>">
                                                                    <i class="fa-solid fa-pencil"></i>
                                                                </button>

                                                                <button class="btn btn-danger btn-sm deleteService" data-id="<?= $row['service_id']; ?>"><i class="fa-solid fa-trash-can"></i></button>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th></th>
                                                    <th>Service Id (#)</th>
                                                    <th>Service Name</th>
                                                    <th>Service Short Name</th>
                                                    <th>Service Type</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </section>


        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="statusToast" class="toast align-items-center text-bg-success border-0" role="alert">
                <div class="d-flex">
                <div class="toast-body" id="toastMsg">Status updated successfully!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
        
        <!-- Add Service Type -->
        <div class="modal fade" id="addServiceTypeModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add New Service Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="addServiceTypeForm">
                    
                    <div class="mb-3">
                        <label class="form-label">Service Name</label>
                        <input type="text" class="form-control" name="service_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Short Name</label>
                        <input type="text" class="form-control" name="service_short_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service Type</label>
                        <select name="service_type" id="service_type" class="form-select" required>
                            <option value="">Select Service Type</option>
                            <option value="Normal">Normal</option>
                            <option value="Recurring">Recurring</option>
                        </select>
                    </div>

                    </form>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" id="saveServiceType">Save</button>
                </div>

                </div>
            </div>
        </div>

        <!-- Edit Service Type -->
        <div class="modal fade" id="editServiceModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Service Type</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="editServiceForm">

                    <input type="hidden" name="edit_service_id" id="edit_service_id">

                    <div class="mb-3">
                        <label>Service Name</label>
                        <input class="form-control" id="edit_service_name" name="service_name" required>
                    </div>

                    <div class="mb-3">
                        <label>Short Name</label>
                        <input class="form-control" id="edit_service_short" name="service_short_name" required>
                    </div>

                    <div class="mb-3">
                        <label>Service Type</label>
                        <select name="service_type" id="edit_service_type" class="form-select" required>
                            <option value="">Select Service Type</option>
                            <option value="Normal">Normal</option>
                            <option value="Recurring">Recurring</option>
                        </select>
                    </div>

                    </form>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" id="updateServiceBtn">Update</button>
                </div>

                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>   
        <!-- DataTables JS -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function updateClock() {
                const now = new Date();

                // Format date
                const day = String(now.getDate()).padStart(2, '0');
                const month = now.toLocaleString('default', { month: 'short' });
                const year = now.getFullYear();

                // Format time
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');

                // Final string (e.g., 18 Sep 2025 | 14:35:08)
                const dateTimeString = `${day} ${month} ${year} | ${hours}:${minutes}:${seconds}`;

                document.getElementById('clock').textContent = dateTimeString;
            }

            // Update every second
            setInterval(updateClock, 1000);

            // Initial call
            updateClock();

        </script>
        <!-- DataTables Initialization -->
        <script>
            new DataTable('#servicesTable', {
                responsive: true,
                scrollX: true,
                pageLength: 7,
                lengthMenu: [7, 10, 25, 50, 100],
            });
        </script>

        <script>
            $(document).on("click", ".toggleStatus", function () {
                let button = $(this); // clicked button
                let id = button.data("id");
                let newStatus = button.data("status");

                Swal.fire({
                    title: "Are you sure?",
                    text: `Do you want to change the status to ${newStatus}?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, change it!",
                    cancelButtonText: "Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {

                        $.ajax({
                            url: "toggle_status.php",
                            type: "POST",
                            data: { service_id: id, service_status: newStatus },

                            success: function(response) {
                                if (response === "success") {

                                    // Update status cell text
                                    let row = button.closest("tr");
                                    row.find("td:nth-child(5)").text(newStatus);

                                    // Update toggle button dynamically
                                    if (newStatus === "Active") {
                                        button
                                            .removeClass("btn-success")
                                            .addClass("btn-danger")
                                            .text("Make Inactive")
                                            .data("status", "Inactive");
                                    } else {
                                        button
                                            .removeClass("btn-danger")
                                            .addClass("btn-success")
                                            .text("Make Active")
                                            .data("status", "Active");
                                    }

                                    Swal.fire("Updated!", "Status changed successfully.", "success");
                                } else {
                                    Swal.fire("Error", "Unable to update status.", "error");
                                }
                            }
                        });

                    }
                });
            });


            // Reset modal form when it closes
            $("#addServiceTypeModal").on("hidden.bs.modal", function () {
                $("#addServiceTypeForm")[0].reset();
            });

            // Save button click
            $("#saveServiceType").click(function () {

                let formData = $("#addServiceTypeForm").serialize();

                $.ajax({
                    url: "add_service_type.php",
                    type: "POST",
                    data: formData,
                    dataType: "json",

                    success: function (response) {

                        // Duplicate validation
                        if (response.status === "duplicate") {
                            Swal.fire({
                                title: "Duplicate!",
                                text: "A service with this name already exists.",
                                icon: "warning"
                            });
                            return;
                        }

                        // Success case
                        if (response.status === "success") {

                            Swal.fire({
                                title: "Added!",
                                text: "New service type added successfully.",
                                icon: "success"
                            }).then(() => {
                                location.reload(); // Refresh page to show new data
                            });

                            return;
                        }

                        // General fallback
                        Swal.fire({
                            title: "Error!",
                            text: "Something went wrong. Please try again.",
                            icon: "error"
                        });
                    },

                    error: function () {
                        Swal.fire({
                            title: "Server Error!",
                            text: "Unable to reach the server.",
                            icon: "error"
                        });
                    }
                });
            });

            // Update Service
            $(document).on("click", ".editService", function () {

                $("#edit_service_id").val($(this).data("id"));
                $("#edit_service_name").val($(this).data("name"));
                $("#edit_service_short").val($(this).data("short"));
                $("#edit_service_type").val($(this).data("type"));

                $("#editServiceModal").modal("show");
            });

            $("#updateServiceBtn").click(function () {
                $.ajax({
                    url: "update_service_type.php",
                    type: "POST",
                    data: $("#editServiceForm").serialize(),
                    success: function (response) {

                        if (response === "duplicate") {
                            Swal.fire("Duplicate!", "Service name already exists.", "warning");
                            return;
                        }

                        if (response === "success") {
                            Swal.fire("Updated!", "Service updated.", "success")
                            .then(() => location.reload());
                        }
                    }
                });
            });

            // Delete Service
            $(document).on("click", ".deleteService", function () {

                let id = $(this).data("id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "This will permanently delete the service type.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete"
                }).then(result => {

                    if (result.isConfirmed) {

                        $.post("delete_service_type.php", { id: id }, function (response) {

                            if (response === "success") {
                                Swal.fire("Deleted!", "Service removed.", "success")
                                .then(() => location.reload());
                            }
                        });
                    }
                });
            });

            // Bulk Select - DeSelect
            function getSelectedIds() {
                let ids = [];
                $(".rowCheck:checked").each(function () {
                    ids.push($(this).val());
                });
                return ids;
            }

            $("#bulkActivate, #bulkDeactivate").click(function () {

                let ids = getSelectedIds();
                if (ids.length === 0) {
                    Swal.fire("No Selection", "Please select at least one service.", "warning");
                    return;
                }

                let newStatus = $(this).attr("id") === "bulkActivate" ? "Active" : "Inactive";

                Swal.fire({
                    title: "Are you sure?",
                    text: `Change status of ${ids.length} items to ${newStatus}?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes"
                }).then(result => {

                    if (result.isConfirmed) {
                        $.post("bulk_status_update.php", { ids: ids, status: newStatus }, function (response) {

                            if (response === "success") {
                                Swal.fire("Updated", "Bulk status updated.", "success")
                                .then(() => location.reload());
                            }
                        });
                    }
                });
            });

        </script>
    </body>
</html>