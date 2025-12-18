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

// Pastikan kolom created_by tersedia untuk melacak pemilik laporan
// Kompatibel untuk MySQL/MariaDB yang tidak mendukung IF NOT EXISTS pada ADD COLUMN
$checkColSql = "SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'created_by'";
$colResult = mysqli_query($conn, $checkColSql);
if ($colResult && mysqli_num_rows($colResult) === 0) {
	// Kolom belum ada -> tambahkan
	@mysqli_query($conn, "ALTER TABLE reports ADD COLUMN created_by VARCHAR(50) AFTER jenis");
}

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

// Handle hapus laporan milik sendiri
if (isset($_GET['delete_id'])) {
	$deleteId = intval($_GET['delete_id']);
	$stmtDel = $conn->prepare("DELETE FROM reports WHERE id=? AND created_by=?");
	$stmtDel->bind_param("is", $deleteId, $username);
	if ($stmtDel->execute()) {
		$_SESSION['success'] = "Laporan berhasil dihapus.";
	} else {
		$_SESSION['error'] = "Gagal menghapus laporan.";
	}
	$stmtDel->close();
	header("Location: produksi_import.php");
	exit;
}

// Proses simpan data import
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nomor_registrasi = generateNomorRegistrasi($conn);

	$stmt = $conn->prepare("INSERT INTO reports 
			(status, nomor_registrasi, nomor_mesin, aset, tgl_masuk, jam_masuk, keterangan, jenis, created_by) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

	$status = 'pending';
	$jam_masuk = date('H:i:s');

	$stmt->bind_param("sssssssss",
		$status,
		$nomor_registrasi,
		$_POST['no_seri'],
		$_POST['nama_alat'],
		$_POST['tgl_masuk'],
		$jam_masuk,
		$_POST['uraian'],
		$_POST['jenis_masalah'],
		$username
	);

	try {
		if ($stmt->execute()) {
			$_SESSION['success'] = "Laporan berhasil diimport";
			header("Location: produksi_import.php");
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
	<title>Produksi - Import Laporan</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
	<style>
		body {
			font-family: Arial, sans-serif;
			margin: 0;
			padding: 0;
			min-height: 100vh;
			/* Sedikit lebih gelap agar kartu lebih kontras */
			background: linear-gradient(180deg, #eaf0f7 0%, #e1e7f0 100%);
			color: #2c3e50;
		}
		.top-bar { display: none; }
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
		@media (max-width: 992px) {
			.mobile-toggle { display: inline-flex; align-items: center; gap: 6px; }
			.sidebar { left: -260px; transition:left .25s ease; width: 230px; }
			.sidebar.open { left: 0; }
			.content { margin-left: 0; padding: 16px; }
		}
		.card-container {
			max-width: 1080px;
			margin: 0 auto;
			/* Buat kartu lebih kontras dan nyaman dipandang */
			background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
			border: 1px solid #e1e7f0;
			box-shadow: 0 10px 28px rgba(13, 71, 161, 0.12);
			border-radius: 14px;
		}
		.form-label { font-weight: 600; color: #1e88e5; }
		.form-control, .form-select { border-radius: 10px; border: 1px solid #d8dee9; padding: 10px 15px; background: #fff; }
		.form-control:focus, .form-select:focus { border-color: #1e88e5; box-shadow: 0 0 0 0.22rem rgba(30, 136, 229, 0.18); }
		.btn-primary { background: linear-gradient(90deg, #1e88e5, #42a5f5); border: none; padding: 10px 22px; font-weight: 600; box-shadow: 0 6px 14px rgba(30,136,229,0.25); }
		.btn-primary:hover { background: linear-gradient(90deg, #1976d2, #1e88e5); box-shadow: 0 8px 18px rgba(25,118,210,0.28); }
		.card-header { background: linear-gradient(180deg, #f3f8ff 0%, #eef4ff 100%); border-bottom: 1px solid #d9e4f5; }
		/* Tabel lebih kontras namun tetap lembut */
		.table thead th { background: #f3f8ff; border-bottom-color: #d9e4f5; }
		.table tbody tr:hover { background: #f7fbff; }
		/* Badge status default agar lebih terlihat */
		.badge.bg-secondary { background-color: #90a4ae !important; }
	</style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="logo_w.png" alt="Banshu Plastic Logo">
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard_produksi.php">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li>
                <a href="produksi_import.php" class="active">
                    <i class="bi bi-file-earmark-plus"></i>Input Laporan
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
		<div class="card card-container">
            <div class="card-header">
                <h5 class="m-0"><i class="bi bi-clipboard-plus me-2"></i>Form Input Laporan</h5>
            </div>
            <div class="card-body">
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-tools me-1"></i>Nama Alat / Mould</label>
							<select name="nama_alat" id="namaAlatSelect" class="form-select" required>
								<option value="">-- Pilih Nama Alat / Mould --</option>
								<option value="Injection 4">Injection 4</option>
								<option value="Injection 5">Injection 5</option>
								<option value="Injection 10">Injection 10</option>
								<option value="Injection 11">Injection 11</option>
								<option value="Injection 13">Injection 13</option>
								<option value="Injection 20">Injection 20</option>
								<option value="Injection 22">Injection 22</option>
								<option value="Injection 28">Injection 28</option>
								<option value="Injection 29">Injection 29</option>
								<option value="Injection 30">Injection 30</option>
								<option value="Injection 31">Injection 31</option>
								<option value="Mtc 01">Mtc 01</option>
								<option value="Mtc 02">Mtc 02</option>
								<option value="Mtc 03">Mtc 03</option>
								<option value="Mtc 04">Mtc 04</option>
								<option value="Mtc 05">Mtc 05</option>
								<option value="Mtc 06">Mtc 06</option>
								<option value="Mtc 07">Mtc 07</option>
								<option value="Mtc 08">Mtc 08</option>
								<option value="Mtc 09">Mtc 09</option>
								<option value="Mtc 10">Mtc 10</option>
								<option value="Mtc 11">Mtc 11</option>
								<option value="Mtc 12">Mtc 12</option>
								<option value="Pemanas Tang 01">Pemanas Tang 01</option>
								<option value="Pemanas Tang 02">Pemanas Tang 02</option>
								<option value="Pemanas Tang 03">Pemanas Tang 03</option>
								<option value="Pemanas Tang 04">Pemanas Tang 04</option>
								<option value="Chiller">Chiller</option>
								<option value="Colling Tower">Colling Tower</option>
							</select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-upc me-1"></i>Nomor Register PRD</label>
                            <input type="text" name="no_seri" class="form-control" required>
                            
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-calendar me-1"></i>Tanggal Pengaduan</label>
                            <input type="date" name="tgl_masuk" id="tglMasukProduksi" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-exclamation-triangle me-1"></i>Jenis Masalah</label>
                            <select name="jenis_masalah" id="jenisMasalahSelect" class="form-select" required>
                                <option value="">-- Pilih Jenis Masalah --</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-chat-text me-1"></i>Uraian Masalah</label>
                            <textarea name="uraian" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Kirim Laporan</button>
                    </div>
                </form>
            </div>
		</div>

		<!-- Histori input milik saya (section atas) -->
		<div class="card card-container mt-3">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h6 class="m-0"><i class="bi bi-clock-history me-2"></i>Histori Pelaporan Masalah Mesin Terbaru</h6>
				<small class="text-muted">Hanya data yang Anda input</small>
			</div>
			<div class="card-body">
				<form method="get" class="row g-2 align-items-end mb-3">
					<div class="col-md-4">
						<label class="form-label">Jenis Masalah</label>
						<select name="f_jenis" id="filterJenisMasalah" class="form-select">
							<option value="">-- Semua Jenis --</option>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Tanggal Mulai</label>
						<input type="date" name="start" value="<?= isset($_GET['start']) ? htmlspecialchars($_GET['start']) : '' ?>" class="form-control">
					</div>
					<div class="col-md-2 d-flex gap-2">
						<button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
						<a href="produksi_import.php" class="btn btn-outline-secondary w-100">Reset</a>
					</div>
				</form>
				<div class="table-responsive">
					<table class="table table-sm align-middle">
						<thead>
							<tr>
								<th>Nomor Registrasi</th>
								<th>Nomor Register PRD</th>
								<th>Aset</th>
								<th>Tanggal</th>
								<th>Jam</th>
								<th>Jenis</th>
								<th>Uraian Masalah</th>
								<th>Status</th>
								<th style="width:80px">Aksi</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$baseSql = "SELECT id, nomor_registrasi, nomor_mesin, aset, tgl_masuk, jam_masuk, jenis, keterangan, status FROM reports WHERE created_by=?";
						$params = [$username];
						$types = "s";
						if (!empty($_GET['f_jenis'])) { $baseSql .= " AND jenis=?"; $params[] = $_GET['f_jenis']; $types .= "s"; }
						if (!empty($_GET['start'])) { $baseSql .= " AND tgl_masuk >= ?"; $params[] = $_GET['start']; $types .= "s"; }
						if (!empty($_GET['end'])) { $baseSql .= " AND tgl_masuk <= ?"; $params[] = $_GET['end']; $types .= "s"; }
						$baseSql .= " ORDER BY id DESC LIMIT 50";
						$q = $conn->prepare($baseSql);
						$q->bind_param($types, ...$params);
							$q->execute();
							$res = $q->get_result();
							while ($r = $res->fetch_assoc()) {
								echo "<tr>";
								echo "<td>" . htmlspecialchars($r['nomor_registrasi']) . "</td>";
								echo "<td>" . htmlspecialchars($r['nomor_mesin']) . "</td>";
								echo "<td>" . htmlspecialchars($r['aset']) . "</td>";
								echo "<td>" . htmlspecialchars($r['tgl_masuk']) . "</td>";
								echo "<td>" . htmlspecialchars($r['jam_masuk']) . "</td>";
								echo "<td>" . htmlspecialchars($r['jenis']) . "</td>";
								echo "<td>" . htmlspecialchars($r['keterangan']) . "</td>";
								echo "<td><span class=\"badge bg-secondary\">" . htmlspecialchars($r['status']) . "</span></td>";
								echo "<td><button type=\"button\" class=\"btn btn-sm btn-outline-danger btn-open-delete\" data-id=\"" . intval($r['id']) . "\" data-nomor=\"" . htmlspecialchars($r['nomor_registrasi']) . "\"><i class=\"bi bi-trash\"></i> Hapus</button></td>";
								echo "</tr>";
							}
							$q->close();
						?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
		<!-- Select2 CSS & JS CDN -->
		<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Data mapping masalah untuk setiap jenis mesin
        const problemsByMachine = {
            'injection': [
                'Screw Problem/ Rusak',
                'Selang Air Pecah',
                'Selang Hydraulic Pecah',
                'Leaking Barrel',
                'Cleaning Nozzle',
                'Cleaning Barrel',
                'Charging Tidak Normal',
                'Display Problem',
                'Ejector Macet',
                'Electrical Problem/ Alarm',
                'Heater Mati',
                'Hoper Mati/ Problem',
                'Mesin Error/ Problem',
                'Nozzle Bocor',
                'Nozzle Kendor',
                'Nozzle Lepas',
                'Nozzle Mampet Matrial',
                'Nozzle Mampet Chp',
                'Nozzle Mampet Leaking',
                'Open Close',
                'Screw Bongkar',
                'Other'
            ],
            'mtc': [
                'Suhu tidak stabil / tidak sesuai setpoint',
                'Flow air/oil rendah (filter kotor, pipa tersumbat)',
                'Kebocoran hose atau seal',
                'Pompa melemah atau tidak berfungsi',
                'Sensor suhu error atau alarm'
            ],
            'pemanas_tang': [
                'Elemen pemanas tidak panas / panas tidak merata',
                'Plat pemanas kotor atau aus sehingga hasil potong/penyambungan jelek',
                'Termostat atau kontrol suhu tidak akurat',
                'Kabel pemanas putus atau isolasi rusak',
                'Handle longgar / tekanan tidak stabil'
            ],
            'chiller': [
                'Tekanan refrigerant rendah (kebocoran)',
                'Kondensor kotor → pendinginan tidak maksimal',
                'Kompresor overheat',
                'Pompa sirkulasi lemah atau mati',
                'Sensor suhu/tekanan error'
            ],
            'cooling_tower': [
                'Air bak kotor (lumpur, lumut, kerak)',
                'Fan tidak normal (motor rusak, belt kendor)',
                'Nozzle mampet sehingga air tidak merata',
                'Pompa melemah',
                'Kebocoran pada pipa'
            ]
        };

        // Fungsi untuk menentukan tipe mesin dari nama alat
        function getMachineType(namaAlat) {
            console.log('Checking machine type for:', namaAlat);
            if (namaAlat.includes('Injection')) return 'injection';
            if (namaAlat.includes('Mtc')) return 'mtc';
            if (namaAlat.includes('Pemanas Tang')) return 'pemanas_tang';
            if (namaAlat.includes('Chiller')) return 'chiller';
            if (namaAlat.includes('Colling Tower')) return 'cooling_tower';
            console.log('No machine type found');
            return null;
        }

        // Fungsi untuk update opsi Jenis Masalah
        function updateProblemOptions(machineType) {
            console.log('Updating problem options for machine type:', machineType);
            const jenisMasalahSelect = $('#jenisMasalahSelect');
            const filterJenisMasalah = $('#filterJenisMasalah');
            
            // Destroy Select2 dahulu
            if (jenisMasalahSelect.data('select2')) {
                jenisMasalahSelect.select2('destroy');
            }
            
            // Clear dan set opsi baru
            jenisMasalahSelect.empty().append('<option value="">-- Pilih Jenis Masalah --</option>');
            filterJenisMasalah.empty().append('<option value="">-- Semua Jenis --</option>');
            
            if (machineType && problemsByMachine[machineType]) {
                const problems = problemsByMachine[machineType];
                console.log('Problems found:', problems);
                problems.forEach(function(problem) {
                    jenisMasalahSelect.append(`<option value="${problem}">${problem}</option>`);
                    filterJenisMasalah.append(`<option value="${problem}">${problem}</option>`);
                });
            } else {
                console.log('No problems found for machine type:', machineType);
            }
            
            // Reinitialize Select2 untuk Jenis Masalah
            jenisMasalahSelect.select2({
                placeholder: '-- Pilih Jenis Masalah --',
                allowClear: true,
                width: '100%'
            });
        }

        // Toggle sidebar on mobile
        var mobileToggle = document.getElementById('mobileToggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function(){
                document.getElementById('sidebar').classList.toggle('open');
            });
        }

        // Set tanggal otomatis saat halaman dibuka
        document.addEventListener('DOMContentLoaded', function(){
            console.log('DOM loaded, initializing...');
            
            // Inisialisasi Select2 pada Nama Alat
            $('#namaAlatSelect').select2({
                placeholder: '-- Pilih Nama Alat / Mould --',
                allowClear: true,
                width: '100%'
            });
            
            // Inisialisasi Select2 pada Jenis Masalah
            $('#jenisMasalahSelect').select2({
                placeholder: '-- Pilih Jenis Masalah --',
                allowClear: true,
                width: '100%'
            });
            
            // Event listener untuk perubahan Nama Alat
            $('#namaAlatSelect').on('change', function() {
                const namaAlat = $(this).val();
                console.log('Nama alat selected:', namaAlat);
                const machineType = getMachineType(namaAlat);
                updateProblemOptions(machineType);
            });
            
            const tglInput = document.getElementById('tglMasukProduksi');
            if (tglInput) {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                const formattedDate = `${year}-${month}-${day}`;
                tglInput.value = formattedDate;
            }
        });

        // Custom delete confirmation modal handler
        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('.btn-open-delete').forEach(function(btn){
                btn.addEventListener('click', function(){
                    var id = this.getAttribute('data-id');
                    var nomor = this.getAttribute('data-nomor');
                    var confirmTextEl = document.getElementById('confirmDeleteText');
                    var confirmBtnEl = document.getElementById('btnConfirmDelete');
                    if (confirmTextEl && confirmBtnEl) {
                        confirmTextEl.textContent = 'Anda yakin ingin menghapus laporan dengan Nomor Registrasi ' + nomor + '? Tindakan ini tidak dapat dibatalkan.';
                        confirmBtnEl.setAttribute('data-id', id);
                        var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
                        modal.show();
                    }
                });
            });

            var confirmBtn = document.getElementById('btnConfirmDelete');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function(){
                    var id = this.getAttribute('data-id');
                    if (id) {
                        window.location.href = 'produksi_import.php?delete_id=' + encodeURIComponent(id);
                    }
                });
            }
        });
    </script>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Konfirmasi Penghapusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmDeleteText">Anda yakin ingin menghapus laporan ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="btnConfirmDelete" class="btn btn-danger">Hapus</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>