<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Pastikan admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Lowongan</title>
</head>
<body>
    <div class="top-bar">
        <div class="nav-menu">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="vacancies.php" class="active">💼 Lowongan</a>
            <a href="applications.php">📝 Lamaran</a>
            <a href="manage_admins.php">👥 Kelola Admin</a>
        </div>
        <div class="user-menu">
            <span class="admin-badge">👑 Admin</span>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="content-box">
            <h2 class="content-title">➕ Tambah Lowongan Baru</h2>

            <form method="POST" action="add_vacancy.php">
                <div class="form-group">
                    <label for="title">Judul Posisi:</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi Pekerjaan:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="requirements">Persyaratan:</label>
                    <textarea id="requirements" name="requirements" required></textarea>
                </div>

                <div class="form-group">
                    <label for="max_applicants">
                        👥 Batas Maksimal Pelamar
                        <span class="label-hint">(Isi 0 untuk tanpa batas)</span>
                    </label>
                    <div class="input-wrapper">
                        <input type="number" 
                               name="max_applicants" 
                               id="max_applicants" 
                               min="0" 
                               value="<?= htmlspecialchars($vacancy['max_applicants'] ?? 0) ?>" 
                               class="form-control"
                               placeholder="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_visible" checked> Tampilkan lowongan sekarang
                    </label>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-save">💾 Simpan Lowongan</button>
                    <a href="vacancies.php" class="btn btn-back">← Kembali</a>
                </div>
            </form>
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
    }

    .top-bar {
        background: linear-gradient(135deg, var(--primary), #2c44b3);
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        box-sizing: border-box;
    }

    .nav-menu {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .nav-menu a {
        color: white;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        font-size: 15px;
        border-radius: 25px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .nav-menu a:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-1px);
    }

    .nav-menu a.active {
        background: #6688ee;
        box-shadow: 0 2px 8px rgba(102, 136, 238, 0.3);
    }

    .nav-menu a.active {
        background: rgba(255, 255, 255, 0.2);
    }

    .user-menu {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .admin-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.1);
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 15px;
    }

    .logout {
        background: var(--danger);
        color: white;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .logout:hover {
        background: #d90429;
        transform: translateY(-1px);
    }

    .container {
        max-width: 800px;
        margin: 30px auto;
        padding: 0 20px;
    }

    .content-box {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07);
    }

    .content-title {
        font-size: 20px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary);
        color: var(--text-dark);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    label {
        display: block;
        margin-top: 15px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
    }

    .label-hint {
        font-weight: 400;
        font-size: 13px;
        color: #718096;
        margin-left: 8px;
    }

    .input-wrapper {
        margin-top: 5px;
    }

    input[type="text"], 
    input[type="number"], 
    textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #cbd5e0;
        border-radius: 6px;
        font-size: 15px;
        font-family: inherit;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    input[type="text"]:focus, 
    input[type="number"]:focus, 
    textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    .input-note {
        margin-top: 8px;
        padding: 10px 12px;
        background: #f0f9ff;
        border-left: 3px solid var(--primary);
        border-radius: 4px;
        font-size: 13px;
        color: #475569;
        line-height: 1.5;
    }

    textarea {
        resize: vertical;
        min-height: 100px;
    }

    .btn-group {
        margin-top: 25px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn {
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 15px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        text-decoration: none;
    }

    .btn-save {
        background: var(--primary);
        color: white;
    }

    .btn-save:hover {
        background: #3251d4;
        transform: translateY(-2px);
    }

    .btn-back {
        background: #e2e8f0;
        color: var(--text-dark);
        text-decoration: none;
    }

    .btn-back:hover {
        background: #cbd5e0;
        transform: translateY(-2px);
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
</style>
</body>
</html>
