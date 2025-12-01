<?php
include("../includes/connection.php");

$sql = "SELECT work_order_number FROM work_order ORDER BY work_order_id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastOrder = $row['work_order_number'];

    // Extract numeric part and increment
    $num = (int)substr($lastOrder, 1);
    $num++;
    $newOrder = 'W' . str_pad($num, 6, '0', STR_PAD_LEFT);
} else {
    // First record or query error
    $newOrder = 'W000001';
}

echo $newOrder;
$conn->close();
?>