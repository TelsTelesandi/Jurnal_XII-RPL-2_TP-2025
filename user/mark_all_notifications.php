<?php
session_start();
require_once '../config/database.php';

// Cek login user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Tandai semua notifikasi sebagai sudah dibaca (dukung skema ada/tanpa user_id)
$hasUserIdCol = false;
$colCheck = mysqli_query($conn, "SHOW COLUMNS FROM notifications LIKE 'user_id'");
if ($colCheck && mysqli_num_rows($colCheck) > 0) $hasUserIdCol = true;

$sql = $hasUserIdCol
    ? "UPDATE notifications SET is_read = 1 WHERE (recipient_role = 'all' OR recipient_role = 'user' OR recipient_user_id = ? OR user_id = ?) AND is_read = 0"
    : "UPDATE notifications SET is_read = 1 WHERE (recipient_role = 'all' OR recipient_role = 'user' OR recipient_user_id = ?) AND is_read = 0";
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($hasUserIdCol) {
        $stmt->bind_param('ii', $user_id, $user_id);
    } else {
        $stmt->bind_param('i', $user_id);
    }
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'All notifications marked as read', 'affected' => $affected]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
exit();
?>