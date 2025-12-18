<?php
session_start();
include "config.php";

// Set timezone ke WIB
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Function untuk format durasi
function formatDuration($hours) {
    // Pastikan input adalah float
    $hours = floatval($hours);
    
    // Hitung hari dan jam
    $days = floor($hours / 24);
    $remainingHours = $hours - ($days * 24);
    
    if ($days > 0) {
        return sprintf("%d hari %.1f jam", $days, $remainingHours);
    }
    return sprintf("%.1f jam", $hours);
}

// Ambil data filter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$mesin = isset($_GET['mesin']) ? $_GET['mesin'] : '';

// Query untuk mendapatkan daftar aset/lokasi unik
$query_mesin = "SELECT DISTINCT aset FROM reports WHERE aset IS NOT NULL ORDER BY aset";
$mesin_list = mysqli_query($conn, $query_mesin);

// Base query untuk statistik dengan total downtime per aset
$query = "SELECT 
            aset,
            COUNT(*) as total_masalah,
            COALESCE(SUM(NULLIF(CAST(downtime AS DECIMAL(10,2)), 0)), 0) as total_downtime,
            GROUP_CONCAT(DISTINCT jenis) as jenis_masalah,
            COUNT(CASE WHEN status = 'close' THEN 1 END) as solved_problems,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_problems,
            COUNT(CASE WHEN status = 'proses' THEN 1 END) as ongoing_problems
          FROM reports 
          WHERE MONTH(tgl_masuk) = ? AND YEAR(tgl_masuk) = ?";

if (!empty($mesin)) {
    $query .= " AND aset = ?";
}

$query .= " GROUP BY aset ORDER BY total_downtime DESC";

$stmt = $conn->prepare($query);

if (!empty($mesin)) {
    $stmt->bind_param("sss", $bulan, $tahun, $mesin);
} else {
    $stmt->bind_param("ss", $bulan, $tahun);
}

$stmt->execute();
$result = $stmt->get_result();

// Query untuk chart data (top 5 masalah)
$query_masalah = "SELECT jenis, COUNT(*) as jumlah, 
                  SUM(CAST(downtime AS DECIMAL(10,2))) as total_downtime
                  FROM reports 
                  WHERE MONTH(tgl_masuk) = ? AND YEAR(tgl_masuk) = ?
                  GROUP BY jenis 
                  ORDER BY jumlah DESC 
                  LIMIT 5";
$stmt_masalah = $conn->prepare($query_masalah);
$stmt_masalah->bind_param("ss", $bulan, $tahun);
$stmt_masalah->execute();
$result_masalah = $stmt_masalah->get_result();

// Prepare data for charts
$labels = [];
$data_jumlah = [];
$data_downtime = [];
while ($row = $result_masalah->fetch_assoc()) {
    $labels[] = $row['jenis'];
    $data_jumlah[] = $row['jumlah'];
    $data_downtime[] = $row['total_downtime'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analisis Downtime</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .content {
            margin-left: 250px;
            padding: 32px;
        }
        .stats-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 20px;
            height: 100%;
        }
        .chart-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-top: 24px;
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
        .filter-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 24px;
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
        }
        .sidebar-menu a:hover, 
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #64b5f6;
        }
        .sidebar-menu i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
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
                <a href="kelola_laporan.php">
                    <i class="bi bi-file-text"></i>Kelola Laporan
                </a>
            </li>
            <li>
                <a href="analisis_downtime.php" class="active">
                    <i class="bi bi-graph-up"></i>Analisis Downtime
                </a>
            </li>
            <li>
                <a href="data_user.php">
                    <i class="bi bi-people"></i>Data User
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
        <h2 class="mb-4">Analisis Downtime</h2>

        <!-- Filter Section -->
        <div class="filter-card">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php
                        for ($i = 1; $i <= 12; $i++) {
                            $selected = ($i == $bulan) ? 'selected' : '';
                            echo "<option value='$i' $selected>" . date('F', mktime(0, 0, 0, $i, 1)) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        <?php
                        $current_year = date('Y');
                        for ($i = $current_year - 2; $i <= $current_year; $i++) {
                            $selected = ($i == $tahun) ? 'selected' : '';
                            echo "<option value='$i' $selected>$i</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Aset/Lokasi</label>
                    <select name="mesin" class="form-select">
                        <option value="">Semua Aset/Lokasi</option>
                        <?php while ($row_mesin = mysqli_fetch_assoc($mesin_list)): ?>
                            <option value="<?= $row_mesin['aset'] ?>" <?= ($mesin == $row_mesin['aset']) ? 'selected' : '' ?>>
                                <?= $row_mesin['aset'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Top 5 Masalah Terbanyak</h5>
                    <canvas id="masalahChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Downtime per Jenis Masalah</h5>
                    <canvas id="downtimeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="table-container">
            <h5 class="mb-4">Detail Statistik per Mesin</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Aset/Lokasi</th>
                            <th>Total Masalah</th>
                            <th>Total Downtime</th>
                            <th>Jenis Masalah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $row['aset'] ?></td>
                                <td><?= $row['total_masalah'] ?></td>
                                <td><?= formatDuration($row['total_downtime']) ?></td>
                                <td>
                                    <?php
                                    $jenis_array = explode(',', $row['jenis_masalah']);
                                    foreach ($jenis_array as $jenis) {
                                        $jenis = trim($jenis);
                                        if (!empty($jenis)) {
                                            // Hitung jumlah untuk jenis ini
                                            $count_query = "SELECT COUNT(*) as count FROM reports 
                                                          WHERE aset = ? AND jenis = ? 
                                                          AND MONTH(tgl_masuk) = ? AND YEAR(tgl_masuk) = ?";
                                            $stmt_count = $conn->prepare($count_query);
                                            $stmt_count->bind_param("ssss", $row['aset'], $jenis, $bulan, $tahun);
                                            $stmt_count->execute();
                                            $count_result = $stmt_count->get_result();
                                            $count = $count_result->fetch_assoc()['count'];
                                            
                                            echo "<div class='badge bg-info me-1 mb-1'>$jenis ($count)</div>";
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge bg-success">Selesai: <?= $row['solved_problems'] ?></span>
                                        <span class="badge bg-warning">Pending: <?= $row['pending_problems'] ?></span>
                                        <span class="badge bg-primary">Proses: <?= $row['ongoing_problems'] ?></span>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart for Masalah
        const masalahCtx = document.getElementById('masalahChart').getContext('2d');
        new Chart(masalahCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Jumlah Masalah',
                    data: <?= json_encode($data_jumlah) ?>,
                    backgroundColor: 'rgba(33, 150, 243, 0.8)',
                    borderColor: 'rgba(33, 150, 243, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Chart for Downtime
        const downtimeCtx = document.getElementById('downtimeChart').getContext('2d');
        new Chart(downtimeCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    data: <?= json_encode($data_downtime) ?>,
                    backgroundColor: [
                        'rgba(33, 150, 243, 0.8)',
                        'rgba(0, 200, 83, 0.8)',
                        'rgba(255, 152, 0, 0.8)',
                        'rgba(244, 67, 54, 0.8)',
                        'rgba(156, 39, 176, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>