<?php
session_start();
include('../includes/connection.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'invalid_request';
    exit;
}

$accounts_email = trim($_POST['accounts_email'] ?? '');
$accounts_password = trim($_POST['accounts_password'] ?? '');

if ($accounts_email === '' || $accounts_password === '') {
    echo 'empty';
    exit;
}

/**
 * 1️⃣ Fetch user by email ONLY
 */
$stmt = $conn->prepare("
    SELECT account_id, account_email, password 
    FROM accounts 
    WHERE account_email = ?
");

if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}

$stmt->bind_param("s", $accounts_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo 'email_not_found';
    exit;
}

$user = $result->fetch_assoc();

/**
 * 2️⃣ Verify hashed password
 */
if (!password_verify($accounts_password, $user['password'])) {
    echo 'password_mismatch';
    exit;
}

/**
 * 3️⃣ Login success
 */
$_SESSION['account_id'] = $user['account_id'];
$_SESSION['account_email'] = $user['account_email'];

echo 'success';
