<?php
include_once('includes/connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $work_order_number = mysqli_real_escape_string($conn, $_POST['work_order_number']);
    $notes_comment = mysqli_real_escape_string($conn, $_POST['notes_comment']);

    $sql = "INSERT INTO notes (work_order_number, notes_comment) VALUES ('$work_order_number', '$notes_comment')";
    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
