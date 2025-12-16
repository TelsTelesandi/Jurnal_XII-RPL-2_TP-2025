<?php
require_once '../includes/db_connect.php';

// Hanya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';

// Ambil daftar permintaan keluar
try {
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';

    $query = "
        SELECT ot.id, ot.request_code, ot.request_date, ot.status, u.name AS requester,
               (SELECT COUNT(*) FROM outgoing_transaction_items oti WHERE oti.transaction_id = ot.id) AS item_count
        FROM outgoing_transactions ot
        JOIN users u ON u.id = ot.requested_by
        WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (ot.request_code LIKE ? OR u.name LIKE ?)";
        $like = "%$search%";
        $params[] = $like; $params[] = $like;
    }
    if (!empty($status)) {
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

    $query .= " ORDER BY ot.request_date DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();

    $hasRequested = false; $hasApproved = false; $hasQuantity = false;
    try { $hasRequested = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'requested_quantity'")->fetch(); } catch (Exception $e) {}
    try { $hasApproved  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'approved_quantity'")->fetch(); } catch (Exception $e) {}
    try { $hasQuantity  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'quantity'")->fetch(); } catch (Exception $e) {}
    $requestedCol = $hasRequested ? 'requested_quantity' : ($hasQuantity ? 'quantity' : 'requested_quantity');

    if ((isset($_GET['export']) && $_GET['export'] === 'csv')) {
        $where = "WHERE 1=1";
        $csvParams = [];
        if (!empty($search)) { $where .= " AND (ot.request_code LIKE ? OR u.name LIKE ?)"; $like = "%$search%"; $csvParams[] = $like; $csvParams[] = $like; }
        if (!empty($status)) { $where .= " AND ot.status = ?"; $csvParams[] = $status; }
        if (!empty($date_from)) { $where .= " AND DATE(ot.request_date) >= ?"; $csvParams[] = $date_from; }
        if (!empty($date_to)) { $where .= " AND DATE(ot.request_date) <= ?"; $csvParams[] = $date_to; }
        $reportSQL = "SELECT ot.request_code, u.name AS requester, ot.request_date, ot.status, COUNT(oti.id) AS item_count, SUM(oti.".$requestedCol.") AS requested_qty" . ($hasApproved ? ", SUM(oti.approved_quantity) AS approved_qty" : ", NULL AS approved_qty") . ", SUM(oti.total_price) AS total_value FROM outgoing_transactions ot JOIN users u ON u.id = ot.requested_by JOIN outgoing_transaction_items oti ON oti.transaction_id = ot.id " . $where . " GROUP BY ot.id, ot.request_code, u.name, ot.request_date, ot.status ORDER BY ot.request_date DESC";
        $csvStmt = $pdo->prepare($reportSQL);
        $csvStmt->execute($csvParams);
        $rows = $csvStmt->fetchAll();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="outgoing_requests_report_'.date('Ymd').'.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Request #','Requester','Date','Status','Items','Requested Qty','Approved Qty','Total Value']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['request_code'],
                $r['requester'],
                $r['request_date'],
                $r['status'],
                (int)$r['item_count'],
                (int)($r['requested_qty'] ?? 0),
                (int)($r['approved_qty'] ?? 0),
                number_format((float)($r['total_value'] ?? 0), 2)
            ]);
        }
        fclose($out);
        exit;
    }
} catch (PDOException $e) {
    $error = 'Gagal memuat data: ' . $e->getMessage();
}

include '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Outgoing Items</h1>
    <div class="d-flex gap-2">
        <a href="../dashboard_admin.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
        <?php 
        $qs = http_build_query([
            'export' => 'csv',
            'search' => $search,
            'status' => $status,
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);
        ?>
        <a href="outgoing_items.php?<?php echo $qs; ?>" class="btn btn-success"><i class="fas fa-file-export me-1"></i> Export CSV</a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau peminta" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <button class="btn btn-outline-secondary"><i class="fas fa-search"></i> Cari</button>
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <?php foreach (['pending','approved','partially_approved','rejected'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo ($status ?? '') === $s ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to ?? ''); ?>">
            </div>
            <?php if (!empty(($search ?? '')) || !empty(($status ?? ''))): ?>
            <div class="col-md-3">
                <a href="outgoing_items.php" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
    </div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Request #</th>
                        <th>Requested By</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['request_code']); ?></td>
                                <td><?php echo htmlspecialchars($r['requester']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($r['request_date'])); ?></td>
                                <td><?php echo (int)$r['item_count']; ?></td>
                                <td>
                                    <?php $cls = ['pending'=>'warning','approved'=>'success','partially_approved'=>'info','rejected'=>'danger'][$r['status']] ?? 'secondary'; ?>
                                    <span class="badge bg-<?php echo $cls; ?>"><?php echo ucfirst(str_replace('_',' ',$r['status'])); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-sm btn-outline-primary" href="outgoing_items_view.php?id=<?php echo $r['id']; ?>">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <form method="post" action="outgoing_request_action.php" onsubmit="return handleListActionSubmit(this)">
                                            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                            <input type="hidden" name="admin_notes" value="">
                                            <div class="btn-group" role="group">
                                                <button name="action" value="approve" type="submit" class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></button>
                                                <button name="action" value="reject" type="submit" class="btn btn-sm btn-warning" title="Reject"><i class="fas fa-times"></i></button>
                                                <button name="action" value="delete" type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Hapus request ini?');"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                                <div>Tidak ada permintaan.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>

<?php include '../includes/footer.php'; ?>

<script>
function handleListActionSubmit(form){
  var actionBtn = null;
  var buttons = form.querySelectorAll('button[name="action"]');
  buttons.forEach(function(btn){ if (btn === document.activeElement) actionBtn = btn; });
  var action = actionBtn ? actionBtn.value : form.querySelector('button[name="action"]').value;
  if (action === 'reject'){
    var note = window.prompt('Masukkan catatan penolakan (wajib):');
    if (!note || !note.trim()){
      alert('Catatan wajib diisi untuk penolakan.');
      return false;
    }
    var hidden = form.querySelector('input[name="admin_notes"]');
    if (hidden) hidden.value = note.trim();
  }
  return confirm('Yakin melakukan aksi ini?');
}
</script>