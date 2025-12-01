<?php
include("../includes/connection.php");
$sql = "SELECT user_id, name FROM users ORDER BY name ASC";
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