<?php
require_once 'includes/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? ''; // Require role selection
    
        // Basic validation
        if (empty($username) || empty($password) || empty($confirm_password) || empty($name) || empty($email) || empty($role)) {
            throw new Exception('Semua field harus diisi');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Konfirmasi password tidak cocok');
        }
        
        if (strlen($password) < 8) {
            throw new Exception('Password minimal 8 karakter');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid');
        }
        
        if (!in_array($role, ['admin', 'production'])) {
            throw new Exception('Peran tidak valid');
        }
        
        // Allow multiple admin accounts
        // Removed restriction for single admin account
        
        // Check if username already exists (allow duplicate emails)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            throw new Exception('Username sudah terdaftar');
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (username, password, name, email, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            if (!$stmt->execute([$username, $hashed_password, $name, $email, $role])) {
                throw new Exception('Gagal menyimpan data pengguna');
            }
            
            // Set role based on form selection
            // No automatic role change for first user
            
            $pdo->commit();
            $success = 'Pendaftaran berhasil! Silakan login untuk melanjutkan.';
            // Clear form
            $_POST = [];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e; // Re-throw to be caught by the outer try-catch
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log('Registration error: ' . $error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Purchase Order System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .btn-register {
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-outline-secondary {
            transition: all 0.3s;
        }
        
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white !important;
        }
        .register-container {
            width: 100%;
            margin: 2rem 0;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .register-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-logo i {
            font-size: 2.5rem;
            color: #0d6efd;
        }
        .form-text {
            font-size: 0.85rem;
            color: #6c757d;
        }
        /* Style for role select */
        #role {
            cursor: pointer;
        }
        #role option[value=""] {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="register-container">
        <div class="register-header">
            <div class="register-logo">
                <i class="fas fa-user-plus"></i>
                <h2 class="h4 mb-0">Buat Akun Baru</h2>
                <p class="mb-0 mt-2 opacity-75">Isi form di bawah untuk mendaftar</p>
            </div>
        </div>
        
        <div class="register-body">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success text-center">
                    <div class="d-flex justify-content-center mb-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <h5 class="alert-heading">Pendaftaran Berhasil!</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($success); ?></p>
                    <div class="mt-3">
                        <a href="login.php" class="btn btn-success px-4">
                            <i class="fas fa-sign-in-alt me-2"></i> Masuk Sekarang
                        </a>
                    </div>
                </div>
            <?php else: ?>
            <form method="POST" action="" id="registerForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                   placeholder="Masukkan nama lengkap"
                                   required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   placeholder="contoh@email.com"
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">@</span>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                               placeholder="pilih username"
                               pattern="[a-zA-Z0-9_]+"
                               title="Hanya huruf, angka, dan underscore (_) yang diperbolehkan"
                               required>
                    </div>
                    <div class="form-text">Gunakan huruf, angka, dan underscore (_) saja</div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Minimal 8 karakter"
                                   minlength="8"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength mt-2">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <div class="form-text" id="passwordStrengthText">Kekuatan password: <span>Lemah</span></div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Ketik ulang password"
                                   required>
                        </div>
                        <div class="form-text" id="passwordMatchText"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="role" class="form-label">Pilih Role <span class="text-danger">*</span></label>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                        <select class="form-select" id="role" name="role" required>
                            <option value="" disabled <?php echo !isset($_POST['role']) ? 'selected' : ''; ?>>-- Pilih peran --</option>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="production" <?php echo (isset($_POST['role']) && $_POST['role'] === 'production') ? 'selected' : ''; ?>>Produksi</option>
                        </select>
                    </div>
                    <div class="form-text">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="roleDescription">
                                <?php 
                                if (isset($_POST['role'])) {
                                    echo $_POST['role'] === 'admin' 
                                        ? 'Mengelola sistem, data barang, dan persetujuan permintaan' 
                                        : 'Membuat dan melacak permintaan barang';
                                } else {
                                    echo 'Pilih peran pengguna';
                                }
                                ?>
                            </span>
                        </small>
                    </div>
                </div>
                
                
                <div class="text-center mt-3">
                    <p class="mb-0">Sudah punya akun? <a href="login.php" class="text-primary fw-bold">Login</a></p>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="login.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
                    </a>
                    <button type="submit" class="btn btn-primary btn-register" id="submitBtn">
                        <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update role description when role changes
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll to error message if any
            const errorAlert = document.querySelector('.alert-danger');
            if (errorAlert) {
                errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            // Focus on first input field
            const firstInput = document.querySelector('form input');
            if (firstInput) {
                firstInput.focus();
            }
            const roleSelect = document.getElementById('role');
            const roleDescription = document.getElementById('roleDescription');
            
            if (roleSelect) {
                // Set initial description
                updateRoleDescription(roleSelect.value);
                
                // Update description when role changes
                roleSelect.addEventListener('change', function() {
                    updateRoleDescription(this.value);
                });
                
                // Jangan set default role otomatis; biarkan user memilih sendiri
            }
            
            function updateRoleDescription(role) {
                if (!roleDescription) return;
                
                const descriptions = {
                    '': 'Pilih peran pengguna',
                    'admin': 'Mengelola sistem, data barang, dan persetujuan permintaan',
                    'production': 'Membuat dan melacak permintaan barang'
                };
                
                roleDescription.textContent = descriptions[role] || 'Pilih peran pengguna';
            }
            
            // Tambahkan event listener untuk form submit
            const form = document.getElementById('registerForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const roleSelectEl = document.getElementById('role');
                    const radioChecked = document.querySelector('input[name="role"]:checked');
                    const selectValue = roleSelectEl ? roleSelectEl.value : '';

                    if (!radioChecked && (!selectValue || selectValue === '')) {
                        e.preventDefault();
                        alert('Silakan pilih peran (Admin/Produksi)');
                        return false;
                    }
                });
            }
            
            // Debug: Tampilkan semua elemen dengan class role-card
            console.log('Semua card role:', document.querySelectorAll('.role-card'));
        });
        // Password strength indicator
        // Inisialisasi strength indicator
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthBadge = document.getElementById('password-strength');
            
            if (!strengthBadge) return;
            
            // Reset strength indicator
            strengthBadge.className = 'badge';
            
            if (password.length === 0) {
                strengthBadge.textContent = '';
                return;
            }
            
            let strength = 0;
            
            // Check for lowercase, uppercase, numbers, and special characters
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[!@#$%^&*(),.?":{}|<>]+/)) strength++;
            
            // Update UI
            if (strength <= 2) {
                strengthBadge.className = 'badge bg-danger';
                strengthBadge.textContent = 'Weak';
            } else if (strength <= 4) {
                strengthBadge.className = 'badge bg-warning';
                strengthBadge.textContent = 'Medium';
            } else {
                strengthBadge.className = 'badge bg-success';
                strengthBadge.textContent = 'Strong';
            }
            });
        }
    </script>
</body>
</html>
