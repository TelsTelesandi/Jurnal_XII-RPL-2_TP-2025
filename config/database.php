<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'dbcv';

// Create mysqli connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
mysqli_set_charset($conn, 'utf8mb4');


// Important: Don't close the connection here!
// Let the requiring script handle the connection closing
?>