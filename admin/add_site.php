<?php
include "includes/connection.php";

$response = ["status" => "error"];

$company = trim($_POST['company']);
$site    = trim($_POST['site_name']);
$service = trim($_POST['service_type']);
$status  = trim($_POST['site_status']);

/**
 * 1️⃣ Duplicate check (company + site_name)
 */
$check = $conn->prepare("
    SELECT id 
    FROM site_tbl 
    WHERE company = ? AND site_name = ?
");
$check->bind_param("ss", $company, $site);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $response["status"] = "duplicate";
    echo json_encode($response);
    exit;
}

/**
 * 2️⃣ Get next site_id
 */
$siteIdQuery = $conn->query("
    SELECT COALESCE(MAX(CAST(site_id AS UNSIGNED)), 0) + 1 AS next_site_id
    FROM site_tbl
");
$row = $siteIdQuery->fetch_assoc();
$nextSiteId = $row['next_site_id'];

/**
 * 3️⃣ Insert new site
 */
$stmt = $conn->prepare("
    INSERT INTO site_tbl (site_id, company, site_name, service_type, site_status)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "issis",
    $nextSiteId,
    $company,
    $site,
    $service,
    $status
);

if ($stmt->execute()) {
    $response["status"] = "success";
    $response["site_id"] = $nextSiteId; // optional (for frontend)
}

header("Content-Type: application/json");
echo json_encode($response);
?>