<?php
/**
 * File: pelanggan/request_form.php
 * Fungsi: Form Buat Request Baru
 */

require_once '../config/database.php';
require_once '../config/session.php';

requirePelanggan();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get product if specified
$productId = $_GET['product_id'] ?? 0;
$selectedProduct = null;

if ($productId) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $selectedProduct = $stmt->fetch();
}

// Get all active products
$stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY name");
$products = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $note = $_POST['note'] ?? '';
    
    // Get product details (only active products)
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Generate request number
        $requestNumber = 'REQ-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Insert request
        $stmt = $pdo->prepare("
            INSERT INTO requests (request_number, customer_id, company_name, product_id, product_name, quantity, note, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([
            $requestNumber,
            $user['id'],
            $user['company_name'] ?? 'N/A',
            $productId,
            $product['name'],
            $quantity,
            $note
        ]);
        
        setFlashMessage('Request berhasil dibuat! Menunggu approval dari admin.', 'success');
        header('Location: my_requests.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Request Baru - RollMate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white">

    <?php include 'includes/navbar.php'; ?>

    <!-- Main Content -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header -->
        <div class="mb-8">
            <a href="dashboard.php" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Produk
            </a>
            <h1 class="text-4xl font-bold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent mb-2">
                Buat Request Baru
            </h1>
            <p class="text-lg text-slate-600">Isi form di bawah untuk membuat permintaan pembelian</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
            <form method="POST" action="">
                
                <!-- Product Selection -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">
                        Pilih Produk <span class="text-red-500">*</span>
                    </label>
                    <select name="product_id" id="productSelect" required onchange="updateProductInfo()" class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?php echo $p['id']; ?>" 
                                data-price="<?php echo $p['price']; ?>"
                                data-unit="<?php echo $p['unit']; ?>"
                                data-description="<?php echo htmlspecialchars($p['description']); ?>"
                                <?php echo ($selectedProduct && $selectedProduct['id'] == $p['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['name']); ?> - Rp <?php echo number_format($p['price'], 0, ',', '.'); ?>/<?php echo $p['unit']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Product Info Display -->
                <div id="productInfo" class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-lg <?php echo !$selectedProduct ? 'hidden' : ''; ?>">
                    <p class="text-sm font-semibold text-blue-900 mb-2">Informasi Produk:</p>
                    <p id="productDescription" class="text-sm text-blue-800 mb-2"><?php echo $selectedProduct ? htmlspecialchars($selectedProduct['description']) : ''; ?></p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-blue-700">Harga:</span>
                        <span id="productPrice" class="text-lg font-bold text-blue-900">
                            <?php echo $selectedProduct ? 'Rp ' . number_format($selectedProduct['price'], 0, ',', '.') : ''; ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-sm text-blue-700">Satuan:</span>
                        <span id="productUnit" class="text-sm font-semibold text-blue-900">
                            <?php echo $selectedProduct ? $selectedProduct['unit'] : ''; ?>
                        </span>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">
                        Jumlah <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="quantity" 
                        id="quantity"
                        min="1"
                        required
                        onchange="calculateTotal()"
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        placeholder="Masukkan jumlah"
                    >
                </div>

                <!-- Total Estimate -->
                <div id="totalEstimate" class="mb-6 p-4 bg-green-50 border border-green-100 rounded-lg hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-green-900">Estimasi Total:</span>
                        <span id="totalPrice" class="text-2xl font-bold text-green-900"></span>
                    </div>
                    <p class="text-xs text-green-700 mt-1">* Harga dapat berubah setelah konfirmasi admin</p>
                </div>

                <!-- Note -->
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-slate-800 mb-2">
                        Catatan Tambahan
                    </label>
                    <textarea 
                        name="note" 
                        rows="4"
                        class="w-full px-4 py-3 border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        placeholder="Spesifikasi khusus, ukuran, warna, atau catatan lainnya..."
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex space-x-3">
                    <a href="dashboard.php" class="flex-1 px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg text-center transition duration-300">
                        Batal
                    </a>
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300 transform hover:scale-105">
                        Kirim Request
                    </button>
                </div>

            </form>
        </div>

        <!-- Info Box -->
        <div class="mt-8 p-6 bg-slate-50 border border-slate-200 rounded-xl">
            <h3 class="text-sm font-bold text-slate-900 mb-3">📋 Proses Request:</h3>
            <ol class="space-y-2 text-sm text-slate-600">
                <li class="flex items-start">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs font-bold mr-3 flex-shrink-0">1</span>
                    <span>Request Anda akan dikirim ke admin untuk direview</span>
                </li>
                <li class="flex items-start">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs font-bold mr-3 flex-shrink-0">2</span>
                    <span>Admin akan approve atau reject request Anda</span>
                </li>
                <li class="flex items-start">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs font-bold mr-3 flex-shrink-0">3</span>
                    <span>Jika disetujui, admin akan membuat Purchase Order untuk staff</span>
                </li>
                <li class="flex items-start">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full text-xs font-bold mr-3 flex-shrink-0">4</span>
                    <span>Staff akan memproses pesanan Anda hingga selesai</span>
                </li>
            </ol>
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

    <script>
        function updateProductInfo() {
            const select = document.getElementById('productSelect');
            const option = select.options[select.selectedIndex];
            const productInfo = document.getElementById('productInfo');
            
            if (option.value) {
                document.getElementById('productDescription').textContent = option.dataset.description;
                document.getElementById('productPrice').textContent = 'Rp ' + parseInt(option.dataset.price).toLocaleString('id-ID');
                document.getElementById('productUnit').textContent = option.dataset.unit;
                productInfo.classList.remove('hidden');
                calculateTotal();
            } else {
                productInfo.classList.add('hidden');
                document.getElementById('totalEstimate').classList.add('hidden');
            }
        }
        
        function calculateTotal() {
            const select = document.getElementById('productSelect');
            const option = select.options[select.selectedIndex];
            const quantity = document.getElementById('quantity').value;
            const totalEstimate = document.getElementById('totalEstimate');
            
            if (option.value && quantity > 0) {
                const price = parseInt(option.dataset.price);
                const total = price * quantity;
                document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
                totalEstimate.classList.remove('hidden');
            } else {
                totalEstimate.classList.add('hidden');
            }
        }
    </script>

</body>
</html>
