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
        <title><?php echo htmlspecialchars($name); ?> - Sites</title>
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
                            <a href="recurring_work_orders.php"><p class="menu"><span><i class="fa-solid fa-clipboard"></i>  Recurring Work Orders </span></p></a>
                            <a href="wo_status.php"><p class="menu"><span><i class="fa-solid fa-circle-user"></i> Work Order Status</span></p></a>
                            <a href="recurring_wo_status.php"><p class="menu"><span><i class="fa-solid fa-circle-user"></i> Recurring Status</span></p></a>
                            <a href="service_types.php"><p class="menu"><span><i class="fa-solid fa-gears"></i> Service Types</span></p></a>
                            <a href="sites.php"><p class="active-menu"><span><i class="fa-regular fa-building"></i> Sites</span></p></a>
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
                                        <h2 class="title greentitle text-center mb-3">Sites</h2>
                                        <?php
                                        include('includes/connection.php');
                                        $serviceResult = $conn->query("SELECT service_id, service_name FROM service_type_tbl ORDER BY service_name ASC");
                                        ?>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Filter by Service Type:</label>
                                                    <select id="filterServiceType" class="form-select" style="width: 300px;">
                                                        <option value="">Show All</option>
                                                        <?php while ($s = $serviceResult->fetch_assoc()): ?>
                                                            <option value="<?= htmlspecialchars($s['service_name']); ?>">
                                                                <?= htmlspecialchars($s['service_name']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Add New Site</label><br>
                                                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addSiteModal"><i class="fa-solid fa-plus"></i> Add New Site</button>
                                                </div>
                                            </div>
                                        </div>
                                        <table id="example" class="table table-striped table-sm" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Site Id</th>
                                                    <th>Company</th>
                                                    <th>Site Name</th>
                                                    <th>Service Type</th>
                                                    <th>Site Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            // Connect to the database
                                            include('includes/connection.php');

                                            // Fetch employee names
                                            $sql = "SELECT 
                                                s.site_id,
                                                s.company,
                                                s.site_name,
                                                st.service_name AS service_type_name,
                                                s.site_status
                                                FROM site_tbl s
                                                LEFT JOIN service_type_tbl st ON s.service_type = st.service_id
                                                ORDER BY s.site_id "; // latest first
                                            $result = $conn->query($sql);
                                            ?>
                                            <tbody>
                                                <?php if ($result->num_rows > 0): ?>
                                                    <?php while ($row = $result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row['site_id']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['company']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['site_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['service_type_name']); ?></td>
                                                            <td class="status-text"><?php echo htmlspecialchars($row['site_status']); ?></td>
                                                            
                                                            <td>
                                                                <label class="switch">
                                                                    <input type="checkbox" class="status-toggle"
                                                                        data-id="<?= $row['site_id']; ?>"
                                                                        <?= ($row['site_status'] === 'Active') ? 'checked' : ''; ?>>
                                                                    <span class="slider round"></span>
                                                                </label>

                                                                <!-- Edit Button -->
                                                                <button class="btn btn-warning btn-sm editSite"
                                                                    data-id="<?= $row['site_id']; ?>"
                                                                    data-company="<?= htmlspecialchars($row['company']); ?>"
                                                                    data-site="<?= htmlspecialchars($row['site_name']); ?>"
                                                                    data-service="<?= htmlspecialchars($row['service_type_name']); ?>"
                                                                    data-status="<?= htmlspecialchars($row['site_status']); ?>">
                                                                    <i class="fa-solid fa-pencil"></i>
                                                                </button>

                                                                <!-- Delete Button -->
                                                                <button class="btn btn-danger btn-sm deleteSite"
                                                                    data-id="<?= $row['site_id']; ?>">
                                                                    <i class="fa-solid fa-trash-can"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>Site Id</th>
                                                    <th>Company</th>
                                                    <th>Site Name</th>
                                                    <th>Service Type</th>
                                                    <th>Site Status</th>
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

        <!-- Add Site Modal -->
        <div class="modal fade" id="addSiteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Add New Site</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form id="addSiteForm">

                            <div class="mb-3">
                                <label>Company</label>
                                <input type="text" name="company" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Site Name</label>
                                <input type="text" name="site_name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Service Type</label>
                                <select name="service_type" class="form-select" required>
                                    <option value="">Select Service Type</option>
                                    <?php
                                        $services = $conn->query("SELECT * FROM service_type_tbl ORDER BY service_name ASC");
                                        while ($s = $services->fetch_assoc()):
                                    ?>
                                        <option value="<?= $s['service_id']; ?>"><?= htmlspecialchars($s['service_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Status</label>
                                <select name="site_status" class="form-select" required>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                        </form>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-success" id="saveSiteBtn">
                            <span class="btn-text">Save</span>
                            <span class="spinner-border spinner-border-sm d-none" id="saveLoader"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="js/generate_work_order.js"></script>
        <script src="js/select_sites.js"></script>
        <script src="js/select_type.js"></script>
        <script src="js/select_priority.js"></script>
        <script src="js/select_user.js"></script>    
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

        <!-- DataTables JS -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- DataTables Initialization -->
        <script>
            // new DataTable('#example', {
            //     responsive: true,
            //     scrollX: true,
            //     pageLength: 7,
            //     lengthMenu: [7, 10, 25, 50, 100],
            // });
        </script>

        <script>
            $(document).ready(function () {
                var table = $('#example').DataTable({
                    pageLength: 7,
                    lengthMenu: [7, 10, 25, 50, 100],
                    responsive: true,
                    scrollX: true,
                });

                $("#filterServiceType").on("change", function () {
                    let value = $(this).val();
                    table.column(3).search(value).draw();  // column 3 = service type column
                });

            });

            // Change Site Status
            $(document).on("change", ".status-toggle", function () {

                let checkbox = $(this);
                let siteId = checkbox.data("id");
                let newStatus = checkbox.is(":checked") ? "Active" : "Inactive";

                // Get the status text cell in the same row
                let statusCell = checkbox.closest("tr").find(".status-text");

                $.ajax({
                    url: "toggle_site_status.php",
                    type: "POST",
                    data: { id: siteId, status: newStatus },
                    success: function (response) {

                        if (response === "success") {

                            // 🔥 Instantly update status text without page reload
                            statusCell.text(newStatus);

                            Swal.fire({
                                title: "Updated!",
                                text: "Status changed to " + newStatus,
                                icon: "success",
                                timer: 1200,
                                showConfirmButton: false
                            });

                        } else {

                            Swal.fire("Error!", "Unable to update status.", "error");

                            // ❗ Revert toggle on failure
                            checkbox.prop("checked", !checkbox.is(":checked"));
                        }
                    }
                });

            });

            $(document).on("click", ".editSite", function () {

                $("#edit_site_id").val($(this).data("id"));
                $("#edit_company").val($(this).data("company"));
                $("#edit_site_name").val($(this).data("site"));
                $("#edit_service_type").val($(this).data("service"));

                $("#editSiteModal").modal("show");
            });

            $("#updateSiteBtn").click(function () {

                $.ajax({
                    url: "update_site.php",
                    type: "POST",
                    data: $("#editSiteForm").serialize(),
                    success: function (response) {

                        if (response === "success") {
                            Swal.fire({
                                title: "Updated!",
                                text: "Site updated successfully.",
                                icon: "success"
                            }).then(() => location.reload());
                        } else {
                            Swal.fire("Error!", "Unable to update site.", "error");
                        }
                    }
                });

            });

            $(document).on("click", ".deleteSite", function () {

                let siteId = $(this).data("id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "This site will be permanently deleted.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete"
                }).then((result) => {

                    if (result.isConfirmed) {

                        $.ajax({
                            url: "delete_site.php",
                            type: "POST",
                            data: { id: siteId },
                            success: function (response) {

                                if (response === "success") {
                                    Swal.fire({
                                        title: "Deleted!",
                                        text: "Site deleted successfully.",
                                        icon: "success"
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire("Error!", "Unable to delete site.", "error");
                                }
                            }
                        });

                    }
                });

            });

            $("#saveSiteBtn").click(function () {

                let btn = $(this);
                let loader = $("#saveLoader");
                let btnText = btn.find(".btn-text");

                // Disable button + show loader
                btn.prop("disabled", true);
                btnText.addClass("d-none");
                loader.removeClass("d-none");

                $.ajax({
                    url: "add_site.php",
                    type: "POST",
                    data: $("#addSiteForm").serialize(),
                    dataType: "json",

                    success: function (response) {

                        // Restore button state
                        btn.prop("disabled", false);
                        btnText.removeClass("d-none");
                        loader.addClass("d-none");

                        // Duplicate validation
                        if (response.status === "duplicate") {
                            Swal.fire({
                                title: "Duplicate!",
                                text: "A site with the same company and site name already exists.",
                                icon: "warning"
                            });
                            return;
                        }

                        // Success
                        if (response.status === "success") {
                            Swal.fire({
                                title: "Site Added!",
                                text: "New site has been added successfully.",
                                icon: "success"
                            }).then(() => location.reload());
                            return;
                        }

                        // General error fallback
                        Swal.fire("Error!", "Unable to add site. Try again.", "error");
                    },

                    error: function () {

                        // Restore button state on failure
                        btn.prop("disabled", false);
                        btnText.removeClass("d-none");
                        loader.addClass("d-none");

                        Swal.fire("Server Error!", "Unable to connect to server.", "error");
                    }
                });

            });
        </script>
    </body>
</html>