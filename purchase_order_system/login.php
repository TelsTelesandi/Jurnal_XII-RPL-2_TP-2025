<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    // Secure session configuration
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    
    session_start();
}

// Define base path
$base_path = __DIR__;

// Include database connection
require_once __DIR__ . '/includes/db_connect.php';

// Check if database is ready
if (!defined('DB_READY') || !DB_READY) {
    $error = 'Database belum siap. Silakan pastikan database sudah diatur dengan benar.';
    // Log the error
    error_log('Database not ready or tables missing');
}

// Initialize error variable
$error = '';

// Check for logout message in URL
if (isset($_GET['logout'])) {
    if ($_GET['logout'] === 'success' && isset($_GET['message'])) {
        $success_message = htmlspecialchars(urldecode($_GET['message']));
    } elseif ($_GET['logout'] === 'error') {
        $error = 'Terjadi kesalahan saat logout. Silakan coba lagi.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            // Set cookie for 30 days if remember me is checked
            if (isset($_POST['remember'])) {
                try {
                    $token = bin2hex(random_bytes(32));
                    $expire = time() + (86400 * 30); // 30 days
                    setcookie('remember_token', $token, [
                        'expires' => $expire,
                        'path' => '/',
                        'domain' => '',
                        'secure' => false,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                    
                    // Store token in database
                    $hashed_token = password_hash($token, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$hashed_token, $user['id']]);
                } catch (Exception $e) {
                    error_log('Error setting remember me: ' . $e->getMessage());
                    // Continue without remember me if there's an error
                }
            }
            
            // Redirect based on role from database
            if ($user['role'] === 'admin') {
                header('Location: dashboard_admin.php');
            } else {
                header('Location: dashboard_production.php');
            }
            exit();
        } else {
            $error = 'Username atau password salah';
        }
    }
}

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    try {
        $token = $_COOKIE['remember_token'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token IS NOT NULL AND remember_token != ''");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        foreach ($users as $user) {
            if (!empty($user['remember_token']) && password_verify($token, $user['remember_token'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                
                // Redirect based on role from database
                $redirect = ($user['role'] === 'admin') ? 'dashboard_admin.php' : 'dashboard_production.php';
                if (file_exists($redirect)) {
                    header("Location: $redirect");
                    exit();
                } else {
                    throw new Exception("Halaman tujuan tidak ditemukan: $redirect");
                }
            }
        }
        
        // If we get here, the token is invalid, so clear it
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    } catch (PDOException $e) {
        error_log('Database error in remember me: ' . $e->getMessage());
        // Clear invalid cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    } catch (Exception $e) {
        error_log('Error in remember me: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Purchase Order System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo i {
            font-size: 3rem;
            color: #0d6efd;
        }
        .btn-login {
            padding: 0.5rem 2rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card card">
            <div class="card-body p-4">
                <div class="login-logo">
                    <img src="OIP-removebg-preview.png" alt="Logo" width="80" class="mb-2">
                    <h2 class="mt-2">Purchase Order System</h2>
                </div>
                
                <div class="login-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['login_required'])): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php 
                            echo $_SESSION['login_required']; 
                            unset($_SESSION['login_required']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                            <?php if (!defined('DB_READY') || !DB_READY): ?>
                                <hr>
                                <p class="mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Jika Anda adalah administrator, silakan import file SQL terlebih dahulu.
                                </p>
                            <?php endif; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="mt-4">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required autofocus>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>
                        
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="register.php" class="text-primary">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menambahkan animasi saat form muncul
        document.addEventListener('DOMContentLoaded', function() {
            const loginCard = document.querySelector('.login-card');
            loginCard.style.opacity = '0';
            loginCard.style.transform = 'translateY(20px)';
            loginCard.style.transition = 'all 0.4s ease-out';
            
            setTimeout(() => {
                loginCard.style.opacity = '1';
                loginCard.style.transform = 'translateY(0)';
            }, 100);
            
            // Form validation
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const username = document.querySelector('input[name="username"]');
                    const password = document.querySelector('input[name="password"]');
                    
                    if (!username.value || !password.value) {
                        e.preventDefault();
                        alert('Harap isi username dan password');
                        return false;
                    }
                    
                    // Show loading state
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Masuk...';
                    }
                    
                    return true;
                });
            }
            
            // Animasi untuk input focus
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('.input-group-text').style.borderColor = '#4361ee';
                    this.parentElement.querySelector('.input-group-text').style.backgroundColor = '#e9ecef';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.querySelector('.input-group-text').style.borderColor = '#e0e0e0';
                    this.parentElement.querySelector('.input-group-text').style.backgroundColor = '#f8f9fa';
                });
            });
        });
    </script>
</body>
</html>
