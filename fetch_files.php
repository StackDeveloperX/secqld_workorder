<?php
include_once('includes/connection.php');

$table = $_GET['table'];
$work_order_number = $_GET['work_order_number'];

$valid_tables = ["not_for_invoices", "hidden_request", "any_document"];
if (!in_array($table, $valid_tables)) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, file_path FROM `$table` WHERE work_order_number = ?");
$stmt->bind_param("s", $work_order_number);
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}
echo json_encode($files);
?>
