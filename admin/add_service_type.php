<?php
include('includes/connection.php');

$response = ["status" => "error"];

if (isset($_POST['service_name'], $_POST['service_short_name'], $_POST['service_type'])) {

    $name = trim($_POST['service_name']);
    $short = trim($_POST['service_short_name']);
    $type = trim($_POST['service_type']);

    // Check duplicate
    $check = $conn->prepare("SELECT service_id FROM service_type_tbl WHERE service_name = ?");
    $check->bind_param("s", $name);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $response["status"] = "duplicate";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO service_type_tbl (service_name, service_short_name, service_type, service_status) VALUES (?, ?, ?, 'Active')
        ");
        $stmt->bind_param("sss", $name, $short, $type);

        if ($stmt->execute()) {
            $response["status"] = "success";
        }
    }
}

header("Content-Type: application/json");
echo json_encode($response);
?>