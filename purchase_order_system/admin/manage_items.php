<?php
require_once '../includes/db_connect.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$success = '';
$error = '';

// Tambah Barang Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $item_code = trim($_POST['item_code']);
    $item_name = trim($_POST['item_name']);
    $unit = trim($_POST['unit']);
    $stock = (int)$_POST['stock'];
    $unit_price = (float)$_POST['unit_price'];
    $description = trim($_POST['description']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO items (item_code, item_name, unit, stock, unit_price, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$item_code, $item_name, $unit, $stock, $unit_price, $description]);
        $success = 'Barang berhasil ditambahkan';
    } catch (PDOException $e) {
        $error = 'Gagal menambahkan barang: ' . $e->getMessage();
    }
}

// Hapus Barang
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = 'Barang berhasil dihapus';
    } catch (PDOException $e) {
        $error = 'Gagal menghapus barang: ' . $e->getMessage();
    }
}

// Ambil data barang dengan pencarian dan filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$query = "SELECT * FROM items WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (item_code LIKE ? OR item_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter === 'low_stock') {
    $query .= " AND stock < 10";
} elseif ($filter === 'out_of_stock') {
    $query .= " AND stock = 0";
}

$query .= " ORDER BY item_name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Barang</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="fas fa-plus"></i> Tambah Barang
        </button>
    </div>
</div>

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
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Cari kode atau nama barang..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <select name="filter" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Semua Barang</option>
                    <option value="low_stock" <?php echo $filter === 'low_stock' ? 'selected' : ''; ?>>Stok Sedikit (<10)</option>
                    <option value="out_of_stock" <?php echo $filter === 'out_of_stock' ? 'selected' : ''; ?>>Stok Habis</option>
                </select>
            </div>
            <?php if (!empty($search) || $filter !== 'all'): ?>
                <div class="col-md-2">
                    <a href="manage_items.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabel Daftar Barang -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th>Stok</th>
                        <th>Harga Satuan</th>
                        <th>Total Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_code']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                    <?php if (!empty($item['description'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($item['description']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $item['stock'] == 0 ? 'danger' : 
                                            ($item['stock'] < 10 ? 'warning' : 'success'); 
                                    ?>">
                                        <?php echo number_format($item['stock']); ?>
                                    </span>
                                </td>
                                <td>Rp <?php echo number_format($item['unit_price'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($item['stock'] * $item['unit_price'], 0, ',', '.'); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Yakin ingin menghapus barang ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="mb-0">Tidak ada data barang</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (count($items) > 0): ?>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="4" class="text-end"><strong>Total Nilai Stok:</strong></td>
                            <td colspan="3">
                                <strong>Rp <?php 
                                    $total_value = array_reduce($items, function($carry, $item) {
                                        return $carry + ($item['stock'] * $item['unit_price']);
                                    }, 0);
                                    echo number_format($total_value, 0, ',', '.');
                                ?></strong>
                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Barang -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Tambah Barang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="item_code" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="item_code" name="item_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="item_name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="item_name" name="item_name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">Satuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unit" name="unit" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stok Awal <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" value="0" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="unit_price" class="form-label">Harga Satuan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="unit_price" name="unit_price" min="0" step="100" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_item" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Barang -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editItemForm" method="POST">
                <input type="hidden" name="item_id" id="edit_item_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editItemModalLabel">Edit Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_item_code" class="form-label">Kode Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_item_code" name="item_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_item_name" class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_item_name" name="item_name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_unit" class="form-label">Satuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_unit" name="unit" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_stock" class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_stock" name="stock" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_unit_price" class="form-label">Harga Satuan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="edit_unit_price" name="unit_price" min="0" step="100" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_item" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fungsi untuk mengisi form edit
function editItem(item) {
    document.getElementById('edit_item_id').value = item.id;
    document.getElementById('edit_item_code').value = item.item_code;
    document.getElementById('edit_item_name').value = item.item_name;
    document.getElementById('edit_unit').value = item.unit;
    document.getElementById('edit_stock').value = item.stock;
    document.getElementById('edit_unit_price').value = item.unit_price;
    document.getElementById('edit_description').value = item.description || '';
    
    // Tampilkan modal edit
    var editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
    editModal.show();
}

// Handle form edit submission
document.getElementById('editItemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Kirim data form menggunakan fetch API
    const formData = new FormData(this);
    
    fetch('update_item.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh halaman jika berhasil
            window.location.reload();
        } else {
            alert('Gagal memperbarui data: ' + (data.message || 'Terjadi kesalahan'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
