<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db_connect.php';
if (!defined('BASE_URL')) {
    $p = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $h = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $s = str_replace('\\','/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $parts = explode('/', trim($s,'/'));
    $root = isset($parts[0]) ? '/' . $parts[0] . '/' : '/';
    define('BASE_URL', $p . '://' . $h . rtrim($root,'/') . '/');
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'production') {
    header('Location: ' . BASE_URL . 'purchase_order_system/login.php');
    exit();
}
$page_title = 'New Item Request';
$user_id = (int)$_SESSION['user_id'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $notes = trim($_POST['notes'] ?? '');
        $items = $_POST['items'] ?? [];
        if (empty($items) || !is_array($items)) { throw new Exception('Tidak ada item yang diminta'); }
        $pdo->beginTransaction();
        $code = 'REQ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        $pdo->prepare("INSERT INTO outgoing_transactions (request_code, requested_by, request_date, status, production_notes, created_at, updated_at) VALUES (?, ?, NOW(), 'pending', ?, NOW(), NOW())")
            ->execute([$code, $user_id, $notes]);
        $txId = (int)$pdo->lastInsertId();
        if ($txId <= 0) { throw new Exception('Gagal membuat transaksi'); }
        $hasRq = false; $hasAp = false; $hasSt = false; $hasQty = false;
        try { $hasRq = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'requested_quantity'")->fetch(); } catch (Exception $e) {}
        try { $hasAp = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'approved_quantity'")->fetch(); } catch (Exception $e) {}
        try { $hasSt = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'status'")->fetch(); } catch (Exception $e) {}
        try { $hasQty = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'quantity'")->fetch(); } catch (Exception $e) {}
        foreach ($items as $row) {
            $itemId = (int)($row['item_id'] ?? 0);
            $qty = (int)($row['quantity'] ?? 0);
            if ($itemId <= 0 || $qty <= 0) { continue; }
            $info = $pdo->prepare('SELECT unit_price FROM items WHERE id = ?');
            $info->execute([$itemId]);
            $it = $info->fetch();
            if (!$it) { throw new Exception('Item tidak ditemukan'); }
            $unit = (float)$it['unit_price'];
            $total = $unit * $qty;
            if ($hasRq) {
                $pdo->prepare("INSERT INTO outgoing_transaction_items (transaction_id, item_id, requested_quantity, approved_quantity, unit_price, total_price, status, notes) VALUES (?, ?, ?, NULL, ?, ?, 'pending', NULL)")
                    ->execute([$txId, $itemId, $qty, $unit, $total]);
            } else {
                $pdo->prepare("INSERT INTO outgoing_transaction_items (transaction_id, item_id, quantity, unit_price, total_price, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())")
                    ->execute([$txId, $itemId, $qty, $unit, $total]);
            }
        }
        $pdo->commit();
        $_SESSION['success_message'] = 'Permintaan berhasil dikirim: ' . $code;
        header('Location: ' . BASE_URL . 'purchase_order_system/production/history.php?success=1');
        exit();
    } catch (Exception $ex) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        $error = $ex->getMessage();
    }
}
$itemsList = [];
try { $itemsList = $pdo->query('SELECT id, item_name, unit, stock FROM items ORDER BY item_name')->fetchAll(); } catch (Exception $e) { $error = $e->getMessage(); }
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h1 class="h3 mb-0">New Request</h1>
        <a href="<?php echo BASE_URL; ?>purchase_order_system/dashboard_production.php" class="btn btn-outline-secondary">Back</a>
    </div>
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-plus-circle me-2"></i>Create Request</span>
            <button type="submit" form="requestForm" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Submit</button>
        </div>
        <div class="card-body">
            <form id="requestForm" method="POST" action="<?php echo BASE_URL; ?>purchase_order_system/production/request_item.php">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Add notes for admin"></textarea>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50%">Item</th>
                                <th style="width:20%">Qty</th>
                                <th style="width:20%">Stock</th>
                                <th style="width:10%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <tr class="item-row">
                                <td>
                                    <select name="items[0][item_id]" class="form-select item-select" required>
                                        <option value="">Select Item</option>
                                        <?php foreach ($itemsList as $it): ?>
                                            <option value="<?php echo (int)$it['id']; ?>" data-stock="<?php echo (int)$it['stock']; ?>" data-unit="<?php echo htmlspecialchars($it['unit']); ?>">
                                                <?php echo htmlspecialchars($it['item_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" name="items[0][quantity]" class="form-control quantity" min="1" required>
                                        <span class="input-group-text unit">-</span>
                                    </div>
                                </td>
                                <td><span class="stock-available">0</span></td>
                                <td><button type="button" class="btn btn-outline-danger btn-sm btn-remove" style="display:none"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <button type="button" id="addRow" class="btn btn-secondary"><i class="fas fa-plus"></i> Add Row</button>
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  function updateRow(row){
    const sel = row.querySelector('.item-select');
    const unit = row.querySelector('.unit');
    const stock = row.querySelector('.stock-available');
    if (sel && sel.selectedIndex > 0){
      const opt = sel.options[sel.selectedIndex];
      unit.textContent = opt.dataset.unit || '-';
      stock.textContent = opt.dataset.stock || '0';
    } else {
      unit.textContent = '-';
      stock.textContent = '0';
    }
  }
  updateRow(document.querySelector('.item-row'));
  document.addEventListener('change', function(e){
    if (e.target.classList.contains('item-select')) updateRow(e.target.closest('.item-row'));
  });
  document.getElementById('addRow').addEventListener('click', function(){
    const body = document.getElementById('itemsBody');
    const count = body.querySelectorAll('.item-row').length;
    const tpl = body.querySelector('.item-row');
    const row = tpl.cloneNode(true);
    row.querySelector('.item-select').selectedIndex = 0;
    row.querySelector('.quantity').value = '';
    row.querySelector('.unit').textContent = '-';
    row.querySelector('.stock-available').textContent = '0';
    row.querySelectorAll('[name]').forEach(function(el){
      const name = el.getAttribute('name');
      el.setAttribute('name', name.replace(/\[\d+\]/, '['+count+']'));
    });
    row.querySelector('.btn-remove').style.display = 'inline-block';
    body.appendChild(row);
  });
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-remove');
    if (!btn) return;
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) btn.closest('.item-row').remove();
  });
  document.getElementById('requestForm').addEventListener('submit', function(e){
    let valid = true;
    document.querySelectorAll('.item-row').forEach(function(row, idx){
      const sel = row.querySelector('.item-select');
      const qty = row.querySelector('.quantity');
      if (!sel.value){ alert('Pilih barang untuk item #'+(idx+1)); sel.focus(); valid = false; }
      if (!qty.value || parseInt(qty.value) < 1){ alert('Jumlah tidak valid untuk item #'+(idx+1)); qty.focus(); valid = false; }
    });
    if (!valid){ e.preventDefault(); }
  });
});
</script>