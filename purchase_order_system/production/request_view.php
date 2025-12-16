<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db_connect.php';

// Hanya untuk role production
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'production') {
    header('Location: ../login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$request = null;
$items = [];
$requestedTotal = 0;
$approvedTotal = 0;
$amountTotal = 0.0;

try {
    // Ambil detail transaksi milik user saat ini
    $stmt = $pdo->prepare("SELECT ot.*, u.name AS requester FROM outgoing_transactions ot JOIN users u ON u.id = ot.requested_by WHERE ot.id = ? AND ot.requested_by = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $request = $stmt->fetch();

    if ($request) {
        // Deteksi kolom agar kompatibel dengan dua skema (quantity vs requested_quantity)
        $hasRequested = false; $hasQuantity = false; $hasApproved = false; $hasStatus = false;
        try {
            $hasRequested = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'requested_quantity'")->fetch();
            $hasQuantity  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'quantity'")->fetch();
            $hasApproved  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'approved_quantity'")->fetch();
            $hasStatus    = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'status'")->fetch();
        } catch (Exception $ex) {}

        $requestedCol = $hasRequested ? 'requested_quantity' : ($hasQuantity ? 'quantity' : 'requested_quantity');
        $approvedExpr = $hasApproved ? 'COALESCE(oti.approved_quantity, 0) AS approved_quantity' : '0 AS approved_quantity';
        $statusExpr   = $hasStatus ? "COALESCE(oti.status, 'pending') AS status" : "'pending' AS status";

        $sql = "SELECT 
                i.item_name, i.unit,
                oti.$requestedCol AS requested_quantity,
                $approvedExpr,
                oti.unit_price, oti.total_price,
                $statusExpr,
                COALESCE(oti.notes, '') AS item_notes
            FROM outgoing_transaction_items oti
            JOIN items i ON i.id = oti.item_id
            WHERE oti.transaction_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();
        foreach ($items as $it) {
            $requestedTotal += (int)($it['requested_quantity'] ?? 0);
            $approvedTotal += (int)($it['approved_quantity'] ?? 0);
            $amountTotal += (float)($it['total_price'] ?? 0);
        }
    } else {
        $error = 'Transaksi tidak ditemukan atau bukan milik Anda.';
    }
} catch (PDOException $e) {
    $error = 'Gagal memuat data: ' . $e->getMessage();
}

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detail Permintaan</h1>
    <div class="d-flex gap-2">
        <a href="../dashboard_production.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        <?php if ($request): ?>
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i> Cetak</button>
        <?php endif; ?>
    </div>
</div>

<style>
@page { size: A4; margin: 20mm; }
@media print {
  .navbar, .btn, .alert, .border-bottom { display: none !important; }
  body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .card { border: none; box-shadow: none; }
  .print-header { display: block; }
  .letterhead { display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 16px; margin-bottom: 12px; }
  .divider { height: 2px; background: #000; margin: 8px 0 16px; }
  .print-signatures { margin-top: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
  .print-signatures .sign { text-align: center; }
  .print-signatures .sign .label { margin-top: 8px; margin-bottom: 60px; }
  .print-signatures .sign:first-child .label { margin-top: 26px; }
  .print-signatures .line { border-bottom: 1px solid #000; width: 60%; margin: 0 auto 8px; }
  .print-signatures .date { margin-bottom: 18px; display: block; }
  .print-signatures .hidden-date { visibility: hidden; }
}
@media screen {
  .print-header { display: none; }
  .letterhead, .divider, .print-signatures { display: none; }
}
.report-title { font-size: 1.25rem; font-weight: 600; }
.report-meta { font-size: .95rem; }
.lh-text { text-align: center; }
.lh-text h1 { margin: 0; font-size: 20px; letter-spacing: .5px; }
.lh-text h2 { margin: 2px 0 0; font-size: 16px; font-weight: 400; }
.lh-sub { margin-top: 4px; font-size: 12px; color: #333; }
.letterhead img { max-height: 90px; }
</style>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($request): ?>
    <div class="letterhead">
        <div><img src="../OIP-removebg-preview.png" alt="Logo"></div>
        <div class="lh-text">
            <h1>PT Teknopro Sukses Persada</h1>
            <h2>Purchase Order System</h2>
            <div class="lh-sub">Laporan Permintaan Barang (Produksi)</div>
        </div>
    </div>
    <div class="divider"></div>
    <div class="print-header mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="report-title">Laporan Permintaan Barang (Produksi)</div>
                <div class="report-meta">Kode: <?php echo htmlspecialchars($request['request_code']); ?></div>
                <div class="report-meta">Tanggal: <?php echo date('d/m/Y H:i', strtotime($request['request_date'])); ?></div>
                <div class="report-meta">Peminta: <?php echo htmlspecialchars($request['requester']); ?></div>
            </div>
            <div class="text-end">
                <?php $cls = ['pending'=>'warning','approved'=>'success','partially_approved'=>'info','rejected'=>'danger'][$request['status']] ?? 'secondary'; ?>
                <span class="badge bg-<?php echo $cls; ?>"><?php echo ucfirst(str_replace('_',' ',$request['status'])); ?></span>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Request #:</strong> <?php echo htmlspecialchars($request['request_code']); ?></p>
                    <p class="mb-1"><strong>Requested By:</strong> <?php echo htmlspecialchars($request['requester']); ?></p>
                    <p class="mb-1"><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($request['request_date'])); ?></p>
                </div>
                <div class="col-md-6">
                    <?php 
                        $cls = ['pending'=>'warning','approved'=>'success','partially_approved'=>'info','rejected'=>'danger'][$request['status']] ?? 'secondary';
                        $text = [
                            'pending' => 'Menunggu',
                            'approved' => 'Disetujui',
                            'partially_approved' => 'Disetujui Sebagian',
                            'rejected' => 'Ditolak'
                        ][$request['status']] ?? $request['status'];
                    ?>
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?php echo $cls; ?>"><?php echo $text; ?></span></p>
                    <?php if (!empty($request['admin_notes'])): ?>
                        <p class="mb-1"><strong>Admin Notes:</strong> <?php echo nl2br(htmlspecialchars($request['admin_notes'])); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($request['production_notes'])): ?>
                        <p class="mb-1"><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($request['production_notes'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Items</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Requested Qty</th>
                            <th>Approved Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($it['item_name']); ?> <small class="text-muted"><?php echo htmlspecialchars($it['unit']); ?></small></td>
                                    <td><?php echo (int)$it['requested_quantity']; ?></td>
                                    <td><?php echo (int)($it['approved_quantity'] ?? 0); ?></td>
                                    <td><?php echo number_format((float)$it['unit_price'], 2); ?></td>
                                    <td><?php echo number_format((float)$it['total_price'], 2); ?></td>
                                    <td>
                                        <?php 
                                            $cls = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$it['status']] ?? 'secondary';
                                            $text = [
                                                'pending' => 'Menunggu',
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak'
                                            ][$it['status']] ?? $it['status'];
                                        ?>
                                        <span class="badge bg-<?php echo $cls; ?>"><?php echo $text; ?></span>
                                    </td>
                                    <td><?php echo !empty($it['item_notes']) ? nl2br(htmlspecialchars($it['item_notes'])) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">Tidak ada item</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th><?php echo number_format($requestedTotal); ?></th>
                            <th><?php echo number_format($approvedTotal); ?></th>
                            <th></th>
                            <th><?php echo number_format($amountTotal, 2); ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="print-signatures">
        <div class="sign">
            <div class="small date hidden-date">&nbsp;</div>
            <div class="label">Mengetahui</div>
            <div class="line"></div>
            <div class="small">(Produksi)</div>
        </div>
        <div class="sign">
            <?php $m = (int)date('n'); $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$m-1]; $tanggalCetak = 'Bekasi, ' . $bulan . ' ' . date('Y'); ?>
            <div class="small date"><?php echo htmlspecialchars($tanggalCetak); ?></div>
            <div class="label">Bagian Produksi</div>
            <div class="line"></div>
            <div class="small">(<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>)</div>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
