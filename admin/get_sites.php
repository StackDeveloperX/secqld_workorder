<?php
include("../includes/connection.php");
$sql = "SELECT site_id, site_name FROM site_tbl";
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