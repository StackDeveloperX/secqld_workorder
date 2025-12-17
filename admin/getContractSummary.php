<?php
include("includes/connection.php");

$contract_id = (int)$_GET['contract_id'];

$sql = "
SELECT 
    rc.annual_value,
    rc.work_order_value,
    s.site_name,
    IFNULL(SUM(wo.value), 0) AS current_billing
FROM recurring_contracts rc
JOIN site_tbl s 
    ON s.id = rc.site_id
LEFT JOIN recurring_work_orders wo 
    ON wo.contract_id = rc.contract_id
    AND wo.status = 'Completed'
WHERE rc.contract_id = $contract_id
GROUP BY rc.contract_id
";

$result = $conn->query($sql);

if (!$result) {
    // IMPORTANT: prevents fetch_assoc() fatal error
    die(json_encode([
        'error' => true,
        'message' => $conn->error
    ]));
}

$r = $result->fetch_assoc();

$annual  = (float)$r['annual_value'];
$current = (float)$r['current_billing'];
$weekly  = (float)$r['work_order_value'];
$balance = $annual - $current;

echo json_encode([
    /* formatted values (tables) */
    'annual'  => number_format($annual, 2),
    'weekly'  => number_format($weekly, 2),
    'current' => number_format($current, 2),
    'balance' => number_format($balance, 2),

    /* raw values (graph) */
    'annual_raw'  => $annual,
    'weekly_raw'  => $weekly,
    'current_raw' => $current,
    'balance_raw' => $balance,

    /* other info */
    'site' => $r['site_name'],
    'over_budget' => $balance < 0
]);