<?php
/**
 * File: session.php
 * Fungsi: Mengelola session untuk login dan menyimpan data sementara
 * 
 * Session digunakan untuk menyimpan data user yang sedang login
 * dan data sementara seperti pilihan order yang sedang dibuat.
 */

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi: Cek apakah user sudah login
 * Return: true jika sudah login, false jika belum
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Fungsi: Cek apakah user adalah admin
 * Return: true jika admin, false jika bukan
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Fungsi: Cek apakah user adalah staff
 * Return: true jika staff, false jika bukan
 */
function isStaff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}

/**
 * Fungsi: Cek apakah user adalah pelanggan
 * Return: true jika pelanggan, false jika bukan
 */
function isPelanggan() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'pelanggan';
}

/**
 * Fungsi: Ambil data user yang sedang login
 * Return: array berisi data user atau null jika belum login
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role']
    ];
}

/**
 * Fungsi: Set data user ke session setelah login berhasil
 * Parameter: $user - array data user dari database
 */
function setUserSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_time'] = time();
}

/**
 * Fungsi: Hapus semua data session (logout)
 */
function destroyUserSession() {
    session_unset();
    session_destroy();
}

/**
 * Fungsi: Redirect ke halaman login jika belum login
 * Digunakan untuk proteksi halaman yang butuh login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /rollmate/login.php');
        exit;
    }
}

/**
 * Fungsi: Redirect ke halaman login jika bukan admin
 * Digunakan untuk proteksi halaman khusus admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /rollmate/dashboard.php');
        exit;
    }
}

/**
 * Fungsi: Redirect ke halaman login jika bukan staff
 * Digunakan untuk proteksi halaman khusus staff
 */
function requireStaff() {
    requireLogin();
    if (!isStaff()) {
        header('Location: /rollmate/dashboard.php');
        exit;
    }
}

/**
 * Fungsi: Redirect ke halaman login jika bukan pelanggan
 * Digunakan untuk proteksi halaman khusus pelanggan
 */
function requirePelanggan() {
    requireLogin();
    if (!isPelanggan()) {
        header('Location: /rollmate/dashboard.php');
        exit;
    }
}

/**
 * Fungsi: Redirect ke dashboard sesuai role user
 * Digunakan setelah login untuk mengarahkan ke halaman yang sesuai
 */
function redirectToDashboard() {
    if (isAdmin()) {
        header('Location: /rollmate/admin/dashboard.php');
    } elseif (isStaff()) {
        header('Location: /rollmate/staff/dashboard.php');
    } elseif (isPelanggan()) {
        header('Location: /rollmate/pelanggan/dashboard.php');
    } else {
        header('Location: /rollmate/login.php');
    }
    exit;
}

/**
 * Fungsi: Set pesan notifikasi (toast)
 * Parameter: $message - teks pesan
 *           $type - jenis pesan (success, error, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Fungsi: Ambil dan hapus pesan notifikasi
 * Return: array berisi message dan type, atau null jika tidak ada
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type']
        ];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return $flash;
    }
    return null;
}

/**
 * Fungsi: Simpan data order sementara ke session
 * Digunakan saat user memilih jenis laminasi dan tipe order
 */
function setOrderSession($laminationType, $orderType) {
    $_SESSION['order_lamination_type'] = $laminationType;
    $_SESSION['order_type'] = $orderType;
    $_SESSION['order_step'] = 2; // Step saat ini
}

/**
 * Fungsi: Ambil data order dari session
 * Return: array berisi data order atau null
 */
function getOrderSession() {
    if (isset($_SESSION['order_lamination_type']) && isset($_SESSION['order_type'])) {
        return [
            'lamination_type' => $_SESSION['order_lamination_type'],
            'order_type' => $_SESSION['order_type'],
            'step' => $_SESSION['order_step'] ?? 1
        ];
    }
    return null;
}

/**
 * Fungsi: Hapus data order dari session
 * Digunakan setelah order berhasil disimpan ke database
 */
function clearOrderSession() {
    unset($_SESSION['order_lamination_type']);
    unset($_SESSION['order_type']);
    unset($_SESSION['order_step']);
}

/**
 * Fungsi: Buat notifikasi untuk user
 * Parameter: $userId - ID user yang akan menerima notifikasi
 *           $title - judul notifikasi
 *           $message - isi pesan notifikasi
 *           $type - jenis notifikasi (info, success, warning, error)
 *           $link - link ke halaman terkait (opsional)
 */
function createNotification($userId, $title, $message, $type = 'info', $link = null) {
    require_once __DIR__ . '/database.php';
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, link)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $message, $type, $link]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Fungsi: Ambil notifikasi user yang belum dibaca
 * Parameter: $userId - ID user
 * Return: array notifikasi
 */
function getUnreadNotifications($userId) {
    require_once __DIR__ . '/database.php';
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? AND is_read = 0 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Fungsi: Tandai notifikasi sebagai sudah dibaca
 * Parameter: $notificationId - ID notifikasi
 */
function markNotificationAsRead($notificationId) {
    require_once __DIR__ . '/database.php';
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$notificationId]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Fungsi: Hitung jumlah notifikasi yang belum dibaca
 * Parameter: $userId - ID user
 * Return: jumlah notifikasi
 */
function countUnreadNotifications($userId) {
    require_once __DIR__ . '/database.php';
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return $stmt->fetch()['total'];
    } catch (PDOException $e) {
        return 0;
    }
}
?>
