<?php
/**
 * File: admin/view_po.php
 * Fungsi: Detail Purchase Order untuk Admin
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$user = getCurrentUser();
$pdo = getDBConnection();

$poId = $_GET['id'] ?? 0;

// Get PO detail
$stmt = $pdo->prepare("
    SELECT po.*, r.request_number, r.product_name, r.quantity, r.note,
           s.full_name as staff_name, s.email as staff_email, s.phone as staff_phone,
           c.full_name as customer_name, c.company_name, c.email as customer_email, c.phone as customer_phone
    FROM purchase_orders po
    JOIN requests r ON po.request_id = r.id
    LEFT JOIN users s ON po.staff_id = s.id
    JOIN users c ON r.customer_id = c.id
    WHERE po.id = ?
");
$stmt->execute([$poId]);
$po = $stmt->fetch();

if (!$po) {
    header('Location: purchase_orders.php');
    exit;
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail PO - RollMate Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full bg-white">

    <div class="flex h-screen overflow-hidden">
        
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Header -->
            <header class="flex-shrink-0 border-b border-slate-200 bg-white">
                <div class="px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-3xl font-bold tracking-tight text-slate-900">Detail Purchase Order</h2>
                            <code class="mt-2 inline-block text-lg font-mono text-slate-600"><?php echo htmlspecialchars($po['po_number']); ?></code>
                        </div>
                        <?php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-700 ring-yellow-600/20',
                            'process' => 'bg-purple-100 text-purple-700 ring-purple-600/20',
                            'done' => 'bg-green-100 text-green-700 ring-green-600/20'
                        ];
                        $statusLabels = [
                            'pending' => 'Pending',
                            'process' => 'Diproses',
                            'done' => 'Selesai'
                        ];
                        ?>
                        <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold ring-2 ring-inset <?php echo $statusColors[$po['status']]; ?>">
                            <?php echo $statusLabels[$po['status']]; ?>
                        </span>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto bg-slate-50 p-8">

                <!-- Flash Message -->
                <?php if ($flashMessage): ?>
                <div class="mb-6 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-50 border-green-100' : 'bg-blue-50 border-blue-100'; ?> border p-4">
                    <p class="text-sm font-medium <?php echo $flashMessage['type'] === 'success' ? 'text-green-800' : 'text-blue-800'; ?>">
                        <?php echo htmlspecialchars($flashMessage['message']); ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Back Button -->
                <div class="mb-6">
                    <a href="purchase_orders.php" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali ke Purchase Orders
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- PO Info -->
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-slate-200">
                            <h2 class="text-lg font-bold text-slate-900">Informasi Purchase Order</h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">PO Number</dt>
                                    <dd class="text-base font-mono font-semibold text-slate-900"><?php echo htmlspecialchars($po['po_number']); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Request Number</dt>
                                    <dd class="text-base font-mono font-semibold text-slate-900"><?php echo htmlspecialchars($po['request_number']); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Tanggal Dibuat</dt>
                                    <dd class="text-base text-slate-900"><?php echo date('d F Y, H:i', strtotime($po['created_at'])); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Total Amount</dt>
                                    <dd class="text-lg font-bold text-blue-600">Rp <?php echo number_format($po['total_amount'], 0, ',', '.'); ?></dd>
                                </div>
                                <?php if ($po['started_at']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Mulai Dikerjakan</dt>
                                    <dd class="text-base text-slate-900"><?php echo date('d F Y, H:i', strtotime($po['started_at'])); ?></dd>
                                </div>
                                <?php endif; ?>
                                <?php if ($po['completed_at']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Selesai</dt>
                                    <dd class="text-base text-green-600 font-semibold"><?php echo date('d F Y, H:i', strtotime($po['completed_at'])); ?></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <!-- Staff Info -->
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 bg-purple-50 border-b border-slate-200">
                            <h2 class="text-lg font-bold text-slate-900">Staff yang Ditugaskan</h2>
                        </div>
                        <div class="p-6">
                            <?php if ($po['staff_name']): ?>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Nama Staff</dt>
                                    <dd class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($po['staff_name']); ?></dd>
                                </div>
                                <?php if ($po['staff_email']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Email</dt>
                                    <dd class="text-base text-slate-900"><?php echo htmlspecialchars($po['staff_email']); ?></dd>
                                </div>
                                <?php endif; ?>
                                <?php if ($po['staff_phone']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Telepon</dt>
                                    <dd class="text-base text-slate-900"><?php echo htmlspecialchars($po['staff_phone']); ?></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                            <?php else: ?>
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <p class="text-sm text-slate-500">Belum ada staff yang ditugaskan</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                            <h2 class="text-lg font-bold text-slate-900">Informasi Pelanggan</h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Nama Pelanggan</dt>
                                    <dd class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($po['customer_name']); ?></dd>
                                </div>
                                <?php if ($po['company_name']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Perusahaan</dt>
                                    <dd class="text-base text-slate-900"><?php echo htmlspecialchars($po['company_name']); ?></dd>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Email</dt>
                                    <dd class="text-base text-slate-900"><?php echo htmlspecialchars($po['customer_email']); ?></dd>
                                </div>
                                <?php if ($po['customer_phone']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Telepon</dt>
                                    <dd class="text-base text-slate-900"><?php echo htmlspecialchars($po['customer_phone']); ?></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                            <h2 class="text-lg font-bold text-slate-900">Detail Produk</h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Nama Produk</dt>
                                    <dd class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($po['product_name']); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Jumlah</dt>
                                    <dd class="text-lg font-bold text-blue-600"><?php echo $po['quantity']; ?></dd>
                                </div>
                                <?php if ($po['note']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-2">Catatan dari Pelanggan</dt>
                                    <dd class="text-base text-slate-900 p-4 bg-blue-50 border border-blue-100 rounded-lg"><?php echo nl2br(htmlspecialchars($po['note'])); ?></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                </div>

                <!-- Notes (if exists) -->
                <?php if ($po['notes']): ?>
                <div class="mt-6 bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-100">
                        <h2 class="text-lg font-bold text-slate-900">Catatan</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-base text-slate-900 whitespace-pre-wrap"><?php echo htmlspecialchars($po['notes']); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Status Info -->
                <?php if ($po['status'] === 'done'): ?>
                <div class="mt-6 text-center p-6 bg-green-50 border border-green-200 rounded-xl">
                    <svg class="w-16 h-16 mx-auto mb-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-lg font-bold text-green-900 mb-2">Purchase Order Selesai</p>
                    <p class="text-sm text-green-700">PO ini sudah diselesaikan oleh staff</p>
                </div>
                <?php endif; ?>

            </main>

        </div>

    </div>

</body>
</html>
