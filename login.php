<?php
session_start();
include "config.php";

// Cek jika user sudah login, redirect ke dashboard sesuai role
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: dashboard_admin.php");
    } elseif($_SESSION['role'] == 'produksi') {
        header("Location: dashboard_produksi.php");
    } else {
        header("Location: dashboard_user.php"); 
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // verifikasi password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Update status user menjadi online
            mysqli_query($conn, "UPDATE users SET status='aktif' WHERE id='{$user['id']}'");

            if ($user['role'] == 'admin') {
                header("Location: dashboard_admin.php");
            } elseif ($user['role'] == 'produksi') {
                header("Location: dashboard_produksi.php");
            } else {
                header("Location: dashboard_user.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login PPAM</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: url('A.png') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        /* Overlay untuk membuat gambar lebih gelap dan text lebih mudah dibaca */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6); /* Overlay hitam transparan */
            z-index: 0;
        }
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #2196f3 0%, rgba(33,150,243,0) 70%);
            top: -150px;
            right: -150px;
            opacity: 0.1;
            filter: blur(20px);
        }
        body::after {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, #2196f3 0%, rgba(33,150,243,0) 70%);
            bottom: -125px;
            left: -125px;
            opacity: 0.1;
            filter: blur(20px);
        }
        .login-container {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.3);
            padding: 40px 32px;
            width: 350px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .login-container::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: linear-gradient(45deg, rgba(33,150,243,0.1), rgba(33,150,243,0));
            border-radius: 25px;
            z-index: -1;
            pointer-events: none;
        }
        .login-container h2 {
            margin-bottom: 24px;
            color: #fff;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .login-logo {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-logo img {
            height: 60px;
            width: auto;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.3));
        }
        label {
            color: rgba(255,255,255,0.8);
            font-weight: 400;
            display: block;
            margin-bottom: 8px;
            text-align: left;
            font-size: 0.9em;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            margin-bottom: 18px;
            background: rgba(255,255,255,0.05);
            font-size: 16px;
            color: #fff;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            background: rgba(255,255,255,0.1);
            border-color: rgba(33,150,243,0.5);
            outline: none;
        }
        button {
            background: #2196f3;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 14px 0;
            width: 100%;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        button:hover {
            background: #1976d2;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(33,150,243,0.3);
        }
        button:active {
            transform: translateY(0);
        }
        .error-message {
            color: #ef5350;
            margin-bottom: 16px;
            font-weight: 400;
            background: rgba(239,83,80,0.1);
            padding: 10px;
            border-radius: 6px;
            border: 1px solid rgba(239,83,80,0.2);
        }
        ::placeholder {
            color: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="logo_w.png" alt="Banshu Plastic Logo">
        </div>
        <h2>Login Aplikasi PPAM</h2>
        <?php if (!empty($error)) echo "<div class='error-message'>$error</div>"; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
