<?php
session_start();
include "config.php";

// Set timezone ke WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

// Izinkan hanya user dengan role produksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'produksi') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Generator nomor registrasi (sama seperti di import_data.php)
function generateNomorRegistrasi($conn) {
    $date_part = date('m/y');
    $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(nomor_registrasi, '/', 1) AS UNSIGNED)) as last_number 
            FROM reports 
            WHERE nomor_registrasi LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_pattern = "%/mtn/" . $date_part;
    $stmt->bind_param("s", $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $next_number = ($row['last_number'] ?? 0) + 1;
    return $next_number . "/mtn/" . $date_part;
}

// Proses simpan data import
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_registrasi = generateNomorRegistrasi($conn);

    $stmt = $conn->prepare("INSERT INTO reports 
            (status, nomor_registrasi, nomor_mesin, aset, tgl_masuk, jam_masuk, keterangan, jenis) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $status = 'pending';
    $jam_masuk = date('H:i:s');

    $stmt->bind_param("ssssssss",
        $status,                // status
        $nomor_registrasi,      // nomor registrasi auto
        $_POST['no_seri'],      // nomor mesin
        $_POST['nama_alat'],    // aset
        $_POST['tgl_masuk'],    // tgl_masuk
        $jam_masuk,             // jam_masuk
        $_POST['uraian'],       // keterangan
        $_POST['jenis_masalah'] // jenis
    );

    try {
        if ($stmt->execute()) {
            $_SESSION['success'] = "Laporan berhasil diimport";
            header("Location: dashboard_produksi.php");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard Produksi - Import Laporan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(180deg, #f5f7fb 0%, #eef2f7 100%);
            color: #2c3e50;
        }
        .top-bar {
            /* Disembunyikan agar logo pada sidebar terlihat penuh */
            display: none;
        }
        .menu-toggle { display:none; background: rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); color:#fff; padding:6px 10px; border-radius:6px; }
        .logo-top { height: 36px; width: auto; }

        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, #0d47a1 0%, #1565c0 60%, #1976d2 100%);
            padding-top: 20px;
            z-index: 1000;
            box-shadow: 0 8px 24px rgba(13, 71, 161, 0.25);
        }
        .sidebar-brand { padding: 16px 22px; display: flex; align-items: center; margin-bottom: 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.15); }
        .sidebar-brand img { height: 42px; width: auto; filter: brightness(0) invert(1); }
        .sidebar-menu { list-style: none; padding: 8px; margin: 0; }
        .sidebar-menu a { display: flex; align-items: center; gap: 10px; color: #e3f2fd; padding: 10px 14px; margin: 6px 6px; border-radius: 10px; text-decoration: none; transition: transform .15s ease, background .15s ease, color .15s ease; }
        .sidebar-menu a:hover { background: rgba(255, 255, 255, 0.15); color: #ffffff; transform: translateX(3px); }
        .sidebar-menu a.active { background: linear-gradient(90deg, rgba(255,255,255,0.22), rgba(255,255,255,0.10)); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.15); color: #ffffff; }
        .sidebar-menu i { margin-right: 2px; width: 20px; text-align: center; font-size: 18px; }

        .content { margin-left: 250px; padding: 24px; }

        /* Mobile toggle button */
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

        /* Gaya untuk dashboard seperti dashboard_user, tanpa laporan singkat */
        .welcome-card {
            background: linear-gradient(135deg, #1e88e5 0%, #42a5f5 50%, #90caf9 100%);
            color: #fff;
            padding: 24px 26px;
            border-radius: 14px;
            margin-bottom: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 12px 28px rgba(30, 136, 229, 0.25);
            border: 1px solid rgba(255,255,255,0.25);
        }
        .welcome-text h2 { margin: 0; font-size: 26px; font-weight: 600; letter-spacing: .2px; }
        .welcome-text p { margin: 6px 0 0 0; opacity: 0.95; }
        .datetime { text-align: right; color: #e3f2fd; }
        .time { font-size: 26px; font-weight: 600; }
        .date { font-size: 13px; opacity: 0.95; }

        @media (max-width: 992px) {
            .mobile-toggle { display: inline-flex; align-items: center; gap: 6px; }
            .sidebar { left: -260px; transition:left .25s ease; width: 230px; }
            .sidebar.open { left: 0; }
            .content { margin-left: 0; padding: 16px; }
            .welcome-card { flex-direction: column; align-items: flex-start; gap: 10px; }
            .datetime { width: 100%; text-align: left; }
        }

        .card-container { max-width: 1080px; margin: 0 auto; }
        .form-label { font-weight: 600; color: #1e88e5; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #d8dee9; padding: 10px 15px; background: #fff; }
        .form-control:focus, .form-select:focus { border-color: #1e88e5; box-shadow: 0 0 0 0.22rem rgba(30, 136, 229, 0.18); }
        .btn-primary { background: linear-gradient(90deg, #1e88e5, #42a5f5); border: none; padding: 10px 22px; font-weight: 600; box-shadow: 0 6px 14px rgba(30,136,229,0.25); }
        .btn-primary:hover { background: linear-gradient(90deg, #1976d2, #1e88e5); box-shadow: 0 8px 18px rgba(25,118,210,0.28); }
    </style>
</head>
<body>
    <!-- Top bar dihilangkan sesuai permintaan -->

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="logo_w.png" alt="Banshu Plastic Logo">
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard_produksi.php" class="active">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
             <li>
                <a href="produksi_import.php" class="active">
                    <i class="bi bi-speedometer2"></i>Input Laporan
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
        <div class="welcome-card">
            <div class="welcome-text">
                <h2 id="greeting">Good Morning, <?php echo $username; ?>!</h2>
                <p>Welcome To Dashboard.</p>
            </div>
            <div class="datetime">
                <div class="time" id="current-time">00:00:00</div>
                <div class="date" id="current-date">Loading...</div>
            </div>
        </div>
        <!-- Tidak ada laporan singkat atau tabel pada dashboard produksi -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        var mobileToggle = document.getElementById('mobileToggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function(){
                document.getElementById('sidebar').classList.toggle('open');
            });
        }

        function updateDateTime() {
            const now = new Date();
            const hours = now.getHours();
            let greeting = "";
            if (hours >= 5 && hours < 12) { greeting = "Good Morning"; }
            else if (hours >= 12 && hours < 18) { greeting = "Good Afternoon"; }
            else { greeting = "Good Evening"; }

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

            document.getElementById('greeting').textContent = `${greeting}, <?php echo $username; ?>!`;
            document.getElementById('current-time').textContent = timeString;
            document.getElementById('current-date').textContent = dateString;
        }
        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>
</body>
</html>
