<?php
include('includes/connection.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['contract_id'])) {
    echo "Invalid request";
    exit;
}

$contract_id = (int)$_POST['contract_id'];

$stmt = $conn->prepare("
    UPDATE recurring_contracts
    SET status = 'Cancelled',
        cancelled_on = CURDATE()
    WHERE contract_id = ?
      AND status = 'Active'
");

$stmt->bind_param("i", $contract_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo "success";
} else {
    echo "Unable to cancel contract or already cancelled.";
}

$stmt->close();
$conn->close();
