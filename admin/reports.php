<?php
/**
 * File: admin/reports.php
 * Fungsi: Laporan dan Statistik dengan Export PDF
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Date Filter
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today
$reportType = $_GET['report_type'] ?? 'overview';

// Get Statistics for date range
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

// Get detailed requests for the period
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

// Get top customers
$stmt = $pdo->prepare("
    SELECT u.company_name, u.full_name, COUNT(*) as order_count, 
           SUM(r.quantity * p.price) as total_spent
    FROM requests r
    JOIN users u ON r.customer_id = u.id
    LEFT JOIN products p ON r.product_id = p.id
    WHERE DATE(r.created_at) BETWEEN ? AND ?
    GROUP BY r.customer_id
    ORDER BY order_count DESC
    LIMIT 5
");
$stmt->execute([$startDate, $endDate]);
$topCustomers = $stmt->fetchAll();

// Get PO statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_po,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_po,
        SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as process_po,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_po,
        SUM(total_amount) as total_po_value
    FROM purchase_orders
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$poStats = $stmt->fetch();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - RollMate Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="h-full bg-white">

    <div class="flex h-screen overflow-hidden">
        
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Header -->
            <header class="flex-shrink-0 border-b border-slate-200 bg-white no-print">
                <div class="px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-3xl font-bold tracking-tight text-slate-900">Laporan & Statistik</h2>
                            <p class="mt-1 text-sm text-slate-600">Analisis data dan performa bisnis RollMate</p>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white font-semibold rounded-lg transition duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print Halaman Ini
                            </button>
                            <a href="generate_pdf.php?type=overview&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto bg-slate-50 p-8">

                <!-- Date Range Filter -->
                <div class="mb-6 bg-white rounded-xl border border-slate-200 p-6 no-print">
                    <form method="GET" class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai</label>
                            <input 
                                type="date" 
                                name="start_date" 
                                value="<?php echo htmlspecialchars($startDate); ?>"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Akhir</label>
                            <input 
                                type="date" 
                                name="end_date" 
                                value="<?php echo htmlspecialchars($endDate); ?>"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                        </div>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300">
                            Terapkan Filter
                        </button>
                        <a href="reports.php" class="px-6 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg transition duration-300">
                            Reset
                        </a>
                    </form>
                </div>

                <!-- Period Info -->
                <div class="mb-6 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-6 text-white">
                    <h3 class="text-lg font-bold mb-2">Periode Laporan</h3>
                    <p class="text-blue-100"><?php echo date('d F Y', strtotime($startDate)); ?> - <?php echo date('d F Y', strtotime($endDate)); ?></p>
                </div>

                <!-- Request Statistics -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-slate-900 mb-4">Statistik Request</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                        <div class="bg-white rounded-xl border border-slate-200 p-6">
                            <p class="text-sm font-medium text-slate-600">Total Request</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900"><?php echo $stats['total_requests']; ?></p>
                        </div>
                        <div class="bg-white rounded-xl border border-slate-200 p-6">
                            <p class="text-sm font-medium text-slate-600">Selesai</p>
                            <p class="mt-2 text-3xl font-bold text-green-600"><?php echo $stats['completed_requests']; ?></p>
                        </div>
                        <div class="bg-white rounded-xl border border-slate-200 p-6">
                            <p class="text-sm font-medium text-slate-600">Pending</p>
                            <p class="mt-2 text-3xl font-bold text-yellow-600"><?php echo $stats['pending_requests']; ?></p>
                        </div>
                        <div class="bg-white rounded-xl border border-slate-200 p-6">
                            <p class="text-sm font-medium text-slate-600">Approved</p>
                            <p class="mt-2 text-3xl font-bold text-blue-600"><?php echo $stats['approved_requests']; ?></p>
                        </div>
                        <div class="bg-white rounded-xl border border-slate-200 p-6">
                            <p class="text-sm font-medium text-slate-600">Process</p>
                            <p class="mt-2 text-3xl font-bold text-purple-600"><?php echo $stats['process_requests']; ?></p>
                        </div>
                        <div class="bg-white rounded-xl border border-slate-200 p-6">
                            <p class="text-sm font-medium text-slate-600">Ditolak</p>
                            <p class="mt-2 text-3xl font-bold text-red-600"><?php echo $stats['rejected_requests']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Revenue & PO Stats -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Revenue -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Statistik Pendapatan</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-slate-600">Total Potensi Revenue</p>
                                    <p class="mt-1 text-2xl font-bold text-blue-600">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></p>
                                </div>
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-slate-600">Revenue Terealisasi</p>
                                    <p class="mt-1 text-2xl font-bold text-green-600">Rp <?php echo number_format($completedRevenue, 0, ',', '.'); ?></p>
                                </div>
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PO Stats -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Statistik Purchase Order</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-600">Total PO</span>
                                <span class="text-2xl font-bold text-slate-900"><?php echo $poStats['total_po']; ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-600">PO Selesai</span>
                                <span class="text-xl font-bold text-green-600"><?php echo $poStats['completed_po']; ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-600">PO Diproses</span>
                                <span class="text-xl font-bold text-purple-600"><?php echo $poStats['process_po']; ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-slate-600">PO Pending</span>
                                <span class="text-xl font-bold text-yellow-600"><?php echo $poStats['pending_po']; ?></span>
                            </div>
                            <div class="pt-4 border-t border-slate-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-slate-600">Total Nilai PO</span>
                                    <span class="text-xl font-bold text-indigo-600">Rp <?php echo number_format($poStats['total_po_value'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products & Customers -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Top Products -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Top 5 Produk Terlaris</h3>
                        <div class="space-y-3">
                            <?php if (count($topProducts) > 0): ?>
                                <?php foreach ($topProducts as $index => $product): ?>
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                            <?php echo $index + 1; ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($product['product_name']); ?></p>
                                            <p class="text-xs text-slate-500"><?php echo $product['order_count']; ?> orders • <?php echo $product['total_quantity']; ?> qty</p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-blue-600">Rp <?php echo number_format($product['revenue'], 0, ',', '.'); ?></p>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-slate-500 py-8">Tidak ada data produk</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Top Customers -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Top 5 Pelanggan</h3>
                        <div class="space-y-3">
                            <?php if (count($topCustomers) > 0): ?>
                                <?php foreach ($topCustomers as $index => $customer): ?>
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                            <?php echo $index + 1; ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($customer['company_name'] ?: $customer['full_name']); ?></p>
                                            <p class="text-xs text-slate-500"><?php echo $customer['order_count']; ?> orders</p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-green-600">Rp <?php echo number_format($customer['total_spent'], 0, ',', '.'); ?></p>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-slate-500 py-8">Tidak ada data pelanggan</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Request Status Chart -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Grafik Status Request</h3>
                    <div class="max-w-md mx-auto">
                        <canvas id="requestChart"></canvas>
                    </div>
                </div>

                <!-- Detailed Request List -->
                <div class="bg-white rounded-xl border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900">Detail Request (<?php echo count($requests); ?>)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Request No</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Pelanggan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php if (count($requests) > 0): ?>
                                    <?php foreach ($requests as $req): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <code class="text-xs font-mono text-slate-900"><?php echo htmlspecialchars($req['request_number']); ?></code>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            <?php echo date('d/m/Y', strtotime($req['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($req['company_name'] ?: $req['customer_name']); ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-900">
                                            <?php echo htmlspecialchars($req['product_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                            <?php echo $req['quantity']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                            Rp <?php echo number_format($req['total_amount'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'approved' => 'bg-green-100 text-green-700',
                                                'rejected' => 'bg-red-100 text-red-700',
                                                'paid' => 'bg-blue-100 text-blue-700',
                                                'process' => 'bg-purple-100 text-purple-700',
                                                'done' => 'bg-green-100 text-green-700'
                                            ];
                                            ?>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold <?php echo $statusColors[$req['status']]; ?>">
                                                <?php echo ucfirst($req['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                            Tidak ada data request untuk periode ini
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>

        </div>

    </div>

    <script>
        // Request Status Chart
        const ctx = document.getElementById('requestChart').getContext('2d');
        const requestChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Selesai', 'Pending', 'Approved', 'Process', 'Ditolak'],
                datasets: [{
                    data: [
                        <?php echo $stats['completed_requests']; ?>,
                        <?php echo $stats['pending_requests']; ?>,
                        <?php echo $stats['approved_requests']; ?>,
                        <?php echo $stats['process_requests']; ?>,
                        <?php echo $stats['rejected_requests']; ?>
                    ],
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b',
                        '#3b82f6',
                        '#8b5cf6',
                        '#ef4444'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12,
                                family: 'Inter'
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>
