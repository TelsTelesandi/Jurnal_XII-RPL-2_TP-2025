<?php
require_once '../includes/db_connect.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$success = '';
$error = '';

// Tambah Supplier Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    $supplier_id = trim($_POST['supplier_id']);
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $contact_person = trim($_POST['contact_person']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO suppliers (supplier_id, name, address, contact_person, phone, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$supplier_id, $name, $address, $contact_person, $phone, $email]);
        $success = 'Supplier berhasil ditambahkan';
    } catch (PDOException $e) {
        $error = 'Gagal menambahkan supplier: ' . $e->getMessage();
    }
}

// Hapus Supplier
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        // Cek apakah supplier memiliki transaksi
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM incoming_transactions WHERE supplier_id = ?");
        $stmt->execute([$_GET['delete']]);
        $has_transactions = $stmt->fetchColumn() > 0;
        
        if ($has_transactions) {
            $error = 'Tidak dapat menghapus supplier yang sudah memiliki transaksi';
        } else {
            $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
            $stmt->execute([$_GET['delete']]);
            $success = 'Supplier berhasil dihapus';
        }
    } catch (PDOException $e) {
        $error = 'Gagal menghapus supplier: ' . $e->getMessage();
    }
}

// Ambil data supplier dengan pencarian
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM suppliers WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (supplier_id LIKE ? OR name LIKE ? OR contact_person LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$query .= " ORDER BY name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$suppliers = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manajemen Supplier</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="fas fa-plus"></i> Tambah Supplier
        </button>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Pencarian -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Cari supplier..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </div>
            <?php if (!empty($search)): ?>
                <div class="col-md-2">
                    <a href="manage_suppliers.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tabel Daftar Supplier -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode Supplier</th>
                        <th>Nama Perusahaan</th>
                        <th>Contact Person</th>
                        <th>Kontak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($suppliers) > 0): ?>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier['supplier_id']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($supplier['name']); ?></strong>
                                    <?php if (!empty($supplier['address'])): ?>
                                        <br><small class="text-muted"><?php echo nl2br(htmlspecialchars($supplier['address'])); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                                <td>
                                    <?php if (!empty($supplier['phone'])): ?>
                                        <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($supplier['phone']); ?><br>
                                    <?php endif; ?>
                                    <?php if (!empty($supplier['email'])): ?>
                                        <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($supplier['email']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editSupplier(<?php echo htmlspecialchars(json_encode($supplier)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $supplier['id']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Yakin ingin menghapus supplier ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="mb-0">Tidak ada data supplier</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Supplier -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalLabel">Tambah Supplier Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Kode Supplier <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="supplier_id" name="supplier_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Telepon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_supplier" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Supplier -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editSupplierForm" method="POST">
                <input type="hidden" name="supplier_id" id="edit_supplier_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_supplier_code" class="form-label">Kode Supplier <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_supplier_code" name="supplier_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_contact_person" class="form-label">Contact Person <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_contact_person" name="contact_person" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Telepon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="edit_supplier" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fungsi untuk mengisi form edit supplier
function editSupplier(supplier) {
    document.getElementById('edit_supplier_id').value = supplier.id;
    document.getElementById('edit_supplier_code').value = supplier.supplier_id;
    document.getElementById('edit_name').value = supplier.name;
    document.getElementById('edit_contact_person').value = supplier.contact_person;
    document.getElementById('edit_phone').value = supplier.phone || '';
    document.getElementById('edit_email').value = supplier.email || '';
    document.getElementById('edit_address').value = supplier.address || '';
    
    // Tampilkan modal edit
    var editModal = new bootstrap.Modal(document.getElementById('editSupplierModal'));
    editModal.show();
}

// Handle form edit submission
document.getElementById('editSupplierForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Kirim data form menggunakan fetch API
    const formData = new FormData(this);
    
    fetch('update_supplier.php', {
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
