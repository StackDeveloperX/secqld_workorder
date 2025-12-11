<?php
include("../includes/connection.php");
$sql = "SELECT service_id, service_name FROM service_type_tbl WHERE service_type = 'Normal' AND service_status = 'Active'";
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