<?php
include('includes/connection.php');

$id = $_POST['id'];

$stmt = $conn->prepare("DELETE FROM service_type_tbl WHERE service_id = ?");
$stmt->bind_param("i", $id);

echo $stmt->execute() ? "success" : "error";
?>