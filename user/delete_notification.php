<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$notif_id = (int)($_GET['id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($notif_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

// Hapus notifikasi
$stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND recipient_user_id = ?");
$stmt->bind_param('ii', $notif_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
exit();