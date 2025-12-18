<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include "config.php";

// Tambah User
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];
    $status   = $_POST['status'];

    // Cek apakah username sudah ada
    $checkQuery = "SELECT id FROM users WHERE username = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Username sudah ada, tampilkan error
        $_SESSION['error'] = "Username '$username' sudah ada. Silakan gunakan username lain.";
    } else {
        // Username belum ada, lakukan INSERT
        $query = "INSERT INTO users (username, password, role, status) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $password, $role, $status);
        if ($stmt->execute()) {
            $_SESSION['success'] = "User '$username' berhasil ditambahkan.";
        } else {
            $_SESSION['error'] = "Gagal menambahkan user: " . $stmt->error;
        }
    }
    header("Location: data_user.php");
    exit();
}

// Hapus User
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $deleteQuery = "DELETE FROM users WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $id);
    if ($deleteStmt->execute()) {
        $_SESSION['success'] = "User berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus user: " . $deleteStmt->error;
    }
    header("Location: data_user.php");
    exit();
}

// Update User
if (isset($_POST['update_user'])) {
    $id       = $_POST['id'];
    $username = $_POST['username'];
    $role     = $_POST['role'];
    $status   = $_POST['status'];

    // Cek apakah username baru sudah digunakan oleh user lain
    $checkQuery = "SELECT id FROM users WHERE username = ? AND id != ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("si", $username, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $_SESSION['error'] = "Username '$username' sudah digunakan oleh user lain.";
    } else {
        $query = "UPDATE users SET username = ?, role = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $username, $role, $status, $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "User '$username' berhasil diperbarui.";
        } else {
            $_SESSION['error'] = "Gagal memperbarui user: " . $stmt->error;
        }
    }
    header("Location: data_user.php");
    exit();
}

// Ambil data user
$result = mysqli_query($conn, "SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #e0f7fa 0%, #00bcd4 100%);
            min-height: 100vh;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-button {
            text-decoration: none;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }
        .back-button:hover {
            color: #0d6efd;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .table {
            width: 100%;
            margin-bottom: 0;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(135deg, #0288d1 0%, #01579b 100%);
            padding-top: 20px;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar-brand img {
            height: 40px;
            width: auto;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover, 
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            margin-left: 250px;
            padding: 32px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f0f7fa 0%, #e1f5fe 100%);
        }
        
        .table-container {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-top: 24px;
        }

        .table thead th {
            background: #1976d2;
            color: white;
            font-weight: 500;
            border: none;
            padding: 12px 16px;
        }

        .table tbody td {
            padding: 12px 16px;
            vertical-align: middle;
        }

        .form-container {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
        }

        .header {
            background: white;
            padding: 20px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 24px;
        }

        .header h3 {
            color: #1976d2;
            margin: 0;
            font-weight: 600;
        }

        /* Mobile toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 12px;
            left: 12px;
            z-index: 1200;
            background: #1e88e5;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 8px 12px;
            box-shadow: 0 6px 14px rgba(30,136,229,0.25);
        }

        @media (max-width: 992px) {
            .sidebar { left: -260px; transition:left .25s ease; width: 230px; position: fixed; }
            .sidebar.open { left: 0; }
            .main-content { margin-left: 0; padding: 16px; }
            .mobile-toggle { display: inline-flex; align-items: center; gap: 6px; }
        }
    </style>
</head>
<body>
    <!-- Add Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="logo_w.png" alt="Banshu Plastic Logo">
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard_admin.php">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li>
                <a href="kelola_laporan.php">
                    <i class="bi bi-file-text"></i>Kelola Laporan
                </a>
            </li>
            <li>
                <a href="data_user.php" class="active">
                    <i class="bi bi-people"></i>Data User
                </a>
            </li>
            <li>
                <a href="analisis_downtime.php">
                    <i class="bi bi-graph-down"></i>Analisis Downtime
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Change container to main-content -->
    <div class="main-content">
        <button class="mobile-toggle" id="mobileToggle"><i class="bi bi-list"></i> Menu</button>
        <!-- Remove the back button since we have sidebar -->
        <div class="header">
            <h3 class="mb-0">👥 Kelola User</h3>
        </div>

        <!-- Alert Messages -->
        <?php if(!empty($_SESSION['success'])) { 
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">'.$_SESSION['success'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; 
            unset($_SESSION['success']); 
        } ?>
        <?php if(!empty($_SESSION['error'])) { 
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">'.$_SESSION['error'].'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'; 
            unset($_SESSION['error']); 
        } ?>

        <!-- Form Tambah User -->
        <form method="post" class="mb-4 form-container">
            <div class="row g-2">
                <div class="col"><input type="text" name="username" placeholder="Username" class="form-control" required></div>
                <div class="col"><input type="password" name="password" placeholder="Password" class="form-control" required></div>
                <div class="col">
                    <select name="role" class="form-select">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        <option value="produksi">Produksi</option>
                    </select>
                </div>
                <div class="col">
                    <select name="status" class="form-select">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div class="col"><button type="submit" name="add_user" class="btn btn-primary">Tambah</button></div>
            </div>
        </form>

        <!-- Tabel User -->
        <div class="table-container">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= $row['username']; ?></td>
                        <td><?= ucfirst($row['role']); ?></td>
                        <td><?= ucfirst($row['status']); ?></td>
                        <td>
                            <!-- Form Edit -->
                            <form method="post" class="d-inline">
                                <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                <input type="hidden" name="username" value="<?= $row['username']; ?>">
                                <input type="hidden" name="role" value="<?= $row['role']; ?>">
                                <input type="hidden" name="status" value="<?= $row['status']; ?>">
                                <button type="button" class="btn btn-sm btn-warning" 
                                    onclick="openEditModal('<?= $row['id']; ?>','<?= $row['username']; ?>','<?= $row['role']; ?>','<?= $row['status']; ?>')">Edit</button>
                            </form>
                            <a href="data_user.php?delete=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus user ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Modal Edit -->
        <div class="modal fade" id="editModal" tabindex="-1">
          <div class="modal-dialog">
            <form method="post" class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit User</h5></div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-2">
                        <label>Username</label>
                        <input type="text" name="username" id="editUsername" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Role</label>
                        <select name="role" id="editRole" class="form-select">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="produksi">Produksi</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Status</label>
                        <select name="status" id="editStatus" class="form-select">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_user" class="btn btn-success">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
          </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.getElementById('mobileToggle').addEventListener('click', function(){
                document.querySelector('.sidebar').classList.toggle('open');
            });
            function openEditModal(id, username, role, status) {
                document.getElementById('editId').value = id;
                document.getElementById('editUsername').value = username;
                document.getElementById('editRole').value = role;
                document.getElementById('editStatus').value = status;
                var modal = new bootstrap.Modal(document.getElementById('editModal'));
                modal.show();
            }
        </script>
    </div>
</body>
</html>
