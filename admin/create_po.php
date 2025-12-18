<?php
/**
 * File: admin/create_po.php
 * Fungsi: Form Buat Purchase Order dari Request yang Approved
 */

require_once '../config/database.php';
require_once '../config/session.php';

requireAdmin();

$user = getCurrentUser();
$pdo = getDBConnection();

$requestId = $_GET['request_id'] ?? 0;

// Get request detail with product info
$stmt = $pdo->prepare("
    SELECT r.*, u.full_name as customer_name, u.company_name, u.email, u.phone, p.price as product_price
    FROM requests r
    JOIN users u ON r.customer_id = u.id
    JOIN products p ON r.product_id = p.id
    WHERE r.id = ? AND r.status = 'approved'
");
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request) {
    setFlashMessage('Request tidak ditemukan atau belum diapprove!', 'error');
    header('Location: requests.php');
    exit;
}

// Get staff list
$stmt = $pdo->query("SELECT id, full_name FROM users WHERE role = 'staff' AND is_active = 1 ORDER BY full_name");
$staffList = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffId = $_POST['staff_id'];
    $totalAmount = floatval($_POST['total_amount'] ?? 0);
    $notes = $_POST['notes'] ?? '';
    
    // Validate total amount (DECIMAL(12,2) max is 9999999999.99)
    if ($totalAmount < 0) {
        $totalAmount = 0;
    }
    if ($totalAmount > 9999999999.99) {
        $totalAmount = 9999999999.99;
    }
    
    // Generate PO Number
    $poNumber = 'PO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Insert PO
    $stmt = $pdo->prepare("
        INSERT INTO purchase_orders (po_number, request_id, admin_id, staff_id, total_amount, notes, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$poNumber, $requestId, $user['id'], $staffId, $totalAmount, $notes]);
    
    // Update request status
    $stmt = $pdo->prepare("UPDATE requests SET status = 'process' WHERE id = ?");
    $stmt->execute([$requestId]);
    
    setFlashMessage('Purchase Order berhasil dibuat!', 'success');
    header('Location: purchase_orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Purchase Order - RollMate Admin</title>
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
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">Buat Purchase Order</h2>
                    <p class="mt-1 text-sm text-slate-600">Buat PO baru dari request yang sudah diapprove</p>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto bg-slate-50 p-8">

                <!-- Back Button -->
                <div class="mb-6">
                    <a href="requests.php" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Kembali ke Requests
                    </a>
                </div>

                <div class="max-w-4xl">

                    <!-- Request Info -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-6 mb-8">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Informasi Request</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm font-semibold text-blue-900 mb-1">Request Number</p>
                                <code class="text-base font-mono font-bold text-slate-900"><?php echo htmlspecialchars($request['request_number']); ?></code>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-blue-900 mb-1">Pelanggan</p>
                                <p class="text-base font-bold text-slate-900"><?php echo htmlspecialchars($request['company_name'] ?: $request['customer_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-blue-900 mb-1">Produk</p>
                                <p class="text-base font-bold text-slate-900"><?php echo htmlspecialchars($request['product_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-blue-900 mb-1">Jumlah</p>
                                <p class="text-base font-bold text-slate-900"><?php echo $request['quantity']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-blue-900 mb-1">Harga Satuan</p>
                                <p class="text-base font-bold text-slate-900">Rp <?php echo number_format($request['product_price'], 0, ',', '.'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-blue-900 mb-1">Total Estimasi</p>
                                <p class="text-base font-bold text-green-600">Rp <?php echo number_format($request['product_price'] * $request['quantity'], 0, ',', '.'); ?></p>
                            </div>
                            <?php if ($request['note']): ?>
                            <div class="md:col-span-3">
                                <p class="text-sm font-semibold text-blue-900 mb-1">Catatan</p>
                                <p class="text-sm text-slate-700"><?php echo nl2br(htmlspecialchars($request['note'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- PO Form -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
                        <form method="POST" action="">
                            
                            <!-- Staff Selection -->
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-slate-800 mb-2">
                                    Pilih Staff <span class="text-red-500">*</span>
                                </label>
                                <select name="staff_id" required class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <option value="">-- Pilih Staff --</option>
                                    <?php foreach ($staffList as $staff): ?>
                                    <option value="<?php echo $staff['id']; ?>">
                                        <?php echo htmlspecialchars($staff['full_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-xs text-slate-500 mt-2">Pilih staff yang akan mengerjakan PO ini</p>
                            </div>

                            <!-- Total Amount -->
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-slate-800 mb-2">
                                    Total Amount <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-slate-500">Rp</span>
                                    <input 
                                        type="number" 
                                        name="total_amount" 
                                        id="totalAmount"
                                        required
                                        min="0"
                                        max="9999999999.99"
                                        step="0.01"
                                        value="<?php echo $request['product_price'] * $request['quantity']; ?>"
                                        readonly
                                        class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-lg text-slate-900 bg-slate-50 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        placeholder="0.00"
                                    >
                                </div>
                                <p class="text-xs text-slate-500 mt-2">Total dihitung otomatis: Harga × Jumlah (Anda dapat mengubah jika diperlukan)</p>
                                <button type="button" onclick="toggleEditTotal()" class="mt-2 text-xs text-blue-600 hover:text-blue-700 font-semibold">
                                    📝 Edit Manual
                                </button>
                            </div>

                            <!-- Notes -->
                            <div class="mb-8">
                                <label class="block text-sm font-semibold text-slate-800 mb-2">
                                    Catatan untuk Staff
                                </label>
                                <textarea 
                                    name="notes" 
                                    rows="4"
                                    class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    placeholder="Instruksi khusus atau catatan tambahan untuk staff..."
                                ></textarea>
                            </div>

                            <!-- Info Box -->
                            <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h3 class="text-sm font-bold text-blue-900 mb-2">ℹ️ Informasi:</h3>
                                <ul class="space-y-1 text-sm text-blue-800">
                                    <li>• PO Number akan digenerate otomatis</li>
                                    <li>• Staff akan menerima notifikasi PO baru</li>
                                    <li>• Status request akan berubah menjadi "Process"</li>
                                    <li>• Staff dapat mulai mengerjakan setelah PO dibuat</li>
                                </ul>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex space-x-3">
                                <a href="requests.php" class="flex-1 px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg text-center transition duration-300">
                                    Batal
                                </a>
                                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300 transform hover:scale-105">
                                    Buat Purchase Order
                                </button>
                            </div>

                        </form>
                    </div>

                </div>

            </main>

        </div>

    </div>

    <script>
        function toggleEditTotal() {
            const totalInput = document.getElementById('totalAmount');
            const isReadonly = totalInput.hasAttribute('readonly');
            
            if (isReadonly) {
                totalInput.removeAttribute('readonly');
                totalInput.classList.remove('bg-slate-50');
                totalInput.classList.add('bg-white');
                totalInput.focus();
                event.target.textContent = '🔒 Kunci Otomatis';
            } else {
                totalInput.setAttribute('readonly', true);
                totalInput.classList.add('bg-slate-50');
                totalInput.classList.remove('bg-white');
                // Reset to calculated value
                const calculatedTotal = <?php echo $request['product_price'] * $request['quantity']; ?>;
                totalInput.value = calculatedTotal;
                event.target.textContent = '📝 Edit Manual';
            }
        }
    </script>

</body>
</html>
