<?php
/**
 * File: staff/update_po.php
 * Fungsi: Update Status Purchase Order
 */

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/emailjs_sender.php';
require_once '../config/email.php';

requireStaff();

$user = getCurrentUser();
$pdo = getDBConnection();
$emailConfig = getEmailConfig();

$poId = $_GET['id'] ?? 0;

// Get PO detail
$stmt = $pdo->prepare("
    SELECT po.*, r.request_number, r.product_name, r.quantity, r.note
    FROM purchase_orders po
    JOIN requests r ON po.request_id = r.id
    WHERE po.id = ? AND po.staff_id = ?
");
$stmt->execute([$poId, $user['id']]);
$po = $stmt->fetch();

if (!$po || $po['status'] === 'done') {
    header('Location: dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'];
    
    if ($newStatus === 'process') {
        // Start working
        $stmt = $pdo->prepare("
            UPDATE purchase_orders 
            SET status = 'process', started_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$poId]);
        
        // Update request status
        $stmt = $pdo->prepare("UPDATE requests SET status = 'process' WHERE id = ?");
        $stmt->execute([$po['request_id']]);
        
        setFlashMessage('Status berhasil diupdate menjadi "Diproses"', 'success');
        
    } elseif ($newStatus === 'done') {
        // Complete work
        $stmt = $pdo->prepare("
            UPDATE purchase_orders 
            SET status = 'done', completed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$poId]);
        
        // Update request status
        $stmt = $pdo->prepare("UPDATE requests SET status = 'done' WHERE id = ?");
        $stmt->execute([$po['request_id']]);
        
        setFlashMessage('Pekerjaan selesai! Email notifikasi sedang dikirim ke pelanggan.', 'success');
        
        // Redirect to send email page
        header('Location: send_email.php?po_id=' . $poId);
        exit;
    }
    
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status PO - RollMate Staff</title>
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
    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header -->
        <div class="mb-8">
            <a href="view_po.php?id=<?php echo $poId; ?>" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Detail PO
            </a>
            <h1 class="text-4xl font-bold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent mb-2">
                Update Status Pekerjaan
            </h1>
            <code class="text-lg font-mono text-slate-600"><?php echo htmlspecialchars($po['po_number']); ?></code>
        </div>

        <!-- PO Summary -->
        <div class="bg-gradient-to-r from-purple-50 to-violet-50 rounded-xl border border-purple-200 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-semibold text-purple-900 mb-1">Produk</p>
                    <p class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($po['product_name']); ?></p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-purple-900 mb-1">Jumlah</p>
                    <p class="text-lg font-bold text-slate-900"><?php echo $po['quantity']; ?></p>
                </div>
                <?php if ($po['note']): ?>
                <div class="md:col-span-2">
                    <p class="text-sm font-semibold text-purple-900 mb-1">Catatan</p>
                    <p class="text-sm text-slate-700"><?php echo nl2br(htmlspecialchars($po['note'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Current Status -->
        <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-semibold text-blue-900">
                    Status Saat Ini: 
                    <span class="ml-2 px-3 py-1 bg-blue-100 rounded-full">
                        <?php echo $po['status'] === 'pending' ? 'Pending' : 'Diproses'; ?>
                    </span>
                </span>
            </div>
        </div>

        <!-- Update Form -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
            <form method="POST" action="">
                
                <!-- Status Selection -->
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-slate-800 mb-4">
                        Pilih Status Baru <span class="text-red-500">*</span>
                    </label>
                    
                    <div class="space-y-3">
                        <?php if ($po['status'] === 'pending'): ?>
                        <!-- Start Working -->
                        <label class="flex items-start p-4 border-2 border-purple-200 rounded-lg cursor-pointer hover:bg-purple-50 transition duration-150">
                            <input type="radio" name="status" value="process" required class="mt-1 mr-3">
                            <div class="flex-1">
                                <p class="text-base font-bold text-slate-900">Mulai Mengerjakan</p>
                                <p class="text-sm text-slate-600 mt-1">Tandai bahwa Anda sudah mulai mengerjakan PO ini</p>
                            </div>
                            <div class="ml-3 p-2 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </label>
                        <?php endif; ?>
                        
                        <!-- Complete Work -->
                        <label class="flex items-start p-4 border-2 border-green-200 rounded-lg cursor-pointer hover:bg-green-50 transition duration-150">
                            <input type="radio" name="status" value="done" required class="mt-1 mr-3">
                            <div class="flex-1">
                                <p class="text-base font-bold text-slate-900">Selesaikan Pekerjaan</p>
                                <p class="text-sm text-slate-600 mt-1">Tandai bahwa pekerjaan sudah selesai 100%</p>
                            </div>
                            <div class="ml-3 p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </label>
                    </div>
                </div>


                <!-- Info Box -->
                <div class="mb-8 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                    <h3 class="text-sm font-bold text-slate-900 mb-2">ℹ️ Informasi:</h3>
                    <ul class="space-y-1 text-sm text-slate-600">
                        <li>• Status akan otomatis diupdate di sistem</li>
                        <li>• <strong>Pelanggan akan menerima notifikasi email</strong> ketika status berubah menjadi selesai</li>
                        <li>• Pastikan pekerjaan sudah selesai sebelum mengubah status</li>
                    </ul>
                </div>

                <!-- Submit Buttons -->
                <div class="flex space-x-3">
                    <a href="view_po.php?id=<?php echo $poId; ?>" class="flex-1 px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg text-center transition duration-300">
                        Batal
                    </a>
                    <button type="submit" class="flex-1 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition duration-300 transform hover:scale-105">
                        Update Status
                    </button>
                </div>

            </form>
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
