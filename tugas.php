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

// Handle proses tugas (set jam_ambil jika belum ada, status='proses')
if (isset($_GET['proses'])) {
    $id = mysqli_real_escape_string($conn, $_GET['proses']);
    $jam_ambil = date('H:i:s');
    $query = "UPDATE reports SET status='proses', jam_ambil=IF(jam_ambil IS NULL OR jam_ambil='', '{$jam_ambil}', jam_ambil) WHERE id=$id AND pic='$username'";
    mysqli_query($conn, $query);
    header("Location: tugas.php");
    exit();
}

// Handle selesai tugas dengan form
if (isset($_POST['selesai_tugas'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $analysis = mysqli_real_escape_string($conn, $_POST['analysis']);
    $countermeasure = mysqli_real_escape_string($conn, $_POST['countermeasure']);
    $sparepart = mysqli_real_escape_string($conn, $_POST['sparepart']);
    $tgl_selesai = date('Y-m-d');
    $jam_selesai = date('H:i:s');
    
    // Get jam_ambil from the database
    $result = mysqli_query($conn, "SELECT jam_ambil FROM reports WHERE id=$id");
    $row = mysqli_fetch_assoc($result);
    $jam_ambil = $row['jam_ambil'];
    
    // Calculate downtime
    $time1 = strtotime($jam_ambil);
    $time2 = strtotime($jam_selesai);
    $difference = round(abs($time2 - $time1) / 3600, 2); // Convert to hours with 2 decimal places
    
    $query = "UPDATE reports SET status='close', tgl_selesai='$tgl_selesai', jam_selesai='$jam_selesai', 
              downtime=$difference, analysis='$analysis', countermeasure='$countermeasure', sparepart='$sparepart' 
              WHERE id=$id AND pic='$username'";
    mysqli_query($conn, $query);
    header("Location: tugas.php");
    exit();
}

// Handle lepas tugas dengan alasan (wajib)
if (isset($_POST['lepas_tugas'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan']);
    
    if (empty(trim($alasan))) {
        $_SESSION['error'] = "Alasan lepas tugas tidak boleh kosong!";
        header("Location: tugas.php");
        exit();
    }
    
    // Catat alasan di kolom keterangan atau tambah field (untuk saat ini update di notes di keterangan)
    $query = "UPDATE reports SET pic=NULL, status='pending', jam_ambil=NULL WHERE id=$id AND pic='$username'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_affected_rows($conn) > 0) {
        $_SESSION['success'] = "Tugas berhasil dilepaskan. Alasan: " . $alasan;
    }
    header("Location: tugas.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="UTF-8">
    <title>Tugas Saya</title>
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
            display: none; /* only for mobile */
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
            .top-bar { display: flex; }
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
        .btn-ambil {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            transition: background 0.3s;
        }
        .btn-ambil:hover {
            background: #218838;
            color: white;
        }
        .btn-proses {
            background: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            transition: background 0.3s;
        }
        .btn-proses:hover {
            background: #0056b3;
            color: white;
        }
        .btn-selesai {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            transition: background 0.3s;
        }
        .btn-selesai:hover {
            background: #218838;
            color: white;
        }
        .btn-lepas {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            transition: background 0.3s;
        }
        .btn-lepas:hover {
            background: #c82333;
            color: white;
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
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .modal-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .modal-header .btn-close {
            filter: invert(1);
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .form-control:focus, .form-select:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 10px 20px;
            font-weight: 500;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
            transform: translateY(-1px);
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
                <a href="tugas.php" class="active">
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
        <div class="d-flex justify-content-between align-items-center">
            <h2>Tugas Saya</h2>
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
        
        <!-- Tugas Saya (Delegated Tasks) -->
        <div class="section-title">
            <i class="bi bi-person-check"></i> Tugas yang Didelegasikan
        </div>
        <div class="table-container">
            <div class="table-responsive">
                <table id="myTasksTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Nomor Registrasi</th>
                            <th>Aset/Lokasi</th>
                            <th>Tanggal Pengaduan</th>
                            <th>Tanggal Selesai</th>
                            <th>Jam Masuk</th>
                            <th>Jam Ambil</th>
                            <th>Jam Selesai</th>
                            <th>Keterangan</th>
                            <th>Jenis</th>
                            <th>Down Time</th>
                            <th>Analysis</th>
                            <th>Countermeasure</th>
                            <th>Spare Part</th>
                            <th>Aksi</th>
        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $my_tasks = mysqli_query($conn, "SELECT * FROM reports WHERE pic='$username' AND status != 'close' ORDER BY tgl_masuk DESC");
                        
                        
                        if (mysqli_num_rows($my_tasks) > 0) {
                            while ($row = mysqli_fetch_assoc($my_tasks)) {
                                $status_badge = '';
                                switch($row['status']) {
                                    case 'pending':
                                        $status_badge = '<span class="badge bg-warning">Pending</span>';
                                        break;
                                    case 'proses':
                                        $status_badge = '<span class="badge bg-primary">Proses</span>';
                                        break;
                                    case 'close':
                                        $status_badge = '<span class="badge bg-success">Selesai</span>';
                                        break;
                                }
                                
                                echo "<tr>";
                                echo "<td>{$status_badge}</td>";
                                echo "<td>" . (!empty($row['nomor_registrasi']) ? $row['nomor_registrasi'] : '-') . "</td>";
                                echo "<td>{$row['aset']}</td>";
                                echo "<td>{$row['tgl_masuk']}</td>";
                                echo "<td>{$row['tgl_selesai']}</td>";
                                echo "<td>{$row['jam_masuk']}</td>";
                                echo "<td>" . (!empty($row['jam_ambil']) ? $row['jam_ambil'] : '-') . "</td>";
                                echo "<td>{$row['jam_selesai']}</td>";
                                echo "<td>{$row['keterangan']}</td>";
                                echo "<td>{$row['jenis']}</td>";
                                echo "<td>" . ($row['downtime'] ? number_format($row['downtime'], 2) . " jam" : "-") . "</td>";
                                echo "<td>{$row['analysis']}</td>";
                                echo "<td>{$row['countermeasure']}</td>";
                                echo "<td>{$row['sparepart']}</td>";
                                echo "<td>";
                                
                                if ($row['status'] == 'pending') {
                                    echo "<a href='?proses={$row['id']}' class='btn-proses' onclick=\"return confirmAction(event,'Yakin mulai proses tugas ini?','Mulai','btn-primary')\">Proses</a>";
                                } elseif ($row['status'] == 'proses') {
                                    echo "<button class='btn-selesai' onclick='openSelesaiModal({$row['id']})'>Selesai</button>";
                                } else {
                                    echo "<span class='text-muted'>Selesai</span>";
                                }
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='14' class='text-center'>Belum ada tugas</td></tr>";
                        }
                        ?>
                    </tbody>
    </table>
</div>
        </div>
    </div>

    <!-- Modal Selesai Tugas -->
    <div class="modal fade" id="selesaiModal" tabindex="-1" aria-labelledby="selesaiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selesaiModalLabel">
                        <i class="bi bi-check-circle me-2"></i>Selesaikan Tugas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="selesaiForm">
                    <div class="modal-body">
                        <input type="hidden" name="selesai_tugas" value="1">
                        <input type="hidden" name="id" id="selesaiId">
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Informasi:</strong> Silakan lengkapi data berikut sebelum menyelesaikan tugas.
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-clock me-1"></i>Down Time
                                </label>
                                <div class="form-control bg-light">Otomatis dihitung</div>
                                <small class="text-muted">Dihitung otomatis dari jam ambil sampai selesai</small>
                            </div>
                            <div class="col-md-6">
                                <label for="sparepart" class="form-label">
                                    <i class="bi bi-tools me-1"></i>Spare Part (Opsional)
                                </label>
                                <input type="text" class="form-control" id="sparepart" name="sparepart" 
                                       placeholder="Contoh: Bearing, Seal, dll">
                                <small class="text-muted">Spare part yang digunakan (jika ada)</small>
                            </div>
                            <div class="col-12">
                                <label for="analysis" class="form-label">
                                    <i class="bi bi-search me-1"></i>Analysis <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="analysis" name="analysis" rows="3" required 
                                          placeholder="Jelaskan analisis masalah yang ditemukan..."></textarea>
                                <small class="text-muted">Analisis mendalam tentang penyebab masalah</small>
                            </div>
                            <div class="col-12">
                                <label for="countermeasure" class="form-label">
                                    <i class="bi bi-shield-check me-1"></i>Countermeasure <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="countermeasure" name="countermeasure" rows="3" required 
                                          placeholder="Jelaskan langkah perbaikan yang dilakukan..."></textarea>
                                <small class="text-muted">Langkah-langkah perbaikan yang telah dilakukan</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Selesaikan Tugas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Lepas Tugas -->
    <div class="modal fade" id="lepasModal" tabindex="-1" aria-labelledby="lepasModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lepasModalLabel">
                        <i class="bi bi-hand-thumbs-down me-2"></i>Lepas Tugas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="lepasForm">
                    <div class="modal-body">
                        <input type="hidden" name="lepas_tugas" value="1">
                        <input type="hidden" name="id" id="lepasId">
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Peringatan:</strong> Tugas akan dikembalikan ke status pending dan dapat diberikan ke user lain.
                        </div>
                        
                        <div class="mb-3">
                            <label for="alasan" class="form-label">
                                <i class="bi bi-chat-left-text me-1"></i>Alasan Melepas Tugas <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="alasan" name="alasan" rows="4" required 
                                      placeholder="Jelaskan mengapa Anda melepas tugas ini..."></textarea>
                            <small class="text-muted">Wajib diisi - alasan akan dicatat dalam sistem</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-check me-1"></i>Lepas Tugas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Reusable Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmTitle">Konfirmasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="confirmMessage">Yakin?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a id="confirmOkBtn" href="#" class="btn btn-primary">OK</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            document.getElementById('menuToggle').addEventListener('click', function(){
                document.getElementById('sidebar').classList.toggle('open');
            });
            $('#availableTasksTable').DataTable({
                pageLength: 10,
                order: [[4, 'desc'], [5, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                }
            });
            
            $('#myTasksTable').DataTable({
                pageLength: 10,
                order: [[4, 'desc'], [6, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json'
                }
            });
        });

        // Fungsi untuk membuka modal selesai
        function openSelesaiModal(id) {
            document.getElementById('selesaiId').value = id;
            var modal = new bootstrap.Modal(document.getElementById('selesaiModal'));
            modal.show();
        }

        // Fungsi untuk membuka modal lepas tugas
        function openLepasModal(id) {
            document.getElementById('lepasId').value = id;
            document.getElementById('alasan').value = '';
            var modal = new bootstrap.Modal(document.getElementById('lepasModal'));
            modal.show();
        }

        // Reusable confirm for anchor/button actions
        window.confirmAction = function(event, message, okLabel, okBtnClass) {
            event.preventDefault();
            var target = event.currentTarget;
            var href = target.getAttribute('href');
            var modalEl = document.getElementById('confirmModal');
            document.getElementById('confirmMessage').textContent = message || 'Yakin?';
            var okBtn = document.getElementById('confirmOkBtn');
            okBtn.textContent = okLabel || 'OK';
            okBtn.className = 'btn ' + (okBtnClass || 'btn-primary');
            okBtn.setAttribute('href', href);
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
            return false;
        }

            // Validasi form sebelum submit
        document.getElementById('selesaiForm').addEventListener('submit', function(e) {
            var analysis = document.getElementById('analysis').value.trim();
            var countermeasure = document.getElementById('countermeasure').value.trim();
            
            if (!analysis || !countermeasure) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi (Analysis, Countermeasure)');
                return false;
            }            // Konfirmasi sebelum submit via modal
            e.preventDefault();
            var modalEl = document.getElementById('confirmModal');
            document.getElementById('confirmMessage').textContent = 'Yakin ingin menyelesaikan tugas ini? Data yang sudah diisi tidak dapat diubah.';
            var okBtn = document.getElementById('confirmOkBtn');
            okBtn.textContent = 'Selesaikan';
            okBtn.className = 'btn btn-success';
            okBtn.removeAttribute('href');
            okBtn.onclick = function(){
                document.getElementById('selesaiForm').submit();
            };
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
            return false;
        });

        // Validasi dan submit form lepas tugas
        document.getElementById('lepasForm').addEventListener('submit', function(e) {
            var alasan = document.getElementById('alasan').value.trim();
            
            if (!alasan) {
                e.preventDefault();
                alert('Mohon isi alasan melepas tugas');
                return false;
            }
            
            // Konfirmasi sebelum submit via modal
            e.preventDefault();
            var modalEl = document.getElementById('confirmModal');
            document.getElementById('confirmMessage').textContent = 'Yakin ingin melepas tugas ini? Tugas akan dikembalikan ke admin untuk didelegasikan ke user lain.';
            var okBtn = document.getElementById('confirmOkBtn');
            okBtn.textContent = 'Lepas Tugas';
            okBtn.className = 'btn btn-danger';
            okBtn.removeAttribute('href');
            okBtn.onclick = function(){
                document.getElementById('lepasForm').submit();
            };
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
            return false;
        });
    </script>
</body>
</html>