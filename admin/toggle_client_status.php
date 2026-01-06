<?php
include "includes/connection.php";

$id = $_POST['id'];
$status = $_POST['status'];

$stmt = $conn->prepare("UPDATE clients SET status=? WHERE client_id=?");
$stmt->bind_param("si", $status, $id);

echo $stmt->execute() ? "success" : "error";
