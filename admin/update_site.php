<?php
// update_site.php
// DEVELOPMENT: show errors (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "includes/connection.php";

// Simple JSON/text response helper
function respond($msg) {
    // keep it simple so your existing JS expecting "success" still works
    echo $msg;
    exit;
}

// Basic input validation
if (!isset($_POST['site_id'])) {
    respond('error');
}

$site_id     = (int) ($_POST['site_id'] ?? 0);
$company     = trim($_POST['company'] ?? '');
$site_name   = trim($_POST['site_name'] ?? '');
$service_type= (int) ($_POST['service_type'] ?? 0);

// Optional: quick sanity checks
if ($site_id <= 0) respond('error');
if ($company === '' || $site_name === '') respond('error');

// Prepared statement
$sql = "UPDATE site_tbl 
        SET company = ?, site_name = ?, service_type = ?
        WHERE site_id = ?";

// prepare
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    // log error to php error log for debugging
    error_log("MySQL prepare error in update_site.php: " . $conn->error);
    // optionally return DB error in development (comment out in production)
    // respond('error: ' . $conn->error);
    respond('error');
}

// bind_param types: s = string, i = integer
// company (s), site_name (s), service_type (i), site_status (s), site_id (i)
$bind = $stmt->bind_param("ssii", $company, $site_name, $service_type, $site_id);
if ($bind === false) {
    error_log("bind_param failed: " . $stmt->error);
    respond('error');
}

// execute
if ($stmt->execute()) {
    respond('success');
} else {
    error_log("execute failed: " . $stmt->error);
    respond('error');
}
