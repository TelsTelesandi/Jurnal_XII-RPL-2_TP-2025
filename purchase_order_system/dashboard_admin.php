<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan user sudah login dan memiliki role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Include database connection after session check
require_once __DIR__ . '/includes/db_connect.php';

// Pastikan koneksi database berhasil
if (!isset($pdo)) {
    die('Tidak dapat terhubung ke database. Silakan coba lagi nanti.');
}

// Set judul halaman
$page_title = 'Dashboard Admin';

// Set base path
$base_path = __DIR__;

// Include header
require_once __DIR__ . '/includes/header.php';

// Get counts for dashboard
try {
    $item_count = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
    $supplier_count = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
    $incoming_count = $pdo->query("SELECT COUNT(*) FROM incoming_transactions WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $pending_requests = $pdo->query("SELECT COUNT(*) FROM outgoing_transactions WHERE status = 'pending'")->fetchColumn();

    // Get recent requests
    $stmt = $pdo->query("
        SELECT ot.id, ot.request_code, u.name as requester, ot.request_date, ot.status 
        FROM outgoing_transactions ot 
        JOIN users u ON ot.requested_by = u.id 
        ORDER BY ot.request_date DESC 
        LIMIT 5
    ");
    $recent_requests = $stmt->fetchAll();

    // Get low stock items
    $low_stock_items = $pdo->query("SELECT * FROM items WHERE stock < 10 ORDER BY stock ASC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    $error = 'Gagal mengambil data: ' . $e->getMessage();
}
?>

<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Items</h6>
                        <h2 class="mb-0"><?php echo number_format($item_count); ?></h2>
                    </div>
                    <div class="icon-shape bg-white text-primary rounded-circle p-3">
                        <i class="fas fa-box fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="admin/manage_items.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Suppliers</h6>
                        <h2 class="mb-0"><?php echo number_format($supplier_count); ?></h2>
                    </div>
                    <div class="icon-shape bg-white text-success rounded-circle p-3">
                        <i class="fas fa-truck fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="admin/manage_suppliers.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Today's Outgoing</h6>
                        <h2 class="mb-0"><?php echo number_format($incoming_count); ?></h2>
                    </div>
                    <div class="icon-shape bg-white text-warning rounded-circle p-3">
                        <i class="fas fa-arrow-down fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="admin/incoming_items.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Pending Requests</h6>
                        <h2 class="mb-0"><?php echo number_format($pending_requests); ?></h2>
                    </div>
                    <div class="icon-shape bg-white text-danger rounded-circle p-3">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="admin/outgoing_items.php">View Details</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Requests -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Recent Requests</h6>
                <a href="admin/outgoing_items.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request #</th>
                                <th>Requested By</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_requests) > 0): ?>
                                <?php foreach ($recent_requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['request_code']); ?></td>
                                        <td><?php echo htmlspecialchars($request['requester']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
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
                                            <div class="d-flex gap-2">
                                                <a href="admin/outgoing_items_view.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <form method="post" action="admin/outgoing_request_action.php" onsubmit="return confirm('Yakin melakukan aksi ini?');">
                                                    <input type="hidden" name="id" value="<?php echo (int)$request['id']; ?>">
                                                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                                    <div class="btn-group" role="group">
                                                        <button name="action" value="approve" type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                                        <button name="action" value="reject" type="submit" class="btn btn-sm btn-warning"><i class="fas fa-times"></i></button>
                                                        <button name="action" value="delete" type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus request ini?');"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">No recent requests found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Items -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-danger">Low Stock Items</h6>
                <a href="#" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (count($low_stock_items) > 0): ?>
                        <?php foreach ($low_stock_items as $item): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['item_name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($item['item_code']); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="mb-1">
                                            <span class="badge bg-<?php echo $item['stock'] < 5 ? 'danger' : 'warning'; ?>">
                                                <?php echo number_format($item['stock']); ?> in stock
                                            </span>
                                        </div>
                                        <a href="admin/manage_items.php?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="mb-0">All items are well stocked!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
