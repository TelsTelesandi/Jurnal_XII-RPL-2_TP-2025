<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (isset($_POST['delete_admin'])) {
    $admin_id = (int)($_POST['admin_id'] ?? 0);
    if ($admin_id && $admin_id !== (int)$_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
        if ($stmt) {
            $stmt->bind_param('i', $admin_id);
            if (!$stmt->execute()) {
                $_SESSION['error'] = "Gagal menghapus admin: " . $stmt->error;
            } else {
                $_SESSION['success'] = "Admin berhasil dihapus.";
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Query delete gagal: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Perintah tidak valid atau mencoba menghapus akun sendiri.";
    }

    header('Location: manage_admins.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $admin_id = isset($_POST['admin_id']) && $_POST['admin_id'] !== '' ? (int)$_POST['admin_id'] : null;

    if ($username === '') {
        $_SESSION['error'] = "Username tidak boleh kosong.";
    } else {
        $stmt = null;

        if ($admin_id) {
            // Update existing admin
            if ($password !== '') {
                $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ? AND role = 'admin'");
                if ($stmt) {
                    $stmt->bind_param('ssi', $username, $password, $admin_id);
                }
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ? AND role = 'admin'");
                if ($stmt) {
                    $stmt->bind_param('si', $username, $admin_id);
                }
            }
        } else {
            // Add new admin - password required
            if ($password === '') {
                $_SESSION['error'] = "Password wajib diisi untuk admin baru.";
            } else {
                $major = ''; // supaya field major tidak error
                $stmt = $conn->prepare("INSERT INTO users (username, password, role, major) VALUES (?, ?, 'admin', ?)");
                if ($stmt) {
                    $stmt->bind_param('sss', $username, $password, $major);
                }
            }
        }

        // Execute only jika $stmt valid dan tidak ada error
        if (!isset($_SESSION['error']) && $stmt) {
            if ($stmt->execute()) {
                $_SESSION['success'] = $admin_id ? "Admin berhasil diperbarui." : "Admin baru berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Gagal menyimpan data: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    header('Location: manage_admins.php');
    exit();
}

// 🔹 Ambil semua admin
$admins = $conn->query("SELECT id, username FROM users WHERE role = 'admin' ORDER BY username");
if ($admins === false) {
    die('Query error: ' . $conn->error);
}

// ✳️ Jika sedang edit: ambil data admin untuk prefill
$editing = false;
$edit_admin = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['admin_id'])) {
    $edit_id = (int)$_GET['admin_id'];
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $edit_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $edit_admin = $res->fetch_assoc();
        $stmt->close();
        if ($edit_admin) $editing = true;
    }
}

// ✳️ Jika sedang konfirmasi hapus
$confirm_delete = false;
$confirm_admin = null;
if (isset($_GET['action']) && $_GET['action'] === 'confirm_delete' && isset($_GET['admin_id'])) {
    $cd_id = (int)$_GET['admin_id'];
    if ($cd_id !== (int)$_SESSION['user_id']) {
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $cd_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $confirm_admin = $res->fetch_assoc();
            $stmt->close();
            if ($confirm_admin) $confirm_delete = true;
        }
    } else {
        $_SESSION['error'] = "Tidak bisa menghapus akun sendiri.";
        header('Location: manage_admins.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

<!-- 🔹 Navbar -->
<div class="top-bar">
    <div class="nav-menu">
        <?php
        $current = basename($_SERVER['PHP_SELF']);
        $menus = [
            'dashboard.php' => ['📊', 'Dashboard'],
            'applications.php' => ['📝', 'Lamaran'],
            'vacancies.php' => ['💼', 'Lowongan'],
            'manage_admins.php' => ['👥', 'Kelola Admin']
        ];
        foreach ($menus as $file => $data) {
            $active = $current === $file ? 'active' : '';
            echo '<a href="' . htmlspecialchars($file) . '" class="' . $active . '">' . $data[0] . ' ' . htmlspecialchars($data[1]) . '</a>';
        }
        ?>
    </div>

    <div class="user-menu">
        <div class="admin-badge" title="<?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>">
            <span class="badge-emoji">👑</span>
            <span class="badge-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
        </div>
    </div>
</div>

<!-- 🔹 Konten -->
<div class="container">
    <div class="content-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 class="content-title">👥 Kelola Admin</h2>
            <!-- tombol ini sekarang link ke server-side form -->
            <a href="manage_admins.php?action=add" class="btn btn-green">➕ Tambah Admin</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="admin-grid">
            <?php while ($a = $admins->fetch_assoc()): ?>
                <div class="card">
                    <h3>👤 <?php echo htmlspecialchars($a['username']); ?></h3>
                    <div class="btn-group">
                        <!-- Edit via GET action -->
                        <a class="btn btn-blue" href="manage_admins.php?action=edit&admin_id=<?php echo (int)$a['id']; ?>">✏️ Edit</a>

                        <?php if ((int)$a['id'] !== (int)$_SESSION['user_id']): ?>
                            <!-- Hapus -> menuju halaman konfirmasi (server-side) -->
                            <a class="btn btn-red" href="manage_admins.php?action=confirm_delete&admin_id=<?php echo (int)$a['id']; ?>">🗑️ Hapus</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Jika action = add atau edit -> tampilkan form (server-side modal replacement) -->
        <?php if ((isset($_GET['action']) && $_GET['action'] === 'add') || $editing): ?>
            <div class="form-panel">
                <h3><?php echo $editing ? 'Edit Admin' : 'Tambah Admin'; ?></h3>
                <form method="POST" action="manage_admins.php">
                    <input type="hidden" name="admin_id" value="<?php echo $editing ? (int)$edit_admin['id'] : ''; ?>">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required value="<?php echo $editing ? htmlspecialchars($edit_admin['username']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Password<?php echo $editing ? ' (kosongkan jika tidak ingin mengubah)' : ''; ?></label>
                        <input type="password" name="password" class="form-control" <?php echo $editing ? '' : 'required'; ?>>
                    </div>
                    <div class="btn-row">
                        <button type="submit" name="save_admin" class="btn btn-primary">💾 Simpan</button>
                        <a href="manage_admins.php" class="btn btn-gray">Batal</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Halaman konfirmasi hapus (server-side) -->
        <?php if ($confirm_delete && $confirm_admin): ?>
            <div class="confirm-panel">
                <h3>Konfirmasi Hapus</h3>
                <p>Yakin ingin menghapus admin <strong><?php echo htmlspecialchars($confirm_admin['username']); ?></strong> ?</p>
                <form method="POST" action="manage_admins.php" style="display:inline-block;margin-right:10px;">
                    <input type="hidden" name="admin_id" value="<?php echo (int)$confirm_admin['id']; ?>">
                    <button type="submit" name="delete_admin" class="btn btn-red">🗑️ Ya, Hapus</button>
                </form>
                <a href="manage_admins.php" class="btn btn-gray">Batal</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- 🔹 CSS (tombol dan form diperbarui) -->
<style>
    :root {
        --primary: #4361ee;
        --primary-2: #2c44b3;
        --danger: #ef233c;
        --success: #0e9f6e;
        --text-dark: #1f2937;
        --text-light: #6b7280;
        --bg-light: #f7fafc;
        --card-bg: #ffffff;
        --muted: #94a3b8;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Arial, sans-serif;
        background: var(--bg-light);
        color: var(--text-dark);
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

    .admin-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.1);
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 15px;
    }

    .container { max-width:1200px; margin:30px auto; padding:0 20px; }

    .content-box {
        background: var(--card-bg);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 8px 24px rgba(15,23,42,0.06);
    }

    .content-title { font-size:20px; margin:0; display:flex; align-items:center; gap:10px; font-weight:600; color:var(--text-dark); }

    /* Grid of admin cards */
    .admin-grid {
        display:grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap:16px;
        margin-top:20px;
    }

    .card {
        background: linear-gradient(180deg, rgba(255,255,255,0.95), #fbfdff);
        border-radius: 12px;
        padding:16px;
        box-shadow: 0 6px 18px rgba(16,24,40,0.04);
        display:flex;
        flex-direction:column;
        gap:12px;
    }
    .card h3 { margin:0; font-size:16px; color:var(--text-dark); }

    .btn-group { display:flex; gap:8px; margin-top:auto; }

    /* Button system */
    .btn {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        padding:10px 14px;
        border-radius:10px;
        text-decoration:none;
        font-weight:600;
        font-size:14px;
        border: none;
        cursor: pointer;
        transition: transform .12s ease, box-shadow .12s ease;
    }
    .btn:focus { outline: 3px solid rgba(67,97,238,0.18); }

    .btn-green {
        background: linear-gradient(135deg,#2dd4bf,#10b981);
        color: white;
        box-shadow: 0 6px 18px rgba(16,185,129,0.18);
    }
    .btn-blue, .btn-primary {
        background: linear-gradient(135deg,var(--primary),var(--primary-2));
        color: white;
        box-shadow: 0 8px 24px rgba(67,97,238,0.16);
    }
    .btn-red {
        background: linear-gradient(135deg,#ff7b7b,var(--danger));
        color: white;
        box-shadow: 0 8px 24px rgba(239,35,60,0.12);
    }
    .btn-gray {
        background: #eef2f7;
        color: var(--text-dark);
        box-shadow: none;
        border: 1px solid #e2e8f0;
    }

    .btn:hover { transform: translateY(-3px); }
    .btn:active { transform: translateY(-1px); }

    /* form panel (server-side "modal") */
    .form-panel, .confirm-panel {
        margin-top:30px;
        padding:20px;
        border-radius:12px;
        border: 1px solid #edf2f7;
        background: linear-gradient(180deg, #ffffff, #fbfdff);
        box-shadow: 0 12px 30px rgba(2,6,23,0.04);
    }
    .form-panel h3, .confirm-panel h3 { margin-top:0; }

    .form-group { margin-bottom:14px; }
    .form-group label { display:block; margin-bottom:6px; color:var(--text-dark); font-weight:600; }
    .form-control { width:100%; padding:10px 12px; border-radius:10px; border:1px solid #e6eef6; font-size:14px; }

    .btn-row { display:flex; gap:12px; margin-top:8px; }

    .alert { padding:12px 14px; border-radius:10px; margin-bottom:16px; font-weight:600; }
    .alert.success { background:#ecfdf5; color:#03543f; border:1px solid #bbf7d0; }
    .alert.danger { background:#fff1f2; color:#7f1d1d; border:1px solid #ffc7d0; }

    @media (max-width:720px) {
        .admin-grid { grid-template-columns: 1fr; }
        .top-bar { padding:12px 16px; height:auto; gap:10px; flex-direction:row; }
    }
</style>

</body>
</html>
