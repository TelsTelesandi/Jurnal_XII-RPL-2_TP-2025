<?php
require_once '../includes/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header('Location: ../login.php'); exit(); }
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_incoming'])) {
    try {
        $pdo->beginTransaction();
        $tid = (int)($_POST['transaction_id'] ?? 0);
        if ($tid <= 0) { throw new Exception('Transaksi tidak valid'); }
        $stmt = $pdo->prepare('SELECT * FROM incoming_transactions WHERE id = ?');
        $stmt->execute([$tid]);
        $tx = $stmt->fetch();
        if (!$tx) { throw new Exception('Transaksi tidak ditemukan'); }
        $supplier_id = (int)($_POST['supplier_id'] ?? 0);
        $transaction_date = trim($_POST['transaction_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $items = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];
        if ($supplier_id <= 0 || $transaction_date === '') { throw new Exception('Data transaksi tidak lengkap'); }
        $oldStmt = $pdo->prepare('SELECT id, item_id, quantity, unit_price FROM incoming_transaction_items WHERE transaction_id = ?');
        $oldStmt->execute([$tid]);
        $oldRows = $oldStmt->fetchAll();
        $oldMap = [];
        foreach ($oldRows as $r) { $oldMap[(int)$r['item_id']] = ['detail_id'=>(int)$r['id'], 'qty'=>(int)$r['quantity'], 'price'=>(float)$r['unit_price']]; }
        $newMap = [];
        foreach ($items as $row) {
            $iid = (int)($row['item_id'] ?? 0);
            $qty = (int)($row['quantity'] ?? 0);
            $price = (float)($row['unit_price'] ?? 0);
            if ($iid > 0 && $qty >= 0) { $newMap[$iid] = ['qty'=>$qty, 'price'=>$price]; }
        }
        $allKeys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));
        foreach ($allKeys as $iid) {
            $hasOld = isset($oldMap[$iid]);
            $hasNew = isset($newMap[$iid]);
            if ($hasOld && $hasNew) {
                $oldQty = $oldMap[$iid]['qty'];
                $newQty = $newMap[$iid]['qty'];
                $price = $newMap[$iid]['price'];
                $delta = $newQty - $oldQty;
                if ($delta !== 0) { $pdo->prepare('UPDATE items SET stock = stock + ? WHERE id = ?')->execute([$delta, $iid]); }
                $pdo->prepare('UPDATE incoming_transaction_items SET quantity = ?, unit_price = ?, total_price = ? WHERE id = ?')
                    ->execute([$newQty, $price, $price * $newQty, $oldMap[$iid]['detail_id']]);
            } elseif ($hasOld && !$hasNew) {
                $oldQty = $oldMap[$iid]['qty'];
                $pdo->prepare('UPDATE items SET stock = stock - ? WHERE id = ?')->execute([$oldQty, $iid]);
                $pdo->prepare('DELETE FROM incoming_transaction_items WHERE id = ?')->execute([$oldMap[$iid]['detail_id']]);
            } elseif (!$hasOld && $hasNew) {
                $newQty = $newMap[$iid]['qty'];
                $price = $newMap[$iid]['price'];
                $pdo->prepare('INSERT INTO incoming_transaction_items (transaction_id, item_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$tid, $iid, $newQty, $price, $price * $newQty]);
                if ($newQty > 0) { $pdo->prepare('UPDATE items SET stock = stock + ? WHERE id = ?')->execute([$newQty, $iid]); }
            }
        }
        $pdo->prepare('UPDATE incoming_transactions SET supplier_id = ?, transaction_date = ?, notes = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$supplier_id, $transaction_date, $notes, $tid]);
        $pdo->commit();
        $success = 'Transaksi berhasil diperbarui';
        $id = $tid;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $error = 'Gagal memperbarui transaksi: ' . $e->getMessage();
    }
}
$stmt = $pdo->prepare('SELECT it.*, s.name AS supplier_name, s.id AS supplier_id, u.name AS created_by_name FROM incoming_transactions it JOIN suppliers s ON s.id = it.supplier_id JOIN users u ON u.id = it.created_by WHERE it.id = ?');
$stmt->execute([$id]);
$transaction = $stmt->fetch();
if (!$transaction) { $error = 'Transaksi tidak ditemukan'; }
$itemsList = [];
if ($transaction) {
    $itemsStmt = $pdo->prepare('SELECT iti.*, i.item_name, i.item_code, i.unit FROM incoming_transaction_items iti JOIN items i ON i.id = iti.item_id WHERE iti.transaction_id = ?');
    $itemsStmt->execute([$id]);
    $itemsList = $itemsStmt->fetchAll();
}
$suppliers = $pdo->query('SELECT id, supplier_id, name FROM suppliers ORDER BY name')->fetchAll();
$allItems = $pdo->query('SELECT id, item_code, item_name, unit, unit_price FROM items ORDER BY item_name')->fetchAll();
include '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Barang Masuk</h1>
    <div class="d-flex gap-2">
        <a href="incoming_items.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        <?php if ($transaction): ?>
        <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i> Cetak</button>
        <a href="incoming_items.php?delete=<?php echo (int)$transaction['id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus transaksi ini? Stok akan dikembalikan.')"><i class="fas fa-trash me-1"></i> Hapus</a>
        <?php endif; ?>
    </div>
    </div>
<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if ($transaction): ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Kode:</strong> <?php echo htmlspecialchars($transaction['transaction_code']); ?></p>
                    <p class="mb-1"><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($transaction['transaction_date'])); ?></p>
                    <p class="mb-1"><strong>Supplier:</strong> <?php echo htmlspecialchars($transaction['supplier_name']); ?></p>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($transaction['notes'])): ?>
                        <p class="mb-1"><strong>Catatan:</strong> <?php echo nl2br(htmlspecialchars($transaction['notes'])); ?></p>
                    <?php endif; ?>
                    <p class="mb-1"><strong>Dibuat Oleh:</strong> <?php echo htmlspecialchars($transaction['created_by_name']); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Items</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($itemsList)): ?>
                            <?php $sum = 0; foreach ($itemsList as $it): $sum += (float)$it['total_price']; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($it['item_name']); ?> <small class="text-muted">(<?php echo htmlspecialchars($it['item_code']); ?>)</small></td>
                                    <td><?php echo (int)$it['quantity']; ?></td>
                                    <td><?php echo htmlspecialchars($it['unit']); ?></td>
                                    <td><?php echo number_format((float)$it['unit_price'], 2); ?></td>
                                    <td><?php echo number_format((float)$it['total_price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">Tidak ada item</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>
                                <?php $sum = 0; foreach ($itemsList as $it) { $sum += (float)$it['total_price']; } echo number_format($sum, 2); ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Edit Transaksi</span>
            <button class="btn btn-primary" form="editForm"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
        </div>
        <div class="card-body">
            <form id="editForm" method="post">
                <input type="hidden" name="transaction_id" value="<?php echo (int)$transaction['id']; ?>">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Transaksi</label>
                        <input type="datetime-local" class="form-control" name="transaction_date" value="<?php echo date('Y-m-d\TH:i', strtotime($transaction['transaction_date'])); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="supplier_id" required>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?php echo (int)$s['id']; ?>" <?php echo ((int)$s['id'] === (int)$transaction['supplier_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['name'] . ' (' . $s['supplier_id'] . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 mt-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="notes" rows="2"><?php echo htmlspecialchars($transaction['notes'] ?? ''); ?></textarea>
                    </div>
                </div>
                <h5 class="mb-3">Daftar Barang</h5>
                <div id="edit-items-container">
                    <?php $idx = 0; foreach ($itemsList as $it): ?>
                        <div class="item-row row g-3 mb-3">
                            <div class="col-md-5">
                                <select class="form-select item-select" name="items[<?php echo $idx; ?>][item_id]" required>
                                    <option value="">Pilih Barang</option>
                                    <?php foreach ($allItems as $item): ?>
                                        <option value="<?php echo (int)$item['id']; ?>" data-unit="<?php echo htmlspecialchars($item['unit']); ?>" data-price="<?php echo $item['unit_price']; ?>" <?php echo ((int)$item['id'] === (int)$it['item_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($item['item_name'] . ' (' . $item['item_code'] . ')'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control quantity" name="items[<?php echo $idx; ?>][quantity]" min="0" value="<?php echo (int)$it['quantity']; ?>" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control unit" value="<?php echo htmlspecialchars($it['unit']); ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control price" name="items[<?php echo $idx; ?>][unit_price]" min="0" step="100" value="<?php echo (float)$it['unit_price']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    <?php $idx++; endforeach; ?>
                </div>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-edit-item"><i class="fas fa-plus"></i> Tambah Barang</button>
                </div>
                <div class="mt-4 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Total Item: <span id="edit-total-items">0</span></h6>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5>Total Nilai: <span id="edit-total-value">Rp 0</span></h5>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="update_incoming" value="1">
            </form>
        </div>
    </div>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>
<script>
let editIndex = <?php echo isset($idx) ? (int)$idx : 0; ?>;
document.getElementById('add-edit-item')?.addEventListener('click', function(){
  const c = document.getElementById('edit-items-container');
  const row = document.createElement('div');
  row.className = 'item-row row g-3 mb-3';
  const i = editIndex++;
  row.innerHTML = `
    <div class="col-md-5">
      <select class="form-select item-select" name="items[${i}][item_id]" required>
        <option value="">Pilih Barang</option>
        <?php foreach ($allItems as $item): ?>
        <option value="<?php echo (int)$item['id']; ?>" data-unit="<?php echo htmlspecialchars($item['unit']); ?>" data-price="<?php echo $item['unit_price']; ?>"><?php echo htmlspecialchars($item['item_name'] . ' (' . $item['item_code'] . ')'); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <input type="number" class="form-control quantity" name="items[${i}][quantity]" min="0" value="0" required>
    </div>
    <div class="col-md-2">
      <input type="text" class="form-control unit" value="" readonly>
    </div>
    <div class="col-md-2">
      <div class="input-group">
        <span class="input-group-text">Rp</span>
        <input type="number" class="form-control price" name="items[${i}][unit_price]" min="0" step="100" value="0" required>
      </div>
    </div>
    <div class="col-md-1">
      <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-times"></i></button>
    </div>`;
  c.appendChild(row);
});
document.addEventListener('change', function(e){
  if (e.target.classList.contains('item-select')){
    const opt = e.target.options[e.target.selectedIndex];
    const unit = opt.dataset.unit || '';
    const price = opt.dataset.price || '0';
    const row = e.target.closest('.item-row');
    row.querySelector('.unit').value = unit;
    const p = row.querySelector('.price');
    if (p && (!p.value || p.value === '0')) p.value = price;
    calcEditTotals();
  }
});
document.addEventListener('input', function(e){
  if (e.target.classList.contains('quantity') || e.target.classList.contains('price')) calcEditTotals();
});
document.addEventListener('click', function(e){
  const btn = e.target.closest('.remove-item');
  if (btn){ btn.closest('.item-row').remove(); calcEditTotals(); }
});
function calcEditTotals(){
  let tItems = 0, tVal = 0;
  document.querySelectorAll('#edit-items-container .item-row').forEach(function(row){
    const q = parseFloat(row.querySelector('.quantity')?.value || '0');
    const p = parseFloat(row.querySelector('.price')?.value || '0');
    tItems += q; tVal += q * p;
  });
  const fmt = new Intl.NumberFormat('id-ID');
  document.getElementById('edit-total-items').textContent = tItems;
  document.getElementById('edit-total-value').textContent = 'Rp ' + fmt.format(tVal);
}
document.addEventListener('DOMContentLoaded', calcEditTotals);
</script>
<style>
.item-row{transition:all .3s ease}
.item-row:hover{background-color:#f8f9fa}
</style>