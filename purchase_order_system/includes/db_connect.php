<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = '127.0.0.1'; // Gunakan IP untuk menghindari masalah DNS
$dbname = 'purchase_order_db';
$username = 'root';
$password = '';
$port = 3306; // Port default MySQL

// Function to create database tables
function createDatabaseTables($pdo) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `email` varchar(100) NOT NULL,
            `name` varchar(100) NOT NULL,
            `role` enum('admin','production') NOT NULL DEFAULT 'production',
            `remember_token` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        // Add other tables if needed
        
        return true;
    } catch (PDOException $e) {
        error_log('Error creating tables: ' . $e->getMessage());
        return false;
    }
}

// Cek apakah MySQL berjalan
try {
    // Coba koneksi ke MySQL server terlebih dahulu (tanpa memilih database)
    $pdo = new PDO(
        "mysql:host=$host;port=$port;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5, // Timeout 5 detik
        ]
    );

        // Cek dan buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    
    // Tutup koneksi sementara
    $pdo = null;
    
    // Sekarang buat koneksi ke database yang spesifik
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Set timezone
    $pdo->exec("SET time_zone = '+07:00'");
    
} catch (PDOException $e) {
    $error_message = 'Kesalahan Koneksi Database: ' . $e->getMessage();
    error_log($error_message);
    
    // Tampilkan pesan error yang lebih informatif
    $suggestion = '';
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        $suggestion = '\n\nSaran: Database belum dibuat. Silakan buat database "' . $dbname . '" terlebih dahulu.';
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        $suggestion = '\n\nSaran: Pastikan username dan password database benar.';
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        $suggestion = '\n\nSaran: Pastikan MySQL server berjalan di Laragon.';
    }
    
    $error_details = '';
    if (strpos($e->getMessage(), 'No connection') !== false) {
        $error_details = '
        <div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
            <h3>Langkah Perbaikan:</h3>
            <ol>
                <li>Buka Laragon</li>
                <li>Klik kanan ikon Laragon di system tray</li>
                <li>Pilih <strong>MySQL > Restart</strong></li>
                <li>Jika masih error, coba <strong>Stop All</strong> lalu <strong>Start All</strong></li>
                <li>Pastikan lampu hijau menyala di sebelah MySQL</li>
            </ol>
            <p>Jika masih bermasalah, coba matikan aplikasi lain yang mungkin menggunakan port 3306 (seperti XAMPP/WAMP).</p>
        </div>';
    }
    
    die('<div style="font-family: Arial, sans-serif; padding: 20px; color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;">
            <h2>⚠️ Gagal Terhubung ke Database</h2>
            <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>' . 
            ($suggestion ? '<div style="margin: 10px 0; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107;">' . nl2br(htmlspecialchars(trim($suggestion))) . '</div>' : '') .
            $error_details . '
            <div style="margin-top: 20px; padding: 10px; background: #e2e3e5; border-radius: 4px;">
                <p><strong>Konfigurasi saat ini:</strong></p>
                <ul>
                    <li>Host: ' . htmlspecialchars($host) . '</li>
                    <li>Database: ' . htmlspecialchars($dbname) . '</li>
                    <li>Port: ' . $port . '</li>
                </ul>
            </div>
         </div>');
}

// Define a function to check if database tables exist
function checkDatabaseTables($pdo) {
    try {
        $tables = ['users', 'items', 'outgoing_transactions', 'outgoing_transaction_items'];
        $existing_tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            if (!in_array($table, $existing_tables)) {
                return false;
            }
        }
        return true;
    } catch (PDOException $e) {
        error_log('Error checking database tables: ' . $e->getMessage());
        return false;
    }
}

// Check if database is properly set up
$isDbReady = false;
try {
    // Cek apakah tabel-tabel sudah ada
    $isDbReady = checkDatabaseTables($pdo);
    
    // Jika tabel belum ada, coba buat
    if (!$isDbReady) {
        $isDbReady = createDatabaseTables($pdo);
        // Cek lagi setelah membuat tabel
        if ($isDbReady) {
            $isDbReady = checkDatabaseTables($pdo);
        }
    }
    
    // Jika masih belum ada tabel users, buat minimal tabel users
    if (!$isDbReady) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `email` varchar(100) NOT NULL,
            `name` varchar(100) NOT NULL,
            `role` enum('admin','production') NOT NULL DEFAULT 'production',
            `remember_token` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        
        $isDbReady = true;
    }
} catch (Exception $e) {
    error_log('Database Setup Error: ' . $e->getMessage());
    $isDbReady = false;
}
try {
    $isDbReady = checkDatabaseTables($pdo);
    
    // Jika tabel belum ada, coba import skema
    if (!$isDbReady) {
        $schema_file = __DIR__ . '/../database/create_tables.sql';
        if (file_exists($schema_file)) {
            $sql = file_get_contents($schema_file);
            $pdo->exec($sql);
            $isDbReady = checkDatabaseTables($pdo); // Cek lagi setelah import
        }
    }
} catch (Exception $e) {
    error_log('Database Setup Error: ' . $e->getMessage());
}

// Drop unique index on email if exists (allow multiple accounts share same email)
try {
    $idxStmt = $pdo->query("SHOW INDEX FROM users");
    $indexes = $idxStmt ? $idxStmt->fetchAll() : [];
    foreach ($indexes as $index) {
        if (!empty($index['Key_name']) && $index['Key_name'] === 'email') {
            $pdo->exec("ALTER TABLE users DROP INDEX email");
            break;
        }
    }
} catch (Exception $e) {
    error_log('Attempt to drop unique email index failed: ' . $e->getMessage());
}

// Ensure a default admin exists and has a known password (admin123)
try {
    // Check admin user by username
    $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ? LIMIT 1");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();

    if (!$admin) {
        // Create default admin
        $hashed = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (username, password, name, email, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $insert->execute(['admin', $hashed, 'Administrator', 'admin@example.com', 'admin']);
    } else {
        // If seeded with common placeholder hash for 'password', upgrade to 'admin123'
        $currentHash = $admin['password'];
        $isPlaceholder = password_verify('password', $currentHash);
        $alreadyAdmin123 = password_verify('admin123', $currentHash);
        if ($isPlaceholder && !$alreadyAdmin123) {
            $newHash = password_hash('admin123', PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->execute([$newHash, $admin['id']]);
        }
    }
} catch (Exception $e) {
    error_log('Admin user verification failed: ' . $e->getMessage());
}

define('DB_READY', $isDbReady);
?>
