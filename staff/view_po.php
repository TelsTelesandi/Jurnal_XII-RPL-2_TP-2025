<?php
/**
 * File: staff/view_po.php
 * Fungsi: Detail Purchase Order untuk Staff
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireStaff();

$user = getCurrentUser();
$pdo = getDBConnection();

$poId = $_GET['id'] ?? 0;

// Get PO detail
$stmt = $pdo->prepare("
    SELECT po.*, r.request_number, r.product_name, r.quantity, r.note,
           u.full_name as customer_name, u.company_name, u.email, u.phone
    FROM purchase_orders po
    JOIN requests r ON po.request_id = r.id
    JOIN users u ON r.customer_id = u.id
    WHERE po.id = ? AND po.staff_id = ?
");
$stmt->execute([$poId, $user['id']]);
$po = $stmt->fetch();

if (!$po) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail PO - RollMate Staff</title>
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
                    <h1 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-violet-400 bg-clip-text text-transparent">
                        RollMate Staff
                    </h1>
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
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header -->
        <div class="mb-8">
            <a href="dashboard.php" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Dashboard
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 mb-2">
                        Detail Purchase Order
                    </h1>
                    <code class="text-lg font-mono text-slate-600"><?php echo htmlspecialchars($po['po_number']); ?></code>
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

        <!-- PO Info -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-violet-50 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">Informasi Purchase Order</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Request Number</dt>
                        <dd class="text-base font-mono font-semibold text-slate-900"><?php echo htmlspecialchars($po['request_number']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Tanggal Dibuat</dt>
                        <dd class="text-base text-slate-900"><?php echo date('d F Y, H:i', strtotime($po['created_at'])); ?></dd>
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

        <!-- Customer Info -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">Informasi Pelanggan</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <dd class="text-base text-slate-900"><?php echo htmlspecialchars($po['email']); ?></dd>
                    </div>
                    <?php if ($po['phone']): ?>
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Telepon</dt>
                        <dd class="text-base text-slate-900"><?php echo htmlspecialchars($po['phone']); ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Product Info -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">Detail Produk</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Nama Produk</dt>
                        <dd class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($po['product_name']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Jumlah</dt>
                        <dd class="text-lg font-bold text-purple-600"><?php echo $po['quantity']; ?></dd>
                    </div>
                    <?php if ($po['note']): ?>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-semibold text-slate-600 mb-2">Catatan dari Pelanggan</dt>
                        <dd class="text-base text-slate-900 p-4 bg-blue-50 border border-blue-100 rounded-lg"><?php echo nl2br(htmlspecialchars($po['note'])); ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Notes (if exists) -->
        <?php if ($po['notes']): ?>
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-900">Catatan dari Admin</h2>
            </div>
            <div class="p-6">
                <p class="text-base text-slate-900 whitespace-pre-wrap"><?php echo htmlspecialchars($po['notes']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <?php if ($po['status'] !== 'done'): ?>
        <div class="flex space-x-3">
            <a href="dashboard.php" class="flex-1 px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg text-center transition duration-300">
                Kembali
            </a>
            <a href="update_po.php?id=<?php echo $po['id']; ?>" class="flex-1 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg text-center transition duration-300 transform hover:scale-105">
                Update Status
            </a>
        </div>
        <?php else: ?>
        <div class="text-center p-6 bg-green-50 border border-green-200 rounded-xl">
            <svg class="w-16 h-16 mx-auto mb-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-lg font-bold text-green-900 mb-2">Pekerjaan Selesai</p>
            <p class="text-sm text-green-700">Purchase Order ini sudah diselesaikan</p>
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
