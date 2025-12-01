<?php
include_once('includes/connection.php');

if ($_FILES['file']['error'] == 0) {
    $work_order_number = $_POST['work_order_number'];
    $table_name = $_POST['table_name'];

    $folder_map = [
        "not_for_invoices" => "uploads/not_for_invoices/",
        "hidden_request" => "uploads/hidden_request/",
        "any_document" => "uploads/any_document/"
    ];

    if (!isset($folder_map[$table_name])) {
        echo json_encode(["status" => "error", "message" => "Invalid table name"]);
        exit;
    }

    $upload_dir = $folder_map[$table_name];
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = basename($_FILES['file']['name']);
    $file_path = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
        $stmt = $conn->prepare("INSERT INTO `$table_name` (work_order_number, file_path) VALUES (?, ?)");
        $stmt->bind_param("ss", $work_order_number, $file_path);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "File upload failed"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No file uploaded"]);
}
?>