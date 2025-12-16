<?php
session_start();
require_once 'config/database.php';

$error = '';
$show_success = false;

// Cek koneksi database
$use_pdo = (isset($pdo) && $pdo instanceof PDO);
$use_mysqli = (isset($conn) && $conn instanceof mysqli);

if (!$use_pdo && !$use_mysqli) {
    die('Database connection error. Expecting $pdo (PDO) or $conn (mysqli).');
}

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $degree    = trim($_POST['degree'] ?? '');
    $major     = trim($_POST['major'] ?? '');

    if (empty($username) || empty($password) || empty($email)) {
        $error = 'Username, password, dan email wajib diisi.';
    } else {
        $sql = "INSERT INTO users (username, password, email, full_name, degree, major, role, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'user', NOW())";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ssssss', $username, $password, $email, $full_name, $degree, $major);
            if ($stmt->execute()) {
                echo "<script>
                    alert('Registrasi berhasil! Silakan login.');
                    window.location.href = 'login.php';
                </script>";
                exit();
            }
            $stmt->close();
        }
        $error = 'Gagal registrasi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Register - Hire Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
            text-align: center;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 1rem;
        }

        .form-label {
            color: #1e293b;
            font-weight: 500;
            font-size: 15px;
            margin-bottom: 6px;
        }

        input.form-control, select.form-select {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            font-size: 15px;
            transition: all 0.3s ease;
            color: #1e293b;
        }

        input.form-control:focus, select.form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 8px rgba(59, 130, 246, 0.3);
        }

        input::placeholder {
            color: #94a3b8;
            font-style: italic;
        }

        .btn-primary {
            background-color: #3b82f6;
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .btn-secondary {
            background-color: #e2e8f0;
            color: #1e293b;
            border: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background-color: #cbd5e1;
        }

        .alert {
            font-size: 14px;
        }

        .card-body {
            padding: 2rem;
        }

        .register-container {
            width: 100%;
            max-width: 550px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card shadow">
            <div class="card-header">
                📝 Register Hire Job
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post" action="register.php" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input name="username" class="form-control" required placeholder="Masukkan username"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="Masukkan email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input name="full_name" class="form-control" placeholder="Masukkan nama lengkap"
                            value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lulusan</label>
                        <select name="degree" class="form-select" required>
                            <option value="" disabled <?php echo (empty($_POST['degree'] ?? '')) ? 'selected' : ''; ?>>Pilih tingkat pendidikan</option>
                            <option value="S1" <?php echo (($_POST['degree'] ?? '') === 'S1') ? 'selected' : ''; ?>>S1</option>
                            <option value="S2" <?php echo (($_POST['degree'] ?? '') === 'S2') ? 'selected' : ''; ?>>S2</option>
                            <option value="S3" <?php echo (($_POST['degree'] ?? '') === 'S3') ? 'selected' : ''; ?>>S3</option>
                            <option value="Lainnya" <?php echo (($_POST['degree'] ?? '') === 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jurusan</label>
                        <div class="form-text">Masukkan jurusan pendidikan.</div>
                        <input name="major" class="form-control" placeholder="Contoh: Teknik Informatika"
                            value="<?php echo htmlspecialchars($_POST['major'] ?? ''); ?>">
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary px-4">Register</button>
                        <a href="login.php" class="btn btn-secondary px-4">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
