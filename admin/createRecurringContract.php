<?php
include("includes/connection.php");
include("includes/recurring-generator.php");

header('Content-Type: application/json');

try {

    $sitename_recurring = $_POST['sitename_recurring'];
    $service_type_recurring = $_POST['service_type_recurring'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $frequency = $_POST['frequency'];
    $work_order_amount = $_POST['work_order_amount'];

    $user_select_recurring = $_POST['user_select_recurring'];
    $logged_by   = $_POST['admin_name'];
    $priority_recurring    = $_POST['priority_recurring'];
    $job_description_recurring    = $_POST['job_description_recurring'];

    switch ($frequency) {
        case 'Weekly': $annual = $work_order_amount * 52; break;
        case 'Fortnightly': $annual = $work_order_amount * 26; break;
        case 'Monthly': $annual = $work_order_amount * 12; break;
        case 'Quarterly': $annual = $work_order_amount * 4; break;
    }

    $stmt = $conn->prepare("
        INSERT INTO recurring_contracts
        (site_id, service_type_id, start_date, end_date, frequency,
         work_order_value, annual_value, assigned_to, logged_by, priority, description)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "iisssddssss",
        $sitename_recurring,
        $service_type_recurring,
        $start_date,
        $end_date,
        $frequency,
        $work_order_amount,
        $annual,
        $user_select_recurring,
        $logged_by,
        $priority_recurring,
        $job_description_recurring
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to create recurring contract");
    }

    $contract_id = $conn->insert_id;

    generateWorkOrders($conn, $contract_id);

    echo json_encode([
        "status" => "success",
        "message" => "Recurring contract created and work orders generated successfully"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>