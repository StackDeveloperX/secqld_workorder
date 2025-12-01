<?php
include_once('includes/connection.php');

$table_name = $_POST['table_name'];
$id = intval($_POST['id']);

$valid_tables = ["not_for_invoices", "hidden_request", "any_document"];
if (!in_array($table_name, $valid_tables)) {
    echo json_encode(["status" => "error", "message" => "Invalid table"]);
    exit;
}

// Get file path before deleting from DB
$stmt = $conn->prepare("SELECT file_path FROM `$table_name` WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();

if ($file) {
    // Delete file from server
    if (file_exists($file['file_path'])) {
        unlink($file['file_path']);
    }

    // Delete record from DB
    $stmt = $conn->prepare("DELETE FROM `$table_name` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "File not found"]);
}
?>