<?php
session_start();
include("includes/connection.php");

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'admin/PHPMailer/src/Exception.php';
require 'admin/PHPMailer/src/PHPMailer.php';
require 'admin/PHPMailer/src/SMTP.php';

// Collect form data
$work_order_number_new  = $_POST['work_order_number_new'];
$invoice_date = $_POST['invoice_date'];
$invoice_number = $_POST['invoice_number'];
$sub_total = $_POST['sub_total'];

$admin_email     = "webdeveloper@webp.com.au";  // change
$uploaded_file   = "";

// ---------------------------
// Handle File Upload
// ---------------------------
if (!empty($_FILES['upload_invoice_pdf']['name'])) {

    $upload_dir = __DIR__ . "/invoice_uploads/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_tmp  = $_FILES['upload_invoice_pdf']['tmp_name'];
    $file_name = basename($_FILES['upload_invoice_pdf']['name']);
    $safe_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $file_name);

    $target_file = $upload_dir . $safe_name;

    if (move_uploaded_file($file_tmp, $target_file)) {
        $uploaded_file = $target_file;
    } else {
        echo "Failed to upload PDF.";
        exit;
    }
} else {
    echo "Please upload a PDF file.";
    exit;
}

// ---------------------------
// Insert into invoice table
// ---------------------------
$sql = "INSERT INTO invoice_tbl (work_order_number, invoice_date, invoice_number, sub_total, file_path)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $work_order_number_new, $invoice_date, $invoice_number, $sub_total, $safe_name);

if (!$stmt->execute()) {
    echo "Database Error: " . $stmt->error;
    exit;
}
$stmt->close();

// ---------------------------
// Update Status TO FINAL COMPLETE
// ---------------------------
$sql_new = "UPDATE work_order SET status='Final Complete', inv_status='Submitted' WHERE work_order_number=?";
$stmt_new = $conn->prepare($sql_new);
$stmt_new->bind_param("s", $work_order_number_new);

if (!$stmt_new->execute()) {
    echo "Database Error: " . $stmt_new->error;
    exit;
}
$stmt_new->close();
// ---------------------------
// SEND EMAIL TO ADMIN
// ---------------------------
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@webp.com.au'; 
    $mail->Password   = 'surz elpv ttip nkxg'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender & Recipient
    $mail->setFrom('info@webp.com.au', 'Work Order System');
    $mail->addAddress($admin_email);

    // Attach the PDF
    $mail->addAttachment($uploaded_file, $safe_name);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "New Invoice Submitted - #" . $invoice_number;
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
                            Invoice Submitted For – $work_order_number_new
                        </h2>

                        <!-- Work Order Details Table -->
                        <table width='100%' border='0' cellspacing='0' cellpadding='10' 
                            style='border-collapse:collapse; background:#fafafa; border-radius:8px; overflow:hidden; font-size:15px;'>

                            <tr style='background:#e7e7e7;'>
                                <td width='40%' style='font-weight:600;'>Work Order Number</td>
                                <td>$work_order_number_new</td>
                            </tr>

                            <tr>
                                <td style='font-weight:600;'>Invoice Date</td>
                                <td>$invoice_date</td>
                            </tr>

                            <tr style='background:#e7e7e7;'>
                                <td style='font-weight:600;'>Invoice Number</td>
                                <td>$invoice_number</td>
                            </tr>

                            <tr>
                                <td style='font-weight:600;'>Total Amount</td>
                                <td>$ $sub_total</td>
                            </tr>
                        </table>
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

    echo "Invoice submitted successfully!";

} catch (Exception $e) {
    echo "Email Error: {$mail->ErrorInfo}";
}

$conn->close();
?>
