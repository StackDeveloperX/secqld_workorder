<?php
header('Content-Type: application/json');
include('includes/connection.php');

$sql = "SELECT work_order_id, work_order_number, site_name, work_order_date 
        FROM work_order 
        ORDER BY work_order_id DESC LIMIT 1";

$result = $conn->query($sql);

// Check if query failed
if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => "Query failed: " . $conn->error
    ]);
    exit;
}

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "status" => "success",
        "id" => $row['work_order_id'],
        "number" => $row['work_order_number'],
        "site" => $row['site_name'],
        "date" => $row['work_order_date']
    ]);
} else {
    echo json_encode(["status" => "empty"]);
}
?>