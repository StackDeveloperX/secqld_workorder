<?php
include('includes/connection.php'); // adjust path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['work_order_id'];
    $invoice_number = $_POST['invoice_number'];
    $work_began_date = $_POST['work_began_date'];
    $expected_completion = $_POST['expected_completion'];
    $actual_completion = $_POST['actual_completion'];
    $invoice_amouunt = $_POST['invoice_amouunt'];
    $inv_status = "Waiting For Approval";
    $status = "Await Approval";

    // Prepare statement
    $stmt = $conn->prepare("UPDATE work_order 
        SET work_begin_date=?, expected_completion_date=?, actual_completion_date=?, actual_value=?, inv_number=?, status=?, inv_status=? 
        WHERE work_order_number=?");

    // ⚠️ I assumed `value` = invoice amount and `description` = invoice number (since your DB table doesn’t have separate invoice columns).
    $stmt->bind_param("sssdssss", $work_began_date, $expected_completion, $actual_completion, $invoice_amouunt, $invoice_number, $status, $inv_status, $id);

    if ($stmt->execute()) {
        echo "Billing details updated successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
?>