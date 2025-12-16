<?php
    

// pastikan session hanya dimulai 1x dan sebelum output apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// jika belum login redirect ke login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// helper sanitizer
if (!function_exists('in_str')) {
    function in_str($v) {
        global $conn;
        return mysqli_real_escape_string($conn, trim($v));
    }
}

// inisialisasi variabel
$role = $_SESSION['role'] ?? 'kepsek';
$role = in_array($role, ['admin','tatausaha','kepsek']) ? $role : 'kepsek';
$userid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$page = $_GET['page'] ?? 'transaksi';
$can_crud = in_array($role, ['admin','tatausaha']);
$can_manage_users = $role === 'admin';
// inisialisasi filter jenis agar tidak undefined
$jenis_filter = '';

// DATA RINGKASAN (dipindahkan ke atas untuk validasi)
$qP = "SELECT SUM(jumlah) AS total FROM transaksi_keuangan WHERE jenis='pemasukan'";
$rP = mysqli_query($conn, $qP);
$total_pemasukan = (float) (mysqli_fetch_assoc($rP)['total'] ?? 0);

$qK = "SELECT SUM(jumlah) AS total FROM transaksi_keuangan WHERE jenis='pengeluaran'";
$rK = mysqli_query($conn, $qK);
$total_pengeluaran = (float) (mysqli_fetch_assoc($rK)['total'] ?? 0);

$saldo = $total_pemasukan - $total_pengeluaran;

// HANDLE POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // TRANSAKSI (tatausaha/admin)
    if ($can_crud && isset($_POST['action']) && in_array($_POST['action'], ['add_trans','edit_trans','delete_trans'])) {
        $act = $_POST['action'];
        if ($act === 'add_trans') {
            // ambil dan sanitize input form
            $tanggal_input = in_str($_POST['tanggal'] ?? '');
            $jenis = in_str($_POST['jenis'] ?? '');
            $keterangan = in_str($_POST['keterangan'] ?? '');
            // jumlah sebagai float (angka) — pastikan tidak menghasilkan SQL syntax error
            $jumlah = (float) (in_str($_POST['jumlah'] ?? '0'));

            // Validasi: cek saldo jika pengeluaran
            if ($jenis === 'pengeluaran' && $jumlah > $saldo) {
                $_SESSION['error'] = 'Saldo tidak mencukupi! Saldo saat ini: Rp ' . number_format($saldo, 0, ',', '.');
            } else {
                // gunakan jam server (NOW()) saat insert sehingga jam selalu tercatat oleh server
                $sql = "INSERT INTO transaksi_keuangan (tanggal, jenis, keterangan, jumlah, created_by) 
                        VALUES (CONCAT('$tanggal_input', ' ', DATE_FORMAT(NOW(), '%H:%i:%s')), '$jenis', '$keterangan', $jumlah, $userid)";
                mysqli_query($conn, $sql);
                $_SESSION['success'] = 'Transaksi berhasil ditambahkan.';
            }
        } elseif ($act === 'edit_trans' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            $tanggal_input = in_str($_POST['tanggal']); // 'YYYY-MM-DD' dari form
            $jam_input = in_str($_POST['jam']); // 'HH:MM'
            // gabungkan tanggal dan jam dari form
            $tanggal = date('Y-m-d H:i:s', strtotime($tanggal_input . ' ' . $jam_input));
            $jenis = in_str($_POST['jenis']);
            $keterangan = in_str($_POST['keterangan']);
            $jumlah = (float) in_str($_POST['jumlah']);
            
            // Ambil transaksi lama
            $old_trans = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jenis, jumlah FROM transaksi_keuangan WHERE id=$id LIMIT 1"));
            
            // Hitung saldo setelah remove transaksi lama
            if ($old_trans['jenis'] === 'pemasukan') {
                $saldo_temp = $saldo - $old_trans['jumlah'];
            } else {
                $saldo_temp = $saldo + $old_trans['jumlah'];
            }
            
            // Validasi untuk pengeluaran baru
            if ($jenis === 'pengeluaran' && $jumlah > $saldo_temp) {
                $_SESSION['error'] = 'Saldo tidak mencukupi! Saldo saat ini: Rp ' . number_format($saldo_temp, 0, ',', '.');
            } else {
                $sql = "UPDATE transaksi_keuangan SET tanggal='$tanggal', jenis='$jenis', keterangan='$keterangan', jumlah=$jumlah WHERE id=$id";
                mysqli_query($conn, $sql);
                $_SESSION['success'] = 'Transaksi berhasil diubah.';
            }
        } elseif ($act === 'delete_trans' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            $sql = "DELETE FROM transaksi_keuangan WHERE id=$id";
            mysqli_query($conn, $sql);
            $_SESSION['success'] = 'Transaksi berhasil dihapus.';
        }
        
        if (!isset($_SESSION['error'])) {
            header('Location: dashboard.php?page=transaksi');
            exit();
        }
    }

    // USER management (admin only)
    if ($can_manage_users && isset($_POST['action']) && in_array($_POST['action'], ['add_user','edit_user','delete_user'])) {
        $act = $_POST['action'];
        if ($act === 'add_user') {
            $username = in_str($_POST['username']);
            $password = in_str($_POST['password']); // plain text per permintaan
            $role_u = in_str($_POST['role']);
            $nama = in_str($_POST['nama_lengkap']);
            $sql = "INSERT INTO users (username, password, role, nama_lengkap) VALUES ('$username', '$password', '$role_u', '$nama')";
            mysqli_query($conn, $sql);
        } elseif ($act === 'edit_user' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            $username = in_str($_POST['username']);
            $password = isset($_POST['password']) && $_POST['password'] !== '' ? in_str($_POST['password']) : null;
            $role_u = in_str($_POST['role']);
            $nama = in_str($_POST['nama_lengkap']);
            if ($password !== null) {
                $sql = "UPDATE users SET username='$username', password='$password', role='$role_u', nama_lengkap='$nama' WHERE id=$id";
            } else {
                $sql = "UPDATE users SET username='$username', role='$role_u', nama_lengkap='$nama' WHERE id=$id";
            }
            mysqli_query($conn, $sql);
        } elseif ($act === 'delete_user' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            if ($id !== $userid) {
                $sql = "DELETE FROM users WHERE id=$id";
                mysqli_query($conn, $sql);
            }
        }
        header('Location: dashboard.php?page=users');
        exit();
    }

    // PENGATURAN: update profile (semua)
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $new_username = in_str($_POST['username']);
        $new_password = in_str($_POST['password']);
        if ($new_password !== '') {
            $sql = "UPDATE users SET username='$new_username', password='$new_password' WHERE id=$userid";
        } else {
            $sql = "UPDATE users SET username='$new_username' WHERE id=$userid";
        }
        mysqli_query($conn, $sql);
        // refresh session nama jika changed
        $resn = mysqli_query($conn, "SELECT nama_lengkap FROM users WHERE id=$userid LIMIT 1");
        if ($rn = mysqli_fetch_assoc($resn)) $_SESSION['nama'] = $rn['nama_lengkap'];
        header('Location: dashboard.php?page=pengaturan&msg=updated');
        exit();
    }
}

// ambil transaksi dan users
$limit = 200;
$query_trans = "SELECT t.*, u.nama_lengkap FROM transaksi_keuangan t LEFT JOIN users u ON t.created_by = u.id ORDER BY tanggal DESC, t.id DESC LIMIT $limit";
$result_trans = mysqli_query($conn, $query_trans);

$result_users = null;
if ($role === 'admin' || $role === 'tatausaha') {
    $result_users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Dashboard - Aplikasi Manajemen Keuangan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-600 text-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="font-semibold">SMP Islam Al Azhar 44 - Aplikasi Manajemen Keuangan</div>
            <div class="flex items-center space-x-4">
                <div class="text-sm">Halo, <?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?> (<?= htmlspecialchars($role); ?>)</div>
                <a href="logout.php" class="bg-white text-blue-600 px-3 py-1 rounded">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-6">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 flex justify-between items-center" role="alert">
                <span><?= htmlspecialchars($_SESSION['error']); ?></span>
                <button onclick="this.parentElement.style.display='none'" class="text-red-700 font-bold">✕</button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex justify-between items-center" role="alert">
                <span><?= htmlspecialchars($_SESSION['success']); ?></span>
                <button onclick="this.parentElement.style.display='none'" class="text-green-700 font-bold">✕</button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="flex gap-4 mb-6">
            <a href="dashboard.php?page=transaksi" class="px-4 py-2 rounded <?= $page==='transaksi' ? 'bg-blue-600 text-white' : 'bg-white' ?>">Transaksi</a>
            <a href="dashboard.php?page=laporan" class="px-4 py-2 rounded <?= $page==='laporan' ? 'bg-blue-600 text-white' : 'bg-white' ?>">Laporan</a>
            <?php if ($role === 'admin'): ?>
                <a href="dashboard.php?page=users" class="px-4 py-2 rounded <?= $page==='users' ? 'bg-blue-600 text-white' : 'bg-white' ?>">Manajemen User</a>
            <?php endif; ?>
            <?php if ($role !== 'admin'): ?>
                <a href="dashboard.php?page=pengaturan" class="px-4 py-2 rounded <?= $page==='pengaturan' ? 'bg-blue-600 text-white' : 'bg-white' ?>">Pengaturan</a>
            <?php endif; ?>
        </div>

        <!-- Ringkasan -->
        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Total Pemasukan</div>
                <div class="text-2xl font-semibold">Rp <?= number_format($total_pemasukan, 0, ',', '.'); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Total Pengeluaran</div>
                <div class="text-2xl font-semibold">Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Saldo</div>
                <div class="text-2xl font-semibold <?= $saldo < 0 ? 'text-red-600' : '' ?>">Rp <?= number_format($saldo, 0, ',', '.'); ?></div>
            </div>
        </div>

        <!-- PAGE: transaksi -->
        <?php if ($page === 'transaksi'): ?>
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Riwayat Transaksi</h2>
                <?php if ($can_crud): ?>
                    <button onclick="document.getElementById('form-add').classList.toggle('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded">Tambah Transaksi</button>
                <?php endif; ?>
            </div>

            <?php if ($can_crud): ?>
                <div id="form-add" class="bg-white rounded-lg shadow p-6 mb-6 hidden">
                    <form method="POST" id="form-add-form" class="space-y-4">
                        <input type="hidden" name="action" value="add_trans">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600">Tanggal</label>
                                <input type="date" name="tanggal" required class="mt-1 w-full border rounded px-3 py-2" value="<?= date('Y-m-d'); ?>">
                            </div>
                            <!-- jam dihapus dari form: akan diisi otomatis oleh server saat submit -->
                             <div>
                                 <label class="block text-sm text-gray-600">Jenis</label>
                                 <select name="jenis" required class="mt-1 w-full border rounded px-3 py-2">
                                     <option value="pemasukan">Pemasukan</option>
                                     <option value="pengeluaran">Pengeluaran</option>
                                 </select>
                             </div>
                            <div>
                                <label class="block text-sm text-gray-600">Jumlah</label>
                                <input type="number" step="0.01" min="0" name="jumlah" required class="mt-1 w-full border rounded px-3 py-2">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Keterangan</label>
                            <input type="text" name="keterangan" required class="mt-1 w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Tambah</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm text-gray-600">#</th>
                            <th class="px-4 py-2 text-left text-sm text-gray-600">Tanggal</th>
                            <th class="px-4 py-2 text-left text-sm text-gray-600">Jenis</th>
                            <th class="px-4 py-2 text-left text-sm text-gray-600">Keterangan</th>
                            <th class="px-4 py-2 text-right text-sm text-gray-600">Jumlah</th>
                            <th class="px-4 py-2 text-left text-sm text-gray-600">Oleh</th>
                            <?php if ($can_crud): ?><th class="px-4 py-2 text-center text-sm text-gray-600">Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php $no = 1; while ($r = mysqli_fetch_assoc($result_trans)): ?>
                            <tr>
                                <td class="px-4 py-2 text-sm"><?= $no++; ?></td>
                                <?php
                                    $ts = strtotime($r['tanggal']);
                                    $d_datetime = $ts ? date('Y-m-d H:i:s', $ts) : htmlspecialchars($r['tanggal']);
                                ?>
                                <td class="px-4 py-2 text-sm"><?= $d_datetime ?> WIB</td>
                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($r['jenis']); ?></td>
                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($r['keterangan']); ?></td>
                                <td class="px-4 py-2 text-sm text-right">Rp <?= number_format($r['jumlah'], 0, ',', '.'); ?></td>
                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($r['nama_lengkap'] ?? '-'); ?></td>
                                <?php if ($can_crud): ?>
                                    <td class="px-4 py-2 text-sm text-center">
                                        <a href="dashboard.php?page=transaksi&edit=<?= (int)$r['id'] ?>" class="text-blue-600 mr-2">Edit</a>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Hapus transaksi ini?')">
                                            <input type="hidden" name="action" value="delete_trans">
                                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                            <button type="submit" class="text-red-600">Hapus</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Edit form if edit param present -->
            <?php if ($can_crud && isset($_GET['edit'])):
                $eid = (int) $_GET['edit'];
                $res = mysqli_query($conn, "SELECT * FROM transaksi_keuangan WHERE id=$eid LIMIT 1");
                if ($er = mysqli_fetch_assoc($res)):
            ?>
                <div class="bg-white rounded-lg shadow p-6 mt-6">
                    <h3 class="font-medium mb-4">Edit Transaksi</h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="edit_trans">
                        <input type="hidden" name="id" value="<?= (int)$er['id'] ?>">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600">Tanggal</label>
                                <input type="date" name="tanggal" required class="mt-1 w-full border rounded px-3 py-2" value="<?= htmlspecialchars(date('Y-m-d', strtotime($er['tanggal']))) ?>">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Jam</label>
                                <input type="time" name="jam" required class="mt-1 w-full border rounded px-3 py-2" value="<?= htmlspecialchars(date('H:i', strtotime($er['tanggal']))) ?>">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Jenis</label>
                                <select name="jenis" required class="mt-1 w-full border rounded px-3 py-2">
                                    <option value="pemasukan" <?= $er['jenis']==='pemasukan'?'selected':'' ?>>Pemasukan</option>
                                    <option value="pengeluaran" <?= $er['jenis']==='pengeluaran'?'selected':'' ?>>Pengeluaran</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Jumlah</label>
                                <input type="number" step="0.01" min="0" name="jumlah" required class="mt-1 w-full border rounded px-3 py-2" value="<?= htmlspecialchars($er['jumlah']) ?>">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Keterangan</label>
                            <input type="text" name="keterangan" required class="mt-1 w-full border rounded px-3 py-2" value="<?= htmlspecialchars($er['keterangan']) ?>">
                        </div>
                        <div>
                            <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded">Simpan Perubahan</button>
                            <a href="dashboard.php?page=transaksi" class="ml-3 text-gray-600">Batal</a>
                        </div>
                    </form>
                </div>
            <?php endif; endif; ?>
        <?php endif; ?>

        <!-- PAGE: laporan -->
        <?php if ($page === 'laporan'): ?>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Laporan</h2>
                <div class="text-sm text-gray-600">
                    <span id="current-time" class="text-yellow-500 font-semibold"></span> <!-- Coretan kuning untuk jam -->
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" class="grid md:grid-cols-4 gap-4">
                    <input type="hidden" name="page" value="laporan">
                    <div>
                        <label class="block text-sm text-gray-600">Dari</label>
                        <input type="date" name="from" class="mt-1 w-full border rounded px-3 py-2" value="<?= htmlspecialchars($_GET['from'] ?? date('Y-m-01')) ?>">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Sampai</label>
                        <input type="date" name="to" class="mt-1 w-full border rounded px-3 py-2" value="<?= htmlspecialchars($_GET['to'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Jenis</label>
                        <select name="jenis" class="mt-1 w-full border rounded px-3 py-2">
                            <option value="">Semua</option>
                            <option value="pemasukan" <?= (($_GET['jenis'] ?? '')==='pemasukan')?'selected':'' ?>>Pemasukan</option>
                            <option value="pengeluaran" <?= (($_GET['jenis'] ?? '')==='pengeluaran')?'selected':'' ?>>Pengeluaran</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Tampilkan</button>
                        <button type="button" onclick="printReport()" class="bg-green-600 text-white px-4 py-2 rounded ml-2">Cetak PDF</button>
                    </div>
                </form>
            </div>

            <?php
            if (isset($_GET['from']) || isset($_GET['to']) || isset($_GET['jenis'])) {
                $from = in_str($_GET['from'] ?? '1970-01-01');
                $to = in_str($_GET['to'] ?? date('Y-m-d'));
                // ambil filter jenis dari query string (aman dengan in_str)
                $jenis_filter = in_str($_GET['jenis'] ?? '');
                // gunakan rentang full day agar DATETIME ikut terfilter
                $from_dt = $from . ' 00:00:00';
                $to_dt = $to . ' 23:59:59';
                $where = "tanggal BETWEEN '$from_dt' AND '$to_dt'";
                if ($jenis_filter !== '') $where .= " AND jenis='$jenis_filter'";
                $q = "SELECT * FROM transaksi_keuangan WHERE $where ORDER BY tanggal ASC";
                $res = mysqli_query($conn, $q);
                $sum_q = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM transaksi_keuangan WHERE $where");
                $sum = (float)(mysqli_fetch_assoc($sum_q)['total'] ?? 0);

                // Ambil waktu transaksi terakhir jenis "pemasukan" (yang terakhir diinput) pada periode filter
                // jika tidak ada pemasukan pada periode, ambil transaksi terakhir apa saja pada periode
                $last_q = mysqli_query($conn, "SELECT tanggal FROM transaksi_keuangan WHERE $where AND jenis='pemasukan' ORDER BY id DESC LIMIT 1");
                if ($last_q && mysqli_num_rows($last_q) > 0) {
                    $last_row = mysqli_fetch_assoc($last_q);
                    $raw_last = $last_row['tanggal'] ?? null;
                } else {
                    $last_q2 = mysqli_query($conn, "SELECT tanggal FROM transaksi_keuangan WHERE $where ORDER BY id DESC LIMIT 1");
                    $last_row2 = mysqli_fetch_assoc($last_q2);
                    $raw_last = $last_row2['tanggal'] ?? null;
                }
                if ($raw_last && strtotime($raw_last) !== false) {
                    // tampilkan tanggal dan jam sesuai yang tersimpan di DB
                    $report_time_display = date('d/m/Y H:i:s', strtotime($raw_last));
                } else {
                    // fallback: tanggal dan jam server saat ini
                    $report_time_display = date('d/m/Y H:i:s');
                }
            ?>
                <div class="bg-white rounded-lg shadow p-4 mb-6 print-section">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-medium text-gray-800">Hasil Laporan</h3>
                            <div class="text-xs text-gray-500">
                                <?= htmlspecialchars($from).' - '.htmlspecialchars($to) ?>
                                <?= $jenis_filter ? '<span class="ml-2 px-2 py-0.5 bg-gray-100 rounded">'.htmlspecialchars($jenis_filter).'</span>' : '' ?>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">
                            <span class="text-green-500"><?= htmlspecialchars($report_time_display) ?></span>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <div class="min-w-full">
                            <!-- Header -->
                            <table class="w-full border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border border-gray-300 p-1 text-center w-12">No</th>
                                        <th class="border border-gray-300 p-1 text-center w-40">Tanggal</th>
                                        <th class="border border-gray-300 p-1 text-center w-24">Jenis</th>
                                        <th class="border border-gray-300 p-1 text-center w-32">Jumlah</th>
                                        <th class="border border-gray-300 p-1 text-center">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                            
                            <!-- Rows -->
                                <?php 
                                $no = 1;
                                $total_pemasukan = 0;
                                $total_pengeluaran = 0;
                                
                                // Reset the result pointer to the beginning
                                mysqli_data_seek($res, 0);
                                
                                while ($rr = mysqli_fetch_assoc($res)): 
                                    if ($rr['jenis'] === 'pemasukan') {
                                        $total_pemasukan += $rr['jumlah'];
                                    } else {
                                        $total_pengeluaran += $rr['jumlah'];
                                    }
                                    $tanggal = date('d/m/Y H:i', strtotime($rr['tanggal']));
                                ?>
                                    <tr>
                                        <td class="border border-gray-300 p-1 text-center"><?= $no++ ?></td>
                                        <td class="border border-gray-300 p-1 text-center"><?= $tanggal ?></td>
                                        <td class="border border-gray-300 p-1 text-center"><?= ucfirst($rr['jenis']) ?></td>
                                        <td class="border border-gray-300 p-1 text-right pr-3">Rp<?= number_format($rr['jumlah'], 0, ',', '.') ?></td>
                                        <td class="border border-gray-300 p-1 pl-2"><?= htmlspecialchars($rr['keterangan'] ?? '') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-100 font-bold">
                                        <td colspan="2" class="border border-gray-300 p-1 text-right pr-2">Jumlah Total</td>
                                        <td class="border border-gray-300 p-1 text-right pr-3">Rp<?= number_format(($total_pemasukan + $total_pengeluaran), 0, ',', '.') ?></td>
                                        <td class="border border-gray-300 p-1"></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="mt-4 text-xs text-gray-600">
                                <p>Catatan: Laporan ini dibuat pada <?= date('d/m/Y H:i:s') ?> WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>

        <!-- PAGE: MANAGEMEN USER (admin) -->
        <?php if ($page === 'users' && $role === 'admin'): ?>
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Manajemen User</h2>
                <button onclick="document.getElementById('form-user-add').classList.toggle('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded">Tambah User</button>
            </div>

            <div id="form-user-add" class="bg-white rounded-lg shadow p-6 mb-6 hidden">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add_user">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600">Username</label>
                            <input type="text" name="username" required class="mt-1 w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Password (plain)</label>
                            <input type="text" name="password" required class="mt-1 w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Role</label>
                            <select name="role" class="mt-1 w-full border rounded px-3 py-2">
                                <option value="tatausaha">Tata Usaha</option>
                                <option value="kepsek">Kepala Sekolah</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="mt-1 w-full border rounded px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Simpan</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm text-gray-600">#</th>
                            <th class="px-4 py-2 text-left text-sm text-gray-600">Username</th>
                            <th class="px-4 py-2 text-left text-sm text-gray-600">Nama</th>
                            <th class="px-4 py-2 text-left text-sm text-gray-600">Role</th>
                            <th class="px-4 py-2 text-center text-sm text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php if ($result_users): $i=1; while ($u = mysqli_fetch_assoc($result_users)): ?>
                            <tr>
                                <td class="px-4 py-2 text-sm"><?= $i++; ?></td>
                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($u['username']) ?></td>
                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                                <td class="px-4 py-2 text-sm"><?= htmlspecialchars($u['role']) ?></td>
                                <td class="px-4 py-2 text-sm text-center">
                                    <a href="dashboard.php?page=users&edit_user=<?= (int)$u['id'] ?>" class="text-blue-600 mr-2">Edit</a>
                                    <?php if ((int)$u['id'] !== $userid): ?>
                                        <form method="POST" style="display:inline" onsubmit="return confirm('Hapus user ini?')">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                            <button type="submit" class="text-red-600">Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td class="px-4 py-2" colspan="5">Tidak ada data user.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php
            if (isset($_GET['edit_user'])):
                $uid = (int) $_GET['edit_user'];
                $resu = mysqli_query($conn, "SELECT * FROM users WHERE id=$uid LIMIT 1");
                if ($uu = mysqli_fetch_assoc($resu)):
            ?>
                <div class="bg-white rounded-lg shadow p-6 mt-6">
                    <h3 class="font-medium mb-4">Edit User</h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="id" value="<?= (int)$uu['id'] ?>">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-600">Username</label>
                                <input type="text" name="username" required class="mt-1 w-full border rounded px-3 py-2" value="<?= htmlspecialchars($uu['username']) ?>">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Password (kosongkan = tidak diubah)</label>
                                <input type="text" name="password" class="mt-1 w-full border rounded px-3 py-2" value="">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Role</label>
                                <select name="role" class="mt-1 w-full border rounded px-3 py-2">
                                    <option value="tatausaha" <?= $uu['role']==='tatausaha'?'selected':'' ?>>Tata Usaha</option>
                                    <option value="kepsek" <?= $uu['role']==='kepsek'?'selected':'' ?>>Kepala Sekolah</option>
                                    <option value="admin" <?= $uu['role']==='admin'?'selected':'' ?>>Admin</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="mt-1 w-full border rounded px-3 py-2" value="<?= htmlspecialchars($uu['nama_lengkap']) ?>">
                            </div>
                        </div>
                        <div>
                            <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded">Simpan Perubahan</button>
                            <a href="dashboard.php?page=users" class="ml-3 text-gray-600">Batal</a>
                        </div>
                    </form>
                </div>
            <?php
                endif;
            endif;
            ?>
        <?php endif; ?>

        <!-- PAGE: PENGATURAN -->
        <?php if ($page === 'pengaturan'): ?>
            <div class="mb-6">
                <h2 class="text-lg font-semibold">Pengaturan Akun</h2>
            </div>

            <?php
            $me = mysqli_query($conn, "SELECT * FROM users WHERE id=$userid LIMIT 1");
            $me = mysqli_fetch_assoc($me);
            ?>
            <div class="bg-white rounded-lg shadow p-6 max-w-xl">
                <?php if (isset($_GET['msg']) && $_GET['msg']==='updated'): ?>
                    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">Profil berhasil diperbarui.</div>
                <?php endif; ?>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="update_profile">
                    <div>
                        <label class="block text-sm text-gray-600">Username</label>
                        <input type="text" name="username" required class="mt-1 w-full border rounded px-3 py-2" value="<?= htmlspecialchars($me['username'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                        <input type="text" name="password" class="mt-1 w-full border rounded px-3 py-2" value="">
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </main>

    <style id="printStyle" type="text/css" media="print">
        @page { size: A4; margin: 0.5cm; }
        body * { visibility: hidden; }
        .print-section, .print-section * { visibility: visible; }
        .print-section { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 8px; font-size: 10pt; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .border { border: 1px solid #000; }
        .border-t { border-top: 1px solid #000; }
        .border-b { border-bottom: 1px solid #000; }
        .p-2 { padding: 0.5rem; }
        .font-bold { font-weight: bold; }
        .bg-gray-100 { background-color: #f7fafc; }
        .bg-white { background-color: #fff; }
        .text-sm { font-size: 0.875rem; }
        .text-xs { font-size: 0.75rem; }
        .w-full { width: 100%; }
        .text-black { color: #000 !important; }
    </style>
    <script>
        function printReport() {
            // Create a print window
            const printWindow = window.open('', '_blank');
            
            // Get the report content
            const reportContent = document.querySelector('.print-section').outerHTML;
            
            // Write the print content
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Laporan Keuangan</title>
                    <style>
                        @page { size: A4; margin: 0.5cm; }
                        body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                        th, td { border: 1px solid #000; padding: 4px 8px; }
                        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
                        .text-right { text-align: right; }
                        .text-center { text-align: center; }
                        .text-left { text-align: left; }
                        .font-bold { font-weight: bold; }
                        .text-sm { font-size: 9pt; }
                        .p-2 { padding: 0.5rem; }
                        .w-full { width: 100%; }
                        .border { border: 1px solid #000; }
                        .border-t { border-top: 1px solid #000; }
                        .border-b { border-bottom: 1px solid #000; }
                        .bg-gray-100 { background-color: #f7fafc; }
                        .bg-white { background-color: #fff; }
                        .text-black { color: #000 !important; }
                        .mb-4 { margin-bottom: 1rem; }
                        .mt-4 { margin-top: 1rem; }
                    </style>
                </head>
                <body class="bg-white">
                    <div style="text-align: center; margin-bottom: 15px;">
                        <h2 style="margin: 0; padding: 0; font-size: 14pt; font-weight: bold;">LAPORAN KEUANGAN</h2>
                        <h3 style="margin: 5px 0 0 0; padding: 0; font-size: 12pt;">YAYASAN PENDIDIKAN AL AZHAR</h3>
                        <h4 style="margin: 5px 0 0 0; padding: 0; font-size: 11pt;">UNIT SMP ISLAM AL AZHAR 44</h4>
                        <div style="font-size: 9pt; margin-top: 5px; margin-bottom: 10px;">
                            Jl. Sunset Ave, Lambangsari, Kec. Tambun Sel., Kabupaten Bekasi, Jawa Barat 17510<br>
                            Telp. 0812-8300-8344, Website: https://smpialazhar44.sch.id/
                        </div>
                        <hr style="border: 1px solid black; margin: 5px 0 15px 0;">
                    </div>
                    <div style="margin-bottom: 10px; font-size: 10pt;">
                        <div>Periode: ${document.querySelector('[name="from"]').value} s/d ${document.querySelector('[name="to"]').value}</div>
                        <div>Tanggal Cetak: ${new Date().toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'})}</div>
                    </div>
                    ${reportContent}
                    <div style="margin-top: 30px; width: 100%; text-align: right;">
                        <div style="display: inline-block; text-align: center; min-width: 300px; margin-right: 20px;">
                            <div style="margin-bottom: 20px;">
                                <div>Mengetahui,</div>
                                <div>Kepala Sekolah</div>
                            </div>
                            <div style="margin-top: 60px; font-weight: bold;">Drs. H. Moch. Syarif Hidayat, M.Pd</div>
                            <div style="margin-top: 5px; font-weight: normal;">(NIP. 196812312005011001)</div>
                        </div>
                    </div>
                    <script>
                        window.onload = function() {
                            setTimeout(function() {
                                window.print();
                                window.onafterprint = function() {
                                    window.close();
                                };
                            }, 200);
                        };
                    <\/script>
                </body>
                </html>
            `);
            
            printWindow.document.close();
        }
        
        function pad(n){ return String(n).padStart(2,'0'); }
        function updateTime(){ const now=new Date(); const el=document.getElementById('current-time'); if(el) el.textContent = pad(now.getHours())+':'+pad(now.getMinutes())+':'+pad(now.getSeconds()); }
        setInterval(updateTime,1000); updateTime();

        // polling last time tiap 5 detik (memakai id untuk mencocokkan baris)
        async function pollLastTime(){
            const urlParams = new URLSearchParams(window.location.search);
            const from = urlParams.get('from') || document.querySelector('input[name="from"]')?.value || '';
            const to = urlParams.get('to') || document.querySelector('input[name="to"]')?.value || '';
            const jenis = urlParams.get('jenis') || document.querySelector('select[name="jenis"]')?.value || '';

            const q = new URLSearchParams({ from: from, to: to, jenis: jenis });
            try {
                const res = await fetch('api_last_time.php?' + q.toString(), {cache:'no-store'});
                const data = await res.json();
                if (data.ok) {
                    const rep = document.getElementById('report-time');
                    if (rep) rep.textContent = data.display;

                    // gunakan id untuk update baris spesifik saja
                    if (data.id) {
                        const cell = document.querySelector('.report-datetime[data-id="'+data.id+'"]');
                        if (cell) {
                            cell.textContent = data.raw + ' WIB';
                        }
                    }
                }
            } catch(e){
                console.error('pollLastTime error', e);
            }
        }

        pollLastTime();
        setInterval(pollLastTime, 5000);
    </script>
</body>
</html>