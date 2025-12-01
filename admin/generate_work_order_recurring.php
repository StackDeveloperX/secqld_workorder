<?php
include("../includes/connection.php");

// Attempt to fetch the latest WR number
$sql = "SELECT work_order_number 
        FROM recurring_work_order 
        ORDER BY work_order_id DESC 
        LIMIT 1";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastOrder = $row['work_order_number'];

    // Extract numeric part (remove WR prefix)
    $num = (int)preg_replace('/[^0-9]/', '', $lastOrder);

    // Increment
    $num++;
    $newOrder = 'WR' . str_pad($num, 6, '0', STR_PAD_LEFT);

} else {
    // Table is empty, start from WR000001
    $newOrder = 'WR000001';
}

// Output new WR number
echo $newOrder;

$conn->close();
?>
