<?php
session_start();
include "config.php";

// Set timezone ke WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="UTF-8">
    <title>Histori Tugas Saya</title>
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
        .top-bar {
            background: linear-gradient(135deg, #0288d1 0%, #26c6da 100%);
            color: white;
            padding: 12px 16px;
            position: sticky;
            top: 0;
            z-index: 1100;
            display: none; /* mobile only */
            align-items: center;
            justify-content: space-between;
            height: 56px;
        }
        .menu-toggle { display:none; background: rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); color:#fff; padding:6px 10px; border-radius:6px; }
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
        }
        .sidebar-brand img {
            height: 40px;
            width: auto;
            filter: brightness(0) invert(1);
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
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        @media (max-width: 992px) {
            .top-bar { display:flex; }
            .menu-toggle { display:inline-flex; align-items:center; gap:6px; }
            .sidebar { left: -260px; transition:left .25s ease; }
            .sidebar.open { left:0; }
            .content { margin-left: 0; }
            .table-container { padding: 12px; }
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 8px 32px 0 rgba(0,188,212,0.2);
            margin-top: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        h2 {
            color: #1e1e2d;
            margin-bottom: 20px;
        }
        .section-title {
            background: linear-gradient(135deg, #1976d2 0%, #64b5f6 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i> Menu</button>
        <img src="logo_w.png" alt="logo" style="height:32px;">
    </div>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="logo_w.png" alt="Banshu Plastic Logo">
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard_user.php">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li>
                <a href="tugas.php">
                    <i class="bi bi-list-task"></i>Tugas Saya
                </a>
            </li>
            <li>
                <a href="user_histori.php" class="active">
                    <i class="bi bi-clock-history"></i>Histori Tugas
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Histori Tugas Saya</h2>
        </div>
        
        <div class="section-title">
            <i class="bi bi-check-circle"></i> Tugas yang Telah Selesai
        </div>
        <div class="table-container">
            <div class="table-responsive">
                <table id="historiTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Nomor Registrasi</th>
                            <th>Aset/Lokasi</th>
                            <th>PIC</th>
                            <th>Tanggal Pengaduan</th>
                            <th>Tanggal Selesai</th>
                            <th>Jam Masuk</th>
                            <th>Jam Selesai</th>
                            <th>Keterangan</th>
                            <th>Jenis</th>
                            <th>Down Time</th>
                            <th>Analysis</th>
                            <th>Countermeasure</th>
                            <th>Spare Part</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Tampilkan histori tugas selesai milik user login
                        $result = mysqli_query($conn, "SELECT * FROM reports WHERE pic='$username' AND status='close' ORDER BY tgl_selesai DESC");
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td><span class='badge bg-success'>Selesai</span></td>";
                                echo "<td>" . (!empty($row['nomor_registrasi']) ? $row['nomor_registrasi'] : '-') . "</td>";
                                echo "<td>{$row['aset']}</td>";
                                echo "<td>" . (!empty($row['pic']) ? $row['pic'] : '-') . "</td>";
                                echo "<td>{$row['tgl_masuk']}</td>";
                                echo "<td>{$row['tgl_selesai']}</td>";
                                echo "<td>{$row['jam_masuk']}</td>";
                                echo "<td>{$row['jam_selesai']}</td>";
                                echo "<td>{$row['keterangan']}</td>";
                                echo "<td>{$row['jenis']}</td>";
                                echo "<td>{$row['downtime']}</td>";
                                echo "<td>{$row['analysis']}</td>";
                                echo "<td>{$row['countermeasure']}</td>";
                                echo "<td>{$row['sparepart']}</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='13' class='text-center'>Belum ada tugas yang selesai</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            document.getElementById('menuToggle').addEventListener('click', function(){
                document.getElementById('sidebar').classList.toggle('open');
            });
            $('#historiTable').DataTable({
                pageLength: 10,
                order: [[5, 'desc'], [7, 'desc']], // Urutkan berdasarkan tanggal selesai dan jam selesai
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                }
            });
        });
    </script>
</body>
</html>