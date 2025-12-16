<?php
session_start();

if (!isset($_SESSION['account_id'])) {
    header("Location: index.php");
    exit;
}
// Database connection
include('includes/connection.php');

// Get user details
$account_id = $_SESSION['account_id'];
$stmt = $conn->prepare("SELECT * FROM accounts WHERE account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $account_id = $user['account_id'];
    $accounts_name = $user['accounts_name'];       // adjust field names as per your DB
    $account_email = $user['account_email'];     // adjust accordingly
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
        <title><?php echo htmlspecialchars($accounts_name); ?> - All Work Orders</title>
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
                            <a href="dashboard.php"><p class="active-menu"><span><i class="fa-solid fa-clipboard"></i>  Dashboard </span></p></a>
                            <a href="logout.php"><p class="menu"><span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span></p></a>
                        </div>

                        <div class="bottom-section">
                            <div class="user-info text-center">
                                <img src="../assets/images/user.png" alt="User" />
                                <p class="name"><?php echo htmlspecialchars($accounts_name); ?></p>
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
                                        <p class="user-title"><?php echo htmlspecialchars($accounts_name); ?></p>
                                        <p class="user-role"><?php echo htmlspecialchars($role); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card shadow outer-card">
                                    <div class="card-body">
                                        <h2 class="title greentitle text-center mb-3">Invoice Payment Status</h2>
                                        <table id="example" class="table table-striped" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>WO</th>
                                                    <th>Invoice Date</th>
                                                    <th>Invoice Number</th>
                                                    <th>Invoice Amount</th>
                                                    <th>Invoice</th>
                                                    <th>Payment Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            // Connect to the database
                                            include('includes/connection.php');

                                            // Fetch employee names
                                            $sql = "SELECT * FROM invoice_tbl"; // latest first
                                            $result = $conn->query($sql);
                                            ?>
                                            <tbody>
                                                <?php if ($result->num_rows > 0): ?>
                                                    <?php while ($row = $result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row['work_order_number']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['invoice_date']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['sub_total']); ?></td>
                                                            <td><a class="btn btn-warning btn-sm" href="../invoice_uploads/<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">View Invoice</a></td>
                                                            <td>
                                                                <span class="badge 
                                                                    <?php echo ($row['payment_status'] == 'Paid') ? 'bg-success' : 'bg-danger'; ?>">
                                                                    <?php echo htmlspecialchars($row['payment_status']); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if ($row['payment_status'] !== 'Paid'): ?>
                                                                    <button 
                                                                        class="btn btn-success btn-sm mark-paid-btn" data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                                        Mark Paid
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button class="btn btn-secondary btn-sm" disabled>Paid</button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>WO</th>
                                                    <th>Invoice Date</th>
                                                    <th>Invoice Number</th>
                                                    <th>Invoice Amount</th>
                                                    <th>Invoice</th>
                                                    <th>Payment Status</th>
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
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

        <script>
            $(document).on('click', '.mark-paid-btn', function () {

                let button = $(this);
                let invoiceId = button.data('id');

                Swal.fire({
                    title: 'Mark as Paid?',
                    text: 'This will update the invoice status.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Mark Paid'
                }).then((result) => {

                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'mark_invoice_paid.php',
                            type: 'POST',
                            data: { invoice_id: invoiceId },
                            dataType: 'json',
                            success: function (response) {

                                if (response.status === 'success') {

                                    // Update status badge
                                    button.closest('tr').find('td:eq(5)').html(
                                        '<span class="badge bg-success">Paid</span>'
                                    );

                                    // Disable button
                                    button.replaceWith(
                                        '<button class="btn btn-secondary btn-sm" disabled>Paid</button>'
                                    );

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Invoice marked as Paid.'
                                    });

                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function () {
                                Swal.fire('Error', 'Something went wrong!', 'error');
                            }
                        });
                    }
                });
            });
        </script>

    </body>
</html>