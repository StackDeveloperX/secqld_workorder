<?php
include('../includes/connection.php');

$sql = "SELECT *
        FROM site_tbl 
        WHERE site_status = 'Active'
        ORDER BY site_name ASC";

$result = $conn->query($sql);

$sites = [];

while ($row = $result->fetch_assoc()) {
    $sites[] = $row;
}

echo json_encode($sites);
?>