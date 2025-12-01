<?php
session_start();
include('../includes/connection.php'); // adjust path if needed

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $hashed_password = md5($password); // Note: Consider using password_hash()

        // DEBUG: Check if table/columns exist and query is correct
        $stmt = $conn->prepare("SELECT user_id, email FROM users WHERE email = ? AND password = ?");

        if ($stmt === false) {
            // Show SQL error for debugging
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("ss", $email, $hashed_password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            echo 'success';
        } else {
            echo 'invalid';
        }

        $stmt->close();
    } else {
        echo 'empty';
    }
}
?>