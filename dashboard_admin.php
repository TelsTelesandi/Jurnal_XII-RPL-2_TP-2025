<?php
session_start();
include "config.php";

// Set timezone ke WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Hitung statistik laporan
$total_open   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM reports WHERE status='open'"))['total'];
$total_close  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM reports WHERE status='close'"))['total'];
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM reports WHERE status='pending'"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            min-height: 100vh;
            background: linear-gradient(135deg, #e0f7fa 0%, #00bcd4 100%);
        }

        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(135deg, #0099ffff 0%, #0a0a0aff 100%);
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
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .content {
            margin-left: 250px; /* Kembali ke margin normal setelah menghilangkan navbar hijau */
            padding: 20px;
            background-color: #008cffff; /* Background biru seperti pada gambar */
            min-height: 100vh;
        }

        /* Mobile toggle */ 
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 12px;
            left: 12px;
            z-index: 1200;
            background: #063fa8ff;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 8px 12px;
            box-shadow: 0 6px 14px rgba(30,136,229,0.25);
        }

        @media (max-width: 992px) {
            .mobile-toggle { display: inline-flex; align-items: center; gap: 6px; }
            .sidebar { left: -260px; transition:left .25s ease; width: 230px; }
            .sidebar.open { left: 0; }
            .content { margin-left: 0; padding: 16px; }
            .card-container { grid-template-columns: 1fr 1fr; }
        }
        
        .welcome-area {
            padding: 40px 0;
            text-align: center;
        }
        
        .welcome-area h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 300;
            margin: 0;
            margin-bottom: 10px;
        }
        
        .welcome-area p {
            color: white;
            font-size: 1.1rem;
            margin: 0;
            opacity: 0.9;
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
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
        .footer {
            text-align: center;
            
            padding: 20px;
            color: #666;
            margin-top: 40px;
            font-size: 12px;
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
        .table-container {
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="logo_w.png" alt="Banshu Plastic Logo">
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard_admin.php" class="active">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li>
                <a href="kelola_laporan.php">
                    <i class="bi bi-file-text"></i>Kelola Laporan
                </a>
            </li>
            <li>
                <a href="data_user.php">
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

    <!-- Content -->
    <div class="content">
        <button class="mobile-toggle" id="mobileToggle"><i class="bi bi-list"></i> Menu</button>
        <!-- Welcome Area -->
        <div class="welcome-area">
            <h1 id="greeting">Good Afternoon, <?php echo $_SESSION['username']; ?>!</h1>
            <p id="current-date">It's Mon, 06 October 2025</p>
        </div>
        
        <!-- Statistik -->
        <div class="card-container">
            <div class="stat-card">
                <i class="bi bi-box"></i>
                <h3>Pending</h3>
                <h2 class="text-warning"><?php echo $total_pending; ?></h2>
            </div>
            <div class="stat-card">
                <i class="bi bi-check-circle"></i>
                <h3>Proses</h3>
                <h2 class="text-success"><?php echo $total_open; ?></h2>
            </div>
            <div class="stat-card">
                <i class="bi bi-clock"></i>
                <h3>Close</h3>
                <h2 class="text-primary"><?php echo $total_close; ?></h2>
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
        var mobileToggle = document.getElementById('mobileToggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function(){
                document.querySelector('.sidebar').classList.toggle('open');
            });
        }
        function updateDateTime() {
            const now = new Date();
            const hours = now.getHours();
            let greeting = "";

            // Navbar biru horizontal seperti pada gambar
            // Buat elemen navbar di atas konten utama
            // Pastikan navbar tetap (fixed) di atas, dengan teks ucapan dan tanggal
            // (Bagian ini hanya JS, navbar HTML/CSS harus diletakkan di atas <div class="container"> di file utama)
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
            document.getElementById('greeting').textContent = `${greeting}, <?php echo $_SESSION['username']; ?>!`;
            document.getElementById('current-date').textContent = `It's ${dateString}`;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
</body>
</html>
