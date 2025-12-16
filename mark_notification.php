<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/config/database.php';

// require logged-in user
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: /cvjurnal/login.php'); exit();
}

$nid = (int)($_GET['nid'] ?? 0);
$goto = $_GET['goto'] ?? '';
$user_id = (int)$_SESSION['user_id'];

// validate notification exists and belongs to this user (or is for all/user)
if ($nid > 0) {
    // deteksi kolom user_id
    $hasUserIdCol = false;
    $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM notifications LIKE 'user_id'");
    if ($colCheck && mysqli_num_rows($colCheck) > 0) $hasUserIdCol = true;

    $sql = $hasUserIdCol
        ? "UPDATE notifications SET is_read = 1 WHERE id = ? AND (recipient_role = 'all' OR recipient_role = 'user' OR recipient_user_id = ? OR user_id = ?) LIMIT 1"
        : "UPDATE notifications SET is_read = 1 WHERE id = ? AND (recipient_role = 'all' OR recipient_role = 'user' OR recipient_user_id = ?) LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if ($hasUserIdCol) {
            $stmt->bind_param('iii', $nid, $user_id, $user_id);
        } else {
            $stmt->bind_param('ii', $nid, $user_id);
        }
        $stmt->execute();
        $stmt->close();
    }
}

// safe redirect
$goto = trim($goto);
if ($goto === '' || strpos($goto, '/') !== 0) {
    $goto = '/cvjurnal/notifications.php';
}
header('Location: ' . $goto);
exit();