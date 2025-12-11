<?php
include('includes/connection.php');

$id = $_POST['edit_service_id'];
$name = trim($_POST['service_name']);
$short = trim($_POST['service_short_name']);
$type = trim($_POST['service_type']);

// Check duplicate (excluding itself)
$check = $conn->prepare("SELECT service_id FROM service_type_tbl WHERE service_name = ? AND service_id != ?");
$check->bind_param("si", $name, $id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "duplicate";
    exit;
}

$stmt = $conn->prepare("UPDATE service_type_tbl SET service_name=?, service_short_name=?, service_type=? WHERE service_id=?");
$stmt->bind_param("sssi", $name, $short, $type, $id);

echo $stmt->execute() ? "success" : "error";
