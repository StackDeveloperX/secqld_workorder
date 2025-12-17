<?php
function generateWorkOrders($conn, $contract_id) {

    $c = $conn->query("
        SELECT * FROM recurring_contracts
        WHERE contract_id = $contract_id
        AND status = 'Active'
    ")->fetch_assoc();

    if (!$c) return;

    $startDate = $c['start_date'];
    $endDate   = $c['end_date'];
    $frequency = $c['frequency'];

    $day = date('N', strtotime($startDate));
    if ($day != 1) {
        $currentDate = date('Y-m-d', strtotime("+".(8-$day)." days", strtotime($startDate)));
    } else {
        $currentDate = $startDate;
    }

    $intervals = [
        'Weekly' => '+7 days',
        'Fortnightly' => '+14 days',
        'Monthly' => '+1 month',
        'Quarterly' => '+3 months'
    ];

    while (strtotime($currentDate) <= strtotime($endDate)) {

        $exists = $conn->query("
            SELECT 1 FROM recurring_work_orders
            WHERE contract_id = $contract_id
            AND work_order_date = '$currentDate'
        ");

        if ($exists->num_rows == 0) {

            $wo = "WR" . str_pad(mt_rand(1,99999), 5, "0", STR_PAD_LEFT);

            $stmt = $conn->prepare("
                INSERT INTO recurring_work_orders
                (work_order_number, contract_id, site_id, service_type_id,
                 work_order_date, value, assigned_to, logged_by, priority)
                VALUES (?,?,?,?,?,?,?,?,?)
            ");

            $stmt->bind_param(
                "siiisdsss",
                $wo,
                $contract_id,
                $c['site_id'],
                $c['service_type_id'],
                $currentDate,
                $c['work_order_value'],
                $c['assigned_to'],
                $c['logged_by'],
                $c['priority']
            );

            $stmt->execute();
        }

        $currentDate = date('Y-m-d', strtotime($intervals[$frequency], strtotime($currentDate)));
    }
}
?>