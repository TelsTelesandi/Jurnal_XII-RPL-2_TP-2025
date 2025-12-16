<?php
require_once '../includes/db_connect.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') { header('Location: ../login.php'); exit(); }

$error = '';
$section = $_GET['section'] ?? 'incoming';
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$export = $_GET['export'] ?? '';

try {
    $hasRequested = false; $hasApproved = false; $hasQuantity = false;
    try { $hasRequested = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'requested_quantity'")->fetch(); } catch (Exception $e) {}
    try { $hasApproved  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'approved_quantity'")->fetch(); } catch (Exception $e) {}
    try { $hasQuantity  = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'quantity'")->fetch(); } catch (Exception $e) {}
    $requestedCol = $hasRequested ? 'requested_quantity' : ($hasQuantity ? 'quantity' : 'requested_quantity');

    if ($export === 'csv') {
        if ($section === 'incoming') {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($search)) { $where .= " AND (it.transaction_code LIKE ? OR s.name LIKE ?)"; $like = "%$search%"; $params[] = $like; $params[] = $like; }
            if (!empty($date_from)) { $where .= " AND DATE(it.transaction_date) >= ?"; $params[] = $date_from; }
            if (!empty($date_to)) { $where .= " AND DATE(it.transaction_date) <= ?"; $params[] = $date_to; }
            $sql = "SELECT it.transaction_code, it.transaction_date, s.name AS supplier_name,
                           (SELECT COUNT(*) FROM incoming_transaction_items WHERE transaction_id = it.id) AS item_count,
                           (SELECT COALESCE(SUM(total_price),0) FROM incoming_transaction_items WHERE transaction_id = it.id) AS total_value
                    FROM incoming_transactions it
                    JOIN suppliers s ON s.id = it.supplier_id
                    $where
                    ORDER BY it.transaction_date DESC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="incoming_report_'.date('Ymd').'.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Transaction','Date','Supplier','Items','Total Value']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['transaction_code'],
                    $r['transaction_date'],
                    $r['supplier_name'],
                    (int)$r['item_count'],
                    number_format((float)$r['total_value'], 2)
                ]);
            }
            fclose($out); exit;
        } else {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($search)) { $where .= " AND (ot.request_code LIKE ? OR u.name LIKE ?)"; $like = "%$search%"; $params[] = $like; $params[] = $like; }
            if (!empty($date_from)) { $where .= " AND DATE(ot.request_date) >= ?"; $params[] = $date_from; }
            if (!empty($date_to)) { $where .= " AND DATE(ot.request_date) <= ?"; $params[] = $date_to; }
            $sql = "SELECT ot.id, ot.request_code, u.name AS requester, ot.request_date, ot.status,
                           COUNT(oti.id) AS item_count,
                           SUM(oti.$requestedCol) AS requested_qty" . ($hasApproved ? ", SUM(oti.approved_quantity) AS approved_qty" : ", NULL AS approved_qty") . ",
                           SUM(oti.total_price) AS total_value
                    FROM outgoing_transactions ot
                    JOIN users u ON u.id = ot.requested_by
                    JOIN outgoing_transaction_items oti ON oti.transaction_id = ot.id
                    $where
                    GROUP BY ot.id, ot.request_code, u.name, ot.request_date, ot.status
                    ORDER BY ot.request_date DESC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="outgoing_report_'.date('Ymd').'.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Request','Requester','Date','Status','Items','Requested Qty','Approved Qty','Total Value']);
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
            fclose($out); exit;
        }
    } elseif ($export === 'pdf') {
        $companyTitle = 'PT Teknopro Sukses Persada';
        $companySubtitle = 'Jl. Masjid Al-Mujahidin Blok C3/150 Setiadarma, Tambun Selatan Bekasi 17510, Indonesia';
        $logoPath = '../OIP-removebg-preview.png';
        if ($section === 'incoming') {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($search)) { $where .= " AND (it.transaction_code LIKE ? OR s.name LIKE ?)"; $like = "%$search%"; $params[] = $like; $params[] = $like; }
            if (!empty($date_from)) { $where .= " AND DATE(it.transaction_date) >= ?"; $params[] = $date_from; }
            if (!empty($date_to)) { $where .= " AND DATE(it.transaction_date) <= ?"; $params[] = $date_to; }
            $sql = "SELECT it.transaction_code, it.transaction_date, s.name AS supplier_name,
                           (SELECT COUNT(*) FROM incoming_transaction_items WHERE transaction_id = it.id) AS item_count,
                           (SELECT COALESCE(SUM(total_price),0) FROM incoming_transaction_items WHERE transaction_id = it.id) AS total_value
                    FROM incoming_transactions it
                    JOIN suppliers s ON s.id = it.supplier_id
                    $where
                    ORDER BY it.transaction_date DESC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
            $title = 'Laporan Barang Masuk';
        } else {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($search)) { $where .= " AND (ot.request_code LIKE ? OR u.name LIKE ?)"; $like = "%$search%"; $params[] = $like; $params[] = $like; }
            if (!empty($date_from)) { $where .= " AND DATE(ot.request_date) >= ?"; $params[] = $date_from; }
            if (!empty($date_to)) { $where .= " AND DATE(ot.request_date) <= ?"; $params[] = $date_to; }
            $sql = "SELECT ot.id, ot.request_code, u.name AS requester, ot.request_date, ot.status,
                           COUNT(oti.id) AS item_count,
                           SUM(oti.$requestedCol) AS requested_qty" . ($hasApproved ? ", SUM(oti.approved_quantity) AS approved_qty" : ", NULL AS approved_qty") . ",
                           SUM(oti.total_price) AS total_value,
                           COALESCE(ot.production_notes, '') AS production_notes
                    FROM outgoing_transactions ot
                    JOIN users u ON u.id = ot.requested_by
                    JOIN outgoing_transaction_items oti ON oti.transaction_id = ot.id
                    $where
                    GROUP BY ot.id, ot.request_code, u.name, ot.request_date, ot.status, production_notes
                    ORDER BY ot.request_date DESC";
            $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
            $title = 'Laporan Permintaan Barang (Produksi)';
        }
        ?><!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title><?php echo htmlspecialchars($title); ?></title>
            <style>
                @page { size: A4; margin: 20mm; }
                body { font-family: Arial, Helvetica, sans-serif; color: #000; }
                .letterhead { display: grid; grid-template-columns: 100px 1fr; align-items: center; gap: 16px; margin-bottom: 12px; }
                .letterhead img { max-height: 90px; }
                .lh-text { text-align: center; }
                .lh-text h1 { margin: 0; font-size: 20px; letter-spacing: .5px; }
                .lh-text h2 { margin: 2px 0 0; font-size: 16px; font-weight: normal; }
                .lh-sub { margin-top: 4px; font-size: 12px; color: #333; }
                .divider { height: 2px; background: #000; margin: 8px 0 16px; }
                .meta { font-size: 12px; margin-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; font-size: 12px; }
                th, td { border: 1px solid #555; padding: 6px 8px; }
                th { background: #f0f0f0; }
                .footer { margin-top: 28px; }
                .signature-block { text-align: center; }
                .signature-block .sig-title { font-size: 13px; margin-top: 6px; margin-bottom: 8px; }
                .signature-block .sig-image img { height: 80px; }
                .signature-block .sig-line { border-bottom: 1px solid #000; width: 60%; margin: 40px auto 8px; }
                .signature-block .sig-name { font-size: 13px; font-weight: 600; }
                .small { font-size: 11px; color: #444; }
                .signature-block .date { margin-bottom: 18px; display: block; }
                .signature-grid .signature-block:first-child .sig-title { margin-top: 26px; }
                .signature-block .hidden-date { visibility: hidden; }
                @media print { .no-print { display: none !important; } }
            </style>
        </head>
        <body>
            <div class="letterhead">
                <div><img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Logo"></div>
                <div class="lh-text">
                    <h1><?php echo htmlspecialchars($companyTitle); ?></h1>
                    <h2><?php echo htmlspecialchars($companySubtitle); ?></h2>
                    <div class="lh-sub">Laporan periode <?php echo htmlspecialchars($date_from ?: '-'); ?> s/d <?php echo htmlspecialchars($date_to ?: '-'); ?></div>
                </div>
            </div>
            <div class="divider"></div>
            <div class="meta">
                <strong><?php echo htmlspecialchars($title); ?></strong>
                <?php if (!empty($search)): ?>
                    <span class="small"> • Pencarian: "<?php echo htmlspecialchars($search); ?>"</span>
                <?php endif; ?>
            </div>
            <?php if ($section === 'incoming'): ?>
            <table>
                <thead>
                    <tr>
                        <th>Transaksi</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Items</th>
                        <th>Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($rows)): foreach ($rows as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['transaction_code']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r['transaction_date'])); ?></td>
                        <td><?php echo htmlspecialchars($r['supplier_name']); ?></td>
                        <td style="text-align:center;"><?php echo (int)$r['item_count']; ?></td>
                        <td>Rp <?php echo number_format((float)$r['total_value'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" style="text-align:center;">Tidak ada data</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Peminta</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Requested Qty</th>
                        <th>Approved Qty</th>
                        <th>Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($rows)): foreach ($rows as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['request_code']); ?></td>
                        <td><?php echo htmlspecialchars($r['requester']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r['request_date'])); ?></td>
                        <td><?php echo ucfirst(str_replace('_',' ', $r['status'])); ?></td>
                        <td style="text-align:center;"><?php echo (int)$r['item_count']; ?></td>
                        <td style="text-align:center;"><?php echo (int)($r['requested_qty'] ?? 0); ?></td>
                        <td style="text-align:center;"><?php echo (int)($r['approved_qty'] ?? 0); ?></td>
                        <td>Rp <?php echo number_format((float)($r['total_value'] ?? 0), 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="8" style="text-align:center;">Tidak ada data</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            <?php if (!empty($rows) && count($rows) === 1): ?>
                <?php
                try {
                    $hasItemStatus = false; 
                    try { $hasItemStatus = (bool)$pdo->query("SHOW COLUMNS FROM outgoing_transaction_items LIKE 'status'")->fetch(); } catch (Exception $e) {}
                    $approvedExpr = $hasApproved ? 'COALESCE(approved_quantity,0)' : '0';
                    $itemsSQL = "SELECT i.item_name, i.unit, oti.$requestedCol AS requested_quantity, $approvedExpr AS approved_quantity, oti.unit_price, oti.total_price, "
                             . ($hasItemStatus ? "COALESCE(oti.status,'pending')" : "'pending'") . " AS status, COALESCE(oti.notes,'') AS notes
                               FROM outgoing_transaction_items oti
                               JOIN items i ON i.id = oti.item_id
                               WHERE oti.transaction_id = ?";
                    $itemsStmt = $pdo->prepare($itemsSQL);
                    $itemsStmt->execute([$rows[0]['id']]);
                    $itemsRows = $itemsStmt->fetchAll();
                } catch (Exception $e) { $itemsRows = []; }
                $reqSum = 0; $appSum = 0; $valSum = 0.0;
                foreach ($itemsRows as $ir) { $reqSum += (int)($ir['requested_quantity'] ?? 0); $appSum += (int)($ir['approved_quantity'] ?? 0); $valSum += (float)($ir['total_price'] ?? 0); }
                ?>
                <br>
                <div class="meta">
                    <div><strong>Request #:</strong> <?php echo htmlspecialchars($rows[0]['request_code']); ?></div>
                    <div><strong>Requested By:</strong> <?php echo htmlspecialchars($rows[0]['requester']); ?></div>
                    <div><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($rows[0]['request_date'])); ?></div>
                    <?php 
                        $clsText = [
                            'pending' => 'Menunggu',
                            'approved' => 'Disetujui',
                            'partially_approved' => 'Disetujui Sebagian',
                            'rejected' => 'Ditolak'
                        ][$rows[0]['status']] ?? $rows[0]['status'];
                    ?>
                    <div><strong>Status:</strong> <?php echo $clsText; ?></div>
                    <?php if (!empty($rows[0]['production_notes'])): ?>
                        <div><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($rows[0]['production_notes'])); ?></div>
                    <?php endif; ?>
                </div>
                <table>
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
                        <?php if (!empty($itemsRows)): foreach ($itemsRows as $ir): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ir['item_name']); ?> <small class="text-muted"><?php echo htmlspecialchars($ir['unit']); ?></small></td>
                                <td style="text-align:center;"><?php echo (int)($ir['requested_quantity'] ?? 0); ?></td>
                                <td style="text-align:center;"><?php echo (int)($ir['approved_quantity'] ?? 0); ?></td>
                                <td>Rp <?php echo number_format((float)($ir['unit_price'] ?? 0), 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format((float)($ir['total_price'] ?? 0), 0, ',', '.'); ?></td>
                                <td><?php echo ucfirst(str_replace('_',' ', $ir['status'])); ?></td>
                                <td><?php echo !empty($ir['notes']) ? nl2br(htmlspecialchars($ir['notes'])) : '-'; ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="7" style="text-align:center;">Tidak ada item</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th><?php echo (int)$reqSum; ?></th>
                            <th><?php echo (int)$appSum; ?></th>
                            <th></th>
                            <th>Rp <?php echo number_format($valSum, 0, ',', '.'); ?></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
            <?php endif; ?>
            <div class="footer">
                <?php if ($section === 'outgoing'): ?>
                <div class="signature-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div class="signature-block">
                        <div class="small date hidden-date">&nbsp;</div>
                        <div class="sig-title">Mengetahui</div>
                        <div class="sig-line"></div>
                        <div class="small">(Produksi)</div>
                    </div>
                    <div class="signature-block">
                        <?php $m = (int)date('n'); $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$m-1]; $tanggalCetak = 'Bekasi, ' . $bulan . ' ' . date('Y'); ?>
                        <div class="small date"><?php echo htmlspecialchars($tanggalCetak); ?></div>
                        <div class="sig-title">Bagian Produksi</div>
                        <div class="sig-line"></div>
                        <div class="small">(<?php echo !empty($rows) && count($rows)===1 ? htmlspecialchars($rows[0]['requester']) : ''; ?>)</div>
                    </div>
                </div>
                <?php else: ?>
                <div class="signature-block">
                    <div class="sig-title">Administrator</div>
                    <?php if (file_exists(__DIR__ . '/../signature.png')): ?>
                        <div class="sig-image"><img src="../signature.png" alt="Tanda Tangan"></div>
                    <?php else: ?>
                        <div class="sig-line"></div>
                    <?php endif; ?>
                    <div class="small">(Administrator)</div>
                    <div class="sig-name"><?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <div class="no-print" style="margin-top:16px; text-align:right;">
                <button onclick="window.print()">Cetak</button>
            </div>
            <script>if (location.search.indexOf('export=pdf')!==-1){ setTimeout(function(){ window.print(); }, 300); }</script>
        </body>
        </html><?php
        exit;
    }
} catch (PDOException $e) { $error = 'Gagal memuat data: ' . $e->getMessage(); }

include '../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reports</h1>
    <a href="../dashboard_admin.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <select name="section" class="form-select">
                    <option value="incoming" <?php echo $section==='incoming'?'selected':''; ?>>Incoming Items</option>
                    <option value="outgoing" <?php echo $section==='outgoing'?'selected':''; ?>>Outgoing Requests</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Cari kode/transaksi/peminta" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="fas fa-filter me-1"></i> Filter</button>
                <?php $qsCsv = http_build_query(['section'=>$section,'search'=>$search,'date_from'=>$date_from,'date_to'=>$date_to,'export'=>'csv']); ?>
                <?php $qsPdf = http_build_query(['section'=>$section,'search'=>$search,'date_from'=>$date_from,'date_to'=>$date_to,'export'=>'pdf']); ?>
                <a href="report.php?<?php echo $qsCsv; ?>" class="btn btn-success"><i class="fas fa-file-export me-1"></i> Export CSV</a>
                <a href="report.php?<?php echo $qsPdf; ?>" class="btn btn-outline-danger"><i class="fas fa-file-pdf me-1"></i> Print PDF</a>
            </div>
        </form>
    </div>
    </div>

<?php
if ($section === 'incoming') {
    $where = "WHERE 1=1"; $params = [];
    if (!empty($search)) { $where .= " AND (it.transaction_code LIKE ? OR s.name LIKE ?)"; $like = "%$search%"; $params[] = $like; $params[] = $like; }
    if (!empty($date_from)) { $where .= " AND DATE(it.transaction_date) >= ?"; $params[] = $date_from; }
    if (!empty($date_to)) { $where .= " AND DATE(it.transaction_date) <= ?"; $params[] = $date_to; }
    $sql = "SELECT it.id, it.transaction_code, it.transaction_date, s.name AS supplier_name,
                   (SELECT COUNT(*) FROM incoming_transaction_items WHERE transaction_id = it.id) AS item_count,
                   (SELECT COALESCE(SUM(total_price),0) FROM incoming_transaction_items WHERE transaction_id = it.id) AS total_value
            FROM incoming_transactions it
            JOIN suppliers s ON s.id = it.supplier_id
            $where
            ORDER BY it.transaction_date DESC";
    $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Transaction</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Items</th>
                        <th>Total Value</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($rows)): foreach ($rows as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['transaction_code']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r['transaction_date'])); ?></td>
                        <td><?php echo htmlspecialchars($r['supplier_name']); ?></td>
                        <td class="text-center"><?php echo (int)$r['item_count']; ?></td>
                        <td>Rp <?php echo number_format((float)$r['total_value'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="incoming_items_view.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center py-4">Tidak ada data</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php } else {
    $where = "WHERE 1=1"; $params = [];
    if (!empty($search)) { $where .= " AND (ot.request_code LIKE ? OR u.name LIKE ?)"; $like = "%$search%"; $params[] = $like; $params[] = $like; }
    if (!empty($date_from)) { $where .= " AND DATE(ot.request_date) >= ?"; $params[] = $date_from; }
    if (!empty($date_to)) { $where .= " AND DATE(ot.request_date) <= ?"; $params[] = $date_to; }
    $sql = "SELECT ot.id, ot.request_code, ot.request_date, ot.status, u.name AS requester,
                   COUNT(oti.id) AS item_count,
                   SUM(oti.$requestedCol) AS requested_qty" . ($hasApproved ? ", SUM(oti.approved_quantity) AS approved_qty" : ", NULL AS approved_qty") . ",
                   SUM(oti.total_price) AS total_value
            FROM outgoing_transactions ot
            JOIN users u ON u.id = ot.requested_by
            JOIN outgoing_transaction_items oti ON oti.transaction_id = ot.id
            $where
            GROUP BY ot.id, ot.request_code, u.name, ot.request_date, ot.status
            ORDER BY ot.request_date DESC";
    $stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Requester</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Requested Qty</th>
                        <th>Approved Qty</th>
                        <th>Total Value</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($rows)): foreach ($rows as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['request_code']); ?></td>
                        <td><?php echo htmlspecialchars($r['requester']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($r['request_date'])); ?></td>
                        <td>
                            <?php $cls = ['pending'=>'warning','approved'=>'success','partially_approved'=>'info','rejected'=>'danger'][$r['status']] ?? 'secondary'; ?>
                            <span class="badge bg-<?php echo $cls; ?>"><?php echo ucfirst(str_replace('_',' ',$r['status'])); ?></span>
                        </td>
                        <td class="text-center"><?php echo (int)$r['item_count']; ?></td>
                        <td class="text-center"><?php echo (int)($r['requested_qty'] ?? 0); ?></td>
                        <td class="text-center"><?php echo (int)($r['approved_qty'] ?? 0); ?></td>
                        <td>Rp <?php echo number_format((float)($r['total_value'] ?? 0), 0, ',', '.'); ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-primary" href="outgoing_items_view.php?id=<?php echo (int)$r['id']; ?>"><i class="fas fa-eye"></i> View</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="9" class="text-center py-4">Tidak ada data</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php } ?>

<?php include '../includes/footer.php'; ?>
