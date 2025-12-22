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
        <title><?php echo htmlspecialchars($name); ?> - Work Order Status</title>
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
                            <a href="recurring_wo_status.php"><p class="active-menu"><span><i class="fa-solid fa-circle-user"></i> Recurring Status</span></p></a>
                            <a href="service_types.php"><p class="menu"><span><i class="fa-solid fa-gears"></i> Service Types</span></p></a>
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
                                        <h2 class="title greentitle text-center mb-3">Work Order Status</h2>
                                        <table id="example" class="table table-striped" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>WO</th>
                                                    <th>Priority</th>
                                                    <th>Value</th>
                                                    <th>Invoice Amount</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            // Connect to the database
                                            include('includes/connection.php');

                                            // Fetch employee names
                                            $sql = "SELECT 
                                                    w.work_order_date,
                                                    w.work_order_number,
                                                    w.site_id,
                                                    s.service_name AS type,
                                                    p.priority_name AS priority,
                                                    u.name AS assigned_to,
                                                    a.admin_name AS logged_by_name,
                                                    w.value,
                                                    w.status, w.inv_status, w.actual_value
                                                FROM recurring_work_orders w
                                                LEFT JOIN service_type_tbl s ON w.service_type_id = s.service_id
                                                LEFT JOIN priority_tbl p ON w.priority = p.priority_id
                                                LEFT JOIN users u ON w.assigned_to = u.user_id
                                                LEFT JOIN admin a ON w.logged_by = a.admin_id
                                                WHERE w.inv_status = 'Waiting For Approval'"; // latest first
                                            $result = $conn->query($sql);
                                            ?>
                                            <tbody>
                                                <?php if ($result->num_rows > 0): ?>
                                                    <?php while ($row = $result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row['work_order_date']); ?></td>
                                                            <?php
                                                            // include_once ('function.php');
                                                            // $encrypted = urlencode(encrypt($row['work_order_number']));
                                                            ?>
                                                            <td><?php echo htmlspecialchars($row['work_order_number']); ?></td>

                                                            <?php if ($row['priority'] == "Urgent"): ?>
                                                            <td><span class="badge text-bg-danger"><?php echo htmlspecialchars($row['priority']); ?></span></td>
                                                            <?php elseif ($row['priority'] == "Medium"): ?>
                                                                <td><span class="badge text-bg-warning"><?php echo htmlspecialchars($row['priority']); ?></span></td>
                                                            <?php else: ?>
                                                                <td><span class="badge text-bg-primary"><?php echo htmlspecialchars($row['priority']); ?></span></td>
                                                            <?php endif; ?>
                                                            <td>$<?php echo htmlspecialchars($row['value']); ?></td>
                                                            <td>$<?php echo htmlspecialchars($row['actual_value']); ?></td>
                                                            <td>
                                                                <button class="btn btn-success btn-sm approve" data-id="<?php echo htmlspecialchars($row['work_order_number']); ?>">Approve</button>
                                                                <button class="btn btn-danger btn-sm decline" data-id="<?php echo htmlspecialchars($row['work_order_number']); ?>">Decline</button>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>WO</th>
                                                    <th>Priority</th>
                                                    <th>Value</th>
                                                    <th>Invoice Amount</th>
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

        <!-- Rejection Reason Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejection Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <textarea id="rejectReason" class="form-control" placeholder="Enter reason for rejection"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmReject">Reject</button>
            </div>
            </div>
        </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>   
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

        <!-- DataTables Initialization -->
        <script>
            new DataTable('#example', {
                responsive: true,
                scrollX: true
            });
        </script>

        <!-- SweetAlert CDN -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            let rejectId = null;
            let rejectBtn = null;

            // APPROVE
            $(document).on('click', '.approve', function () {
                let $btn = $(this);
                let id = $btn.data('id');

                // Disable + spinner
                $btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> Approving');

                $.post("update_recurring_status.php", {
                    id: id,
                    status: "Approved"
                })
                .done(function (response) {
                    if (response.trim() === "success") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Approved',
                            text: 'Recurring work order approved',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', response, 'error');
                        resetButton($btn, 'Approve');
                    }
                })
                .fail(function () {
                    Swal.fire('Error', 'Server error occurred', 'error');
                    resetButton($btn, 'Approve');
                });
            });

            // DECLINE (open modal)
            $(document).on('click', '.decline', function () {
                rejectId = $(this).data('id');
                rejectBtn = $(this);

                $('#rejectReason').val('');
                $('#rejectModal').modal('show');
            });

            // CONFIRM REJECT
            $('#confirmReject').on('click', function () {
                let reason = $('#rejectReason').val().trim();

                if (reason === "") {
                    Swal.fire('Required', 'Please enter a rejection reason', 'warning');
                    return;
                }

                // Disable decline button + spinner
                rejectBtn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> Rejecting');

                $.post("update_recurring_status.php", {
                    id: rejectId,
                    status: "Rejected",
                    reason: reason
                })
                .done(function (response) {
                    if (response.trim() === "success") {
                        $('#rejectModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Rejected',
                            text: 'Recurring work order rejected',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', response, 'error');
                        resetButton(rejectBtn, 'Decline');
                    }
                })
                .fail(function () {
                    Swal.fire('Error', 'Server error occurred', 'error');
                    resetButton(rejectBtn, 'Decline');
                });
            });

            // BUTTON RESET HELPER
            function resetButton($btn, text) {
                $btn.prop('disabled', false).text(text);
            }

        </script>
    </body>
</html>