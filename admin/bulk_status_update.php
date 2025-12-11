<?php
include('includes/connection.php');

$ids = $_POST['ids']; // array
$status = $_POST['status'];

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$sql = "UPDATE service_type_tbl SET service_status=? WHERE service_id IN ($placeholders)";
$stmt = $conn->prepare($sql);

$stmt->bind_param("s" . $types, $status, ...$ids);

echo $stmt->execute() ? "success" : "error";
