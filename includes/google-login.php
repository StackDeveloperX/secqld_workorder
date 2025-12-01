<?php
require_once '../vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('154550407933-n1cvsdqln5f37qodd58itpb6nkltemo5.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/SecqldSoftware/includes/google-callback.php');
$client->addScope('email');
$client->addScope('profile');

// Redirect to Google login
$auth_url = $client->createAuthUrl();
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
?>