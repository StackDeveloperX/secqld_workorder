<?php
include('includes/connection.php');

header('Content-Type: application/json');

if (!isset($_POST['invoice_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$invoice_id = intval($_POST['invoice_id']);

$conn->begin_transaction();

try {

    // 1️⃣ Fetch work order number from invoice
    $stmt = $conn->prepare(
        "SELECT work_order_number 
         FROM invoice_tbl 
         WHERE id = ?"
    );
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Invoice not found");
    }

    $invoice = $result->fetch_assoc();
    $work_order_number = $invoice['work_order_number'];

    // 2️⃣ Mark invoice as Paid
    $stmt = $conn->prepare(
        "UPDATE invoice_tbl 
         SET payment_status = 'Paid' 
         WHERE id = ?"
    );
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();

    // 3️⃣ Update work order status
    $stmt = $conn->prepare(
        "UPDATE work_order
         SET status = 'Invoice Processed'
         WHERE work_order_number = ?"
    );
    $stmt->bind_param("s", $work_order_number);
    $stmt->execute();


    // 3️⃣ Update Recurring work order status
    $stmt = $conn->prepare(
        "UPDATE recurring_work_orders
         SET status = 'Invoice Processed'
         WHERE work_order_number = ?"
    );
    $stmt->bind_param("s", $work_order_number);
    $stmt->execute();

    // ✅ Commit everything
    $conn->commit();

    echo json_encode(['status' => 'success']);

} catch (Exception $e) {

    // ❌ Rollback if anything fails
    $conn->rollback();

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
