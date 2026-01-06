<?php
session_start();
include('../includes/connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        echo 'empty';
        exit;
    }

    // Fetch client by email
    $stmt = $conn->prepare("
        SELECT 
            client_id,
            business_email,
            password_hash,
            must_change_password,
            status
        FROM clients
        WHERE business_email = ?
        LIMIT 1
    ");

    if (!$stmt) {
        echo 'error';
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        echo 'invalid';
        exit;
    }

    $client = $result->fetch_assoc();

    // Block inactive clients
    if ($client['status'] !== 'Active') {
        echo 'inactive';
        exit;
    }

    // Verify password (bcrypt / PASSWORD_DEFAULT)
    if (!password_verify($password, $client['password_hash'])) {
        echo 'invalid';
        exit;
    }

    // Login success
    $_SESSION['client_id']    = $client['client_id'];
    $_SESSION['client_email'] = $client['business_email'];

    // First login → force password change
    if ((int)$client['must_change_password'] === 1) {
        echo 'change_password';
    } else {
        echo 'success';
    }

    $stmt->close();
    $conn->close();
}
