<?php
/**
 * File: admin/requests.php
 * Fungsi: Kelola Request Masuk dari Pelanggan
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Handle approve/reject request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE requests SET status = 'approved', approved_at = NOW() WHERE id = ?");
        $stmt->execute([$requestId]);
        setFlashMessage('Request berhasil disetujui!', 'success');
    } elseif ($action === 'reject') {
        $rejectReason = $_POST['reject_reason'] ?? 'Tidak memenuhi syarat';
        $stmt = $pdo->prepare("UPDATE requests SET status = 'rejected', rejection_reason = ? WHERE id = ?");
        $stmt->execute([$rejectReason, $requestId]);
        setFlashMessage('Request ditolak.', 'success');
    }
    
    header('Location: requests.php');
    exit;
}

// Filter
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';

// Build query
$sql = "SELECT r.*, u.full_name as customer_name, u.company_name, u.email, u.phone
        FROM requests r
        JOIN users u ON r.customer_id = u.id
        WHERE 1=1";

if ($statusFilter !== 'all') {
    $sql .= " AND r.status = :status";
}

if (!empty($searchQuery)) {
    $sql .= " AND (r.request_number LIKE :search OR r.product_name LIKE :search OR u.company_name LIKE :search)";
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($sql);

if ($statusFilter !== 'all') {
    $stmt->bindValue(':status', $statusFilter);
}
if (!empty($searchQuery)) {
    $stmt->bindValue(':search', '%' . $searchQuery . '%');
}

$stmt->execute();
$requests = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Masuk - RollMate Admin</title>
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
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">Request Masuk</h2>
                    <p class="mt-1 text-sm text-slate-600">Kelola semua request pembelian dari pelanggan</p>
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

                <!-- Filters -->
                <div class="mb-6 bg-white rounded-xl border border-slate-200 p-6">
                    <form method="GET" class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input 
                                type="text" 
                                name="search" 
                                placeholder="Cari request number, produk, atau perusahaan..."
                                value="<?php echo htmlspecialchars($searchQuery); ?>"
                                class="w-full px-4 py-2 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            >
                        </div>
                        <div>
                            <select name="status" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                <option value="process" <?php echo $statusFilter === 'process' ? 'selected' : ''; ?>>Process</option>
                                <option value="done" <?php echo $statusFilter === 'done' ? 'selected' : ''; ?>>Done</option>
                            </select>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300">
                            Filter
                        </button>
                    </form>
                </div>

                <!-- Requests Table -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Request Info</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Pelanggan</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Produk</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php if (count($requests) > 0): ?>
                                    <?php foreach ($requests as $req): ?>
                                    <tr class="hover:bg-slate-50 transition duration-150">
                                        <td class="px-6 py-4">
                                            <code class="text-sm font-mono font-semibold text-slate-900"><?php echo htmlspecialchars($req['request_number']); ?></code>
                                            <p class="text-xs text-slate-500 mt-1"><?php echo date('d M Y H:i', strtotime($req['created_at'])); ?></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($req['company_name'] ?: $req['customer_name']); ?></p>
                                            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($req['customer_name']); ?></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm text-slate-900"><?php echo htmlspecialchars($req['product_name']); ?></p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm font-semibold text-slate-900"><?php echo $req['quantity']; ?></p>
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
                                                'pending' => 'Pending',
                                                'approved' => 'Approved',
                                                'rejected' => 'Rejected',
                                                'paid' => 'Paid',
                                                'process' => 'Process',
                                                'done' => 'Done'
                                            ];
                                            ?>
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset <?php echo $statusColors[$req['status']]; ?>">
                                                <?php echo $statusLabels[$req['status']]; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($req['status'] === 'pending'): ?>
                                            <div class="flex space-x-2">
                                                <form method="POST" class="inline">
                                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded transition duration-150">
                                                        Approve
                                                    </button>
                                                </form>
                                                <button onclick="showRejectModal(<?php echo $req['id']; ?>)" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded transition duration-150">
                                                    Reject
                                                </button>
                                            </div>
                                            <?php elseif ($req['status'] === 'approved'): ?>
                                            <a href="create_po.php?request_id=<?php echo $req['id']; ?>" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition duration-150 inline-block">
                                                Buat PO
                                            </a>
                                            <?php else: ?>
                                            <a href="view_request.php?id=<?php echo $req['id']; ?>" class="text-sm text-blue-600 hover:text-blue-700 font-semibold">
                                                Detail →
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-sm text-slate-500">Tidak ada request ditemukan</p>
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

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Tolak Request</h3>
            <form method="POST">
                <input type="hidden" name="request_id" id="rejectRequestId">
                <input type="hidden" name="action" value="reject">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Alasan Penolakan</label>
                    <textarea name="reject_reason" rows="3" required class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="hideRejectModal()" class="flex-1 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition duration-150">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition duration-150">
                        Tolak Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showRejectModal(requestId) {
            document.getElementById('rejectRequestId').value = requestId;
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        
        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>

</body>
</html>
