<?php
include('includes/connection.php');

header('Content-Type: application/json');

if (!isset($_POST['invoice_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$invoice_id = intval($_POST['invoice_id']);

$stmt = $conn->prepare(
    "UPDATE invoice_tbl 
     SET payment_status = 'Paid' 
     WHERE id = ?"
);

$stmt->bind_param("i", $invoice_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database update failed'
    ]);
}
