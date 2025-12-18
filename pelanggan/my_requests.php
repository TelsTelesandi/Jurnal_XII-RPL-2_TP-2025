<?php
/**
 * File: pelanggan/my_requests.php
 * Fungsi: Lihat Semua Request Pelanggan
 */

require_once '../config/database.php';
require_once '../config/session.php';

requirePelanggan();

$user = getCurrentUser();
$pdo = getDBConnection();

// Filter
$statusFilter = $_GET['status'] ?? 'all';

// Get requests
$sql = "SELECT * FROM requests WHERE customer_id = :customer_id";
$params = [':customer_id' => $user['id']];

if ($statusFilter !== 'all') {
    $sql .= " AND status = :status";
    $params[':status'] = $statusFilter;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as process,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done
    FROM requests WHERE customer_id = ?
");
$stmt->execute([$user['id']]);
$stats = $stmt->fetch();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Saya - RollMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white">

    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent mb-2">
                Request Saya
            </h1>
            <p class="text-lg text-slate-600">Pantau status semua request pembelian Anda</p>
        </div>

        <!-- Flash Message -->
        <?php if ($flashMessage): ?>
        <div class="mb-6 rounded-lg <?php echo $flashMessage['type'] === 'success' ? 'bg-green-50 border-green-100' : 'bg-blue-50 border-blue-100'; ?> border p-4">
            <p class="text-sm font-medium <?php echo $flashMessage['type'] === 'success' ? 'text-green-800' : 'text-blue-800'; ?>">
                <?php echo htmlspecialchars($flashMessage['message']); ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <p class="text-2xl font-bold text-slate-900"><?php echo $stats['total']; ?></p>
                <p class="text-xs font-medium text-slate-600 mt-1">Total</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                <p class="text-xs font-medium text-slate-600 mt-1">Pending</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <p class="text-2xl font-bold text-green-600"><?php echo $stats['approved']; ?></p>
                <p class="text-xs font-medium text-slate-600 mt-1">Approved</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <p class="text-2xl font-bold text-red-600"><?php echo $stats['rejected']; ?></p>
                <p class="text-xs font-medium text-slate-600 mt-1">Rejected</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <p class="text-2xl font-bold text-purple-600"><?php echo $stats['process']; ?></p>
                <p class="text-xs font-medium text-slate-600 mt-1">Process</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4 text-center">
                <p class="text-2xl font-bold text-green-600"><?php echo $stats['done']; ?></p>
                <p class="text-xs font-medium text-slate-600 mt-1">Done</p>
            </div>
        </div>

        <!-- Filter & Action -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <form method="GET" class="flex items-center gap-3">
                <label class="text-sm font-semibold text-slate-800">Filter:</label>
                <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="process" <?php echo $statusFilter === 'process' ? 'selected' : ''; ?>>Process</option>
                    <option value="done" <?php echo $statusFilter === 'done' ? 'selected' : ''; ?>>Done</option>
                </select>
            </form>
            <a href="dashboard.php#products" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300 transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buat Request Baru
            </a>
        </div>

        <!-- Requests List -->
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <?php if (count($requests) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Request Number</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tanggal Pemesanan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($requests as $req): ?>
                        <tr class="hover:bg-slate-50 transition duration-150">
                            <td class="px-6 py-4">
                                <code class="text-sm font-mono font-semibold text-slate-900"><?php echo htmlspecialchars($req['request_number']); ?></code>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($req['product_name']); ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-slate-900"><?php echo $req['quantity']; ?></p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                                    'pending' => 'Menunggu',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    'paid' => 'Dibayar',
                                    'process' => 'Diproses',
                                    'done' => 'Selesai'
                                ];
                                ?>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset <?php echo $statusColors[$req['status']]; ?>">
                                    <?php echo $statusLabels[$req['status']]; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                <?php echo date('d M Y', strtotime($req['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="view_request.php?id=<?php echo $req['id']; ?>" class="text-sm text-blue-600 hover:text-blue-700 font-semibold">
                                    Detail →
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-16">
                <svg class="w-20 h-20 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-semibold text-slate-900 mb-2">Belum Ada Request</p>
                <p class="text-sm text-slate-600 mb-6">Mulai pesan produk laminasi sekarang</p>
                <a href="dashboard.php#products" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300">
                    Lihat Produk
                </a>
            </div>
            <?php endif; ?>
        </div>

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
