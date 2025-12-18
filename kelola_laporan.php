<?php

session_start();
include "config.php";

// Set timezone ke WIB (Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Handle PDF export request - return JSON data hanya status close
if (isset($_GET['export_pdf']) && $_GET['export_pdf'] == 1) {
    header('Content-Type: application/json');
    $query = "SELECT id, nomor_registrasi, nomor_mesin, status, aset, tgl_masuk, tgl_selesai, jam_masuk, jam_ambil, jam_selesai, keterangan, jenis, pic, downtime, analysis, countermeasure, sparepart 
              FROM reports 
              WHERE status='close' 
              ORDER BY tgl_masuk DESC, jam_masuk DESC 
              LIMIT 500";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// Fungsi untuk generate nomor registrasi otomatis
function generateNomorRegistrasi($conn) {
    // Ambil tanggal sekarang untuk format /mtn/mm/yy
    $date_part = date('m/y');
    
    // Cari nomor terakhir dengan prefix yang sama
    $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(nomor_registrasi, '/', 1) AS UNSIGNED)) as last_number 
            FROM reports 
            WHERE nomor_registrasi LIKE ?";
    
    $stmt = $conn->prepare($sql);
    $search_pattern = "%/mtn/" . $date_part;
    $stmt->bind_param("s", $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // Jika belum ada nomor, mulai dari 1
    $next_number = ($row['last_number'] ?? 0) + 1;
    
    // Format nomor registrasi lengkap dengan nomor di depan
    return $next_number . "/mtn/" . $date_part;
}

// Handle form submission for adding/editing reports
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $nomor_registrasi = mysqli_real_escape_string($conn, $_POST['nomor_registrasi']);
        $nomor_mesin = mysqli_real_escape_string($conn, $_POST['nomor_mesin']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $aset = mysqli_real_escape_string($conn, $_POST['aset']);
        $tgl_masuk = mysqli_real_escape_string($conn, $_POST['tgl_masuk']);
        $tgl_selesai = mysqli_real_escape_string($conn, $_POST['tgl_selesai']);
        $jam_masuk = mysqli_real_escape_string($conn, $_POST['jam_masuk']);
        $jam_ambil = mysqli_real_escape_string($conn, $_POST['jam_ambil']);
        $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
        $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
        $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
        $pic = mysqli_real_escape_string($conn, $_POST['pic']);
        $downtime = mysqli_real_escape_string($conn, $_POST['downtime']);
        $analysis = mysqli_real_escape_string($conn, $_POST['analysis']);
        $countermeasure = mysqli_real_escape_string($conn, $_POST['countermeasure']);
        $sparepart = mysqli_real_escape_string($conn, $_POST['sparepart']);
        
        if ($_POST['action'] == 'add') {
            // Generate nomor registrasi otomatis untuk data baru
            $nomor_registrasi = generateNomorRegistrasi($conn);
            $query = "INSERT INTO reports (nomor_registrasi, nomor_mesin, status, aset, tgl_masuk, tgl_selesai, jam_masuk, jam_ambil, jam_selesai, keterangan, jenis, pic, downtime, analysis, countermeasure, sparepart) 
                     VALUES ('$nomor_registrasi', '$nomor_mesin', '$status', '$aset', '$tgl_masuk', '$tgl_selesai', '$jam_masuk', '$jam_ambil', '$jam_selesai', '$keterangan', '$jenis', '$pic', '$downtime', '$analysis', '$countermeasure', '$sparepart')";
        } else if ($_POST['action'] == 'edit') {
            $id = intval($_POST['id']);
            // Cek apakah laporan ini dibuat oleh user dengan role 'produksi'
            $createdByRes = mysqli_query($conn, "SELECT created_by FROM reports WHERE id={$id} LIMIT 1");
            $createdBy = null;
            if ($createdByRes && mysqli_num_rows($createdByRes) > 0) {
                $createdBy = mysqli_fetch_assoc($createdByRes)['created_by'];
            }
            $allowEdit = false;
            if (!empty($createdBy)) {
                $createdByEsc = mysqli_real_escape_string($conn, $createdBy);
                $roleRes = mysqli_query($conn, "SELECT role FROM users WHERE username='{$createdByEsc}' LIMIT 1");
                if ($roleRes && mysqli_num_rows($roleRes) > 0) {
                    $roleRow = mysqli_fetch_assoc($roleRes);
                    if (isset($roleRow['role']) && $roleRow['role'] === 'produksi') {
                        $allowEdit = true;
                    }
                }
            }
            if (!$allowEdit) {
                $_SESSION['error'] = 'Hanya laporan yang diinput oleh user dengan role Produksi yang boleh diedit.';
                header("Location: kelola_laporan.php");
                exit();
            }
            // Lakukan update menggunakan prepared statement untuk keamanan
            $query = "UPDATE reports SET nomor_registrasi=?, nomor_mesin=?, status=?, aset=?, tgl_masuk=?, 
                      tgl_selesai=?, jam_masuk=?, jam_ambil=?, jam_selesai=?, 
                      keterangan=?, jenis=?, pic=?, downtime=?, analysis=?, countermeasure=?, sparepart=? 
                      WHERE id=?";
            $stmtUp = $conn->prepare($query);
            if ($stmtUp) {
                $stmtUp->bind_param("ssssssssssssssssi", $nomor_registrasi, $nomor_mesin, $status, $aset, $tgl_masuk, $tgl_selesai, $jam_masuk, $jam_ambil, $jam_selesai, $keterangan, $jenis, $pic, $downtime, $analysis, $countermeasure, $sparepart, $id);
                // execute later after deciding flow to centralize success/error handling
            }
        }
        // Execute and set session messages depending on action
        if ($_POST['action'] == 'add') {
            if (isset($query) && mysqli_query($conn, $query)) {
                $_SESSION['success'] = 'Laporan berhasil ditambahkan.';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan laporan.';
            }
        } elseif ($_POST['action'] == 'edit') {
            if (isset($stmtUp) && $stmtUp) {
                if ($stmtUp->execute()) {
                    $_SESSION['success'] = 'Perubahan berhasil disimpan.';
                } else {
                    $_SESSION['error'] = 'Gagal menyimpan perubahan: ' . $stmtUp->error;
                }
                $stmtUp->close();
            } else {
                $_SESSION['error'] = 'Gagal menyiapkan pembaruan laporan.';
            }
        }

        header("Location: kelola_laporan.php");
        exit();
    }
}

// Delete report
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM reports WHERE id=$id");
    header("Location: kelola_laporan.php");
    exit();
}

// Handle delegation by admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delegate_submit'])) {
    $report_id = intval($_POST['delegate_report_id']);
    $target_user = mysqli_real_escape_string($conn, $_POST['delegate_user']);

    // Cek user target ada dan aktif
    $u = mysqli_query($conn, "SELECT username, status FROM users WHERE username='$target_user' AND role='user' LIMIT 1");
    if (!$u || mysqli_num_rows($u) == 0) {
        $_SESSION['error'] = "User tujuan tidak ditemukan atau bukan user.";
        header("Location: kelola_laporan.php"); exit();
    }
    $userRow = mysqli_fetch_assoc($u);
    if ($userRow['status'] !== 'aktif') {
        $_SESSION['error'] = "User tujuan tidak aktif.";
        header("Location: kelola_laporan.php"); exit();
    }

    // Cek apakah user sedang punya tugas 'proses'
    $countRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM reports WHERE pic='{$target_user}' AND status='proses'");
    $cnt = 0;
    if ($countRes) { $cnt = intval(mysqli_fetch_assoc($countRes)['cnt']); }
    if ($cnt > 0) {
        $_SESSION['error'] = "User sudah sedang mengerjakan tugas lain dan tidak bisa diberi tugas baru.";
        header("Location: kelola_laporan.php"); exit();
    }

    // UPDATE dengan aman: hanya jika pic masih NULL/kosong
    $safeUpdate = mysqli_query($conn, "UPDATE reports SET pic='{$target_user}', status='pending' WHERE id={$report_id} AND (pic IS NULL OR pic='')");
    if ($safeUpdate && mysqli_affected_rows($conn) > 0) {
        $_SESSION['success'] = "Berhasil mendelegasikan laporan ke {$target_user}.";
    } else {
        $_SESSION['error'] = "Gagal mendelegasikan: laporan mungkin sudah didelegasikan sebelumnya.";
    }
    header("Location: kelola_laporan.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Laporan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f0f7fa 0%, #e1f5fe 100%);
            min-height: 100vh;
            color: #2c3e50;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(135deg, #1976d2 0%, #01579b 100%);
            padding-top: 20px;
            z-index: 1000;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
        }
        .sidebar-brand {
            padding: 20px 25px;
            display: flex;
            align-items: center;
            margin-bottom: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }
        .sidebar-brand img {
            height: 40px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        .sidebar-menu {
            list-style: none;
            padding: 8px 0;
            margin: 0;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.9);
            padding: 12px 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #64b5f6;
        }
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: #2196f3;
        }
        .sidebar-menu i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.1em;
            opacity: 0.9;
        }
        .content {
            margin-left: 250px;
            padding: 32px;
            max-width: 1600px;
        }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        .btn-primary {
            background: #2196f3;
            border-color: #2196f3;
            color: white;
        }
        .btn-primary:hover {
            background: #1976d2;
            border-color: #1976d2;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.25);
        }
        .btn-danger {
            background: #ef5350;
            border-color: #ef5350;
        }
        .btn-danger:hover {
            background: #e53935;
            border-color: #e53935;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 83, 80, 0.25);
        }
        
        /* Mobile toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 1200;
            background: #2196f3;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.25);
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .mobile-toggle:hover {
            background: #1976d2;
            transform: translateY(-1px);
        }

        @media (max-width: 992px) {
            .sidebar { 
                left: -280px; 
                transition: all 0.3s ease; 
                width: 260px; 
                position: fixed;
                box-shadow: none;
            }
            .sidebar.open { 
                left: 0; 
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
            }
            .content { 
                margin-left: 0; 
                padding: 24px 16px;
            }
            .mobile-toggle { 
                display: inline-flex; 
                align-items: center; 
                gap: 8px;
            }
            .table-container {
                padding: 16px;
                border-radius: 12px;
            }
        }
        .table-container {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-top: 24px;
        }
        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            background: #1976d2;
            color: white;
            font-weight: 500;
            border: none;
            padding: 12px 16px;
            white-space: nowrap;
        }
        .table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #e0e0e0;
            color: #37474f;
            vertical-align: middle;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8fafd;
        }
        .table-striped tbody tr:hover {
            background-color: #f5f9ff;
        }
        .btn-export {
            background: #2196f3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn-export:hover {
            background: #1976d2;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.25);
        }
        h2 {
            color: #1976d2;
            margin-bottom: 24px;
            font-weight: 600;
        }
        .form-control, .form-select {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 12px;
            transition: all 0.3s ease;
            color: #37474f;
        }
        .form-control:focus, .form-select:focus {
            border-color: #2196f3;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }
        .form-label {
            color: #455a64;
            font-weight: 500;
            margin-bottom: 6px;
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
                <a href="dashboard_admin.php">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li>
                <a href="kelola_laporan.php" class="active">
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
        <div class="d-flex justify-content-between align-items-center">
            <h2>Kelola Laporan</h2>
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
        
        <div class="table-container">
            <!-- Filter Section -->
            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label">Jenis Masalah</label>
                    <select name="f_jenis" class="form-select">
                        <option value="">-- Semua Jenis --</option>
                        <option value="Screw Problem/ Rusak" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Screw Problem/ Rusak' ? 'selected' : '' ?>>Screw Problem/ Rusak</option>
                        <option value="Selang Air Pecah" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Selang Air Pecah' ? 'selected' : '' ?>>Selang Air Pecah</option>
                        <option value="Selang Hydraulic Pecah" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Selang Hydraulic Pecah' ? 'selected' : '' ?>>Selang Hydraulic Pecah</option>
                        <option value="Leaking Barrel" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Leaking Barrel' ? 'selected' : '' ?>>Leaking Barrel</option>
                        <option value="Cleaning Nozzle" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Cleaning Nozzle' ? 'selected' : '' ?>>Cleaning Nozzle</option>
                        <option value="Cleaning Barrel" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Cleaning Barrel' ? 'selected' : '' ?>>Cleaning Barrel</option>
                        <option value="Charging Tidak Normal" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Charging Tidak Normal' ? 'selected' : '' ?>>Charging Tidak Normal</option>
                        <option value="Display Problem" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Display Problem' ? 'selected' : '' ?>>Display Problem</option>
                        <option value="Ejector Macet" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Ejector Macet' ? 'selected' : '' ?>>Ejector Macet</option>
                        <option value="Electrical Problem/ Alarm" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Electrical Problem/ Alarm' ? 'selected' : '' ?>>Electrical Problem/ Alarm</option>
                        <option value="Heater Mati" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Heater Mati' ? 'selected' : '' ?>>Heater Mati</option>
                        <option value="Hoper Mati/ Problem" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Hoper Mati/ Problem' ? 'selected' : '' ?>>Hoper Mati/ Problem</option>
                        <option value="Mesin Error/ Problem" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Mesin Error/ Problem' ? 'selected' : '' ?>>Mesin Error/ Problem</option>
                        <option value="Nozzle Bocor" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Nozzle Bocor' ? 'selected' : '' ?>>Nozzle Bocor</option>
                        <option value="Nozzle Kendor" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Nozzle Kendor' ? 'selected' : '' ?>>Nozzle Kendor</option>
                        <option value="Nozzle Lepas" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Nozzle Lepas' ? 'selected' : '' ?>>Nozzle Lepas</option>
                        <option value="Nozzle Mampet Matrial" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Nozzle Mampet Matrial' ? 'selected' : '' ?>>Nozzle Mampet Matrial</option>
                        <option value="Nozzle Mampet Chp" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Nozzle Mampet Chp' ? 'selected' : '' ?>>Nozzle Mampet Chp</option>
                        <option value="Nozzle Mampet Leaking" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Nozzle Mampet Leaking' ? 'selected' : '' ?>>Nozzle Mampet Leaking</option>
                        <option value="Open Close" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Open Close' ? 'selected' : '' ?>>Open Close</option>
                        <option value="Screw Bongkar" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Screw Bongkar' ? 'selected' : '' ?>>Screw Bongkar</option>
                        <option value="Other" <?= isset($_GET['f_jenis']) && $_GET['f_jenis']==='Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start" value="<?= isset($_GET['start']) ? htmlspecialchars($_GET['start']) : '' ?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end" value="<?= isset($_GET['end']) ? htmlspecialchars($_GET['end']) : '' ?>" class="form-control">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
                    <a href="kelola_laporan.php" class="btn btn-outline-light w-100" style="border-color:#fff;color:#fff;">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table id="reportsTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Status</th>
                            <th>Nomor Registrasi</th>
                            <th>Nomor Register PRD</th>
                            <th>Aset/Lokasi</th>
                            <th>Tanggal Pengaduan</th>
                            <th>Tanggal Selesai</th>
                            <th>Jam Masuk</th>
                            <th>Jam Ambil</th>
                            <th>Jam Selesai</th>
                            <th>Keterangan</th>
                            <th>Jenis</th>
                            <th>PIC</th>
                            <th>Down Time</th>
                            <th>Analysis</th>
                            <th>Countermeasure</th>
                            <th>Spare Part</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rowNumber = 0;
                        // Build filtered query using prepared statement
                        $sql = "SELECT * FROM reports WHERE 1=1";
                        $params = [];
                        $types = "";
                        if (!empty($_GET['f_jenis'])) { $sql .= " AND jenis=?"; $params[] = $_GET['f_jenis']; $types .= "s"; }
                        if (!empty($_GET['start'])) { $sql .= " AND tgl_masuk >= ?"; $params[] = $_GET['start']; $types .= "s"; }
                        if (!empty($_GET['end'])) { $sql .= " AND tgl_masuk <= ?"; $params[] = $_GET['end']; $types .= "s"; }
                        $sql .= " ORDER BY CAST(SUBSTRING_INDEX(nomor_registrasi,'/',1) AS UNSIGNED) ASC, tgl_masuk ASC, jam_masuk ASC LIMIT 100";

                        if ($types !== "") {
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param($types, ...$params);
                            $stmt->execute();
                            $result = $stmt->get_result();
                        } else {
                            $result = mysqli_query($conn, $sql);
                        }
                        while ($row = mysqli_fetch_assoc($result)) {
                            $rowNumber++;
                            echo "<tr>";
                            echo "<td>".$rowNumber."</td>";
                            echo "<td>{$row['status']}</td>";
                            echo "<td>" . (!empty($row['nomor_registrasi']) ? $row['nomor_registrasi'] : '-') . "</td>";
                            echo "<td>" . (!empty($row['nomor_mesin']) ? $row['nomor_mesin'] : '-') . "</td>";
                            echo "<td>{$row['aset']}</td>";
                            echo "<td>{$row['tgl_masuk']}</td>";
                            echo "<td>{$row['tgl_selesai']}</td>";
                            echo "<td>{$row['jam_masuk']}</td>";
                            echo "<td>" . (!empty($row['jam_ambil']) ? $row['jam_ambil'] : '-') . "</td>";
                            echo "<td>{$row['jam_selesai']}</td>";
                            echo "<td>{$row['keterangan']}</td>";
                            echo "<td>{$row['jenis']}</td>";
                            echo "<td>{$row['pic']}</td>";
                            echo "<td>{$row['downtime']}</td>";
                            echo "<td>{$row['analysis']}</td>";
                            echo "<td>{$row['countermeasure']}</td>";
                            echo "<td>{$row['sparepart']}</td>";
                            // Jika laporan belum didelegasikan (pic kosong/null), tampilkan tombol aksi
                            if (empty($row['pic'])) {
                                // Tentukan apakah laporan ini dibuat oleh user dengan role 'produksi'
                                $canEdit = false;
                                if (isset($row['created_by']) && !empty($row['created_by'])) {
                                    $creator = mysqli_real_escape_string($conn, $row['created_by']);
                                    $rRole = mysqli_query($conn, "SELECT role FROM users WHERE username='$creator' LIMIT 1");
                                    if ($rRole && mysqli_num_rows($rRole) > 0) {
                                        $roleRow = mysqli_fetch_assoc($rRole);
                                        if (isset($roleRow['role']) && $roleRow['role'] === 'produksi') {
                                            $canEdit = true;
                                        }
                                    }
                                }

                                // Icon-only action buttons for a cleaner look
                                echo "<td class='text-center'>\n";
                                if ($canEdit) {
                                    echo "<button class='btn btn-sm btn-outline-primary me-1' title='Edit' onclick='editReport({$row['id']})'><i class=\"bi bi-pencil\"></i></button>\n";
                                } else {
                                    // Tampilkan icon edit namun disabled dengan tooltip
                                    echo "<button class='btn btn-sm btn-outline-secondary me-1' title='Hanya laporan yang diinput oleh Produksi bisa diedit' disabled><i class=\"bi bi-pencil\"></i></button>\n";
                                }
                                echo "<button class='btn btn-sm btn-outline-warning me-1' title='Delegasikan' onclick='openDelegateModal({$row['id']})'><i class=\"bi bi-person-plus\"></i></button>\n";
                                // Replace inline confirm() with modal-based confirmation
                                echo "<button class='btn btn-sm btn-outline-danger' title='Hapus' onclick='openDeleteModal({$row['id']})'><i class=\"bi bi-trash\"></i></button>\n";
                                echo "</td>";
                            } else {
                                // Jika sudah didelegasikan, sembunyikan tombol aksi dan tunjukkan label kecil
                                $assignedUser = htmlspecialchars($row['pic'], ENT_QUOTES);
                                echo "<td class='text-center'><span class='badge bg-info text-dark'>Didelegasikan ke: {$assignedUser}</span></td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Delegasi -->
    <div class="modal fade" id="delegateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delegasikan Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="delegateForm">
                    <input type="hidden" name="delegate_report_id" id="delegateReportId" value="">
                    <div class="modal-body">
                        <p>Pilih user yang idle untuk mendelegasikan laporan ini.</p>
                        <div class="list-group">
                            <?php
                              $usersQ = mysqli_query($conn, "SELECT username, status FROM users WHERE role='user'");
                              while ($u = mysqli_fetch_assoc($usersQ)) {
                                  $uname = $u['username'];
                                  $ustatus = $u['status'];
                                  $cR = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM reports WHERE pic='{$uname}' AND status='proses'");
                                  $c = ($cR) ? intval(mysqli_fetch_assoc($cR)['cnt']) : 0;
                                  $isIdle = ($ustatus === 'aktif' && $c === 0);
                                  $badge = $isIdle ? '<span class="badge bg-success">Idle</span>' : '<span class="badge bg-secondary">Proses</span>';
                                  echo '<div class="d-flex align-items-center justify-content-between py-2 border-bottom">';
                                  echo '<div><strong>' . htmlspecialchars($uname) . '</strong> ' . $badge . '</div>';
                                  echo '<button type="button" class="btn btn-sm btn-outline-primary" onclick="setDelegateUser(\'' . htmlspecialchars($uname, ENT_QUOTES) . '\')"' . ($isIdle ? '' : ' disabled') . '>Pilih</button>';
                                  echo '</div>';
                              }
                            ?>
                        </div>
                        <hr>
                        <div id="delegateConfirm" style="display:none;">
                            <p>Anda akan mendelegasikan laporan ini ke <strong id="delegateToName"></strong>.</p>
                            <input type="hidden" name="delegate_user" id="delegateUserInput" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="delegate_submit" id="delegateSubmitBtn" class="btn btn-primary" style="display:none;">Konfirmasi Delegasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Laporan -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="editId">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="editNomorRegistrasi" class="form-label">Nomor Registrasi</label>
                                <input type="text" class="form-control" id="editNomorRegistrasi" name="nomor_registrasi" readonly>
                                <small class="text-muted">Nomor registrasi otomatis</small>
                            </div>
                            <div class="col-md-6">
                                <label for="editNomorMesin" class="form-label">Nomor Register PRD</label>
                                <input type="text" class="form-control" id="editNomorMesin" name="nomor_mesin" placeholder="Masukkan nomor mesin/alat">
                            </div>
                            <div class="col-md-6">
                                <label for="editStatus" class="form-label">Status</label>
                                <select class="form-select" id="editStatus" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="proses">Proses</option>
                                    <option value="close">Close</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="editAset" class="form-label">Aset/Lokasi</label>
                                <input type="text" class="form-control" id="editAset" name="aset" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editTglMasuk" class="form-label">Tanggal Pengaduan</label>
                                <input type="date" class="form-control" id="editTglMasuk" name="tgl_masuk" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editTglSelesai" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="editTglSelesai" name="tgl_selesai">
                            </div>
                            <div class="col-md-4">
                                <label for="editJamMasuk" class="form-label">Jam Masuk</label>
                                <input type="time" class="form-control" id="editJamMasuk" name="jam_masuk" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editJamAmbil" class="form-label">Jam Ambil</label>
                                <input type="time" class="form-control" id="editJamAmbil" name="jam_ambil">
                            </div>
                            <div class="col-md-4">
                                <label for="editJamSelesai" class="form-label">Jam Selesai</label>
                                <input type="time" class="form-control" id="editJamSelesai" name="jam_selesai">
                            </div>
                            <div class="col-md-6">
                                <label for="editPic" class="form-label">PIC</label>
                                <input type="text" class="form-control" id="editPic" name="pic">
                            </div>
                            <div class="col-md-6">
                                <label for="editDowntime" class="form-label">Down Time</label>
                                <input type="text" class="form-control" id="editDowntime" name="downtime">
                            </div>
                            <div class="col-12">
                                <label for="editKeterangan" class="form-label">Keterangan</label>
                                <textarea class="form-control" id="editKeterangan" name="keterangan" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="editJenis" class="form-label">Jenis</label>
                                <input type="text" class="form-control" id="editJenis" name="jenis" required>
                            </div>
                            <div class="col-12">
                                <label for="editAnalysis" class="form-label">Analysis</label>
                                <textarea class="form-control" id="editAnalysis" name="analysis" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="editCountermeasure" class="form-label">Countermeasure</label>
                                <textarea class="form-control" id="editCountermeasure" name="countermeasure" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="editSparepart" class="form-label">Spare Part</label>
                                <textarea class="form-control" id="editSparepart" name="sparepart" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                            <!-- Modal Hapus Laporan -->
                            <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="get" action="kelola_laporan.php">
                                            <div class="modal-body">
                                                <p>Yakin ingin menghapus laporan ini? Aksi ini tidak dapat dibatalkan.</p>
                                                <input type="hidden" name="delete" id="deleteReportId" value="">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Hapus</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('mobileToggle').addEventListener('click', function(){
            document.querySelector('.sidebar').classList.toggle('open');
        });

        $(document).ready(function() {
            var table = $('#reportsTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        text: 'Export PDF',
                        className: 'btn btn-export',
                        action: function(e, dt, node, config) {
                            exportTableToPDF(dt);
                        }
                    }
                ],
                pageLength: 25,
                order: [[4, 'desc'], [6, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                }
            });

            // Auto-fill tanggal ketika modal edit dibuka
            var editModal = document.getElementById('editModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function () {
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    const formattedDate = `${year}-${month}-${day}`;
                    
                    // Jika field tanggal kosong, isi dengan hari ini
                    const tglMasukField = document.getElementById('editTglMasuk');
                    if (tglMasukField && !tglMasukField.value) {
                        tglMasukField.value = formattedDate;
                    }
                });
            }
        });

        function editReport(id) {
            // Ambil data dari baris tabel
            var row = document.querySelector(`button[onclick="editReport(${id})"]`).closest('tr');
            var cells = row.querySelectorAll('td');
            
            // Isi form dengan data yang ada
            document.getElementById('editId').value = id;
            document.getElementById('editNomorRegistrasi').value = cells[1].textContent;
            document.getElementById('editNomorMesin').value = cells[2].textContent;
            document.getElementById('editStatus').value = cells[0].textContent.toLowerCase();
            document.getElementById('editAset').value = cells[3].textContent;
            document.getElementById('editTglMasuk').value = cells[4].textContent;
            document.getElementById('editTglSelesai').value = cells[5].textContent || '';
            document.getElementById('editJamMasuk').value = cells[6].textContent;
            document.getElementById('editJamAmbil').value = cells[7].textContent || '';
            document.getElementById('editJamSelesai').value = cells[8].textContent || '';
            document.getElementById('editKeterangan').value = cells[8].textContent;
            document.getElementById('editJenis').value = cells[9].textContent;
            document.getElementById('editPic').value = cells[10].textContent;
            document.getElementById('editDowntime').value = cells[11].textContent;
            document.getElementById('editAnalysis').value = cells[12].textContent;
            document.getElementById('editCountermeasure').value = cells[13].textContent;
            document.getElementById('editSparepart').value = cells[14].textContent;
            
            // Tampilkan modal
            var modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }

        function openDelegateModal(reportId) {
            document.getElementById('delegateReportId').value = reportId;
            document.getElementById('delegateConfirm').style.display = 'none';
            document.getElementById('delegateToName').textContent = '';
            document.getElementById('delegateUserInput').value = '';
            document.getElementById('delegateSubmitBtn').style.display = 'none';
            var modal = new bootstrap.Modal(document.getElementById('delegateModal'));
            modal.show();
        }

        function setDelegateUser(username) {
            document.getElementById('delegateToName').textContent = username;
            document.getElementById('delegateUserInput').value = username;
            document.getElementById('delegateConfirm').style.display = 'block';
            document.getElementById('delegateSubmitBtn').style.display = 'inline-block';
        }

        // Open delete confirmation modal and set the id to submit
        function openDeleteModal(reportId) {
            document.getElementById('deleteReportId').value = reportId;
            var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        // Custom PDF export function - FILTER HANYA STATUS CLOSE dengan AJAX
        function exportTableToPDF(dataTable) {
            // Kirim request ke server untuk ambil data close saja
            $.ajax({
                url: 'kelola_laporan.php?export_pdf=1',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (!response.success || response.data.length === 0) {
                        alert('Tidak ada data dengan status "close" untuk dicetak.');
                        return;
                    }
                    
                    buildAndDownloadPDF(response.data);
                },
                error: function() {
                    alert('Gagal mengambil data untuk export.');
                }
            });
        }
        
        // Helper function untuk membangun dan download PDF
        function buildAndDownloadPDF(data) {
            var fileName = 'Laporan_Selesai_' + new Date().toISOString().split('T')[0] + '.pdf';
            
            // Header kolom
            var headers = ['No', 'Status', 'No. Registrasi', 'No. PRD', 'Aset', 'Tgl Pengaduan', 
                          'Tgl Selesai', 'Jam Masuk', 'Jam Ambil', 'Jam Selesai', 'Keterangan', 'Jenis', 'PIC', 
                          'Down Time', 'Analysis', 'Countermeasure', 'Spare Part'];
            
            // Siapkan rows dari response data
            var rows = [];
            data.forEach(function(row, idx) {
                rows.push([
                    idx + 1,
                    row.status || '-',
                    row.nomor_registrasi || '-',
                    row.nomor_mesin || '-',
                    row.aset || '-',
                    row.tgl_masuk || '-',
                    row.tgl_selesai || '-',
                    row.jam_masuk || '-',
                    row.jam_ambil || '-',
                    row.jam_selesai || '-',
                    row.keterangan || '-',
                    row.jenis || '-',
                    row.pic || '-',
                    row.downtime || '-',
                    row.analysis || '-',
                    row.countermeasure || '-',
                    row.sparepart || '-'
                ]);
            });
            
            // Load logo dengan kualitas tinggi
            function loadImageToDataURL(src, maxWidth, maxHeight) {
                return new Promise(function(resolve) {
                    try {
                        var img = new Image();
                        img.onload = function() {
                            var canvas = document.createElement('canvas');
                            var ctx = canvas.getContext('2d', { alpha: true });
                            
                            // Hitung aspect ratio dan resolusi
                            var ratio = Math.min(maxWidth / img.width, maxHeight / img.height, 1);
                            var dpr = 3; // Tingkatkan ke 3x untuk kualitas tinggi
                            
                            canvas.width = Math.round(img.width * ratio * dpr);
                            canvas.height = Math.round(img.height * ratio * dpr);
                            ctx.scale(dpr, dpr);
                            
                            // Enable smoothing untuk hasil lebih baik
                            ctx.imageSmoothingEnabled = true;
                            ctx.imageSmoothingQuality = 'high';
                            
                            // Gambar dengan background putih untuk clarity
                            ctx.fillStyle = '#ffffff';
                            ctx.fillRect(0, 0, Math.round(img.width * ratio), Math.round(img.height * ratio));
                            
                            // Gambar image dengan anti-alias
                            ctx.drawImage(img, 0, 0, Math.round(img.width * ratio), Math.round(img.height * ratio));
                            
                            // Export ke PNG dengan kualitas maksimal
                            var dataURL = canvas.toDataURL('image/png');
                            resolve(dataURL);
                        };
                        img.onerror = function() { resolve(null); };
                        img.crossOrigin = 'anonymous';
                        img.src = 'logo_w.png';
                    } catch (e) {
                        resolve(null);
                    }
                });
            }
            
            // Build doc dengan logo
            loadImageToDataURL('logo_w.png', 80, 80).then(function(logoDataUrl) {
                var headerColumns = [];
                if (logoDataUrl) {
                        headerColumns.push({ image: logoDataUrl, width: 70, margin: [0, 0, 15, 0] });
                } else {
                    headerColumns.push({ text: ' ', width: 70 });
                }
                headerColumns.push({
                    stack: [
                        { text: 'Laporan Permintaan Perbaikan/Pembuatan Mesin', bold: true, fontSize: 13, alignment: 'left' },
                        { text: 'PT. Banshu Plastic Indonesia', fontSize: 10, alignment: 'left', margin: [0, 2, 0, 0] },
                        { text: 'Dicetak: ' + new Date().toLocaleString('id-ID'), fontSize: 8, alignment: 'left', margin: [0, 4, 0, 0] }
                    ],
                    width: '*'
                });

                var docDefinition = {
                    pageSize: 'A4',
                    pageOrientation: 'landscape',
                    pageMargins: [10, 60, 10, 15],
                    header: {
                        columns: headerColumns,
                        margin: [10, 10, 10, 15]
                    },
                    content: [
                        {
                            table: {
                                headerRows: 1,
                                widths: ['2.5%', '4.5%', '6%', '6.5%', '6%', '7%', '7%', '5.5%', '5.5%', '5.5%', '10%', '6%', '4.5%', '5%', '6%', '6%', '6%'],
                                body: [
                                    headers.map(function(h) {
                                        return { text: h, fillColor: '#1976d2', color: '#ffffff', bold: true, alignment: 'center', fontSize: 6, margin: [1,1,1,1] };
                                    })
                                ].concat(
                                    rows.map(function(row, idx) {
                                        return row.map(function(cell, cellIdx) {
                                            return { text: cell || '-', alignment: cellIdx === 0 ? 'center' : (cellIdx <= 3 ? 'center' : 'left'), fontSize: 5.5, margin: [0.5,1,0.5,1], fillColor: idx % 2 === 0 ? '#ffffff' : '#f5f9ff', border: [true, true, true, true], borderColor: '#d0d0d0' };
                                        });
                                    })
                                )
                            },
                            layout: {
                                hLineWidth: function(i, node) { return 0.3; },
                                vLineWidth: function(i, node) { return 0.3; },
                                hLineColor: function(i, node) { return '#d0d0d0'; },
                                vLineColor: function(i, node) { return '#d0d0d0'; },
                                paddingLeft: function(i) { return 0.5; },
                                paddingRight: function(i) { return 0.5; },
                                paddingTop: function(i) { return 0.5; },
                                paddingBottom: function(i) { return 0.5; }
                            }
                        },
                        { text: '', margin: [0, 15, 0, 0] },
                        {
                            columns: [
                                { text: '', width: '60%' },
                                {
                                    stack: [
                                        { text: 'Mengetahui,', fontSize: 9, bold: false, margin: [0, 0, 0, 15] },
                                        { text: 'Human Resource Development', fontSize: 9, margin: [0, 0, 0, 30] },
                                        { text: 'Naufal Abiyu', fontSize: 9, bold: true },
                                    ],
                                    width: '*',
                                    alignment: 'center'
                                }
                            ]
                        }
                    ],
                    footer: { text: 'Halaman {page_current} dari {numpages}', alignment: 'center', fontSize: 7, margin: [0,8,0,0] }
                };

                pdfMake.createPdf(docDefinition).download(fileName);
            });
        }
    </script>
</body>
</html>