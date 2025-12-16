<?php
session_start();
include("../includes/connection.php");

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Collect POST Data
$work_order_no = $_POST['work_order_no'];
$siteid = $_POST['sitename'];
$service_type = $_POST['service_type'];
$priority = $_POST['priority'];
$user_select = $_POST['user_select'];
$wo_amount = $_POST['wo_amount'];
$job_description = $_POST['job_description'];
$work_begin = $_POST['work_begin'];
$expected_completion = $_POST['expected_completion'];
$logged_by = $_SESSION["admin_id"];
$status = "In Progress";

// Get Users Email
$user_email = 'SELECT name, email FROM users WHERE user_id = ?';
$user_email_stmt = $conn->prepare($user_email);
$user_email_stmt->bind_param("s", $user_select);
$user_email_stmt->execute();    
$user_email_result = $user_email_stmt->get_result();
if ($user_email_result->num_rows > 0) {
    $user_email_row = $user_email_result->fetch_assoc();
    $user_email_address = $user_email_row['email'];
    $user_name = $user_email_row['name'];
} else {
    $user_email_address = "Unknown Email";
}

// Fetch Service Type Name
$stype_query = "SELECT service_name FROM service_type_tbl WHERE service_id = ?";
$stype_stmt = $conn->prepare($stype_query);
$stype_stmt->bind_param("s", $service_type);
$stype_stmt->execute();
$stype_result = $stype_stmt->get_result();

if ($stype_result->num_rows > 0) {
    $stype_row = $stype_result->fetch_assoc();
    $service_type_name = $stype_row['service_name'];
} else {
    $service_type_name = "Unknown Service Type";
}

// Fetch Priority Name
$priority_query = "SELECT priority_name FROM priority_tbl WHERE priority_id = ?";
$priority_stmt = $conn->prepare($priority_query);
$priority_stmt->bind_param("s", $priority);
$priority_stmt->execute();
$priority_result = $priority_stmt->get_result();

if ($priority_result->num_rows > 0) {
    $priority_row = $priority_result->fetch_assoc();
    $priority_name = $priority_row['priority_name'];
} else {
    $priority_name = "Unknown Priority";
}

// Fetch Logged By Name
$logged_query = "SELECT admin_name FROM admin WHERE admin_id = ?";
$logged_stmt = $conn->prepare($logged_query);
$logged_stmt->bind_param("s", $logged_by);
$logged_stmt->execute();
$logged_result = $logged_stmt->get_result();

if ($logged_result->num_rows > 0) {
    $logged_row = $logged_result->fetch_assoc();
    $logged_by_name = $logged_row['admin_name'];
} else {
    $logged_by_name = "Unknown User";
}

// Fetch site name
$site_query = "SELECT site_name FROM site_tbl WHERE site_id = ?";
$site_stmt = $conn->prepare($site_query);
$site_stmt->bind_param("s", $siteid);
$site_stmt->execute();
$site_result = $site_stmt->get_result();

if ($site_result && $site_result->num_rows > 0) {

    $site_row = $site_result->fetch_assoc();
    $site_name = $site_row['site_name'];

    // Insert Work Order
    $sql = "INSERT INTO work_order (
                work_order_number, site_id, site_name, type, priority, 
                assigned_to, logged_by, description, value, 
                work_begin_date, expected_completion_date, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssissdsss",
        $work_order_no, $siteid, $site_name, $service_type, $priority,
        $user_select, $logged_by, $job_description, $wo_amount,
        $work_begin, $expected_completion, $status
    );

    if ($stmt->execute()) {

        echo "Work order added successfully.";

        // =======================
        // FILE UPLOAD HANDLING
        // =======================
        $upload_dir = __DIR__ . "/../downloads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_download_links = ""; // To use inside email

        if (!empty($_FILES['add_attachments']['name'][0])) {

            foreach ($_FILES['add_attachments']['tmp_name'] as $key => $tmp_name) {

                if ($_FILES['add_attachments']['error'][$key] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $file_name = basename($_FILES['add_attachments']['name'][$key]);
                $safe_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $file_name);
                $target_file = $upload_dir . $safe_name;

                if (move_uploaded_file($tmp_name, $target_file)) {

                    $file_path_db = "downloads/" . $safe_name;

                    // Insert into downloads table
                    $download_sql = "INSERT INTO downloads (work_order_number, file_path) VALUES (?, ?)";
                    $download_stmt = $conn->prepare($download_sql);
                    $download_stmt->bind_param("ss", $work_order_no, $file_path_db);
                    $download_stmt->execute();
                    $download_stmt->close();

                    // Create public link
                    $public_link = "https://yourdomain.com/" . $file_path_db;
                    $file_download_links .= "<a href='$public_link' target='_blank'>$safe_name</a>";

                }
            }
        } else {
            $file_download_links = "No files uploaded.";
        }

        // ==========================
        // SEND EMAIL NOTIFICATION
        // ==========================
        $mail = new PHPMailer(true);

        try {
            // Server Settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@webp.com.au';  // change
            $mail->Password   = 'surz elpv ttip nkxg';     // change
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender & Recipient
            $mail->setFrom('info@webp.com.au', 'Work Order System');
            $mail->addAddress($user_email_address, $user_name); // change

            // Email Subject
            $mail->Subject = "New Work Order Created - #$work_order_no";

            // Email Body
            $mail->isHTML(true);
            $mail->Body = "
            <div style='font-family: Helvetica, sans-serif; background:#f5f5f5; padding:25px; margin:0;'>

                <!-- Card Wrapper -->
                <div style='max-width:764px; margin:0 auto; background:#ffffff; border-radius:10px; border: 1px solid #d9d9d9; box-shadow:0 4px 18px rgba(0,0,0,0.15); overflow:hidden;'>

                    <!-- Header -->
                    <div style='background:#0B7937; padding:35px 0; text-align:center;'>
                        <img src='https://secqld.services/wp-content/uploads/2024/10/SecQLD-Logo-Email-Template.png' 
                            alt='SEC QLD' 
                            style='width:150px; height:auto; display:block; margin:0 auto;'>
                    </div>

                    <!-- Body -->
                    <div style='padding:35px;'>

                        <h2 style='text-align:center; font-weight:600; margin-top:0; margin-bottom:15px; color:#222;'>
                            New Work Order Created – $work_order_no
                        </h2>

                        <p style='color:#b70000; text-align:center; font-size:15px; margin-top:0; margin-bottom:25px;'>
                            Works not to exceed $$wo_amount (ex GST) without further approval from PM or FM.
                        </p>

                        <!-- Address Section -->
                        <table width='100%' cellspacing='0' cellpadding='0' style='margin-bottom:25px; font-size:15px; color:#333;'>
                            <tr>
                                <td width='50%' valign='top' style='padding:10px 15px;'>
                                    <strong>Please invoice to:</strong><br>
                                    Sec QLD,<br>
                                    GPO Box 389 Brisbane<br>
                                    Markets QLD 4106
                                </td>
                                <td width='50%' valign='top' style='padding:10px 15px;'>
                                    <strong>Request Issued To ABN:</strong><br>
                                    98 679 546 126 Sec QLD<br><br>
                                    Please quote Work Order Number <strong>$work_order_no</strong><br>
                                    on your invoice for prompt payment.
                                </td>
                            </tr>
                        </table>

                        <p style='color:#b70000; text-align:center; margin-bottom:10px; font-size:14px;'>
                            Invoices must be uploaded directly to the Work Order (not emailed or posted).
                        </p>

                        <p style='text-align:center; color:#333; margin-bottom:35px;'>
                            You will be advised when you need to upload your invoice.
                        </p>

                        <!-- Work Order Details Table -->
                        <table width='100%' border='0' cellspacing='0' cellpadding='10' 
                            style='border-collapse:collapse; background:#fafafa; border-radius:8px; overflow:hidden; font-size:15px;'>

                            <tr style='background:#e7e7e7;'>
                                <td width='40%' style='font-weight:600;'>Work Order Number</td>
                                <td>$work_order_no</td>
                            </tr>

                            <tr>
                                <td style='font-weight:600;'>Site Name</td>
                                <td>$site_name</td>
                            </tr>

                            <tr style='background:#e7e7e7;'>
                                <td style='font-weight:600;'>Service Type</td>
                                <td>$service_type_name</td>
                            </tr>

                            <tr>
                                <td style='font-weight:600;'>Priority</td>
                                <td>$priority_name</td>
                            </tr>

                            <tr style='background:#e7e7e7;'>
                                <td style='font-weight:600;'>Work Order Amount</td>
                                <td>$$wo_amount</td>
                            </tr>

                            <tr>
                                <td style='font-weight:600;'>Details</td>
                                <td>$job_description</td>
                            </tr>

                            <tr style='background:#e7e7e7;'>
                                <td style='font-weight:600;'>Work Begin Date</td>
                                <td>$work_begin</td>
                            </tr>

                            <tr>
                                <td style='font-weight:600;'>Expected Completion</td>
                                <td>$expected_completion</td>
                            </tr>

                            <tr style='background:#e7e7e7;'>
                                <td style='font-weight:600;'>Logged By</td>
                                <td>$logged_by_name</td>
                            </tr>

                            <tr>
                                <td style='font-weight:600;'>Status</td>
                                <td>$status</td>
                            </tr>

                            <tr style='background:#e7e7e7;'>
                                <td style='font-weight:600;'>Download Link</td>
                                <td>$file_download_links</td>
                            </tr>
                        </table>

                        <!-- CTA BUTTON -->
                        <div style='text-align:center; margin-top:40px;'>
                            <a href='https://webpst.com.au/secqldsoftware'
                                style='background:#0B7937; color:#ffffff; padding:12px 25px; text-decoration:none; 
                                    border-radius:6px; font-size:16px; font-weight:600; display:inline-block;'>
                                View Work Order
                            </a>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div style='background:#f0f0f0; padding:20px; text-align:center; border-top:2px solid #0B7937;'>
                        <p style='margin:0; font-size:13px; color:#555;'>
                            This is an automated email from the Work Order System.<br>
                            © " . date('Y') . " SEC QLD. All rights reserved.
                        </p>
                    </div>

                </div>

            </div>
            ";

            $mail->send();

            echo " Email sent.";

        } catch (Exception $e) {
            echo " Email failed: {$mail->ErrorInfo}";
        }

    } else {
        echo "❌ Error during work order insert: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "❌ Invalid site name.";
}

$site_stmt->close();
$conn->close();
?>
