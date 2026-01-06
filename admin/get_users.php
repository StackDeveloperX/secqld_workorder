<?php
include("../includes/connection.php");
$sql = "SELECT client_id, business_name FROM clients ORDER BY business_name ASC";
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