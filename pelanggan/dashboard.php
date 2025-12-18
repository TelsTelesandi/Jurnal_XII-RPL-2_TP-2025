<?php
/**
 * File: pelanggan/dashboard.php
 * Fungsi: Dashboard Marketplace untuk Pelanggan
 * 
 * Tampilan seperti homepage TailwindCSS dengan hero section dan product grid
 */

require_once '../config/database.php';
require_once '../config/session.php';

// Proteksi halaman - hanya pelanggan yang bisa akses
requirePelanggan();

$user = getCurrentUser();
$pdo = getDBConnection();

// Ambil statistik pelanggan
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as process,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done
    FROM requests 
    WHERE customer_id = ?
");
$stmt->execute([$user['id']]);
$stats = $stmt->fetch();

// Ambil produk terbaru
$stmt = $pdo->query("
    SELECT * FROM products 
    WHERE is_active = 1 
    ORDER BY created_at DESC 
    LIMIT 6
");
$products = $stmt->fetchAll();

// Ambil request terbaru pelanggan
$stmt = $pdo->prepare("
    SELECT * FROM requests 
    WHERE customer_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$user['id']]);
$recentRequests = $stmt->fetchAll();

$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RollMate</title>
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
                    <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-sky-400 bg-clip-text text-transparent">
                        RollMate
                    </h1>
                    <div class="hidden md:flex space-x-6">
                        <a href="dashboard.php" class="text-sm font-semibold text-slate-900 border-b-2 border-blue-600 pb-1">
                            Produk
                        </a>
                        <a href="my_requests.php" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition duration-300">
                            Request Saya
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:block text-right">
                        <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($user['full_name']); ?></p>
                        <p class="text-xs text-slate-500">Pelanggan</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-sky-400 rounded-full flex items-center justify-center text-white font-bold">
                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                    </div>
                    <a href="../logout.php" class="text-sm font-medium text-slate-600 hover:text-red-600 transition duration-300">
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-5xl sm:text-6xl font-bold tracking-tight mb-6">
                    <span class="block bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 bg-clip-text text-transparent">
                        Solusi Pemesanan Laminating
                    </span>
                    <span class="block bg-gradient-to-r from-blue-600 to-sky-400 bg-clip-text text-transparent">
                        yang Cepat dan Efisien
                    </span>
                </h1>
                <p class="text-xl text-slate-600 mb-10 leading-relaxed">
                    Pesan produk laminasi berkualitas tinggi dengan proses yang mudah dan transparan. 
                    Lacak status pesanan Anda secara real-time.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#products" class="inline-flex items-center justify-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Lihat Produk
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                    <a href="my_requests.php" class="inline-flex items-center justify-center px-8 py-3 bg-white hover:bg-slate-50 text-slate-900 font-semibold rounded-lg border border-slate-300 transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                        Request Saya
                    </a>
                </div>
            </div>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-0 left-0 -z-10 w-full h-full">
            <div class="absolute top-20 left-10 w-72 h-72 bg-blue-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute top-40 right-10 w-72 h-72 bg-sky-100 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="border-y border-slate-200 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <p class="text-4xl font-bold text-slate-900"><?php echo $stats['total_requests']; ?></p>
                    <p class="text-sm font-medium text-slate-600 mt-2">Total Request</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                    <p class="text-sm font-medium text-slate-600 mt-2">Menunggu</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-bold text-blue-600"><?php echo $stats['process']; ?></p>
                    <p class="text-sm font-medium text-slate-600 mt-2">Diproses</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-bold text-green-600"><?php echo $stats['done']; ?></p>
                    <p class="text-sm font-medium text-slate-600 mt-2">Selesai</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent mb-4">
                    Katalog Produk Kami
                </h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    Pilih dari berbagai jenis produk laminasi berkualitas tinggi untuk kebutuhan bisnis Anda
                </p>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($products as $product): ?>
                <div class="group bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-lg hover:border-blue-300 transition duration-300 ease-in-out">
                    <!-- Product Image -->
                    <div class="h-56 bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center overflow-hidden">
                        <?php if (!empty($product['image']) && file_exists('../' . $product['image'])): ?>
                            <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <?php else: ?>
                            <svg class="w-24 h-24 text-slate-300 group-hover:text-blue-400 transition duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                <?php echo htmlspecialchars($product['category']); ?>
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2 group-hover:text-blue-600 transition duration-300">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h3>
                        <p class="text-sm text-slate-600 mb-4 line-clamp-2">
                            <?php echo htmlspecialchars($product['description']); ?>
                        </p>
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-3xl font-bold text-slate-900">
                                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                </p>
                                <p class="text-xs text-slate-500 mt-1">per <?php echo htmlspecialchars($product['unit']); ?></p>
                            </div>
                            <a href="request_form.php?product_id=<?php echo $product['id']; ?>" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition duration-300 ease-in-out transform hover:scale-105">
                                Pesan
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Recent Requests Section -->
    <?php if (count($recentRequests) > 0): ?>
    <section class="bg-slate-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 mb-4">
                    Request Terbaru Anda
                </h2>
                <p class="text-lg text-slate-600">
                    Pantau status pesanan Anda secara real-time
                </p>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">No. Request</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Produk</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php foreach ($recentRequests as $req): ?>
                            <tr class="hover:bg-slate-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono text-slate-900"><?php echo htmlspecialchars($req['request_number']); ?></code>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($req['product_name']); ?></p>
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
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 text-center">
                    <a href="my_requests.php" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition duration-300">
                        Lihat Semua Request →
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <p class="text-center text-sm text-slate-500">
                &copy; 2024 RollMate. Sistem Marketplace Laminasi Industri.
            </p>
        </div>
    </footer>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>

</body>
</html>
