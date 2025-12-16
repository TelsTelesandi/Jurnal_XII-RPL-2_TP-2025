<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Logout</title>
</head>
<body>
    <div class="overlay">
        <div class="logout-modal">
            <h3 class="modal-title">Apakah Anda yakin ingin keluar?</h3>
            <div class="btn-group">
                <form method="post">
                    <button type="submit" name="confirm_logout" class="btn btn-danger">Ya, Keluar</button>
                </form>
                <a href="javascript:history.back()" class="btn btn-back">Kembali</a>
            </div>
        </div>
    </div>

    <style>
        :root {
            --primary: #4361ee;
            --danger: #ef233c;
            --text-dark: #2d3748;
            --bg-light: #f7fafc;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--bg-light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            width: 300px;
        }

        .modal-title {
            font-size: 16px;
            color: #2d3748;
            margin-bottom: 15px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-danger {
            background: #ef233c;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2f45;
        }

        .btn-back {
            background: #e2e8f0;
            color: #2d3748;
            border: none;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-back:hover {
            background: #cbd5e0;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</body>
</html>
