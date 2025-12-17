<?php
include('includes/connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status']; // Approved or Rejected
    $reason = isset($_POST['reason']) ? $_POST['reason'] : null;
    $wo_status_approve = "Await Invoice";
    $wo_status_reject = "Req Attention";

    if ($status === "Rejected") {
        $stmt = $conn->prepare("UPDATE recurring_work_orders SET status=?, inv_status=?, inv_reject_reason=?, actual_value = 0 WHERE work_order_number=?");
        $stmt->bind_param("ssss", $wo_status_reject, $status, $reason, $id);
    } else {
        $stmt = $conn->prepare("UPDATE recurring_work_orders SET status=?, inv_status=?, inv_reject_reason=NULL WHERE work_order_number=?");
        $stmt->bind_param("sss", $wo_status_approve, $status, $id);
    }

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
