<?php
session_start();
include "config.php";

// Update status to offline before logout
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    mysqli_query($conn, "UPDATE users SET status='nonaktif' WHERE id='$user_id'");
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
