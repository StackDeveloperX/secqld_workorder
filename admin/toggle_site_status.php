<?php
include "includes/connection.php";

$id = $_POST['id'];
$status = $_POST['status'];

$stmt = $conn->prepare("UPDATE site_tbl SET site_status=? WHERE site_id=?");
$stmt->bind_param("si", $status, $id);

echo $stmt->execute() ? "success" : "error";
