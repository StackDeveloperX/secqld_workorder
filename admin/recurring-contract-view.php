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
        <title><?php echo htmlspecialchars($name); ?> - All Work Orders</title>
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
                            <a href="recurring_work_orders.php"><p class="active-menu"><span><i class="fa-solid fa-clipboard"></i>  Recurring Work Orders </span></p></a>
                            <a href="wo_status.php"><p class="menu"><span><i class="fa-solid fa-circle-user"></i> Work Order Status</span></p></a>
                            <a href="service_types.php"><p class="menu"><span><i class="fa-solid fa-gears"></i> Service Types</span></p></a>
                            <a href="sites.php"><p class="menu"><span><i class="fa-regular fa-building"></i> Sites</span></p></a>
                            <a href="logout.php"><p class="menu"><span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span></p></a>
                        </div>

                        <!-- <div class="bottom-section">
                            <div class="user-info text-center">
                                <img src="../assets/images/user.png" alt="User" />
                                <p class="name"><?php echo htmlspecialchars($name); ?></p>
                                <p class="role"><?php echo htmlspecialchars($role); ?></p>
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
                                        <h2 class="title greentitle text-center mb-3">Recurring Contract Details</h2>
                                        <?php
                                            $contract_id = $_GET['contract_id'];
                                        ?>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <table border="1" class="table table-striped" width="100%" cellpadding="6" cellspacing="0">
                                                    <tr>
                                                        <td><b>Annual Value</b></td>
                                                        <td id="annualValue"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Current Billing</b></td>
                                                        <td id="currentBilling"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Weekly Value</b></td>
                                                        <td id="weeklyValue"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Balance</b></td>
                                                        <td id="balance"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Site</b></td>
                                                        <td id="siteName"></td>
                                                    </tr>  
                                                </table>
                                            </div>
                                            <div class="col-sm-6">
                                                <div style="width:70%; max-width:900px; margin:20px auto;">
                                                    <canvas id="financialChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <table id="example" class="table table-striped" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>WO#</th>
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

                                            // Fetch employee names
                                            $sql = "SELECT 
                                                        wo.work_order_date,
                                                        wo.work_order_number,
                                                        s.id,
                                                        s.site_name,
                                                        st.service_name,
                                                        wo.priority,
                                                        wo.assigned_to,
                                                        wo.logged_by,
                                                        wo.value,
                                                        wo.status
                                                    FROM recurring_work_orders wo
                                                    JOIN site_tbl s ON s.id = wo.site_id
                                                    JOIN service_type_tbl st ON st.service_id = wo.service_type_id
                                                    WHERE wo.contract_id = $contract_id
                                                    ORDER BY wo.work_order_date DESC
                                                    "; // latest first
                                            $result = $conn->query($sql);
                                            ?>
                                            <tbody>
                                                <?php if ($result->num_rows > 0): ?>
                                                    <?php while ($row = $result->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($row['work_order_date']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['work_order_number']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['site_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['priority']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['assigned_to']); ?></td>
                                                            <td><?php echo htmlspecialchars($row['logged_by']); ?></td>
                                                            <td>$<?php echo htmlspecialchars(number_format($row['value'],2)); ?></td>
                                                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>WO#</th>
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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- DataTables Initialization -->
        <script>
            new DataTable('#example', {
                responsive: true,
                scrollX: true,
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50, 100],
            });
        </script>

        <script>
            const CONTRACT_ID = <?= (int)$contract_id ?>;

            function loadSummary() {
                $.getJSON("getContractSummary.php", { contract_id: CONTRACT_ID }, function(d){
                    $("#annualValue").text("$" + d.annual);
                    $("#weeklyValue").text("$" + d.weekly);
                    $("#currentBilling").text("$" + d.current);
                    $("#balance").text("$" + d.balance);
                    $("#siteName").text(d.site);
                    $("#siteAddress").text(d.address);

                    if (d.over_budget) {
                        $("#balance").css("color","red");
                    } else {
                        $("#balance").css("color","black");
                    }

                    // 🔥 DRAW GRAPH
                    drawChart(
                        d.annual_raw,
                        d.current_raw,
                        d.balance_raw,
                        d.over_budget
                    );
                });
            }

            loadSummary();
        </script>
        <script>
            let chartInstance = null;

            function drawChart(annual, current, balance, overBudget) {

                const ctx = document.getElementById('financialChart').getContext('2d');

                if (chartInstance) {
                    chartInstance.destroy();
                }

                chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Approved Value', 'Billing Current', 'Balance'],
                        datasets: [{
                            label: 'Amount ($)',
                            data: [annual, current, balance],
                            backgroundColor: [
                                '#155e75',
                                '#155e75',
                                overBudget ? '#dc2626' : '#155e75'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '$' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }
        </script>
    </body>
</html>

