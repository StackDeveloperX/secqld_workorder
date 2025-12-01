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

        <style>
            .card{
                border-radius: 20px;
            }
        </style>
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
                            <p class="menu"><span><i class="fa-solid fa-circle-user"></i> Profile</span></p>
                            <p class="menu"><span><i class="fa-solid fa-phone-volume"></i> Contact Us</span></p>
                            <p class="menu"><span><i class="fa-solid fa-gear"></i> Settings</span></p>
                            <a href="logout.php"><p class="menu"><span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span></p></a>
                        </div>

                        <div class="bottom-section">
                            <div class="user-info text-center">
                                <img src="assets/images/user.png" alt="User" />
                                <p class="name"><?php echo htmlspecialchars($name); ?></p>
                                <p class="role"><?php echo htmlspecialchars($job_title); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-10 main-screen">
                        <?php
                        if (isset($_GET['wo'])) {
                        include_once('includes/connection.php');
                        include_once('function.php');

                        $decrypted = decrypt($_GET['wo']);

                        if ($decrypted !== false) {
                            // echo "Decrypted WO: " . htmlspecialchars($decrypted);
                            // Fetch admin details for this work order
                            $sql = "
                                    SELECT 
                                        a.admin_id,
                                        a.admin_name,
                                        a.admin_email,
                                        a.admin_contact,
                                        st.service_name,
                                        p.priority_name,
                                        u.name,
                                        w.*
                                    FROM work_order w
                                    LEFT JOIN admin a 
                                        ON w.logged_by = a.admin_id
                                    LEFT JOIN service_type_tbl st
                                        ON w.type = st.service_id
                                    LEFT JOIN priority_tbl p
                                        ON w.priority = p.priority_id
                                    LEFT JOIN users u
                                        ON w.assigned_to = u.user_id
                                    WHERE w.work_order_number = ?
                                    ";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $decrypted);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $admin = $result->fetch_assoc();
                        ?>
                        <div class="row">
                                <div class="col-sm-4">
                                    <?php if($admin['status'] == "Await Invoice"):?>
                                    <div class="card shadow mb-4">
                                        <div class="card-body">
                                            <button type="button" class="btn btn-success col-12" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                                Upload Invoice
                                            </button>
                                        </div>
                                    </div>
                                    <?php endif ?>
                                    <div class="card shadow ">
                                        <div class="card-body">
                                            <h5 class="card-title greentitle mb-3">Sender's Details</h5>
                                            <?php if ($admin): ?>
                                                <p class="invoice_content"><strong>Site Id:</strong> <?php echo htmlspecialchars($admin['site_id']); ?></p>
                                                <p class="invoice_content"><strong>Side Name:</strong> <?php echo htmlspecialchars($admin['site_name']); ?></p>
                                                <p class="invoice_content"><strong>Contact Name:</strong> <?php echo htmlspecialchars($admin['admin_name']); ?></p>
                                                <p class="invoice_content"><strong>Contact Email:</strong> <?php echo htmlspecialchars($admin['admin_email']); ?></p>
                                                <p class="invoice_content"><strong>Contact Phone:</strong> <?php echo htmlspecialchars($admin['admin_contact']); ?></p>
                                            <?php else: ?>
                                                <p>No admin details found for this work order.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                        <?php
                                        // Fetch files for this work order
                                        $file_sql = "SELECT file_path FROM downloads WHERE work_order_number = ?";
                                        $file_stmt = $conn->prepare($file_sql);
                                        $file_stmt->bind_param("s", $decrypted);
                                        $file_stmt->execute();
                                        $file_result = $file_stmt->get_result();
                                        ?>

                                        <div class="card shadow mt-3">
                                            <div class="card-body">
                                                <h5 class="card-title greentitle mb-3">Downloads</h5>
                                                <?php if ($file_result->num_rows > 0): ?>
                                                    <ul class="list-group">
                                                        <?php while ($file = $file_result->fetch_assoc()): ?>
                                                            <?php 
                                                                $file_url = htmlspecialchars($file['file_path']);
                                                                $file_name = basename($file['file_path']); // Just the name
                                                            ?>
                                                            <li class="list-group-item">
                                                                <i class="fa fa-file-image-o" style="color:#ff9800;"></i> <a href="<?php echo $file_url; ?>" download><?php echo $file_name; ?></a>
                                                            </li>
                                                        <?php endwhile; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p>No attachments found for this work order.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <div class="card shadow mt-3">
                                        <div class="card-body">
                                            <h5 class="card-title greentitle">Attachments</h5>

                                            <h6 class="mt-3">Attach files for this request [Not for Invoices]</h6>
                                            <div id="list_not_for_invoices"></div>

                                            <h6 class="mt-3">Attach files for this request [Hidden]</h6>
                                            <div id="list_hidden_request"></div>

                                            <h6 class="mt-3">Any document that is related to work order</h6>
                                            <div id="list_any_document"></div>
                                        </div>
                                    </div>
                                    <div class="card shadow mt-3">
                                        <div class="card-body">
                                            <h5 class="card-title greentitle">Notes</h5>

                                            <div id="display_notes">
                                                <p>Loading notes...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-8">
                                    <div class="card shadow" style="padding: 20px;">
                                        <div class="card-body">
                                            <div class="row">
                                                <h5 class="card-title greentitle mb-3">Invoicing Details</h5>
                                                <div class="col-sm-4">
                                                    <p class="invoice_content"><strong>Please invoice to</strong> <br> Sec QLD,<br>GPO Box 389 <br>Brisbane Markets QLD 4106</p>
                                                </div>
                                                <div class="col-sm-4">
                                                    <p class="invoice_content"><strong>Request Issued To ABN</strong>: <br> 98 679 546 126 <br> Sec QLD</p>
                                                </div>
                                                <div class="col-sm-4">
                                                    <p class="invoice_content">Quote Work Order Number: <strong><br> <?php echo htmlspecialchars($admin['work_order_number']); ?></strong> <br> on your invoice for prompt payment.</p>
                                                </div>
                                                <div class="col-sm-12 notes">
                                                    <p><strong>Notes:</strong></p>
                                                    <ul>
                                                        <li class="invoice_content">The invoices must be uploaded directly to the work order, as they should not be sent via post or email.</li>
                                                        <li class="invoice_content">You will receive notification when it is time to upload your invoice.</li>
                                                    </ul>
                                                    <div class="col-sm-12 notes_amount">
                                                        <p class="invoice_content">Work costs are not to exceed $<?php echo htmlspecialchars($admin['value']); ?> (excluding GST) without obtaining prior approval from the Sec QLD.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($admin['inv_status'] == "Rejected" || $admin['inv_status'] == ""): ?>
                                        <!-- Show Billing Details Form -->
                                        <div class="card shadow mt-3" id="billing_details">
                                            <div class="card-body">
                                                <h5 class="card-title greentitle mb-3">Billing Details</h5>
                                                <?php if ($admin['inv_status'] == "Rejected"): ?>
                                                    <div class="col-sm-12 mt-3">
                                                        <label for="invoice_amouunt" class="form-label">Status</label>
                                                        <p class="text-danger"><strong><?php echo htmlspecialchars($admin['inv_status']); ?></strong></p>
                                                    </div>
                                                    <div class="col-sm-12 mt-3">
                                                        <label for="invoice_amouunt" class="form-label">Reason for Rejection</label>
                                                        <p class="text-danger"><?php echo htmlspecialchars($admin['inv_reject_reason']); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                <form id="billingForm">
                                                    <div class="row">
                                                        <input type="hidden" name="work_order_id" id="work_order_id" value="<?php echo $admin['work_order_number']; ?>">
                                                        <div class="table-responsive">
                                                            <table class="table">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">Inv No</th>
                                                                        <th scope="col">Work Began Date</th>
                                                                        <th scope="col">Expected Completion</th>
                                                                        <th scope="col">Actual Completion</th>
                                                                        <th scope="col">Inv Amount</th>
                                                                        <th scope="col">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><input type="text" name="invoice_number" id="invoice_number" class="form-control"></td>
                                                                        <td><input type="date" name="work_began_date" id="work_began_date"
                                                                            value="<?php echo htmlspecialchars($admin['work_begin_date']); ?>" class="form-control"></td>
                                                                        <td>
                                                                            <input type="date" name="expected_completion" id="expected_completion"
                                                                            value="<?php echo htmlspecialchars($admin['expected_completion_date']); ?>"
                                                                            class="form-control" readonly>
                                                                        </td>
                                                                        <td><input type="date" name="actual_completion" id="actual_completion" class="form-control"></td>
                                                                        <td><input type="text" name="invoice_amouunt" id="invoice_amouunt" class="form-control"></td>
                                                                        <td><button class="btn btn-success" type="submit">Update</button></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php elseif (!empty($admin['inv_number'])): ?>
                                        <!-- Show Updated Billing Details -->
                                        <div class="card shadow mt-3" id="billing_details_updated">
                                            <div class="card-body">
                                                <h5 class="card-title greentitle mb-3">Billing Details</h5>
                                                    <div class="row">
                                                        <input type="hidden" name="work_order_id" value="<?php echo $admin['work_order_number']; ?>">
                                                        <div class="table-responsive">
                                                            <table class="table">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">Inv No</th>
                                                                        <th scope="col">Work Began Date</th>
                                                                        <th scope="col">Expected Completion</th>
                                                                        <th scope="col">Actual Completion</th>
                                                                        <th scope="col">Inv Amount</th>
                                                                        <th scope="col">Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><p><?php echo htmlspecialchars($admin['inv_number']); ?></p></td>
                                                                        <td><p><?php echo htmlspecialchars($admin['work_begin_date']); ?></p></td>
                                                                        <td><p><?php echo htmlspecialchars($admin['expected_completion_date']); ?></p></td>
                                                                        <td><p><?php echo htmlspecialchars($admin['actual_completion_date']); ?></p></td>
                                                                        <td><p>$<?php echo htmlspecialchars($admin['actual_value']); ?></p></td>
                                                                        <td><?php if ($admin['inv_status'] == "Approved" || $admin['inv_status'] == "Submitted"): ?>
                                                                                <p class="text-success"><strong><?php echo htmlspecialchars($admin['inv_status']); ?></strong></p>
                                                                            <?php else: ?>
                                                                                <p class="text-danger"><strong><?php echo htmlspecialchars($admin['inv_status']); ?></strong></p>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card shadow  mt-3">
                                        <div class="card-body">
                                            <h5 class="card-title greentitle mb-3">Job Details</h5>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">WO</th>
                                                                    <th scope="col">Date / Time</th>
                                                                    <th scope="col">Service Type</th>
                                                                    <th scope="col">Priority</th>
                                                                    <th scope="col">Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($admin['work_order_number']); ?></td>
                                                                    <td><?php echo htmlspecialchars($admin['work_order_date']); ?></td>
                                                                    <td><?php echo htmlspecialchars($admin['service_name']); ?></td>
                                                                    <?php if ($admin['priority_name'] == 'Urgent'): ?>
                                                                    <td><span class="badge text-bg-danger"><?php echo htmlspecialchars($admin['priority_name']); ?></span></td>
                                                                    <?php elseif ($admin['priority_name'] == 'Medium'): ?>
                                                                    <td><span class="badge text-bg-warning"><?php echo htmlspecialchars($admin['priority_name']); ?></span></td>
                                                                    <?php else: ?>
                                                                    <td><span class="badge text-bg-success"><?php echo htmlspecialchars($admin['priority_name']); ?></span></td>  
                                                                    <?php endif; ?>
                                                                    <td><?php echo htmlspecialchars($admin['status']); ?></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12">
                                                    <p><strong>Job Description</strong>: <?php echo htmlspecialchars($admin['description']); ?></p>
                                                </div>
                                            </div>
                                            <div id="hideafterchange">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <button class="btn btn-success col-12" data-bs-toggle="modal" data-bs-target="#attachmentModal">Add Attachments</button>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <button class="btn btn-success col-12" data-bs-toggle="modal" data-bs-target="#notesModal">Add Notes</button>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <select name="wo_status" id="wo_status" class="form-select" data-id="<?php echo $admin['work_order_number']; ?>">
                                                            <option value="<?php echo htmlspecialchars($admin['status']); ?>"><?php echo htmlspecialchars($admin['status']); ?></option>
                                                            <option value="On Hold">On Hold</option>
                                                            <option value="Completed">Completed</option>
                                                            <option value="Completed No Charge">Completed No Charge</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal -->
        <div class="modal fade" id="attachmentModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Attachments</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="uploadProgressContainer" style="display:none; width: 100%; background: #eee; border-radius: 5px; margin-top: 10px;">
                            <div id="uploadProgressBar" style="height: 10px; width: 0%; background: #4caf50; border-radius: 5px; transition: width 0.2s;"></div>
                        </div>
                        <input type="text" class="form-control" hidden id="work_order_number" value="<?php echo htmlspecialchars($admin['work_order_number']); ?>">
                        <div class="col-sm-12 mt-3">
                            <label for="not_for_invoices" class="form-label">Attach files for this request [Not for Invoices]</label>
                            <input type="file" name="not_for_invoices" id="not_for_invoices" class="form-control">
                        </div>
                        <div class="col-sm-12 mt-3">
                            <label for="hidden_request" class="form-label">Attach files for this request [Hidden]</label>
                            <input type="file" name="hidden_request" id="hidden_request" class="form-control">
                        </div>
                        <div class="col-sm-12 mt-3">
                            <label for="any_document" class="form-label">Upload any document that is related to work order – Like Risk assessment, Site visit monitoring</label>
                            <input type="file" name="any_document" id="any_document" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Notes <span class="hidden_tenancy">(Hidden from Tenancy)</span></h6>
                        <input type="text" class="form-control" name="wo_notes" id="wo_notes" value="<?php echo htmlspecialchars($admin['work_order_number']); ?>" hidden>
                        <textarea name="notes_comments" id="notes_comments" class="form-control"></textarea>
                        <button class="btn btn-success mt-3" type="button" id="add_note">Add Notes</button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Upload Invoice</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="invoice_content">This Work Order is Awaiting Upload of an Invoice</p>
                    <p class="invoice_content">The Invoice must not exceed the approved value of $<?php echo htmlspecialchars($admin['value']); ?></p>
                    <form id="upload_invoice">
                        <div class="row">
                            <input type="text" class="form-control" hidden id="work_order_number_new" name="work_order_number_new" value="<?php echo htmlspecialchars($admin['work_order_number']); ?>">
                            <div class="col-sm-4">
                                <div class="mb-3">
                                    <label for="invoice_date" class="form-label">Invoice Date</label>
                                    <input type="date" name="invoice_date" id="invoice_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="mb-3">
                                    <label for="invoice_number" class="form-label">Invoice Number</label>
                                    <input type="text" name="invoice_number" id="invoice_number" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="mb-3">
                                    <label for="sub_total" class="form-label">Sub Total</label>
                                    <input type="text" name="sub_total" id="sub_total" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="mb-3">
                                    <label for="upload_invoice_pdf" class="form-label">Upload Invoice PDF</label>
                                    <input type="file" name="upload_invoice_pdf" id="upload_invoice_pdf" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="mb-3">
                                    <button class="btn btn-success" type="submit">Submit Invoice</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                </div>
            </div>
        </div>

        <div id="uploadProgressContainer" style="display:none; width: 100%; background: #eee; border-radius: 5px; margin-top: 10px;">
            <div id="uploadProgressBar" style="height: 10px; width: 0%; background: #4caf50; border-radius: 5px; transition: width 0.2s;"></div>
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

        <script>
        function getFileIcon(filename) {
            const ext = filename.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                return '<i class="fa fa-file-image-o" style="color:#ff9800;"></i>';
            } else if (ext === 'pdf') {
                return '<i class="fa fa-file-pdf-o" style="color:#e53935;"></i>';
            } else if (['doc', 'docx'].includes(ext)) {
                return '<i class="fa fa-file-word-o" style="color:#1e88e5;"></i>';
            } else if (['xls', 'xlsx', 'csv'].includes(ext)) {
                return '<i class="fa fa-file-excel-o" style="color:#43a047;"></i>';
            } else if (['zip', 'rar', '7z'].includes(ext)) {
                return '<i class="fa fa-file-archive-o" style="color:#6d4c41;"></i>';
            } else {
                return '<i class="fa fa-file-o"></i>';
            }
        }

        function uploadFile(inputId, tableName) {
            let file = document.getElementById(inputId).files[0];
            let workOrder = document.getElementById('work_order_number').value;
            if (!file) return;

            let formData = new FormData();
            formData.append("file", file);
            formData.append("work_order_number", workOrder);
            formData.append("table_name", tableName);
            // Show progress bar
            let progressContainer = document.getElementById("uploadProgressContainer");
            let progressBar = document.getElementById("uploadProgressBar");
            progressContainer.style.display = "block";
            progressBar.style.width = "0%";

            let xhr = new XMLHttpRequest();

            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    let percent = (e.loaded / e.total) * 100;
                    progressBar.style.width = percent + "%";
                }
            };

            xhr.onload = function () {
                progressContainer.style.display = "none";
                if (xhr.status === 200) {
                    let data = JSON.parse(xhr.responseText);
                    if (data.status === "success") {
                        loadFiles(tableName);
                        document.getElementById(inputId).value = "";
                    } else {
                        alert(data.message);
                    }
                } else {
                    alert("Upload failed. Server returned status " + xhr.status);
                }
            };

            xhr.onerror = function () {
                progressContainer.style.display = "none";
                alert("An error occurred during file upload.");
            };

            xhr.open("POST", "upload.php", true);
            xhr.send(formData);
        }

        function loadFiles(tableName) {
            let workOrder = document.getElementById('work_order_number').value;
            fetch(`fetch_files.php?table=${tableName}&work_order_number=${workOrder}`)
            .then(res => res.json())
            .then(files => {
                let container = document.getElementById(`list_${tableName}`);
                container.innerHTML = "";
                files.forEach(file => {
                    const filename = file.file_path.split('/').pop();
                    const icon = getFileIcon(filename);
                    container.innerHTML += `
                        <div style="display:flex;align-items:center;gap:5px;">
                            ${icon} <a href="${file.file_path}" target="_blank">${filename}</a>
                            <button onclick="deleteFile('${tableName}', '${file.id}')" style="border:none;background:none;color:red;cursor:pointer;">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    `;
                });
            });
        }

        function deleteFile(tableName, fileId) {
            if (!confirm("Are you sure you want to delete this file?")) return;

            fetch("delete-file.php", {
                method: "POST",
                body: new URLSearchParams({
                    table_name: tableName,
                    id: fileId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    loadFiles(tableName);
                } else {
                    alert(data.message);
                }
            });
        }

        document.getElementById("not_for_invoices").addEventListener("change", () => uploadFile("not_for_invoices", "not_for_invoices"));
        document.getElementById("hidden_request").addEventListener("change", () => uploadFile("hidden_request", "hidden_request"));
        document.getElementById("any_document").addEventListener("change", () => uploadFile("any_document", "any_document"));

        ["not_for_invoices", "hidden_request", "any_document"].forEach(loadFiles);
    </script>

    <script>
        $(document).ready(function(){

            var work_order_number = $("#wo_notes").val().trim(); // dynamically set this in PHP
            // Load notes on page load
            $.ajax({
                url: "fetch_notes.php",
                type: "GET",
                data: { work_order_number: work_order_number },
                dataType: "json",
                success: function(notes){
                    $("#display_notes").empty();
                    if(notes.length > 0){
                        notes.forEach(function(note){
                            $("#display_notes").append("<p>" + note + "</p>");
                        });
                    } else {
                        $("#display_notes").html("<p>No notes yet.</p>");
                    }
                }
            });

            // Add new note
            $("#add_note").click(function(){
                var note = $("#notes_comments").val().trim();
                if(note === ""){
                    alert("Please enter a note.");
                    return;
                }
                $.ajax({
                    url: "save_note.php",
                    type: "POST",
                    data: {
                        work_order_number: work_order_number,
                        notes_comment: note
                    },
                    success: function(response){
                        if(response === "success"){
                            $("#display_notes").prepend("<p>" + note + "</p>");
                            $("#notes_comments").val("");
                        } else {
                            alert("Error saving note.");
                        }
                    }
                });
            });
        });

        $(document).ready(function(){
            // Initially hide
            if($("#wo_status").val() === "Completed" || $("#wo_status").val() === "Await Invoice" || $("#wo_status").val() === "Await Approval" || $("#wo_status").val() === "Final Complete"){
                $("#billing_details").show();
                $("#hideafterchange").hide();
            } else {
                $("#billing_details").hide();
            }

            // On change
            $("#wo_status").on("change", function(){
                let status = $(this).val();
                let workOrderId = $(this).data("id");

                // Show/hide billing details
                if(status === "Completed"){
                    $("#billing_details").slideDown("slow");
                    $("#hideafterchange").hide();
                } else {
                    $("#billing_details").slideUp("slow");
                    $("#hideafterchange").hide();
                }

                // AJAX call to update status in DB
                $.ajax({
                    url: "update_status.php",
                    type: "POST",
                    data: { id: workOrderId, status: status },
                    success: function(response){
                        console.log("Status updated: " + response);
                    }
                });
            });
        });

        $(document).ready(function(){
            $("#billingForm").on("submit", function(e){
                e.preventDefault(); // stop normal form submission

                $.ajax({
                    url: "update_billing.php",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response){
                        // hide form and show success div
                        location.reload();

                        console.log(response); // debug, can also show inside success div
                    },
                    error: function(){
                        alert("Error updating billing details!");
                    }
                });
            });
        });

        $(document).on("submit", "#upload_invoice", function (e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "submit-invoice.php",   // PHP file to handle request
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    // Optional: disable button / show loader
                },
                success: function (response) {
                    alert(response);
                    $("#upload_invoice")[0].reset();
                },
                error: function () {
                    alert("Something went wrong.");
                }
            });
        });
    </script>

    
    </body>
</html>