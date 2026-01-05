<?php
include('includes/connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id     = $_POST['id'];       // work_order_number
    $status = $_POST['status'];   // Approved / Rejected
    $reason = $_POST['reason'] ?? null;

    $wo_status_approve = "Await Invoice";
    $wo_status_reject  = "Req Attention";

    $conn->begin_transaction();

    try {

        // 1️⃣ Fetch work order + contract + previous status
        $stmt = $conn->prepare("
            SELECT 
                rwo.actual_value,
                rwo.inv_status AS old_inv_status,
                rc.contract_id,
                rc.annual_value,
                rc.current_billing
            FROM recurring_work_orders rwo
            JOIN recurring_contracts rc 
                ON rc.site_id = rwo.site_id
               AND rc.service_type_id = rwo.service_type_id
            WHERE rwo.work_order_number = ?
            FOR UPDATE
        ");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            throw new Exception("Work order / contract not found");
        }

        $row = $res->fetch_assoc();

        $invoice_amount  = (float)$row['actual_value'];
        $old_inv_status  = $row['old_inv_status'];
        $contract_id     = (int)$row['contract_id'];
        $annual_value    = (float)$row['annual_value'];
        $current_billing = (float)$row['current_billing'];

        // 2️⃣ Decide billing update
        $new_current_billing = $current_billing;

        if ($status === "Approved" && $old_inv_status !== "Approved") {
            // ✅ Add only once
            $new_current_billing += $invoice_amount;
        }

        // 3️⃣ Calculate balance
        $balance_amount = $annual_value - $new_current_billing;
        if ($balance_amount < 0) {
            $balance_amount = 0;
        }

        // 4️⃣ Update work order
        if ($status === "Rejected") {

            $stmt = $conn->prepare("
                UPDATE recurring_work_orders
                SET status = ?,
                    inv_status = ?,
                    inv_reject_reason = ?,
                    actual_value = 0
                WHERE work_order_number = ?
            ");
            $stmt->bind_param("ssss", $wo_status_reject, $status, $reason, $id);

        } else {

            $stmt = $conn->prepare("
                UPDATE recurring_work_orders
                SET status = ?,
                    inv_status = ?,
                    inv_reject_reason = NULL
                WHERE work_order_number = ?
            ");
            $stmt->bind_param("sss", $wo_status_approve, $status, $id);
        }

        $stmt->execute();

        // 5️⃣ Update contract billing + balance
        $stmt = $conn->prepare("
            UPDATE recurring_contracts
            SET current_billing = ?,
                balance_amount = ?
            WHERE contract_id = ?
        ");
        $stmt->bind_param("ddi", $new_current_billing, $balance_amount, $contract_id);
        $stmt->execute();

        // ✅ Commit
        $conn->commit();
        echo "success";

    } catch (Exception $e) {

        $conn->rollback();
        echo "error: " . $e->getMessage();
    }

    $stmt->close();
    $conn->close();
}
?>
