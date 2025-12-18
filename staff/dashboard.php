<?php
/**
 * File: staff/dashboard.php
 * Fungsi: Dashboard Pekerjaan untuk Staff
 * Style: Card-based layout TailwindCSS
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireStaff();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_po,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as process,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done
    FROM purchase_orders 
    WHERE staff_id = ?
");
$stmt->execute([$user['id']]);
$stats = $stmt->fetch();

// Get assigned PO
$stmt = $pdo->prepare("
    SELECT po.*, r.request_number, r.product_name, r.quantity, r.note,
           u.full_name as customer_name, u.company_name
    FROM purchase_orders po
    JOIN requests r ON po.request_id = r.id
    JOIN users u ON r.customer_id = u.id
    WHERE po.staff_id = ?
    ORDER BY 
        CASE po.status 
            WHEN 'pending' THEN 1
            WHEN 'process' THEN 2
            WHEN 'done' THEN 3
        END,
        po.created_at DESC
");
$stmt->execute([$user['id']]);
$purchaseOrders = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Staff - RollMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white">

    <!-- Navbar -->
    <nav class="border-b border-slate-200 bg-white/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-8">
                    <img src="../gambar/logo pt.png" alt="Logo PT" class="h-10 w-auto">
                </div>
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($user['full_name']); ?></p>
                        <p class="text-xs text-slate-500">Staff Produksi</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-violet-400 rounded-full flex items-center justify-center text-white font-bold">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                    <a href="../logout.php" class="text-sm font-medium text-slate-600 hover:text-red-600 transition duration-300">
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent mb-2">
                Dashboard Pekerjaan
            </h1>
            <p class="text-lg text-slate-600">Kelola purchase order yang ditugaskan kepada Anda</p>
        </div>

        <!-- Flash Message -->
        <?php if ($flashMessage): ?>
        <div class="mb-6 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-50 border-green-100' : 'bg-blue-50 border-blue-100'; ?> border p-4">
            <p class="text-sm font-medium <?php echo $flashMessage['type'] === 'success' ? 'text-green-800' : 'text-blue-800'; ?>">
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total PO</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900"><?php echo $stats['total_po']; ?></p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Pending</p>
                        <p class="mt-2 text-3xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Process</p>
                        <p class="mt-2 text-3xl font-bold text-purple-600"><?php echo $stats['process']; ?></p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Done</p>
                        <p class="mt-2 text-3xl font-bold text-green-600"><?php echo $stats['done']; ?></p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- PO Cards -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-900 mb-4">Purchase Order Saya</h2>
        </div>

        <?php if (count($purchaseOrders) > 0): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <?php foreach ($purchaseOrders as $po): ?>
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-lg hover:border-purple-300 transition duration-300">
                <!-- Card Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-violet-50 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <code class="text-lg font-mono font-bold text-slate-900"><?php echo htmlspecialchars($po['po_number']); ?></code>
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
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset <?php echo $statusColors[$po['status']]; ?>">
                            <?php echo $statusLabels[$po['status']]; ?>
                        </span>
                    </div>
                    <p class="text-sm text-slate-600 mt-1">Request: <?php echo htmlspecialchars($po['request_number']); ?></p>
                </div>

                <!-- Card Body -->
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-slate-600 mb-1">Pelanggan</p>
                        <p class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($po['company_name'] ?: $po['customer_name']); ?></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-600 mb-1">Produk</p>
                            <p class="text-base text-slate-900"><?php echo htmlspecialchars($po['product_name']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-600 mb-1">Jumlah</p>
                            <p class="text-base font-bold text-slate-900"><?php echo $po['quantity']; ?></p>
                        </div>
                    </div>

                    <?php if ($po['note']): ?>
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-slate-600 mb-1">Catatan:</p>
                        <p class="text-sm text-slate-700 p-3 bg-blue-50 rounded-lg"><?php echo nl2br(htmlspecialchars($po['note'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3 mt-4">
                        <a href="view_po.php?id=<?php echo $po['id']; ?>" class="flex-1 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-sm font-semibold rounded-lg text-center transition duration-150">
                            Detail
                        </a>
                        <?php if ($po['status'] !== 'done'): ?>
                        <a href="update_po.php?id=<?php echo $po['id']; ?>" class="flex-1 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg text-center transition duration-150">
                            Update Status
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-500">
                    Dibuat: <?php echo date('d M Y, H:i', strtotime($po['created_at'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl border border-slate-200 p-16 text-center">
            <svg class="w-20 h-20 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <p class="text-lg font-semibold text-slate-900 mb-2">Belum Ada Purchase Order</p>
            <p class="text-sm text-slate-600">Anda belum memiliki PO yang ditugaskan</p>
        </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <p class="text-center text-sm text-slate-500">
                &copy; 2024 RollMate. Sistem Marketplace Laminasi Industri.
            </p>
        </div>
    </footer>

</body>
</html>
