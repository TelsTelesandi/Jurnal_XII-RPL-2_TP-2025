<?php
/**
 * File: login.php
 * Fungsi: Halaman login untuk admin dan user
 * 
 * Halaman ini menampilkan form login dan memproses autentikasi user.
 */

require_once 'config/database.php';
require_once 'config/session.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (isLoggedIn()) {
    redirectToDashboard();
    exit;
}

$error = '';

// Proses login ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Cari user berdasarkan username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            // Verifikasi password
            if ($user && password_verify($password, $user['password'])) {
                // Cek apakah user aktif
                if ($user['is_active'] != 1) {
                    $error = 'Akun Anda tidak aktif. Hubungi administrator.';
                } else {
                    // Login berhasil, simpan data ke session
                    setUserSession($user);
                    setFlashMessage('Login berhasil! Selamat datang, ' . $user['full_name'], 'success');
                    
                    // Redirect sesuai role
                    // Cek role user untuk menentukan halaman akses
                    // Jika pelanggan, tampilkan halaman marketplace
                    // Jika admin, tampilkan dashboard request
                    // Jika staff, tampilkan daftar purchase order
                    if ($user['role'] === 'admin') {
                        header('Location: admin/dashboard.php');
                    } elseif ($user['role'] === 'staff') {
                        header('Location: staff/dashboard.php');
                    } else {
                        header('Location: pelanggan/dashboard.php');
                    }
                    exit;
                }
            } else {
                $error = 'Username atau password salah';
            }
            
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RollMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-white min-h-screen">
    
    <!-- Navbar -->
    <nav class="border-b border-slate-200 bg-white/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <img src="gambar/logo pt.png" alt="Logo PT" class="h-10 w-auto">
                </div>
                <div class="flex items-center space-x-4">
                    <a href="register.php" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition duration-300">
                        Daftar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex items-center justify-center min-h-[calc(100vh-4rem)] py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            
            <!-- Header -->
            <div class="text-center">
                <h2 class="text-4xl font-bold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">
                    Selamat Datang
                </h2>
                <p class="mt-3 text-base text-slate-500">
                    Masuk ke akun RollMate Anda untuk melanjutkan
                </p>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
            <div class="rounded-lg bg-red-50 border border-red-100 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Form Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
                <form method="POST" action="" class="space-y-6">
                    
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-800 mb-2">
                            Username
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-300 ease-in-out"
                            placeholder="Masukkan username Anda"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        >
                    </div>
                    
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-800 mb-2">
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-300 ease-in-out"
                            placeholder="Masukkan password Anda"
                        >
                    </div>
                    
                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Masuk ke Dashboard
                    </button>
                    
                </form>
            </div>

            <!-- Register Link -->
            <div class="text-center">
                <p class="text-sm text-slate-600">
                    Belum punya akun? 
                    <a href="register.php" class="font-semibold text-blue-600 hover:text-blue-700 transition duration-300">
                        Daftar sebagai Pelanggan
                    </a>
                </p>
            </div>
            
            <!-- Demo Accounts -->
            <div class="mt-12 pt-8 border-t border-slate-200">
                <p class="text-xs font-semibold text-slate-500 text-center mb-4 uppercase tracking-wide">
                    Akun Demo untuk Testing
                </p>
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-sm font-medium text-slate-600">Admin</span>
                        <code class="text-sm font-mono text-slate-800">admin / admin123</code>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-sm font-medium text-slate-600">Staff</span>
                        <code class="text-sm font-mono text-slate-800">staff1 / admin123</code>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-sm font-medium text-slate-600">Pelanggan</span>
                        <code class="text-sm font-mono text-slate-800">customer1 / admin123</code>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-slate-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <p class="text-center text-sm text-slate-500">
                &copy; 2024 RollMate. Sistem Marketplace Laminasi Industri.
            </p>
        </div>
    </footer>
    
</body>
</html>
