<?php
include "includes/connection.php";

$id = $_POST['id'];

$stmt = $conn->prepare("DELETE FROM site_tbl WHERE site_id=?");
$stmt->bind_param("i", $id);

echo $stmt->execute() ? "success" : "error";
?>