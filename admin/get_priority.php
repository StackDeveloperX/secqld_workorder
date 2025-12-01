<?php
include("../includes/connection.php");
$sql = "SELECT priority_id, priority_name FROM priority_tbl ORDER BY priority_name ASC";
$result = $conn->query($sql);

$sites = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sites[] = $row;
    }
}

echo json_encode($sites);
$conn->close();
?>