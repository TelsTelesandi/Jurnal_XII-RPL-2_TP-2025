<?php
/**
 * File: register.php
 * Fungsi: Halaman registrasi untuk pelanggan baru
 * 
 * Pelanggan bisa mendaftar sendiri untuk membuat akun
 * dan mulai melakukan purchase request
 */

require_once 'config/database.php';
require_once 'config/session.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    redirectToDashboard();
}

$error = '';
$success = '';

// Proses registrasi ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validasi input
    if (empty($username) || empty($password) || empty($fullName) || empty($email)) {
        $error = 'Username, password, nama lengkap, dan email wajib diisi';
    } elseif (strlen($username) < 4) {
        $error = 'Username minimal 4 karakter';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirmPassword) {
        $error = 'Password dan konfirmasi password tidak cocok';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        try {
            $pdo = getDBConnection();
            
            // Cek apakah username sudah ada
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username sudah digunakan';
            } else {
                // Cek apakah email sudah ada
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email sudah terdaftar';
                } else {
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert user baru dengan role pelanggan
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, password, full_name, email, phone, company_name, address, role, is_active, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'pelanggan', 1, NOW())
                    ");
                    
                    $stmt->execute([
                        $username,
                        $hashedPassword,
                        $fullName,
                        $email,
                        $phone,
                        $companyName,
                        $address
                    ]);
                    
                    $success = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                    
                    // Redirect ke login setelah 2 detik
                    header("refresh:2;url=login.php");
                }
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
    <title>Daftar Akun - RollMate</title>
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
                    <a href="login.php" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition duration-300">
                        Masuk
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex items-center justify-center min-h-[calc(100vh-4rem)] py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-2xl space-y-8">
            
            <!-- Header -->
            <div class="text-center">
                <h2 class="text-4xl font-bold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">
                    Buat Akun Baru
                </h2>
                <p class="mt-3 text-base text-slate-500">
                    Daftar sebagai pelanggan untuk mulai memesan produk laminasi
                </p>
            </div>

            <!-- Success Message -->
            <?php if ($success): ?>
            <div class="rounded-lg bg-green-50 border border-green-100 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

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
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-semibold text-slate-800 mb-2">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-300 ease-in-out"
                                placeholder="Minimal 4 karakter"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            >
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-800 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-300 ease-in-out"
                                placeholder="nama@email.com"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-800 mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-300 ease-in-out"
                                placeholder="Minimal 6 karakter"
                            >
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-semibold text-slate-800 mb-2">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                required
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-300 ease-in-out"
                                placeholder="Ketik ulang password"
                            >
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label for="full_name" class="block text-sm font-semibold text-slate-800 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            required
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-300 ease-in-out"
                            placeholder="Nama lengkap Anda"
                            value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                        >
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-800 mb-2">
                                No. Telepon
                            </label>
                            <input 
                                type="text" 
                                id="phone" 
                                name="phone"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-300 ease-in-out"
                                placeholder="08xxxxxxxxxx"
                                value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                            >
                        </div>

                        <!-- Company Name -->
                        <div>
                            <label for="company_name" class="block text-sm font-semibold text-slate-800 mb-2">
                                Nama Perusahaan
                            </label>
                            <input 
                                type="text" 
                                id="company_name" 
                                name="company_name"
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-300 ease-in-out"
                                placeholder="PT/CV (opsional)"
                                value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-semibold text-slate-800 mb-2">
                            Alamat
                        </label>
                        <textarea 
                            id="address" 
                            name="address"
                            rows="3"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-300 ease-in-out"
                            placeholder="Alamat lengkap (opsional)"
                        ><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Daftar Sekarang
                    </button>
                    
                </form>
            </div>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-sm text-slate-600">
                    Sudah punya akun? 
                    <a href="login.php" class="font-semibold text-blue-600 hover:text-blue-700 transition duration-300">
                        Masuk di sini
                    </a>
                </p>
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
