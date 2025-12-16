<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db_connect.php';

// Pastikan user sudah login dan memiliki role production
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'production') {
    header('Location: login.php');
    exit();
}

// Set judul halaman
$page_title = 'Dashboard Produksi';

// Set base path
$base_path = __DIR__;

// Include header
include $base_path . '/includes/header.php';

$user_id = $_SESSION['user_id'];
$error = '';

// Get counts for dashboard
try {
    $total_requests = $pdo->prepare("SELECT COUNT(*) FROM outgoing_transactions WHERE requested_by = ?");
    $total_requests->execute([$user_id]);
    $total_requests = $total_requests->fetchColumn();

    $pending_requests = $pdo->prepare("
        SELECT COUNT(*) 
        FROM outgoing_transactions 
        WHERE requested_by = ? AND status = 'pending'");
    $pending_requests->execute([$user_id]);
    $pending_requests = $pending_requests->fetchColumn();

    $approved_requests = $pdo->prepare("
        SELECT COUNT(*) 
        FROM outgoing_transactions 
        WHERE requested_by = ? AND status = 'approved'");
    $approved_requests->execute([$user_id]);
    $approved_requests = $approved_requests->fetchColumn();

    $rejected_requests = $pdo->prepare("
        SELECT COUNT(*) 
        FROM outgoing_transactions 
        WHERE requested_by = ? AND status = 'rejected'");
    $rejected_requests->execute([$user_id]);
    $rejected_requests = $rejected_requests->fetchColumn();

    // Get recent requests
    $stmt = $pdo->prepare("
        SELECT ot.id, ot.request_code, ot.request_date, ot.status, ot.processed_at,
               (SELECT COUNT(*) FROM outgoing_transaction_items WHERE transaction_id = ot.id) as item_count
        FROM outgoing_transactions ot
        WHERE ot.requested_by = ?
        ORDER BY ot.request_date DESC
        LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_requests = $stmt->fetchAll();

    // Get available items
    $items = $pdo->query("SELECT * FROM items WHERE stock > 0 ORDER BY item_name LIMIT 5")->fetchAll();
    $total_items = $pdo->query("SELECT COUNT(*) FROM items WHERE stock > 0")->fetchColumn();

    // Recent incoming transactions (admin)
    $incomingStmt = $pdo->query("SELECT it.id, it.transaction_code, it.transaction_date, s.name AS supplier_name,
        (SELECT COUNT(*) FROM incoming_transaction_items WHERE transaction_id = it.id) AS item_count,
        (SELECT COALESCE(SUM(total_price),0) FROM incoming_transaction_items WHERE transaction_id = it.id) AS total_value
        FROM incoming_transactions it JOIN suppliers s ON s.id = it.supplier_id
        ORDER BY it.transaction_date DESC LIMIT 5");
    $recent_incoming = $incomingStmt->fetchAll();

    // Recent outgoing usage (approved items for this user)
    $hasApproved = false; $hasItemStatus = false; $hasRequested = false; $hasQuantity = false;
    try { $hasApproved  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'approved_quantity'")->fetch(); } catch (Exception $e) {}
    try { $hasItemStatus = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'status'")->fetch(); } catch (Exception $e) {}
    try { $hasRequested = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'requested_quantity'")->fetch(); } catch (Exception $e) {}
    try { $hasQuantity  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'quantity'")->fetch(); } catch (Exception $e) {}
    $requestedCol = $hasRequested ? 'requested_quantity' : ($hasQuantity ? 'quantity' : 'requested_quantity');
    $approvedExpr = $hasApproved ? 'COALESCE(oti.approved_quantity, 0)' : 'CASE WHEN ' . ($hasItemStatus ? "COALESCE(oti.status,'pending') = 'approved'" : "0=1") . ' THEN ' . $requestedCol . ' ELSE 0 END';
    $statusExpr   = $hasItemStatus ? "COALESCE(oti.status,'pending')" : "'pending'";
    $usageSQL = "SELECT i.item_name, i.unit, ot.request_code, ot.request_date, $approvedExpr AS approved_qty,
                 oti.unit_price, ( $approvedExpr * oti.unit_price ) AS total_value, $statusExpr AS status
                 FROM outgoing_transaction_items oti
                 JOIN outgoing_transactions ot ON ot.id = oti.transaction_id
                 JOIN items i ON i.id = oti.item_id
                 WHERE ot.requested_by = ?
                 ORDER BY ot.request_date DESC, ot.id DESC
                 LIMIT 5";
    $usageStmt = $pdo->prepare($usageSQL);
    $usageStmt->execute([$user_id]);
    $recent_usage = $usageStmt->fetchAll();

    $stockSQL = "SELECT i.id, i.item_name, i.unit,
                 SUM( $approvedExpr ) AS stock_qty,
                 SUM( $approvedExpr * oti.unit_price ) AS total_value
                 FROM outgoing_transaction_items oti
                 JOIN outgoing_transactions ot ON ot.id = oti.transaction_id
                 JOIN items i ON i.id = oti.item_id
                 WHERE ot.requested_by = ? AND (ot.status = 'approved' OR ot.status = 'partially_approved')
                 GROUP BY i.id, i.item_name, i.unit
                 ORDER BY i.item_name ASC";
    $stockStmt = $pdo->prepare($stockSQL);
    $stockStmt->execute([$user_id]);
    $production_stock = $stockStmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Gagal mengambil data: ' . $e->getMessage();
}
?>

<h1 class="h3 mb-4 text-gray-800">Production Dashboard</h1>

<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_requests; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_requests; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Approved Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $approved_requests; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Rejected Requests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $rejected_requests; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Incoming Items (Admin)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Transaksi</th>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th>Items</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_incoming)): ?>
                                <?php foreach ($recent_incoming as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['transaction_code']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($r['transaction_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($r['supplier_name']); ?></td>
                                        <td class="text-center"><?php echo (int)$r['item_count']; ?></td>
                                        <td>Rp <?php echo number_format((float)$r['total_value'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">Tidak ada transaksi masuk</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Outgoing Usage (Anda)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Request</th>
                                <th>Tanggal</th>
                                <th>Item</th>
                                <th>Approved Qty</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_usage)): ?>
                                <?php foreach ($recent_usage as $u): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['request_code']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($u['request_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($u['item_name']); ?> <small class="text-muted"><?php echo htmlspecialchars($u['unit']); ?></small></td>
                                        <td class="text-center"><?php echo (int)$u['approved_qty']; ?></td>
                                        <td>Rp <?php echo number_format((float)($u['total_value'] ?? 0), 0, ',', '.'); ?></td>
                                        <td>
                                            <?php $cls = ['pending'=>'warning','approved'=>'success','partially_approved'=>'info','rejected'=>'danger'][$u['status']] ?? 'secondary'; ?>
                                            <span class="badge bg-<?php echo $cls; ?>"><?php echo ucfirst(str_replace('_',' ', $u['status'])); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Tidak ada penggunaan barang</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Stok Barang Produksi</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Unit</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($production_stock)): ?>
                                <?php foreach ($production_stock as $ps): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ps['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($ps['unit']); ?></td>
                                        <td class="text-center"><?php echo (int)($ps['stock_qty'] ?? 0); ?></td>
                                        <td>Rp <?php echo number_format((float)($ps['total_value'] ?? 0), 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">Belum ada stok produksi (menunggu persetujuan admin)</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Requests -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Requests</h6>
                <a href="production/request_item.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> New Request
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Request #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_requests) > 0): ?>
                                <?php foreach ($recent_requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['request_code']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                        <td><?php echo $request['item_count']; ?> items</td>
                                        <td>
                                            <?php 
                                            $status_class = [
                                                'pending' => 'warning',
                                                'approved' => 'success',
                                                'partially_approved' => 'info',
                                                'rejected' => 'danger'
                                            ][$request['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="production/request_view.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="mb-0">No requests found</p>
                                        <a href="production/request_item.php" class="btn btn-primary mt-2">
                                            <i class="fas fa-plus"></i> Create Your First Request
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Items -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Available Items</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $item): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['item_name']); ?></h6>
                                        <small class="text-muted">Stok: <?php echo $item['stock'] . ' ' . $item['unit']; ?></small>
                                    </div>
                                    <span class="badge bg-<?php echo $item['stock'] > 10 ? 'success' : ($item['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                        <?php echo $item['stock'] > 0 ? 'Tersedia' : 'Habis'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($total_items > 5): ?>
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#allItemsModal">
                                Lihat Semua (<?php echo $total_items; ?> item)
                            </a>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="mb-0">Tidak ada item yang tersedia</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="production/request_item.php" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="text">New Item Request</span>
                    </a>
                    <a href="production/history.php" class="btn btn-info btn-icon-split">
                        <span class="icon text-white-50">
                            <i class="fas fa-history"></i>
                        </span>
                        <span class="text">View Request History</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All Items Modal -->
<div class="modal fade" id="allItemsModal" tabindex="-1" aria-labelledby="allItemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allItemsModalLabel">All Available Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Unit</th>
                                <th>Stock</th>
                                <th>Unit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $all_items = $pdo->query("SELECT * FROM items ORDER BY item_name ASC")->fetchAll();
                            foreach ($all_items as $item): 
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_code']); ?></td>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $item['stock'] > 10 ? 'success' : ($item['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                            <?php echo $item['stock']; ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include $base_path . '/includes/footer.php'; ?>

<!-- Page level plugins -->
<script>
// Initialize DataTable for items table
$(document).ready(function() {
    $('#itemsTable').DataTable({
        "pageLength": 5,
        "lengthMenu": [5, 10, 25, 50, 100],
        "order": [[1, 'asc']] // Sort by item name by default
    });
});
</script>
