<?php
require_once '../includes/db_connect.php';

// Hanya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    // Ambil detail transaksi
    $stmt = $pdo->prepare("SELECT ot.*, u.name AS requester FROM outgoing_transactions ot JOIN users u ON u.id = ot.requested_by WHERE ot.id = ?");
    $stmt->execute([$id]);
    $request = $stmt->fetch();

    if ($request) {
        // Deteksi kolom yang tersedia pada schema untuk kompatibilitas
        $hasRequested = false; $hasQuantity = false; $hasApproved = false; $hasStatus = false;
        try {
            $hasRequested = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'requested_quantity'")->fetch();
            $hasQuantity  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'quantity'")->fetch();
            $hasApproved  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'approved_quantity'")->fetch();
            $hasStatus    = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'status'")->fetch();
        } catch (Exception $ex) {
            // Abaikan, gunakan fallback aman
        }

        $requestedCol = $hasRequested ? 'requested_quantity' : ($hasQuantity ? 'quantity' : 'requested_quantity');
        $approvedExpr = $hasApproved ? 'COALESCE(oti.approved_quantity, 0) AS approved_quantity' : '0 AS approved_quantity';
        $statusExpr   = $hasStatus ? "COALESCE(oti.status, 'pending') AS status" : "'pending' AS status";

        $sql = "SELECT 
                oti.id AS detail_id,
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
        $error = 'Transaksi tidak ditemukan';
    }
} catch (PDOException $e) {
    $error = 'Gagal memuat data: ' . $e->getMessage();
}

include '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Request Detail</h1>
    <div class="d-flex gap-2">
        <a href="outgoing_items.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
        <?php if ($request): ?>
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i> Cetak</button>
        <form method="post" action="outgoing_request_action.php" onsubmit="return handleDetailActionSubmit(this)" class="d-flex align-items-center gap-2">
            <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
            <div class="me-2" style="min-width:300px;">
                <input type="text" name="admin_notes" class="form-control" placeholder="Catatan admin (opsional)">
            </div>
            <div class="btn-group" role="group">
                <button name="action" value="approve" type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Approve</button>
                <button name="action" value="reject" type="submit" class="btn btn-warning"><i class="fas fa-times me-1"></i> Reject</button>
                <button name="action" value="delete" type="submit" class="btn btn-danger" onclick="return confirm('Hapus request ini?');"><i class="fas fa-trash me-1"></i> Delete</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<style>
@page { size: A4; margin: 20mm; }
@media print {
  .navbar, .btn, .alert, .border-bottom { display: none !important; }
  input, select, textarea { display: none !important; }
  .select-col { display: none !important; }
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
  .print-hide { display: none !important; }
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
                <?php 
                    $cls = ['pending'=>'warning','approved'=>'success','partially_approved'=>'info','rejected'=>'danger'][$request['status']] ?? 'secondary';
                    $text = [
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'partially_approved' => 'Disetujui Sebagian',
                        'rejected' => 'Ditolak'
                    ][$request['status']] ?? $request['status'];
                ?>
                <span class="badge bg-<?php echo $cls; ?>"><?php echo $text; ?></span>
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
                        <p class="mb-1 print-hide"><strong>Admin Notes:</strong> <?php echo nl2br(htmlspecialchars($request['admin_notes'])); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($request['production_notes'])): ?>
                        <p class="mb-1"><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($request['production_notes'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="outgoing_request_action.php" onsubmit="return validateRejectItems(this)">
        <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="action" value="reject_items">
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
                                <?php if ($request['status'] === 'pending'): ?>
                                <th class="select-col">Select</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $it): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($it['item_name']); ?> <small class="text-muted">(<?php echo htmlspecialchars($it['unit']); ?>)</small></td>
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
                                        <?php if ($request['status'] === 'pending'): ?>
                                        <td class="text-center select-col">
                                            <input type="checkbox" name="reject_item_ids[]" value="<?php echo (int)$it['detail_id']; ?>">
                                        </td>
                                        <?php endif; ?>
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
                                <?php if ($request['status'] === 'pending'): ?>
                                <th class="select-col"></th>
                                <?php endif; ?>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php if ($request['status'] === 'pending'): ?>
                <div class="mt-3">
                    <div class="mb-2">
                        <input type="text" name="admin_notes" class="form-control" placeholder="Alasan penolakan (wajib saat menolak item)">
                    </div>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-times me-1"></i> Reject Selected Items</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
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
            <div class="small">(<?php echo htmlspecialchars($request['requester'] ?? ''); ?>)</div>
        </div>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

<script>
function handleDetailActionSubmit(form){
  var active = document.activeElement;
  var action = active && active.name === 'action' ? active.value : '';
  if (action === 'reject'){
    var notes = form.querySelector('input[name="admin_notes"]');
    if (!notes || !notes.value.trim()){ alert('Catatan wajib diisi untuk penolakan request.'); notes && notes.focus(); return false; }
  }
  return confirm('Yakin melakukan aksi ini?');
}
function validateRejectItems(form){
  if (!form || form.action.indexOf('outgoing_request_action.php') === -1) return true;
  var actionInput = form.querySelector('input[name="action"]');
  if (!actionInput || actionInput.value !== 'reject_items') return true;
  var checks = form.querySelectorAll('input[name="reject_item_ids[]"]:checked');
  if (checks.length === 0){ alert('Pilih minimal satu item untuk ditolak.'); return false; }
  var notes = form.querySelector('input[name="admin_notes"]');
  if (!notes || !notes.value.trim()){ alert('Catatan wajib diisi untuk penolakan item.'); notes.focus(); return false; }
  return true;
}
</script>
