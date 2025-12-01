<?php
header('Content-Type: application/json');
include('includes/connection.php');

if (isset($_POST['invoice_amouunt'], $_POST['record_id'])) {
    $invoice_amount = floatval($_POST['invoice_amouunt']);
    $record_id = intval($_POST['record_id']);

    // Fetch actual value from database
    $sql = "SELECT value FROM work_order WHERE work_order_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $record_id);
    $stmt->execute();
    $stmt->bind_result($value);
    $stmt->fetch();
    $stmt->close();

    if ($invoice_amount > $value) {
        echo json_encode([
            "status" => "error",
            "message" => "Invoice amount cannot exceed the actual value ($value)."
        ]);
    } else {
        echo json_encode([
            "status" => "success",
            "message" => "Valid amount"
        ]);
    }
}
