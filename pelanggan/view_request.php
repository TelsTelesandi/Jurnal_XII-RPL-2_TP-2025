<?php
/**
 * File: pelanggan/view_request.php
 * Fungsi: Detail Request Pelanggan
 */

require_once '../config/database.php';
require_once '../config/session.php';

requirePelanggan();

$user = getCurrentUser();
$pdo = getDBConnection();

$requestId = $_GET['id'] ?? 0;

// Get request detail
$stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ? AND customer_id = ?");
$stmt->execute([$requestId, $user['id']]);
$request = $stmt->fetch();

if (!$request) {
    header('Location: my_requests.php');
    exit;
}

// Get related PO if exists
$po = null;
if (in_array($request['status'], ['process', 'done'])) {
    $stmt = $pdo->prepare("
        SELECT po.*, u.full_name as staff_name
        FROM purchase_orders po
        LEFT JOIN users u ON po.staff_id = u.id
        WHERE po.request_id = ?
    ");
    $stmt->execute([$requestId]);
    $po = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Request - RollMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white">

    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header -->
        <div class="mb-8">
            <a href="my_requests.php" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Request Saya
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 mb-2">
                        Detail Request
                    </h1>
                    <code class="text-lg font-mono text-slate-600"><?php echo htmlspecialchars($request['request_number']); ?></code>
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
                    'pending' => 'Menunggu Approval',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'paid' => 'Dibayar',
                    'process' => 'Sedang Diproses',
                    'done' => 'Selesai'
                ];
                ?>
                <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold ring-2 ring-inset <?php echo $statusColors[$request['status']]; ?>">
                    <?php echo $statusLabels[$request['status']]; ?>
                </span>
            </div>
        </div>

        <!-- Request Info -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">Informasi Request</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Produk</dt>
                        <dd class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($request['product_name']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Jumlah</dt>
                        <dd class="text-lg font-bold text-slate-900"><?php echo $request['quantity']; ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Tanggal Request</dt>
                        <dd class="text-base text-slate-900"><?php echo date('d F Y, H:i', strtotime($request['created_at'])); ?></dd>
                    </div>
                    <?php if ($request['approved_at']): ?>
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Tanggal Approval</dt>
                        <dd class="text-base text-slate-900"><?php echo date('d F Y, H:i', strtotime($request['approved_at'])); ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($request['note']): ?>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Catatan</dt>
                        <dd class="text-base text-slate-900 p-4 bg-slate-50 rounded-lg"><?php echo nl2br(htmlspecialchars($request['note'])); ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($request['status'] === 'rejected' && $request['reject_reason']): ?>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-semibold text-red-600 mb-1">Alasan Penolakan</dt>
                        <dd class="text-base text-red-900 p-4 bg-red-50 border border-red-100 rounded-lg"><?php echo nl2br(htmlspecialchars($request['reject_reason'])); ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">Timeline Status</h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Created -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-semibold text-slate-900">Request Dibuat</p>
                            <p class="text-xs text-slate-500"><?php echo date('d M Y, H:i', strtotime($request['created_at'])); ?></p>
                        </div>
                    </div>

                    <!-- Approved/Rejected -->
                    <?php if ($request['approved_at'] || $request['status'] === 'rejected'): ?>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 <?php echo $request['status'] === 'rejected' ? 'bg-red-100' : 'bg-green-100'; ?> rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 <?php echo $request['status'] === 'rejected' ? 'text-red-600' : 'text-green-600'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-semibold text-slate-900"><?php echo $request['status'] === 'rejected' ? 'Request Ditolak' : 'Request Disetujui'; ?></p>
                            <p class="text-xs text-slate-500"><?php echo $request['approved_at'] ? date('d M Y, H:i', strtotime($request['approved_at'])) : '-'; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Process -->
                    <?php if (in_array($request['status'], ['process', 'done'])): ?>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-semibold text-slate-900">Sedang Diproses</p>
                            <p class="text-xs text-slate-500"><?php echo $po ? 'PO: ' . $po['po_number'] : '-'; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Done -->
                    <?php if ($request['status'] === 'done'): ?>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-semibold text-slate-900">Selesai</p>
                            <p class="text-xs text-slate-500"><?php echo $po && $po['completed_at'] ? date('d M Y, H:i', strtotime($po['completed_at'])) : '-'; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- PO Info (if exists) -->
        <?php if ($po): ?>
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">Informasi Purchase Order</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">PO Number</dt>
                        <dd class="text-base font-mono font-semibold text-slate-900"><?php echo htmlspecialchars($po['po_number']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Staff</dt>
                        <dd class="text-base text-slate-900"><?php echo htmlspecialchars($po['staff_name'] ?? 'Belum ditugaskan'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Status PO</dt>
                        <dd>
                            <?php
                            $poStatusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-700 ring-yellow-600/20',
                                'process' => 'bg-purple-100 text-purple-700 ring-purple-600/20',
                                'done' => 'bg-green-100 text-green-700 ring-green-600/20'
                            ];
                            ?>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset <?php echo $poStatusColors[$po['status']]; ?>">
                                <?php echo ucfirst($po['status']); ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-1">Status Pengambilan</dt>
                        <dd>
                            <div class="flex items-center">
                                <?php if (isset($po['is_taken']) && $po['is_taken']): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Sudah Diambil
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Belum Diambil
                                    </span>
                                <?php endif; ?>
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>
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
