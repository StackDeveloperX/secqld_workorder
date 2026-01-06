<?php
include('includes/connection.php');

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

function generateTempPassword($length = 10) {
    return substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, $length);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request'
    ]);
    exit;
}

// Sanitize
$business_name = trim($_POST['business'] ?? '');
$abn           = trim($_POST['abn'] ?? '');
$email         = trim($_POST['email'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$landline      = trim($_POST['landline'] ?? '');

if (!$business_name || !$email) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Business name and email are required.'
    ]);
    exit;
}

// Duplicate email check
$check = $conn->prepare("SELECT client_id FROM clients WHERE business_email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Client with this email already exists.'
    ]);
    exit;
}

// Create temp password
$temp_password = generateTempPassword();
$password_hash = password_hash($temp_password, PASSWORD_DEFAULT);

// Insert client
$stmt = $conn->prepare("
    INSERT INTO clients 
    (business_name, abn, business_email, password_hash, phone, landline)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssss",
    $business_name,
    $abn,
    $email,
    $password_hash,
    $phone,
    $landline
);

if (!$stmt->execute()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $stmt->error
    ]);
    exit;
}

// Send welcome email
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'info@webp.com.au';
    $mail->Password = 'surz elpv ttip nkxg';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('info@webp.com.au', 'SecQld Support');
    $mail->addAddress($email);
    $mail->Subject = 'Welcome to Client Portal Access';

    $mail->Body = "
Hello {$business_name},

Your client account has been created.

Login Email: {$email}
Temporary Password: {$temp_password}

Login here:
https://yourdomain.com/client-login.php

You will be required to change your password on first login.

Regards,
Support Team
";

    $mail->send();

} catch (Exception $e) {
    // Email failed, but client created
}

echo json_encode([
    'status' => 'success',
    'message' => 'Client created successfully and welcome email sent.'
]);
