<?php
session_start();
include('../includes/connection.php'); // adjust path if needed

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_email = trim($_POST['email']);
    $admin_password = trim($_POST['password']);

    if (!empty($admin_email) && !empty($admin_password)) {
        $hashed_password = md5($admin_password); // Note: Consider using password_hash()

        // DEBUG: Check if table/columns exist and query is correct
        $stmt = $conn->prepare("SELECT admin_id, admin_email FROM admin WHERE admin_email = ? AND password = ?");

        if ($stmt === false) {
            // Show SQL error for debugging
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("ss", $admin_email, $hashed_password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION['admin_id'] = $user['admin_id'];
            $_SESSION['admin_email'] = $user['admin_email'];
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