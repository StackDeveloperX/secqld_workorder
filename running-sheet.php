<?php
session_start();

if (!isset($_SESSION['client_id'])) {
    header("Location: index.php");
    exit;
}
// Database connection
include('includes/connection.php');

// Get user details
$client_id = $_SESSION['client_id'];
$stmt = $conn->prepare("SELECT * FROM clients WHERE client_id = ?");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $business_name = $user['business_name'];       // adjust field names as per your DB
    $business_email  = $user['business_email'];     // adjust accordingly
    $abn = $user['abn'];     // adjust accordingly
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
        <title><?php echo htmlspecialchars($business_name); ?> - Dashboard</title>
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
                            <a href="running-sheet.php"><p class="active-menu"><span><i class="fa-solid fa-clipboard"></i>  Work Request </span></p></a>
                            <a href="recurring.php"><p class="menu"><span><i class="fa-solid fa-clipboard"></i>  Recurring Contracts </span></p></a>
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
                                        <p class="user-title"><?php echo htmlspecialchars($business_name); ?></p>
                                        <p class="user-role"><?php echo htmlspecialchars($business_email); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="row filter">
                            <div class="col-sm-3 mt-2">
                                
                                <select name="building_name" id="building_name" class="form-select">
                                    <?php
                                        // Connect to the database
                                        include('includes/connection.php');

                                        // Fetch employee names
                                        $sql = "SELECT * FROM site_tbl";
                                        $result = $conn->query($sql);
                                    ?>
                                    <option value="">Select the Site Name</option>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($row['site_name']); ?>"><?php echo htmlspecialchars($row['site_name']); ?></option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-3 mt-2">
                                
                                <select name="type" id="type" class="form-select">
                                    <?php
                                        // Connect to the database
                                        include('includes/connection.php');

                                        // Fetch employee names
                                        $sql = "SELECT * FROM service_type_tbl";
                                        $result = $conn->query($sql);
                                    ?>
                                    <option value="">Select the Type</option>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($row['service_id']); ?>"><?php echo htmlspecialchars($row['service_name']); ?></option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-3 mt-2">
                                
                                <select name="priority" id="priority" class="form-select">
                                    <?php
                                        // Connect to the database
                                        include('includes/connection.php');

                                        // Fetch employee names
                                        $sql = "SELECT * FROM priority_tbl";
                                        $result = $conn->query($sql);
                                    ?>
                                    <option value="">Select the Priority</option>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($row['priority_id']); ?>"><?php echo htmlspecialchars($row['priority_name']); ?></option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-3 mt-2">
                                <select name="status" id="status" class="form-select">
                                    <?php
                                        // Connect to the database
                                        include('includes/connection.php');

                                        // Fetch employee names
                                        $sql = "SELECT * FROM status_tbl";
                                        $result = $conn->query($sql);
                                    ?>
                                    <option value="">Select the Status</option>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($row['status_id']); ?>"><?php echo htmlspecialchars($row['status_name']); ?></option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row filter mt-4">
                            <div class="col-sm-12">
                                <table id="example" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>WO</th>
                                            <th>Site ID</th>
                                            <th>Site Name</th>
                                            <th>Type</th>
                                            <th>Priority</th>
                                            <th>Assigned To</th>
                                            <th>Logged By</th>
                                            <th>Value</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    // Connect to the database
                                    include('includes/connection.php');
                                    $user_id = $_SESSION['client_id'];
                                    // Fetch employee names
                                    $sql = "SELECT 
                                            w.work_order_date,
                                            w.work_order_number,
                                            w.site_id,
                                            w.site_name,
                                            s.service_name AS type,
                                            p.priority_name AS priority,
                                            u.business_name AS assigned_to,
                                            a.admin_name AS logged_by_name,
                                            w.value,
                                            w.status
                                        FROM work_order w
                                        LEFT JOIN service_type_tbl s ON w.type = s.service_id
                                        LEFT JOIN priority_tbl p ON w.priority = p.priority_id
                                        LEFT JOIN clients u ON w.assigned_to = u.client_id
                                        LEFT JOIN admin a ON w.logged_by = a.admin_id
                                        WHERE w.assigned_to = $user_id
                                        ORDER BY w.work_order_date DESC, w.work_order_number DESC"; // latest first
                                    $result = $conn->query($sql);
                                    ?>
                                    <tbody>
                                        <?php if ($result->num_rows > 0): ?>
                                            <?php while ($row = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($row['work_order_date']))); ?></td>
                                                    <?php
                                                    include_once ('function.php');
                                                    $encrypted = urlencode(encrypt($row['work_order_number']));
                                                    ?>
                                                    <td>
                                                        <a href="request-details.php?wo=<?php echo $encrypted; ?>">
                                                            <?php echo htmlspecialchars($row['work_order_number']); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['site_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['site_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['type']); ?></td>

                                                    <?php if ($row['priority'] == "Urgent"): ?>
                                                    <td><span class="badge text-bg-danger"><?php echo htmlspecialchars($row['priority']); ?></span></td>
                                                    <?php elseif ($row['priority'] == "Medium"): ?>
                                                        <td><span class="badge text-bg-warning"><?php echo htmlspecialchars($row['priority']); ?></span></td>
                                                    <?php else: ?>
                                                        <td><span class="badge text-bg-primary"><?php echo htmlspecialchars($row['priority']); ?></span></td>
                                                    <?php endif; ?>

                                                    <td><?php echo htmlspecialchars($row['assigned_to']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['logged_by_name']); ?></td>
                                                    <td>$<?php echo htmlspecialchars($row['value']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Date</th>
                                            <th>WO</th>
                                            <th>Site ID</th>
                                            <th>Site Name</th>
                                            <th>Type</th>
                                            <th>Priority</th>
                                            <th>Assigned To</th>
                                            <th>Logged By</th>
                                            <th>Value</th>
                                            <th>Status</th>
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
        <script>
        $(document).ready(function () {
            $('.view-task-btn').on('click', function () {
                const taskNameRaw = $(this).data('task_name');
                const taskDescRaw = $(this).data('task_desc');
                const progress = $(this).data('progress');
                const priority = $(this).data('priority');

                // Convert URLs in text to clickable links
                $('#modalTaskDesc').text($(this).data('task_desc'));

                
            });
        });
    </script>
    <script>
    $(document).ready(function () {
        var table = $('#example').DataTable();

        // Filter function for each dropdown
        $('#building_name, #type, #priority, #status').on('change', function () {
            var building = $('#building_name option:selected').text();
            var type = $('#type option:selected').text();
            var priority = $('#priority option:selected').text();
            var status = $('#status option:selected').text();

            // Reset search first
            table.columns().search('');

            // Apply filter to respective columns
            if ($('#building_name').val() !== '') {
                table.column(3).search('^' + building + '$', true, false); // Site Name column
            }
            if ($('#type').val() !== '') {
                table.column(4).search('^' + type + '$', true, false); // Type column
            }
            if ($('#priority').val() !== '') {
                table.column(5).search('^' + priority + '$', true, false); // Priority column
            }
            if ($('#status').val() !== '') {
                table.column(9).search('^' + status + '$', true, false); // Status column
            }

            // Redraw table
            table.draw();
        });
    });
    </script>
    <script>
        let lastWorkOrderId = 0;
        let firstLoad = true;

        function checkWorkOrders() {
            $.ajax({
                url: "check_workorders.php",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    if (response.status === "success") {
                        if (firstLoad) {
                            lastWorkOrderId = response.id;
                            firstLoad = false;
                        } else if (response.id > lastWorkOrderId) {
                            lastWorkOrderId = response.id;

                            // ✅ Show toast
                            $("#toastMessage").text(
                                "✅ Work Order #" + response.number +
                                " for site '" + response.site +
                                "' created at " + response.date
                            );
                            let toastEl = new bootstrap.Toast(document.getElementById('workorderToast'));
                            toastEl.show();

                            // ✅ Reload page after short delay
                            setTimeout(() => {
                                location.reload();
                            }, 2000); // wait 2 seconds so user sees the toast
                        }
                    }
                }
            });
        }

        // Run once on load
        checkWorkOrders();

        // Repeat every 5 seconds
        setInterval(checkWorkOrders, 5000);
    </script>
    </body>
</html>