<?php
/**
 * File: admin/generate_pdf.php
 * Fungsi: Generate PDF Report using HTML print-friendly layout
 * No external library needed - uses browser's print to PDF
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get parameters
$reportType = $_GET['type'] ?? 'overview';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Get Statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_requests,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
        SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as process_requests
    FROM requests
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$stats = $stmt->fetch();

// Get detailed requests
$stmt = $pdo->prepare("
    SELECT r.*, u.full_name as customer_name, u.company_name, p.price,
           (r.quantity * p.price) as total_amount
    FROM requests r
    JOIN users u ON r.customer_id = u.id
    LEFT JOIN products p ON r.product_id = p.id
    WHERE DATE(r.created_at) BETWEEN ? AND ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$startDate, $endDate]);
$requests = $stmt->fetchAll();

// Calculate revenue
$totalRevenue = 0;
$completedRevenue = 0;
foreach ($requests as $req) {
    $totalRevenue += $req['total_amount'];
    if ($req['status'] === 'done') {
        $completedRevenue += $req['total_amount'];
    }
}

// Get PO statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_po,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_po,
        SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as process_po,
        SUM(total_amount) as total_po_value
    FROM purchase_orders
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$poStats = $stmt->fetch();

// Get top products
$stmt = $pdo->prepare("
    SELECT r.product_name, COUNT(*) as order_count, SUM(r.quantity) as total_quantity,
           SUM(r.quantity * p.price) as revenue
    FROM requests r
    LEFT JOIN products p ON r.product_id = p.id
    WHERE DATE(r.created_at) BETWEEN ? AND ?
    GROUP BY r.product_name
    ORDER BY order_count DESC
    LIMIT 5
");
$stmt->execute([$startDate, $endDate]);
$topProducts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan RollMate - <?php echo date('d-m-Y', strtotime($startDate)); ?> s/d <?php echo date('d-m-Y', strtotime($endDate)); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #1e40af;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header h2 {
            color: #475569;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .header p {
            color: #64748b;
            font-size: 11px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #f1f5f9;
            padding: 8px 12px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 12px;
            border-left: 4px solid #3b82f6;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .stat-box {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: center;
            border-radius: 4px;
        }
        .stat-label {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
        }
        .stat-value.blue { color: #3b82f6; }
        .stat-value.green { color: #10b981; }
        .stat-value.yellow { color: #f59e0b; }
        .stat-value.red { color: #ef4444; }
        .stat-value.purple { color: #8b5cf6; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th {
            background-color: #f1f5f9;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            border: 1px solid #cbd5e1;
        }
        table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .info-box {
            border: 1px solid #e2e8f0;
            padding: 12px;
            border-radius: 4px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
        }
        .info-value {
            color: #0f172a;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #94a3b8;
            font-size: 10px;
        }
        @media print {
            body { padding: 15px; }
            .no-print { display: none; }
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .print-btn:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Print / Save PDF</button>

    <div class="header">
        <h1>ROLLMATE MARKETPLACE</h1>
        <h2>Laporan Statistik & Data</h2>
        <p>Periode: <?php echo date('d F Y', strtotime($startDate)); ?> - <?php echo date('d F Y', strtotime($endDate)); ?></p>
        <p>Digenerate pada: <?php echo date('d F Y H:i:s'); ?> oleh <?php echo htmlspecialchars($user['full_name']); ?></p>
    </div>

    <!-- STATISTIK REQUEST -->
    <div class="section">
        <div class="section-title">STATISTIK REQUEST</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-label">Total Request</div>
                <div class="stat-value blue"><?php echo $stats['total_requests']; ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Selesai</div>
                <div class="stat-value green"><?php echo $stats['completed_requests']; ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Pending</div>
                <div class="stat-value yellow"><?php echo $stats['pending_requests']; ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Approved</div>
                <div class="stat-value blue"><?php echo $stats['approved_requests']; ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Process</div>
                <div class="stat-value purple"><?php echo $stats['process_requests']; ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Ditolak</div>
                <div class="stat-value red"><?php echo $stats['rejected_requests']; ?></div>
            </div>
        </div>
    </div>

    <!-- STATISTIK PENDAPATAN & PO -->
    <div class="section">
        <div class="section-title">STATISTIK PENDAPATAN & PURCHASE ORDER</div>
        <div class="info-grid">
            <div class="info-box">
                <h4 style="margin-bottom: 10px; color: #1e40af;">Pendapatan</h4>
                <div class="info-row">
                    <span class="info-label">Total Potensi Revenue:</span>
                    <span class="info-value" style="color: #3b82f6;">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Revenue Terealisasi:</span>
                    <span class="info-value" style="color: #10b981;">Rp <?php echo number_format($completedRevenue, 0, ',', '.'); ?></span>
                </div>
            </div>
            <div class="info-box">
                <h4 style="margin-bottom: 10px; color: #1e40af;">Purchase Order</h4>
                <div class="info-row">
                    <span class="info-label">Total PO:</span>
                    <span class="info-value"><?php echo $poStats['total_po']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">PO Selesai:</span>
                    <span class="info-value" style="color: #10b981;"><?php echo $poStats['completed_po']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">PO Diproses:</span>
                    <span class="info-value" style="color: #8b5cf6;"><?php echo $poStats['process_po']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Nilai PO:</span>
                    <span class="info-value" style="color: #6366f1;">Rp <?php echo number_format($poStats['total_po_value'], 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP PRODUCTS -->
    <?php if (count($topProducts) > 0): ?>
    <div class="section">
        <div class="section-title">TOP 5 PRODUK TERLARIS</div>
        <table>
            <thead>
                <tr>
                    <th width="8%">No</th>
                    <th width="42%">Produk</th>
                    <th width="15%">Orders</th>
                    <th width="15%">Total Qty</th>
                    <th width="20%">Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topProducts as $index => $product): ?>
                <tr>
                    <td style="text-align: center;"><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                    <td style="text-align: center;"><?php echo $product['order_count']; ?></td>
                    <td style="text-align: center;"><?php echo $product['total_quantity']; ?></td>
                    <td style="text-align: right; font-weight: bold;">Rp <?php echo number_format($product['revenue'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- DETAIL REQUEST -->
    <div class="section" style="page-break-before: always;">
        <div class="section-title">DETAIL REQUEST (<?php echo count($requests); ?> data)</div>
        <?php if (count($requests) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th width="12%">Request No</th>
                    <th width="10%">Tanggal</th>
                    <th width="18%">Pelanggan</th>
                    <th width="22%">Produk</th>
                    <th width="8%">Qty</th>
                    <th width="18%">Total</th>
                    <th width="12%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                <tr>
                    <td style="font-family: monospace; font-size: 9px;"><?php echo htmlspecialchars($req['request_number']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($req['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars(substr($req['company_name'] ?: $req['customer_name'], 0, 25)); ?></td>
                    <td><?php echo htmlspecialchars(substr($req['product_name'], 0, 30)); ?></td>
                    <td style="text-align: center; font-weight: bold;"><?php echo $req['quantity']; ?></td>
                    <td style="text-align: right; font-weight: bold;">Rp <?php echo number_format($req['total_amount'], 0, ',', '.'); ?></td>
                    <td style="text-align: center;"><?php echo ucfirst($req['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align: center; padding: 20px; color: #64748b;">Tidak ada data request untuk periode ini</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>RollMate Marketplace System - Generated by Admin: <?php echo htmlspecialchars($user['full_name']); ?></p>
        <p>Dokumen ini digenerate secara otomatis pada <?php echo date('d F Y H:i:s'); ?></p>
    </div>

    <script>
        // Auto print dialog on load (optional - comment out if not needed)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
