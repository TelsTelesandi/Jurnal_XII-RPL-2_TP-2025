<?php
/**
 * File: admin/dashboard.php
 * Fungsi: Dashboard Manajemen untuk Admin
 * Style: TailwindCSS official dengan sidebar minimalis
 */

require_once '../config/database.php';
require_once '../config/session.php';

// Proteksi halaman - hanya admin yang bisa akses
requireAdmin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Ambil statistik keseluruhan dengan konsistensi data
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_requests,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
        SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as process_requests
    FROM requests
");
$requestStats = $stmt->fetch();

// Validate total consistency
$calculatedTotal = $requestStats['completed_requests'] + $requestStats['rejected_requests'] + 
                  $requestStats['pending_requests'] + $requestStats['approved_requests'] + 
                  $requestStats['process_requests'];

// Ambil statistik lainnya
$stmt = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM purchase_orders) as total_po,
        (SELECT COUNT(*) FROM purchase_orders WHERE status = 'process') as process_po,
        (SELECT COUNT(*) FROM users WHERE role = 'pelanggan') as total_customers,
        (SELECT COUNT(*) FROM products WHERE is_active = 1) as total_products
");
$otherStats = $stmt->fetch();

// Gabungkan semua statistik
$stats = array_merge($requestStats, $otherStats);

// Ambil request yang pending
$stmt = $pdo->query("
    SELECT r.*, u.full_name as customer_name, u.company_name
    FROM requests r
    JOIN users u ON r.customer_id = u.id
    WHERE r.status = 'pending'
    ORDER BY r.created_at DESC
    LIMIT 10
");
$pendingRequests = $stmt->fetchAll();

// Ambil PO yang sedang diproses
$stmt = $pdo->query("
    SELECT po.*, r.request_number, r.product_name, u.full_name as staff_name
    FROM purchase_orders po
    JOIN requests r ON po.request_id = r.id
    LEFT JOIN users u ON po.staff_id = u.id
    WHERE po.status IN ('pending', 'process')
    ORDER BY po.created_at DESC
    LIMIT 5
");
$activePO = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - RollMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full bg-white">

    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <aside class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 border-r border-slate-200 bg-slate-900">
                <!-- Logo -->
                <div class="flex items-center h-16 flex-shrink-0 px-6 border-b border-slate-800">
                    <h1 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-sky-300 bg-clip-text text-transparent">
                        RollMate
                    </h1>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
                    <a href="dashboard.php" class="flex items-center px-3 py-2 text-sm font-semibold text-white bg-slate-800 rounded-lg">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>
                    <a href="requests.php" class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition duration-150">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Request Masuk
                        <?php if ($stats['pending_requests'] > 0): ?>
                        <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-500 text-white">
                            <?php echo $stats['pending_requests']; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <a href="purchase_orders.php" class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition duration-150">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Purchase Orders
                    </a>
                    <a href="products.php" class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition duration-150">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Produk
                    </a>
                    <a href="users.php" class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition duration-150">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Kelola User
                    </a>
                    <a href="reports.php" class="flex items-center px-3 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition duration-150">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Laporan
                    </a>
                </nav>

                <!-- User Profile -->
                <div class="flex-shrink-0 border-t border-slate-800 p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($user['full_name']); ?></p>
                            <p class="text-xs text-slate-400">Administrator</p>
                        </div>
                    </div>
                    <a href="../logout.php" class="mt-3 flex items-center justify-center w-full px-3 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-red-600 rounded-lg transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Header -->
            <header class="flex-shrink-0 border-b border-slate-200 bg-white">
                <div class="px-8 py-6">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">Dashboard Admin</h2>
                    <p class="mt-1 text-sm text-slate-600">Kelola seluruh sistem RollMate Marketplace</p>
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

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
                    <!-- Total Requests -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600">Total Request</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900"><?php echo $stats['total_requests']; ?></p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Completed -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600">Selesai</p>
                                <p class="mt-2 text-3xl font-bold text-green-600"><?php echo $stats['completed_requests']; ?></p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Pending -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600">Pending</p>
                                <p class="mt-2 text-3xl font-bold text-yellow-600"><?php echo $stats['pending_requests']; ?></p>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-lg">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Rejected -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600">Ditolak</p>
                                <p class="mt-2 text-3xl font-bold text-red-600"><?php echo $stats['rejected_requests']; ?></p>
                            </div>
                            <div class="p-3 bg-red-100 rounded-lg">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Approved -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600">Disetujui</p>
                                <p class="mt-2 text-3xl font-bold text-blue-600"><?php echo $stats['approved_requests']; ?></p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Process -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600">Diproses</p>
                                <p class="mt-2 text-3xl font-bold text-purple-600"><?php echo $stats['process_requests']; ?></p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Total Customers -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600">Total Pelanggan</p>
                                <p class="mt-2 text-3xl font-bold text-green-600"><?php echo $stats['total_customers']; ?></p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total PO -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600">Total PO</p>
                                <p class="mt-2 text-3xl font-bold text-indigo-600"><?php echo $stats['total_po']; ?></p>
                            </div>
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Active PO -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-600">PO Aktif</p>
                                <p class="mt-2 text-3xl font-bold text-purple-600"><?php echo $stats['process_po']; ?></p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Request Pending -->
                    <div class="bg-white rounded-xl border border-slate-200">
                        <div class="px-6 py-4 border-b border-slate-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-slate-900">Request Menunggu Approval</h3>
                                <a href="requests.php" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition duration-150">
                                    Lihat Semua →
                                </a>
                            </div>
                        </div>
                        <div class="p-6">
                            <?php if (count($pendingRequests) > 0): ?>
                            <div class="space-y-4">
                                <?php foreach ($pendingRequests as $req): ?>
                                <div class="flex items-start p-4 bg-slate-50 rounded-lg border border-slate-200 hover:border-blue-300 transition duration-150">
                                    <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($req['company_name']); ?></p>
                                        <p class="text-sm text-slate-600"><?php echo htmlspecialchars($req['product_name']); ?> (<?php echo $req['quantity']; ?>x)</p>
                                        <p class="text-xs text-slate-500 mt-1"><?php echo date('d M Y H:i', strtotime($req['created_at'])); ?></p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded transition duration-150">
                                            Approve
                                        </button>
                                        <button class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded transition duration-150">
                                            Reject
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm text-slate-500">Tidak ada request yang menunggu approval</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Active PO -->
                    <div class="bg-white rounded-xl border border-slate-200">
                        <div class="px-6 py-4 border-b border-slate-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-slate-900">Purchase Order Aktif</h3>
                                <a href="purchase_orders.php" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition duration-150">
                                    Lihat Semua →
                                </a>
                            </div>
                        </div>
                        <div class="p-6">
                            <?php if (count($activePO) > 0): ?>
                            <div class="space-y-4">
                                <?php foreach ($activePO as $po): ?>
                                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <p class="text-sm font-mono font-semibold text-slate-900"><?php echo htmlspecialchars($po['po_number']); ?></p>
                                            <p class="text-sm text-slate-600"><?php echo htmlspecialchars($po['product_name']); ?></p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold <?php echo $po['status'] === 'process' ? 'bg-purple-100 text-purple-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                            <?php echo $po['status'] === 'process' ? 'Diproses' : 'Pending'; ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center text-xs text-slate-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Staff: <?php echo htmlspecialchars($po['staff_name'] ?? 'Belum ditugaskan'); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p class="text-sm text-slate-500">Tidak ada PO yang sedang aktif</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </main>

        </div>

    </div>

</body>
</html>
