<?php
include("../includes/connection.php");

$serviceType = isset($_GET['service_type']) ? intval($_GET['service_type']) : 0;

$sql = "SELECT site_id, site_name 
        FROM site_tbl 
        WHERE service_type = $serviceType AND site_status='Active'";

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
