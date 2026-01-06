<?php
session_start();
include('../includes/connection.php');

if (!isset($_SESSION['client_id'])) {
    echo 'unauthorized';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'error';
    exit;
}

$new_password = trim($_POST['new_password'] ?? '');

if ($new_password === '' || strlen($new_password) < 8) {
    echo 'weak';
    exit;
}

// Hash password securely
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    UPDATE clients
    SET password_hash = ?, must_change_password = 0
    WHERE client_id = ?
");

$stmt->bind_param("si", $password_hash, $_SESSION['client_id']);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}

$stmt->close();
$conn->close();
