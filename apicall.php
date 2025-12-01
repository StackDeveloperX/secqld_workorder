<?php
// Step 1: Get the token
$tokenUrl = "https://secqld.s3security.com.au/integrations/service/";
$tokenData = [
    "method" => "s3_connect",
    "params" => [
        "client_id" => "6116"
    ]
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tokenData));

$tokenResponse = curl_exec($ch);
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
    curl_close($ch);
    exit;
}
curl_close($ch);

$tokenResult = json_decode($tokenResponse, true);
$token = $tokenResult['token'] ?? '';

if (!$token) {
    die("Failed to get token from S3 API.");
}

// Step 2: Fetch site data using the token
$siteDataRequest = [
    "method" => "s3_connect", // Replace with the correct API method if different
    "params" => [
        "token" => $token
    ]
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($siteDataRequest));

$siteResponse = curl_exec($ch);
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
    curl_close($ch);
    exit;
}
curl_close($ch);

// Step 3: Decode and process the data
$siteResult = json_decode($siteResponse, true);

if (isset($siteResult['status']) && $siteResult['status'] == 'success') {
    $sites = $siteResult['data'];
    
    echo "<pre>";
    foreach ($sites as $site) {
        echo "Site ID: " . $site['site_id'] . "\n";
        echo "Company: " . $site['company'] . "\n";
        echo "Site Name: " . $site['name'] . "\n";
        echo "CID: " . $site['cid'] . "\n";
        echo "-------------------------\n";
    }
    echo "</pre>";
} else {
    echo "Failed to fetch sites or invalid response.";
}
?>
