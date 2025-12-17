<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
// Database connection
include('includes/connection.php');

// Get user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $name = $user['name'];       // adjust field names as per your DB
    $email = $user['email'];     // adjust accordingly
    $job_title = $user['job_title'];     // adjust accordingly
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
        <title><?php echo htmlspecialchars($name); ?> - Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="assets/css/style.css">

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    </head>
    <body>
        
        <section class="dashboard">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-2 sidebar">
                        <div class="logo text-center">
                            <img src="assets/images/logo_white.png" alt="">
                        </div>
                        <div class="menu_list">
                            <a href="dashboard.php"><p class="menu"><span><i class="fa-solid fa-house"></i> Home</span></p></a>
                            <a href="running-sheet.php"><p class="menu"><span><i class="fa-solid fa-clipboard"></i>  Work Request </span></p></a>
                            <a href="recurring.php"><p class="active-menu"><span><i class="fa-solid fa-clipboard"></i>  Recurring Contracts </span></p></a>
                            <!-- <p class="menu"><span><i class="fa-solid fa-circle-user"></i> Profile</span></p>
                            <p class="menu"><span><i class="fa-solid fa-phone-volume"></i> Contact Us</span></p>
                            <p class="menu"><span><i class="fa-solid fa-gear"></i> Settings</span></p> -->
                            <a href="logout.php"><p class="menu"><span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span></p></a>
                        </div>

                        <!-- <div class="bottom-section">
                            <div class="user-info text-center">
                                <img src="assets/images/user.png" alt="User" />
                                <p class="name"><?php echo htmlspecialchars($name); ?></p>
                                <p class="role"><?php echo htmlspecialchars($job_title); ?></p>
                            </div>
                            
                        </div> -->
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
                                        <img src="assets/images/user.png" width="60px" alt="">
                                    </div>
                                    <div class="col-sm-9 userinfor">
                                        <p class="user-title"><?php echo htmlspecialchars($name); ?></p>
                                        <p class="user-role"><?php echo htmlspecialchars($job_title); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="row filter mt-4">
                            <h2 class="title greentitle text-center mb-3">Recurring Contract</h2>
                            <div class="col-sm-12">
                                <table id="example" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Site</th>
                                            <th>Service Type</th>
                                            <th>Frequency</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>WO Value</th>
                                            <th>Logged By</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    // Connect to the database
                                    include('includes/connection.php');
                                    $user_id = $_SESSION['user_id'];
                                    // Fetch employee names
                                    $sql = "SELECT 
                                                rc.contract_id,
                                                s.site_name,
                                                st.service_name,
                                                rc.frequency,
                                                rc.start_date,
                                                rc.end_date,
                                                rc.work_order_value,
                                                rc.annual_value,
                                                rc.status,
                                                a.admin_name AS logged_by_name
                                            FROM recurring_contracts rc
                                            JOIN site_tbl s 
                                                ON s.id = rc.site_id
                                            JOIN service_type_tbl st 
                                                ON st.service_id = rc.service_type_id
                                            JOIN admin a 
                                                ON a.admin_id = rc.logged_by
                                            WHERE rc.assigned_to = $user_id
                                            ORDER BY rc.contract_id DESC"; // latest first
                                    $result = $conn->query($sql);
                                    ?>
                                    <tbody>
                                        <?php if ($result->num_rows > 0): ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['site_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['frequency']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['work_order_value']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['logged_by_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['status']); ?></td>

                                                    <!-- <?php if ($row['priority'] == "Urgent"): ?>
                                                    <td><span class="badge text-bg-danger"><?php echo htmlspecialchars($row['priority']); ?></span></td>
                                                    <?php elseif ($row['priority'] == "Medium"): ?>
                                                        <td><span class="badge text-bg-warning"><?php echo htmlspecialchars($row['priority']); ?></span></td>
                                                    <?php else: ?>
                                                        <td><span class="badge text-bg-primary"><?php echo htmlspecialchars($row['priority']); ?></span></td>
                                                    <?php endif; ?> -->

                                                    <td>
                                                        <a href='recurring-wo.php?contract_id=<?php echo htmlspecialchars($row['contract_id']); ?>'>
                                                            View Work Orders
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Site</th>
                                            <th>Service Type</th>
                                            <th>Frequency</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>WO Value</th>
                                            <th>Logged By</th>
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
        </section>

        <div class="modal fade" id="taskDetailsModal" tabindex="-1" aria-labelledby="taskDetailsLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskDetailsLabel">Work Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Description:</strong> <span id="modalTaskDesc"></span></p>
                </div>
                </div>
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="workorderToast" class="toast align-items-center text-bg-success border-0" role="alert">
                <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    ✅ Work Order Created!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
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
            new DataTable('#example', {
                responsive: true,
                scrollX: true,
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50, 100],
                order: [[1, "desc"]]   // Sort Date column (0) in descending order
            });
        </script>
    </body>
</html>