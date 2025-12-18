<?php
session_start();
include "config.php";

// Set timezone ke WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Hitung ringkasan user
$total_pending = $conn->query("SELECT COUNT(*) as jml FROM reports WHERE status='pending'")->fetch_assoc()['jml'];
$tugas_saya = $conn->query("SELECT COUNT(*) as jml FROM reports WHERE pic='$username' AND status='proses'")->fetch_assoc()['jml'];
$tugas_selesai = $conn->query("SELECT COUNT(*) as jml FROM reports WHERE pic='$username' AND status='close'")->fetch_assoc()['jml'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            min-height: 100vh;
            background: linear-gradient(135deg, #e0f7fa 0%, #00bcd4 100%);
        }
        .menu-toggle {
            display: none;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            margin: 10px;
            position: fixed;
            z-index: 1000;
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
            background-color: transparent;
        }
        @media (max-width: 992px) {
            .sidebar {
                left: -260px;
                transition: left .25s ease;
            }
            .sidebar.open {
                left: 0;
            }
            .menu-toggle { display: inline-flex; align-items: center; gap: 6px; }
            .content { margin-left: 0; }
            .welcome-card { flex-direction: column; align-items: flex-start; gap: 10px; }
            .datetime { width: 100%; text-align: left; }
            .stat-card h2 { font-size: 28px; }
        }
        .welcome-card {
            background: linear-gradient(135deg, #1976d2 0%, #64b5f6 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .welcome-text h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 500;
        }
        .welcome-text p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .stat-card h3 {
            font-size: 18px;
            margin: 0;
            color: #666;
        }
        .stat-card h2 {
            font-size: 36px;
            margin: 10px 0 0;
            font-weight: bold;
        }
        .stat-card i {
            font-size: 40px;
            margin-bottom: 10px;
            color: #0288d1;
        }
        .table-container {
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .table-container h3 {
            margin-bottom: 20px;
            font-size: 20px;
            color: #333;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 0;
        }
        .table th {
            background: #0288d1;
            color: white;
            text-align: left;
            padding: 10px;
            font-size: 14px;
            border-bottom: 2px solid #ddd;
        }
        .table td {
            padding: 10px;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f9f9f9;
        }
        .table-striped tbody tr:hover {
            background-color: #f1f1f1;
        }
        .datetime {
            text-align: right;
            color: white;
        }
        
        .time {
            font-size: 24px;
            font-weight: 500;
        }
        
        .date {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i> Menu</button>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="logo_w.png" alt="Banshu Plastic Logo">
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard_user.php" class="active">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li>
                <a href="tugas.php">
                    <i class="bi bi-list-task"></i>Tugas Saya
                </a>
            </li>
            <li>
                <a href="user_histori.php">
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
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcome-text">
                <h2 id="greeting">Good Morning, <?php echo $username; ?>!</h2>
                <p>Selamat datang di dashboard user. Kelola tugas dan pantau progress Anda di sini.</p>
            </div>
            <div class="datetime">
                <div class="time" id="current-time">00:00:00</div>
                <div class="date" id="current-date">Loading...</div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="card-container">
            <div class="stat-card">
                <i class="bi bi-clock"></i>
                <h3>Pending</h3>
                <h2 class="text-warning"><?php echo $total_pending; ?></h2>
            </div>
            <div class="stat-card">
                <i class="bi bi-list-task"></i>
                <h3>Proses</h3>
                <h2 class="text-primary"><?php echo $tugas_saya; ?></h2>
            </div>
            <div class="stat-card">
                <i class="bi bi-check-circle"></i>
                <h3>Close</h3>
                <h2 class="text-success"><?php echo $tugas_selesai; ?></h2>
            </div>
        </div>

    <!-- Tabel Laporan Masuk -->
    <div class="table-container">
            <h3>Laporan Masuk Terbaru</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Nomor</th>
                            <th>Aset/Lokasi</th>
                            <th>Tanggal Pengaduan</th>
                            <th>Jam Masuk</th>
                            <th>Keterangan</th>
                            <th>Jenis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = mysqli_query($conn, "SELECT * FROM reports ORDER BY tgl_masuk DESC LIMIT 5");
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>{$row['status']}</td>";
                            echo "<td>" . (!empty($row['nomor_registrasi']) ? $row['nomor_registrasi'] : '-') . "</td>";
                            echo "<td>{$row['aset']}</td>";
                            echo "<td>{$row['tgl_masuk']}</td>";
                            echo "<td>{$row['jam_masuk']}</td>";
                            echo "<td>{$row['keterangan']}</td>";
                            echo "<td>{$row['jenis']}</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('open');
        });

        function updateDateTime() {
            const now = new Date();
            const hours = now.getHours();
            let greeting = "";

            // Tentukan ucapan berdasarkan jam
            if (hours >= 5 && hours < 12) {
                greeting = "Good Morning";
            } else if (hours >= 12 && hours < 18) {
                greeting = "Good Afternoon";
            } else {
                greeting = "Good Evening";
            }

            const timeString = now.toLocaleTimeString('en-US', { 
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            const dateString = now.toLocaleDateString('en-US', {
                weekday: 'short',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });

            // Update ucapan di halaman dengan nama pengguna
            document.getElementById('greeting').textContent = `${greeting}, <?php echo $username; ?>!`;
            document.getElementById('current-time').textContent = timeString;
            document.getElementById('current-date').textContent = dateString;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
</body>
</html>