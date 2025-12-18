<?php
/**
 * File: admin/view_request.php
 * Fungsi: Detail Request untuk Admin
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$user = getCurrentUser();
$pdo = getDBConnection();

$requestId = $_GET['id'] ?? 0;

// Get request detail
$stmt = $pdo->prepare("
    SELECT r.*, u.full_name as customer_name, u.company_name, u.email, u.phone, u.address
    FROM requests r
    JOIN users u ON r.customer_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request) {
    header('Location: requests.php');
    exit;
}

// Get PO if exists
$po = null;
if (in_array($request['status'], ['process', 'done'])) {
    $stmt = $pdo->prepare("
        SELECT po.*, s.full_name as staff_name
        FROM purchase_orders po
        LEFT JOIN users s ON po.staff_id = s.id
        WHERE po.request_id = ?
    ");
    $stmt->execute([$requestId]);
    $po = $stmt->fetch();
}

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Request - RollMate Admin</title>
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
                            <h2 class="text-3xl font-bold tracking-tight text-slate-900">Detail Request</h2>
                            <code class="mt-2 inline-block text-lg font-mono text-slate-600"><?php echo htmlspecialchars($request['request_number']); ?></code>
                        </div>
                        <?php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-700 ring-yellow-600/20',
                            'approved' => 'bg-green-100 text-green-700 ring-green-600/20',
                            'rejected' => 'bg-red-100 text-red-700 ring-red-600/20',
                            'paid' => 'bg-blue-100 text-blue-700 ring-blue-600/20',
                            'process' => 'bg-purple-100 text-purple-700 ring-purple-600/20',
                            'done' => 'bg-green-100 text-green-700 ring-green-600/20'
                        ];
                        $statusLabels = [
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                            'paid' => 'Paid',
                            'process' => 'Process',
                            'done' => 'Done'
                        ];
                        ?>
                        <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold ring-2 ring-inset <?php echo $statusColors[$request['status']]; ?>">
                            <?php echo $statusLabels[$request['status']]; ?>
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
                    <a href="requests.php" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali ke Requests
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Request Info -->
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-slate-200">
                            <h2 class="text-lg font-bold text-slate-900">Informasi Request</h2>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Request Number</dt>
                                    <dd class="text-base font-mono font-semibold text-slate-900"><?php echo htmlspecialchars($request['request_number']); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Tanggal Request</dt>
                                    <dd class="text-base text-slate-900"><?php echo date('d F Y, H:i', strtotime($request['created_at'])); ?></dd>
                                </div>
                                <?php if ($request['approved_at']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Tanggal Approved</dt>
                                    <dd class="text-base text-green-600 font-semibold"><?php echo date('d F Y, H:i', strtotime($request['approved_at'])); ?></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
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
                                    <dd class="text-base font-semibold text-slate-900"><?php echo htmlspecialchars($request['customer_name']); ?></dd>
                                </div>
                                <?php if ($request['company_name']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Perusahaan</dt>
                                    <dd class="text-base text-slate-900"><?php echo htmlspecialchars($request['company_name']); ?></dd>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Email</dt>
                                    <dd class="text-base text-slate-900"><?php echo htmlspecialchars($request['email']); ?></dd>
                                </div>
                                <?php if ($request['phone']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Telepon</dt>
                                    <dd class="text-base text-slate-900"><?php echo htmlspecialchars($request['phone']); ?></dd>
                                </div>
                                <?php endif; ?>
                                <?php if ($request['address']): ?>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Alamat</dt>
                                    <dd class="text-base text-slate-900"><?php echo nl2br(htmlspecialchars($request['address'])); ?></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden lg:col-span-2">
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                            <h2 class="text-lg font-bold text-slate-900">Detail Produk</h2>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Nama Produk</dt>
                                    <dd class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($request['product_name']); ?></dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-semibold text-slate-600 mb-1">Jumlah</dt>
                                    <dd class="text-lg font-bold text-blue-600"><?php echo $request['quantity']; ?></dd>
                                </div>
                                <?php if ($request['note']): ?>
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-semibold text-slate-600 mb-2">Catatan dari Pelanggan</dt>
                                    <dd class="text-base text-slate-900 p-4 bg-blue-50 border border-blue-100 rounded-lg"><?php echo nl2br(htmlspecialchars($request['note'])); ?></dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>

                </div>

                <!-- Rejection Reason -->
                <?php if ($request['status'] === 'rejected' && $request['rejection_reason']): ?>
                <div class="mt-6 bg-white rounded-xl border border-red-200 overflow-hidden">
                    <div class="px-6 py-4 bg-red-50 border-b border-red-100">
                        <h2 class="text-lg font-bold text-red-900">Alasan Penolakan</h2>
                    </div>
                    <div class="p-6">
                        <p class="text-base text-slate-900"><?php echo nl2br(htmlspecialchars($request['rejection_reason'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- PO Info (if exists) -->
                <?php if ($po): ?>
                <div class="mt-6 bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-purple-50 border-b border-slate-200">
                        <h2 class="text-lg font-bold text-slate-900">Purchase Order</h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-semibold text-slate-600 mb-1">PO Number</dt>
                                <dd class="text-base font-mono font-semibold text-slate-900"><?php echo htmlspecialchars($po['po_number']); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold text-slate-600 mb-1">Staff</dt>
                                <dd class="text-base text-slate-900"><?php echo htmlspecialchars($po['staff_name'] ?: 'Belum ditugaskan'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold text-slate-600 mb-1">Status PO</dt>
                                <dd>
                                    <?php
                                    $poStatusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'process' => 'bg-purple-100 text-purple-700',
                                        'done' => 'bg-green-100 text-green-700'
                                    ];
                                    $poStatusLabels = [
                                        'pending' => 'Pending',
                                        'process' => 'Diproses',
                                        'done' => 'Selesai'
                                    ];
                                    ?>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $poStatusColors[$po['status']]; ?>">
                                        <?php echo $poStatusLabels[$po['status']]; ?>
                                    </span>
                                </dd>
                            </div>
                        </dl>
                        <div class="mt-6">
                            <a href="view_po.php?id=<?php echo $po['id']; ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300">
                                Lihat Detail PO →
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <?php if ($request['status'] === 'approved'): ?>
                <div class="mt-6 p-6 bg-green-50 border border-green-200 rounded-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-lg font-bold text-green-900 mb-1">Request Sudah Diapprove</p>
                            <p class="text-sm text-green-700">Silakan buat Purchase Order untuk melanjutkan</p>
                        </div>
                        <a href="create_po.php?request_id=<?php echo $request['id']; ?>" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300 transform hover:scale-105">
                            Buat Purchase Order
                        </a>
                    </div>
                </div>
                <?php endif; ?>

            </main>

        </div>

    </div>

</body>
</html>
