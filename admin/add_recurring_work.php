<?php
include "includes/connection.php";

// Enable mysqli exceptions for better error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_name   = $_POST['admin_name'];
    $start_date   = $_POST['start_date'];
    $end_date     = $_POST['end_date'];
    $frequency    = $_POST['frequency'];
    $amount       = $_POST['work_order_amount'];
    $annual       = $_POST['annual_amount'];
    $service_type = $_POST['service_type_recurring'];
    $site_name    = $_POST['sitename_recurring'];
    $assigned_to  = $_POST['user_select_recurring'];
    $description  = $_POST['job_description_recurring'];

    // Validate frequency
    $freq_days = [
        "Weekly" => 7,
        "Fortnightly" => 14,
        "Monthly" => 30,
        "Quarterly" => 90
    ];

    if (!isset($freq_days[$frequency])) {
        echo json_encode(["status" => "error", "message" => "Invalid frequency"]);
        exit;
    }

    $interval_days = $freq_days[$frequency];
    $start = new DateTime($start_date);
    $end   = new DateTime($end_date);

    if ($start > $end) {
        echo json_encode(["status" => "error", "message" => "Start date cannot be after end date"]);
        exit;
    }

    // Get last work order number from DB
    $sql = "SELECT work_order_number FROM recurring_work_order ORDER BY work_order_id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastOrder = $row['work_order_number'];
        $num = (int)preg_replace('/[^0-9]/', '', $lastOrder); // remove prefix
        $next_num = $num + 1;
    } else {
        $next_num = 1; // first work order
    }

    $conn->begin_transaction();

    try {
        $count = 0;
        $current_date = clone $start;

        while ($current_date <= $end) {
            // Generate Work Order Number
            $work_order_no = 'WR' . str_pad($next_num, 6, '0', STR_PAD_LEFT);
            $next_num++;

            $stmt = $conn->prepare("INSERT INTO recurring_work_order 
                (work_order_number, start_date, end_date, frequency, work_order_amount, annual_amount, service_type, site_name, assigned_to, job_description, admin_name)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $start_date_formatted = $current_date->format("Y-m-d");
            $stmt->bind_param(
                "sssssssssss",
                $work_order_no,
                $start_date_formatted,
                $end_date,
                $frequency,
                $amount,
                $annual,
                $service_type,
                $site_name,
                $assigned_to,
                $description,
                $admin_name
            );

            $stmt->execute();
            $count++;

            // Move to next recurring date
            $current_date->modify("+{$interval_days} days");
        }

        $conn->commit();

        echo json_encode([
            "status" => "success",
            "message" => "$count recurring work orders created successfully"
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $e->getMessage()
        ]);
    }

    $conn->close();
}
?>
