<?php
/**
 * File: admin/purchase_orders.php
 * Fungsi: Kelola Purchase Orders
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$user = getCurrentUser();
$pdo = getDBConnection();


// Handle mark as taken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_taken') {
    $poId = $_POST['po_id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE purchase_orders SET is_taken = 1 WHERE id = ?");
    $stmt->execute([$poId]);
    
    setFlashMessage('Status pengambilan berhasil diperbarui', 'success');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// This section is not used anymore - PO creation moved to create_po.php

// Get all PO
$stmt = $pdo->query("
    SELECT po.*, r.request_number, r.product_name, r.quantity,
           u.full_name as staff_name,
           c.full_name as customer_name, c.company_name
    FROM purchase_orders po
    JOIN requests r ON po.request_id = r.id
    LEFT JOIN users u ON po.staff_id = u.id
    JOIN users c ON r.customer_id = c.id
    ORDER BY po.created_at DESC
");
$purchaseOrders = $stmt->fetchAll();

// Get staff list for assignment
$stmt = $pdo->query("SELECT id, full_name FROM users WHERE role = 'staff' AND is_active = 1");
$staffList = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders - RollMate Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Toggle Switch Style */
        /* Status Badge Styling */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            line-height: 1rem;
            font-weight: 500;
        }
        
        .status-badge svg {
            margin-right: 0.25rem;
            flex-shrink: 0;
        }
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
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">Purchase Orders</h2>
                    <p class="mt-1 text-sm text-slate-600">Kelola semua purchase order untuk staff</p>
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

                <!-- PO Table -->
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">PO Number</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Request</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Pelanggan</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Staff</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Pengambilan</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php if (count($purchaseOrders) > 0): ?>
                                    <?php foreach ($purchaseOrders as $po): ?>
                                    <tr class="hover:bg-slate-50 transition duration-150">
                                        <td class="px-6 py-4">
                                            <code class="text-sm font-mono font-semibold text-slate-900"><?php echo htmlspecialchars($po['po_number']); ?></code>
                                            <p class="text-xs text-slate-500 mt-1"><?php echo date('d M Y', strtotime($po['created_at'])); ?></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($po['request_number']); ?></p>
                                            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($po['product_name']); ?> (<?php echo $po['quantity']; ?>x)</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($po['company_name'] ?: $po['customer_name']); ?></p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm text-slate-900"><?php echo htmlspecialchars($po['staff_name'] ?: 'Belum ditugaskan'); ?></p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm font-semibold text-slate-900">Rp <?php echo number_format($po['total_amount'], 0, ',', '.'); ?></p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $statusColors = [
                                                'pending' => 'bg-yellow-100 text-yellow-700 ring-yellow-600/20',
                                                'process' => 'bg-purple-100 text-purple-700 ring-purple-600/20',
                                                'done' => 'bg-green-100 text-green-700 ring-green-600/20'
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Pending',
                                                'process' => 'Process',
                                                'done' => 'Done'
                                            ];
                                            ?>
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset <?php echo $statusColors[$po['status']]; ?>">
                                                <?php echo $statusLabels[$po['status']]; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if (isset($po['is_taken']) && $po['is_taken']): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                                        <circle cx="4" cy="4" r="3" />
                                                    </svg>
                                                    Sudah Diambil
                                                </span>
                                            <?php else: ?>
                                                <form method="POST" class="inline-flex items-center">
                                                    <input type="hidden" name="action" value="mark_taken">
                                                    <input type="hidden" name="po_id" value="<?php echo $po['id']; ?>">
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                                            onclick="return confirm('Tandai PO ini sudah diambil?')">
                                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                                            <circle cx="4" cy="4" r="3" />
                                                        </svg>
                                                        Tandai Sudah Diambil
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <a href="view_po.php?id=<?php echo $po['id']; ?>" class="text-blue-600 hover:text-blue-900">Lihat</a>
                                                <?php if ($po['status'] === 'pending'): ?>
                                                <a href="edit_po.php?id=<?php echo $po['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <p class="text-sm text-slate-500">Belum ada purchase order</p>
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

</body>
</html>
