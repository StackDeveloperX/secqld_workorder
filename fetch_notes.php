<?php
include_once('includes/connection.php');

if (isset($_GET['work_order_number'])) {
    $work_order_number = mysqli_real_escape_string($conn, $_GET['work_order_number']);
    $result = mysqli_query($conn, "SELECT notes_comment FROM notes WHERE work_order_number='$work_order_number' ORDER BY id DESC");

    $notes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notes[] = $row['notes_comment'];
    }

    echo json_encode($notes);
}
?>