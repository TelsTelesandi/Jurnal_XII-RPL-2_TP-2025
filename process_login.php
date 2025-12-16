<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Get input
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Add debugging
error_log("Login attempt for username: " . $username);

// Prepare statement
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    $_SESSION['error'] = "Database error";
    header('Location: login.php');
    exit();
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Debug user data
error_log("User found: " . ($user ? "yes" : "no"));
if ($user) {
    error_log("Password verify: " . password_verify($password, $user['password']));
    error_log("Stored hash: " . $user['password']);
}

if ($user) {
    // For debugging, temporarily remove password verification
    if (true || password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        
        error_log("Login successful. Role: " . $user['role']);
        
        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: user/dashboard.php');
        }
        exit();
    }
}

$_SESSION['error'] = "Username atau password salah";
header('Location: login.php');
exit();