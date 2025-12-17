<?php
include("includes/connection.php");

$today = date('Y-m-d');

/* 🔹 Get all active recurring contracts */
$contracts = $conn->query("
    SELECT * 
    FROM recurring_contracts 
    WHERE status = 'Active'
");

while ($contract = $contracts->fetch_assoc()) {

    $contract_id = $contract['contract_id'];
    $site_id = $contract['site_id'];
    $service_type_id = $contract['service_type_id'];
    $frequency = $contract['frequency'];
    $value = $contract['work_order_value'];
    $start_date = $contract['start_date'];
    $end_date = $contract['end_date'];

    /* 🔹 Get last work order date */
    $lastWO = $conn->query("
        SELECT work_order_date 
        FROM recurring_work_orders 
        WHERE contract_id = $contract_id
        ORDER BY work_order_date DESC 
        LIMIT 1
    ");

    if ($lastWO->num_rows > 0) {
        $last_date = $lastWO->fetch_assoc()['work_order_date'];
    } else {
        $last_date = $start_date;
    }

    /* 🔹 Calculate next due date */
    switch ($frequency) {
        case 'Weekly':
            $next_date = date('Y-m-d', strtotime($last_date . ' +7 days'));
            break;
        case 'Fortnightly':
            $next_date = date('Y-m-d', strtotime($last_date . ' +14 days'));
            break;
        case 'Monthly':
            $next_date = date('Y-m-d', strtotime($last_date . ' +1 month'));
            break;
        case 'Quarterly':
            $next_date = date('Y-m-d', strtotime($last_date . ' +3 months'));
            break;
    }

    /* 🔹 Stop if end date exceeded */
    if (!empty($end_date) && $next_date > $end_date) {
        continue;
    }

    /* 🔹 Generate work order only if due */
    if ($today >= $next_date) {

        /* 🔹 Prevent duplicate generation */
        $check = $conn->query("
            SELECT 1 FROM recurring_work_orders 
            WHERE contract_id = $contract_id 
            AND work_order_date = '$next_date'
        ");

        if ($check->num_rows == 0) {

            /* 🔹 Generate Work Order Number */
            $res = $conn->query("SELECT MAX(work_order_id) AS max_id FROM work_orders");
            $row = $res->fetch_assoc();
            $nextId = $row['max_id'] + 1;
            $woNumber = 'W' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            /* 🔹 Insert Work Order */
            $stmt = $conn->prepare("
                INSERT INTO recurring_work_orders
                (work_order_number, contract_id, site_id, service_type_id, work_order_date, value)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "siiisd",
                $woNumber,
                $contract_id,
                $site_id,
                $service_type_id,
                $next_date,
                $value
            );

            $stmt->execute();
        }
    }
}

echo "CRON completed on $today";
