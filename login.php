<?php
session_start();
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

// Database connection
$host = 'localhost';
$dbname = 'dbcv';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Login - Hire Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="card shadow">
            <div class="card-header text-center py-4">

                <img src="assets/image.png" 
                     alt="Revolutek" 
                     style="max-height:100px; width:auto; margin-bottom:14px; display:inline-block;">

                <h3 class="mb-0" style="color:white; font-weight:600;">🔑 Login Hire Job</h3>
            </div>

            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger text-center">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="process_login.php" method="post">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Masuk</button>
                </form>

                <div class="mt-4 text-center">
                    <p class="text-muted mb-2">Belum punya akun?</p>
                    <a href="register.php" class="register-btn">📝 Daftar di sini</a>
                </div>
            </div>
        </div>

<style>
    body {
        background: linear-gradient(135deg, #93c5fd, #bfdbfe);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: 'Poppins', sans-serif;
    }

    .card {
        border: none;
        border-radius: 15px;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        color: white;
        border-radius: 15px 15px 0 0;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        box-shadow: inset 0 -2px 6px rgba(255, 255, 255, 0.2);
    }

    .form-label {
        color: #1e293b;
        font-weight: 500;
        font-size: 15px;
    }

    input.form-control {
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        padding: 10px 14px;
        font-size: 15px;
        transition: all 0.3s ease;
        color: #1e293b;
    }

    input.form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 8px rgba(59, 130, 246, 0.3);
    }

    .btn-primary {
        background-color: #3b82f6;
        border: none;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-primary:hover {
        background-color: #2563eb;
    }

    .register-btn {
        display: inline-block;
        background: #e2e8f0;
        color: #2d3748;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .register-btn:hover {
        background: #cbd5e0;
        color: #1a202c;
        transform: translateY(-1px);
    }

    .login-container {
        width: 100%;
        max-width: 500px;
    
    }

    .card:hover {
        transform: translateY(-3px);
        transition: all 0.3s ease;
    }
</style>

    </div>
</body>
</html>
