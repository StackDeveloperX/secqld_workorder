<?php
session_start();
include("../includes/connection.php");

$sitename_recurring = $_POST['sitename_recurring'];
$service_type_recurring = $_POST['service_type_recurring'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'] ?: null;
$frequency = $_POST['frequency'];
$work_order_amount = $_POST['work_order_amount'];
$created_by        = $_POST['admin_name'];

/* 🔹 Calculate annual value */
switch ($frequency) {
    case 'Weekly':
        $annual_value = $work_order_amount * 52;
        break;
    case 'Fortnightly':
        $annual_value = $work_order_amount * 26;
        break;
    case 'Monthly':
        $annual_value = $work_order_amount * 12;
        break;
    case 'Quarterly':
        $annual_value = $work_order_amount * 4;
        break;
}

/* 🔹 Insert recurring contract */
$sql = "INSERT INTO recurring_contracts 
        (site_id, service_type_id, start_date, end_date, frequency, work_order_value, annual_value, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iisssdsi",
    $sitename_recurring,
    $service_type_recurring,
    $start_date,
    $end_date,
    $frequency,
    $work_order_amount,
    $annual_value,
    $created_by
);

if ($stmt->execute()) {
    $recurring_id = $stmt->insert_id;
    echo json_encode([
        "status" => "success",
        "message" => "Recurring contract saved successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to save recurring contract"
    ]);
}
?>