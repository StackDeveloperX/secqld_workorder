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
        <?php
            function getFileIcon($filePath)
            {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

                switch ($ext) {
                    case 'pdf':
                        return '<i class="fas fa-file-pdf text-danger me-2"></i>';
                    case 'doc':
                    case 'docx':
                        return '<i class="fas fa-file-word text-primary me-2"></i>';
                    case 'xls':
                    case 'xlsx':
                        return '<i class="fas fa-file-excel text-success me-2"></i>';
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                        return '<i class="fas fa-file-image text-warning me-2"></i>';
                    case 'zip':
                    case 'rar':
                        return '<i class="fas fa-file-archive text-secondary me-2"></i>';
                    default:
                        return '<i class="fas fa-file text-muted me-2"></i>';
                }
            }
        ?>
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
                            <a href="wo_status.php"><p class="active-menu"><span><i class="fa-solid fa-circle-user"></i> Work Order Status</span></p></a>
                            <a href="recurring_wo_status.php"><p class="menu"><span><i class="fa-solid fa-circle-user"></i> Recurring Status</span></p></a>
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
                        <?php
                        if (isset($_GET['wo'])) {
                        include_once('includes/connection.php');
                        include_once('../function.php');

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

                                    <div class="card shadow">
                                        <div class="card-body">
                                            <?php
                                                function fetchDocuments($conn, $table, $workOrderNumber)
                                                {
                                                    $stmt = $conn->prepare("
                                                        SELECT file_path, uploaded_at 
                                                        FROM {$table}
                                                        WHERE work_order_number = ?
                                                        ORDER BY uploaded_at DESC
                                                    ");
                                                    $stmt->bind_param("s", $workOrderNumber);
                                                    $stmt->execute();
                                                    return $stmt->get_result();
                                                }
                                            ?>
                                            <?php
                                                include('includes/connection.php');

                                                $work_order_number = $decrypted ?? ''; // or from your context
                                            ?>
                                            <h5 class="card-title greentitle">Attachments</h5>

                                            <h6 class="mt-3">Attach files for this request [Not for Invoices]</h6>
                                            <div id="list_not_for_invoices_admin">
                                                <?php
                                                    $docs = fetchDocuments($conn, 'not_for_invoices', $work_order_number);
                                                    if ($docs->num_rows > 0):
                                                        echo '<ul class="list-group">';
                                                        while ($row = $docs->fetch_assoc()):
                                                    ?>
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="text-decoration-none">
                                                                    <?php echo getFileIcon($row['file_path']); ?>
                                                                    <?php echo basename($row['file_path']); ?>
                                                                </a>
                                                                <small class="text-muted">
                                                                    <?php echo date('d M Y, h:i A', strtotime($row['uploaded_at'])); ?>
                                                                </small>
                                                            </li>
                                                    <?php
                                                        endwhile;
                                                        echo '</ul>';
                                                    else:
                                                        echo '<p class="text-muted">No documents uploaded.</p>';
                                                    endif;
                                                ?>
                                            </div>

                                            <h6 class="mt-3">Attach files for this request [Hidden]</h6>
                                            <div id="list_hidden_request_admin">
                                                <?php
                                                    $docs = fetchDocuments($conn, 'hidden_request', $work_order_number);
                                                    if ($docs->num_rows > 0):
                                                        echo '<ul class="list-group">';
                                                        while ($row = $docs->fetch_assoc()):
                                                    ?>
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="text-decoration-none">
                                                                    <?php echo getFileIcon($row['file_path']); ?>
                                                                    <?php echo basename($row['file_path']); ?>
                                                                </a>
                                                                <small class="text-muted">
                                                                    <?php echo date('d M Y, h:i A', strtotime($row['uploaded_at'])); ?>
                                                                </small>
                                                            </li>
                                                    <?php
                                                        endwhile;
                                                        echo '</ul>';
                                                    else:
                                                        echo '<p class="text-muted">No documents uploaded.</p>';
                                                    endif;
                                                ?>
                                            </div>

                                            <h6 class="mt-3">Any document that is related to work order</h6>
                                            <div id="list_any_document_admin">
                                                <?php
                                                    $docs = fetchDocuments($conn, 'any_document', $work_order_number);
                                                    if ($docs->num_rows > 0):
                                                        echo '<ul class="list-group">';
                                                        while ($row = $docs->fetch_assoc()):
                                                    ?>
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="text-decoration-none">
                                                                    <?php echo getFileIcon($row['file_path']); ?>
                                                                    <?php echo basename($row['file_path']); ?>
                                                                </a>
                                                                <small class="text-muted">
                                                                    <?php echo date('d M Y, h:i A', strtotime($row['uploaded_at'])); ?>
                                                                </small>
                                                            </li>
                                                    <?php
                                                        endwhile;
                                                        echo '</ul>';
                                                    else:
                                                        echo '<p class="text-muted">No documents uploaded.</p>';
                                                    endif;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card shadow mt-3">
                                        <div class="card-body">
                                            <h5 class="card-title greentitle">Notes</h5>

                                            <div id="display_notes">
                                                <?php
                                                    function fetchNotes($conn, $workOrderNumber)
                                                    {
                                                        $stmt = $conn->prepare("
                                                            SELECT notes_comment, created_at
                                                            FROM notes
                                                            WHERE work_order_number = ?
                                                            ORDER BY created_at DESC
                                                        ");
                                                        $stmt->bind_param("s", $workOrderNumber);
                                                        $stmt->execute();
                                                        return $stmt->get_result();
                                                    }
                                                ?>
                                                <?php
                                                include('includes/connection.php');

                                                $work_order_number = $decrypted ?? '';

                                                $notes = fetchNotes($conn, $work_order_number);

                                                if ($notes->num_rows > 0):
                                                    while ($row = $notes->fetch_assoc()):
                                                ?>
                                                        <div class="border rounded p-3 mb-3 bg-light">
                                                            <p class="mb-2">
                                                                <?php echo nl2br(htmlspecialchars($row['notes_comment'])); ?>
                                                            </p>
                                                            <small class="text-muted">
                                                                <i class="fas fa-clock me-1"></i>
                                                                <?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?>
                                                            </small>
                                                        </div>
                                                <?php
                                                    endwhile;
                                                else:
                                                    echo '<p class="text-muted">No notes available for this work order.</p>';
                                                endif;
                                                ?>
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
                                                                        <td><button class="btn btn-success" type="submit" id="updateBillingBtn">Update</button></td>
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
                                                                        <th scope="col">Date</th>
                                                                        <th scope="col">Inv No</th>
                                                                        <th scope="col">Site Name</th>
                                                                        <th scope="col">Value</th>
                                                                        <th scope="col">Inv Amount</th>
                                                                        <th scope="col">Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><p><?php echo htmlspecialchars($admin['work_order_date']); ?></p></td>
                                                                        <td><p><?php echo htmlspecialchars($admin['inv_number']); ?></p></td>
                                                                        <td><p><?php echo htmlspecialchars($admin['site_name']); ?></p></td>
                                                                        <td><p>$<?php echo htmlspecialchars($admin['value']); ?></p></td>
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
                                    
                                    <?php
                                        if ($admin['status'] == "Await Approval"):
                                    ?>
                                        <div class="mt-3 text-center">
                                            <button class="btn btn-success btn-sm approve" data-id="<?php echo htmlspecialchars(string: $admin['work_order_number']); ?>">Approve</button>
                                            <button class="btn btn-danger btn-sm decline" data-id="<?php echo htmlspecialchars($admin['work_order_number']); ?>">Decline</button>
                                        </div>
                                    <?php endif; ?>
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

        <div id="uploadProgressContainer" style="display:none; width: 100%; background: #eee; border-radius: 5px; margin-top: 10px;">
            <div id="uploadProgressBar" style="height: 10px; width: 0%; background: #4caf50; border-radius: 5px; transition: width 0.2s;"></div>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

                let $btn = $("#updateBillingBtn");
                // Disable button & change text
                $btn.prop("disabled", true).text("Updating...");

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

            let form = this;
            let formData = new FormData(form);
            let $btn = $("#submitInvoiceBtn");

            // Disable button + show spinner
            $btn.prop("disabled", true).html(
                '<span class="spinner-border spinner-border-sm"></span> Submitting...'
            );

            $.ajax({
                url: "submit-invoice.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,

                success: function (response) {
                    if (response.trim() === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Invoice Submitted",
                            text: "Invoice uploaded successfully",
                            timer: 1500,
                            showConfirmButton: false
                        });

                        form.reset();
                        location.reload();
                    } else {
                        Swal.fire("Error", response, "error");
                    }

                    // Restore button
                    resetSubmitButton();
                },

                error: function () {
                    Swal.fire("Error", "Something went wrong. Please try again.", "error");
                    resetSubmitButton();
                }
            });

            function resetSubmitButton() {
                $btn.prop("disabled", false).text("Submit Invoice");
            }
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

                $.post("update_status.php", { id: id, status: "Approved" })
                    .done(function (response) {
                        if (response.trim() === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Approved',
                                text: 'Work order approved successfully',
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

                $.post("update_status.php", {
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
                            text: 'Work order rejected successfully',
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

            // RESET BUTTON HELPER
            function resetButton($btn, text) {
                $btn.prop('disabled', false).text(text);
            }
        </script>
    </body>
</html>