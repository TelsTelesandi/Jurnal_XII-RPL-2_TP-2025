<?php
require_once '../includes/db_connect.php';

// Pastikan hanya user production yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'production') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = isset($_GET['success']) ? 'Permintaan barang berhasil dikirim' : '';
$error = '';

// Batalkan permintaan yang masih pending
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE outgoing_transactions 
            SET status = 'cancelled', cancelled_at = NOW() 
            WHERE id = ? AND requested_by = ? AND status = 'pending'
        ");
        $stmt->execute([$_GET['cancel'], $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $success = 'Permintaan berhasil dibatalkan';
        } else {
            $error = 'Tidak dapat membatalkan permintaan';
        }
        
    } catch (PDOException $e) {
        $error = 'Gagal membatalkan permintaan: ' . $e->getMessage();
    }
}

// Ambil daftar permintaan
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$query = "
    SELECT ot.*, 
           (SELECT COUNT(*) FROM outgoing_transaction_items WHERE transaction_id = ot.id) as item_count,
           (SELECT SUM(total_price) FROM outgoing_transaction_items WHERE transaction_id = ot.id) as total_value
    FROM outgoing_transactions ot
    WHERE ot.requested_by = ?
";

$params = [$user_id];

if (!empty($search)) {
    $query .= " AND (ot.request_code LIKE ? OR ot.production_notes LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($status !== 'all') {
    $query .= " AND ot.status = ?";
    $params[] = $status;
}

if (!empty($date_from)) {
    $query .= " AND DATE(ot.request_date) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(ot.request_date) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY ot.request_date DESC, ot.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>



<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Filter dan Pencarian -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Cari kode permintaan atau catatan..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="partially_approved" <?php echo $status === 'partially_approved' ? 'selected' : ''; ?>>Disetujui Sebagian</option>
                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" placeholder="Dari tanggal">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" placeholder="Sampai tanggal">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
            <?php if (!empty($search) || $status !== 'all' || !empty($date_from) || !empty($date_to)): ?>
                <div class="col-12">
                    <a href="history.php" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Daftar Permintaan -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Permintaan</th>
                        <th>Tanggal</th>
                        <th>Jumlah Item</th>
                        <th>Total Nilai</th>
                        <th>Status</th>
                        <th>Catatan Admin</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($requests) > 0): ?>
                        <?php foreach ($requests as $request): 
                            $status_class = [
                                'pending' => 'warning',
                                'approved' => 'success',
                                'partially_approved' => 'info',
                                'rejected' => 'danger',
                                'cancelled' => 'secondary'
                            ][$request['status']] ?? 'secondary';
                            
                            $status_text = [
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'partially_approved' => 'Disetujui Sebagian',
                                'rejected' => 'Ditolak',
                                'cancelled' => 'Dibatalkan'
                            ][$request['status']] ?? $request['status'];
                            
                            $admin_notes = !empty($request['admin_notes']) ? 
                                '<small class="text-muted">' . nl2br(htmlspecialchars($request['admin_notes'])) . '</small>' : 
                                '-';
                        ?>
                            <tr>
                                <td>
                                    <a href="request_view.php?id=<?php echo $request['id']; ?>" class="text-primary">
                                        <?php echo htmlspecialchars($request['request_code']); ?>
                                    </a>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($request['request_date'])); ?></td>
                                <td class="text-center"><?php echo number_format($request['item_count']); ?></td>
                                <td>Rp <?php echo number_format($request['total_value'] ?? 0, 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td><?php echo $admin_notes; ?></td>
                                <td>
                                    <a href="request_view.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <a href="?cancel=<?php echo $request['id']; ?>" class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin membatalkan permintaan ini?')" title="Batalkan">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="mb-0">Tidak ada data permintaan</p>
                                <a href="request_item.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus"></i> Buat Permintaan Baru
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Inisialisasi tooltip
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Inisialisasi datepicker
    $('input[type="date"]').on('change', function() {
        if (this.value) {
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
        }
    });
});
</script>

<style>
.badge {
    font-size: 0.85em;
    font-weight: 500;
    padding: 0.35em 0.65em;
}

.table th {
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Style untuk status badge */
.bg-warning {
    background-color: #ffc107 !important;
    color: #000;
}

.bg-success {
    background-color: #198754 !important;
}

.bg-info {
    background-color: #0dcaf0 !important;
    color: #000;
}

.bg-danger {
    background-color: #dc3545 !important;
}

.bg-secondary {
    background-color: #6c757d !important;
}
</style>

<?php include '../includes/footer.php'; ?>
