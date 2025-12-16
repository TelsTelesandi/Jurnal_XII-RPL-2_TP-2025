<?php
require_once '../includes/db_connect.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$success = '';
$error = '';

// Proses tambah transaksi masuk
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_incoming'])) {
    try {
        $pdo->beginTransaction();
        
        // Generate kode transaksi maksimal 20 karakter
        // Format: INCYYYYMMDD-XXXX (XXXX = 4 hex chars)
        $transaction_code = 'INC' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
        $supplier_id = (int)$_POST['supplier_id'];
        $transaction_date = $_POST['transaction_date'];
        $notes = trim($_POST['notes'] ?? '');
        $items = $_POST['items'];
        
        if (empty($items) || !is_array($items)) {
            throw new Exception("Tidak ada item yang ditambahkan");
        }
        
        // Simpan transaksi
        $stmt = $pdo->prepare("
            INSERT INTO incoming_transactions 
            (transaction_code, supplier_id, transaction_date, notes, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $transaction_code,
            $supplier_id,
            $transaction_date,
            $notes,
            $_SESSION['user_id']
        ]);
        
        $transaction_id = $pdo->lastInsertId();
        
        // Simpan detail item
        foreach ($items as $item) {
            $item_id = (int)$item['item_id'];
            $quantity = (int)$item['quantity'];
            $unit_price = (float)$item['unit_price'];
            $total_price = $quantity * $unit_price;
            
            if ($quantity <= 0) continue;
            
            // Simpan detail transaksi
            $stmt = $pdo->prepare("
                INSERT INTO incoming_transaction_items 
                (transaction_id, item_id, quantity, unit_price, total_price)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$transaction_id, $item_id, $quantity, $unit_price, $total_price]);
            
            // Update stok barang
            $stmt = $pdo->prepare("UPDATE items SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$quantity, $item_id]);
        }
        
        $pdo->commit();
        $success = 'Transaksi masuk berhasil dicatat';
        
        // Reset form
        $_POST = [];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Gagal menyimpan transaksi: ' . $e->getMessage();
    }
}

// Hapus transaksi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $pdo->beginTransaction();
        
        // Dapatkan detail item untuk mengembalikan stok
        $stmt = $pdo->prepare("
            SELECT item_id, quantity 
            FROM incoming_transaction_items 
            WHERE transaction_id = ?
        ");
        $stmt->execute([$_GET['delete']]);
        $items = $stmt->fetchAll();
        
        // Kembalikan stok
        foreach ($items as $item) {
            $stmt = $pdo->prepare("UPDATE items SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['item_id']]);
        }
        
        // Hapus transaksi
        $stmt = $pdo->prepare("DELETE FROM incoming_transactions WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        
        $pdo->commit();
        $success = 'Transaksi berhasil dihapus';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Gagal menghapus transaksi: ' . $e->getMessage();
    }
}

// Ambil data transaksi dengan pencarian
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$query = "
    SELECT it.*, s.name as supplier_name, u.name as created_by_name
    FROM incoming_transactions it
    JOIN suppliers s ON it.supplier_id = s.id
    JOIN users u ON it.created_by = u.id
    WHERE 1=1
";

$params = [];

if (!empty($search)) {
    $query .= " AND (it.transaction_code LIKE ? OR s.name LIKE ? OR s.contact_person LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if (!empty($date_from)) {
    $query .= " AND it.transaction_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND it.transaction_date <= ?";
    $params[] = $date_to . ' 23:59:59';
}

$query .= " ORDER BY it.transaction_date DESC, it.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Ambil daftar supplier untuk dropdown
$suppliers = $pdo->query("SELECT id, supplier_id, name FROM suppliers ORDER BY name")->fetchAll();

// Ambil daftar barang untuk form
$items = $pdo->query("SELECT id, item_code, item_name, unit, unit_price FROM items ORDER BY item_name")->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Barang Masuk</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncomingModal">
            <i class="fas fa-plus"></i> Tambah Barang Masuk
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
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Cari kode transaksi atau supplier..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" placeholder="Dari tanggal">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" placeholder="Sampai tanggal">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
            <?php if (!empty($search) || !empty($date_from) || !empty($date_to)): ?>
                <div class="col-12">
                    <a href="incoming_items.php" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Daftar Transaksi -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Transaksi</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Jumlah Item</th>
                        <th>Total Nilai</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $transaction): 
                            // Hitung total item dan total nilai
                            $stmt = $pdo->prepare("
                                SELECT COUNT(*) as item_count, SUM(total_price) as total_value
                                FROM incoming_transaction_items
                                WHERE transaction_id = ?
                            ");
                            $stmt->execute([$transaction['id']]);
                            $summary = $stmt->fetch();
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['transaction_code']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($transaction['transaction_date'])); ?></td>
                                <td><?php echo htmlspecialchars($transaction['supplier_name']); ?></td>
                                <td class="text-center"><?php echo number_format($summary['item_count']); ?></td>
                                <td>Rp <?php echo number_format($summary['total_value'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($transaction['created_by_name']); ?></td>
                                <td>
                                    <a href="incoming_items_view.php?id=<?php echo $transaction['id']; ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="incoming_items_print.php?id=<?php echo $transaction['id']; ?>" target="_blank" class="btn btn-sm btn-secondary" title="Cetak">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="?delete=<?php echo $transaction['id']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Yakin ingin menghapus transaksi ini? Stok barang akan dikembalikan.')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="mb-0">Tidak ada data transaksi</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Barang Masuk -->
<div class="modal fade" id="addIncomingModal" tabindex="-1" aria-labelledby="addIncomingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addIncomingForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addIncomingModalLabel">Tambah Barang Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Transaksi <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="transaction_date" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select class="form-select" name="supplier_id" required>
                                    <option value="">Pilih Supplier</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?php echo $supplier['id']; ?>">
                                            <?php echo htmlspecialchars($supplier['name'] . ' (' . $supplier['supplier_id'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Catatan tambahan (opsional)"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mb-3">Daftar Barang</h5>
                    
                    <div id="items-container">
                        <!-- Baris item akan ditambahkan di sini oleh JavaScript -->
                        <div class="item-row row g-3 mb-3">
                            <div class="col-md-5">
                                <select class="form-select item-select" name="items[0][item_id]" required>
                                    <option value="">Pilih Barang</option>
                                    <?php foreach ($items as $item): ?>
                                        <option value="<?php echo $item['id']; ?>" 
                                                data-unit="<?php echo htmlspecialchars($item['unit']); ?>"
                                                data-price="<?php echo $item['unit_price']; ?>">
                                            <?php echo htmlspecialchars($item['item_name'] . ' (' . $item['item_code'] . ') - Stok: ' . $item['unit_price']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control quantity" name="items[0][quantity]" min="1" value="1" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control unit" value="" readonly>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control price" name="items[0][unit_price]" min="0" step="100" required>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-item" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-more-items">
                            <i class="fas fa-plus"></i> Tambah Barang
                        </button>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Total Item: <span id="total-items">0</span></h6>
                            </div>
                            <div class="col-md-6 text-end">
                                <h5>Total Nilai: <span id="total-value">Rp 0</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_incoming" class="btn btn-primary">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Inisialisasi variabel
let itemIndex = 1;

// Fungsi untuk menambahkan baris item baru
document.getElementById('add-more-items').addEventListener('click', function() {
    const container = document.getElementById('items-container');
    const newRow = document.querySelector('.item-row').cloneNode(true);
    
    // Update nama input untuk mencegah duplikat
    const newIndex = itemIndex++;
    newRow.innerHTML = newRow.innerHTML.replace(/items\[\d+\]/g, `items[${newIndex}]`);
    
    // Reset nilai
    newRow.querySelector('.item-select').value = '';
    newRow.querySelector('.quantity').value = '1';
    newRow.querySelector('.unit').value = '';
    newRow.querySelector('.price').value = '';
    newRow.querySelector('.remove-item').style.display = 'block';
    
    // Tambahkan event listener untuk tombol hapus
    newRow.querySelector('.remove-item').addEventListener('click', function() {
        this.closest('.item-row').remove();
        calculateTotals();
    });
    
    // Tambahkan event listener untuk select item
    newRow.querySelector('.item-select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const unit = selectedOption.dataset.unit || '';
        const price = selectedOption.dataset.price || '0';
        
        const row = this.closest('.item-row');
        row.querySelector('.unit').value = unit;
        
        const priceInput = row.querySelector('.price');
        if (priceInput.value === '') {
            priceInput.value = price;
        }
        
        calculateTotals();
    });
    
    // Tambahkan event listener untuk quantity dan price
    newRow.querySelector('.quantity').addEventListener('input', calculateTotals);
    newRow.querySelector('.price').addEventListener('input', calculateTotals);
    
    container.appendChild(newRow);
    
    // Fokus ke select item yang baru ditambahkan
    newRow.querySelector('.item-select').focus();
});

// Event delegation untuk select item yang sudah ada
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('item-select')) {
        const selectedOption = e.target.options[e.target.selectedIndex];
        const unit = selectedOption.dataset.unit || '';
        const price = selectedOption.dataset.price || '0';
        
        const row = e.target.closest('.item-row');
        row.querySelector('.unit').value = unit;
        
        const priceInput = row.querySelector('.price');
        if (priceInput.value === '') {
            priceInput.value = price;
        }
        
        calculateTotals();
    }
});

// Event delegation untuk quantity dan price
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('quantity') || e.target.classList.contains('price')) {
        calculateTotals();
    }
});

// Event delegation untuk tombol hapus
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
        e.target.closest('.item-row').remove();
        calculateTotals();
    }
});

// Fungsi untuk menghitung total item dan total nilai
function calculateTotals() {
    let totalItems = 0;
    let totalValue = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        
        totalItems += quantity;
        totalValue += quantity * price;
    });
    
    document.getElementById('total-items').textContent = totalItems;
    document.getElementById('total-value').textContent = 'Rp ' + totalValue.toLocaleString('id-ID');
}

// Inisialisasi tombol hapus untuk baris pertama
const firstRemoveBtn = document.querySelector('.remove-item');
if (firstRemoveBtn) {
    firstRemoveBtn.style.display = 'none';
}

// Hitung total saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
    
    // Inisialisasi select2 untuk pencarian yang lebih baik
    $('.item-select').select2({
        placeholder: 'Pilih Barang',
        width: '100%',
        theme: 'bootstrap-5'
    });
});

// Validasi form sebelum submit
document.getElementById('addIncomingForm').addEventListener('submit', function(e) {
    const itemRows = document.querySelectorAll('.item-row');
    let hasValidItem = false;
    
    itemRows.forEach(row => {
        const itemId = row.querySelector('.item-select').value;
        const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
        
        if (itemId && quantity > 0) {
            hasValidItem = true;
        }
    });
    
    if (!hasValidItem) {
        e.preventDefault();
        alert('Minimal harus ada satu barang dengan kuantitas lebih dari 0');
        return false;
    }
    
    return true;
});
</script>

<style>
.item-row {
    transition: all 0.3s ease;
}

.item-row:hover {
    background-color: #f8f9fa;
}

.remove-item {
    opacity: 0.7;
    transition: all 0.2s ease;
}

.remove-item:hover {
    opacity: 1;
}

/* Style untuk select2 */
.select2-container--bootstrap-5 .select2-selection {
    min-height: 38px;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    padding: 0;
}

.select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>

<?php include '../includes/footer.php'; ?>
