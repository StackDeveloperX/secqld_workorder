<?php
include('includes/connection.php'); // adjust path

if(isset($_POST['id']) && isset($_POST['status'])){
    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE work_order SET status = ? WHERE work_order_number = ?");
    $stmt->bind_param("ss", $status, $id);

    if($stmt->execute()){
        echo "success";
    } else {
        echo "error: " . $conn->error;
    }
    $stmt->close();
}
?>