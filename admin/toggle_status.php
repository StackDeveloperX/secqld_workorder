<?php
include('includes/connection.php');

if (isset($_POST['service_id'], $_POST['service_status'])) {
    $id = $_POST['service_id'];
    $status = $_POST['service_status'];

    $stmt = $conn->prepare("UPDATE service_type_tbl SET service_status = ? WHERE service_id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>