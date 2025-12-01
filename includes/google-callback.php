<?php
require_once '../vendor/autoload.php';
session_start();

$client = new Google_Client();
$client->setClientId('154550407933-n1cvsdqln5f37qodd58itpb6nkltemo5.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/SecqldSoftware/includes/google-callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Get profile info
    $oauth = new Google_Service_Oauth2($client);
    $userInfo = $oauth->userinfo->get();

    $email = $userInfo->email;
    $name = $userInfo->name;
    $google_id = $userInfo->id;

    // Connect to your DB
    include('connection.php');

    // Check if user already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        // User exists
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $email;
    } else {
        // Create new user
        $active = "Active";
        $stmt = $conn->prepare("INSERT INTO users (email, name, google_id, user_status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $name, $google_id, $active);
        $stmt->execute();
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['email'] = $email;
    }

    header("Location: ../dashboard.php");
    exit;
}
?>
