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
        <title><?php echo htmlspecialchars($name); ?> - Add Work Order</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="../assets/css/style.css">
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
                            <a href="dashboard.php"><p class="menu"><span><i class="fa-solid fa-house"></i> Home</span></p></a>
                            <a href="add_work_order.php"><p class="active-menu"><span><i class="fa-solid fa-clipboard"></i>  Add Work Orders </span></p></a>
                            <a href="all_work_orders.php"><p class="menu"><span><i class="fa-solid fa-clipboard"></i>  All Work Orders </span></p></a>
                            <a href="wo_status.php"><p class="menu"><span><i class="fa-solid fa-circle-user"></i> Work Order Status</span></p></a>
                            <a href="service_types.php"><p class="menu"><span><i class="fa-solid fa-gears"></i> Add Service Type</span></p></a>
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
                                        <h2 class="title greentitle text-center mb-3">Add work order</h2>
                                        <div class="mb-3">
                                            <label for="work_order_type" class="form-label">Work Order Type</label>
                                            <select class="form-select" id="work_order_type" name="work_order_type">
                                                <option value="">-- Select Type --</option>
                                                <option value="Normal">Normal</option>
                                            </select> 
                                        </div>
                                        <div id="normal" style="display:none;">
                                            <form id="addwork"  method="POST" enctype="multipart/form-data">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="mb-3">
                                                            <input type="text" class="form-control" id="admin_name" name="admin_name" value="<?php echo htmlspecialchars($admin_id); ?>" hidden> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="work_order_no" class="form-label">Work Order Number</label>
                                                            <input type="text" class="form-control" id="work_order_no" name="work_order_no" readonly> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="service_type" class="form-label">Service Types</label>
                                                            <select class="form-select" id="service_type" name="service_type">
                                                                <option value="">Loading...</option>
                                                            </select> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="sitename" class="form-label">Site Name</label>
                                                            <select class="form-select" id="sitename" name="sitename">
                                                                <option value="">Loading...</option>
                                                            </select> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="priority" class="form-label">Priority</label>
                                                            <select class="form-select" id="priority" name="priority">
                                                                <option value="">Loading...</option>
                                                            </select> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="user_select" class="form-label">Assign Work To</label>
                                                            <select class="form-select" id="user_select" name="user_select">
                                                                <option value="">Loading...</option>
                                                            </select> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="wo_amount" class="form-label">Work Order Amount</label>
                                                            <input type="number" step="0.01" class="form-control" id="wo_amount" name="wo_amount">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="work_begin" class="form-label">Work Begin Date</label>
                                                            <input type="date" class="form-control" id="work_begin" name="work_begin">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="expected_completion" class="form-label">Expected Completion Date</label>
                                                            <input type="date" class="form-control" id="expected_completion" name="expected_completion">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="add_attachments" class="form-label">Add Attachments</label>
                                                            <input type="file" class="form-control" id="add_attachments" name="add_attachments[]" multiple>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="mb-1">
                                                            <label for="job_description" class="form-label">Job Description</label>
                                                            <textarea class="form-control" id="job_description" name="job_description"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="mb-3">
                                                            <button type="submit" id="add_work_order_btn" class="btn btn-green">Add Work Order</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div id="recurring" style="display:none;">
                                            <form id="addrecurringwork"  method="POST" enctype="multipart/form-data">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="mb-3">
                                                        <input type="text" class="form-control" id="admin_name" name="admin_name" value="<?php echo htmlspecialchars($admin_id); ?>" hidden> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="work_order_no_recurring" class="form-label">Work Order Number</label>
                                                            <input type="text" class="form-control" id="work_order_no_recurring" name="work_order_no_recurring" readonly> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="start_date" class="form-label">Start Date</label>
                                                            <input type="date" class="form-control" id="start_date" name="start_date">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="end_date" class="form-label">End Date</label>
                                                            <input type="date" class="form-control" id="end_date" name="end_date">
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="frequency" class="form-label">Frequency</label>
                                                            <select class="form-select" id="frequency" name="frequency">
                                                                <option value="">-- Select Frequency --</option>
                                                                <option value="Weekly">Weekly</option>
                                                                <option value="Fortnightly">Fortnightly</option>
                                                                <option value="Monthly">Monthly</option>
                                                                <option value="Quarterly">Quarterly</option>
                                                            </select> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="work_order_amount" class="form-label">Work Order Amount</label>
                                                            <input type="text" class="form-control" id="work_order_amount" name="work_order_amount" > 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="annual_amount" class="form-label">Annual Amount</label>
                                                            <input type="text" class="form-control" id="annual_amount" name="annual_amount" > 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="service_type_recurring" class="form-label">Service Types</label>
                                                            <select class="form-select" id="service_type_recurring" name="service_type_recurring">
                                                                <option value="">Loading...</option>
                                                            </select> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="sitename_recurring" class="form-label">Site Name</label>
                                                            <select class="form-select" id="sitename_recurring" name="sitename_recurring">
                                                                <option value="">Loading...</option>
                                                            </select> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="mb-3">
                                                            <label for="user_select_recurring" class="form-label">Assign Work To</label>
                                                            <select class="form-select" id="user_select_recurring" name="user_select_recurring">
                                                                <option value="">Loading...</option>
                                                            </select> 
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="mb-1">
                                                            <label for="job_description_recurring" class="form-label">Job Description</label>
                                                            <textarea class="form-control" id="job_description_recurring" name="job_description_recurring"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="mb-3">
                                                            <button type="submit" class="btn btn-green">Create Recurring Work Order</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </section>

        <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
            <div id="toastMsg" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="toastBody">
                        <!-- Message goes here -->
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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
            $('#addrecurringwork').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: 'add_recurring_work.php',  // make sure this path is correct
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(res) {
                        console.log("AJAX Success:", res);
                        if(res.status === 'success'){
                            alert(res.message);
                        } else {
                            alert('Error: ' + res.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error);
                        console.log(xhr.responseText); // show PHP errors
                        alert("AJAX request failed. Check console for details.");
                    }
                });
            });

        </script>
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
        <script>
            $(document).ready(function() {
                $('#addwork').on('submit', function(e) {
                    e.preventDefault();

                    $('#add_work_order_btn').prop('disabled', true).text('Adding Work Order...');

                    // Create FormData object to handle files + text
                    var formData = new FormData(this);

                    $.ajax({
                        url: 'insert_work_order.php',
                        type: 'POST',
                        data: formData,
                        contentType: false, // Important: prevent jQuery from messing with the content type
                        processData: false, // Important: don't let jQuery convert it to a query string
                        success: function(response) {
                            $('#toastBody').text(response);
                            var toast = new bootstrap.Toast(document.getElementById('toastMsg'));
                            toast.show();

                            $('#addwork')[0].reset();

                            // Regenerate new work order ID
                            $.get("generate_work_order.php", function(data) {
                                $('#work_order_no').val(data);
                            });

                            location.reload();
                        },
                        error: function(xhr) {
                            alert("An error occurred. Check console.");
                            console.log(xhr.responseText);
                        }
                    });
                });
            });

        </script>

        <script>
            $(document).ready(function() {
                $('#work_order_type').on('change', function() {
                    let selected = $(this).val();

                    if (selected === 'Normal') {
                        $('#recurring').slideUp();
                        $('#normal').stop(true, true).slideDown();
                    } 
                    else if (selected === 'Recurring') {
                        $('#normal').slideUp();
                        $('#recurring').stop(true, true).slideDown();
                    } 
                    else {
                        // Hide both if nothing is selected
                        $('#normal, #recurring').slideUp();
                    }
                });
            });
        </script>

        <script>
            $(document).ready(function() {

                function calculateAnnualAmount() {
                    let amount = parseFloat($('#work_order_amount').val().replace(/,/g, '')) || 0;
                    let frequency = $('#frequency').val();
                    let multiplier = 0;

                    switch(frequency) {
                        case 'Weekly':
                            multiplier = 52;
                            break;
                        case 'Fortnightly':
                            multiplier = 26;
                            break;
                        case 'Monthly':
                            multiplier = 12;
                            break;
                        case 'Quarterly':
                            multiplier = 4;
                            break;
                    }

                    let annual = amount * multiplier;

                    if(annual > 0){
                        let formatted = new Intl.NumberFormat('en-US', { 
                            style: 'currency', 
                            currency: 'USD' 
                        }).format(annual);

                        $('#annual_amount').val(formatted);
                    } else {
                        $('#annual_amount').val('');
                    }
                }

                // Trigger on change of frequency or amount
                $('#frequency, #work_order_amount').on('input change', calculateAnnualAmount);

            });
            </script>
            
    </body>
</html>