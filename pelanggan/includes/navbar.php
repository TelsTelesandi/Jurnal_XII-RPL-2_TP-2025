<!-- Navbar -->
<nav class="border-b border-slate-200 bg-white/80 backdrop-blur-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-8">
                <img src="../gambar/logo pt.png" alt="Logo PT" class="h-10 w-auto">
                <div class="hidden md:flex space-x-6">
                    <a href="dashboard.php" class="text-sm font-<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'semibold text-slate-900 border-b-2 border-blue-600 pb-1' : 'medium text-slate-600 hover:text-slate-900'; ?> transition duration-300">
                        Produk
                    </a>
                    <a href="my_requests.php" class="text-sm font-<?php echo basename($_SERVER['PHP_SELF']) === 'my_requests.php' ? 'semibold text-slate-900 border-b-2 border-blue-600 pb-1' : 'medium text-slate-600 hover:text-slate-900'; ?> transition duration-300">
                        Request Saya
                    </a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="hidden sm:block text-right">
                    <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($user['full_name']); ?></p>
                    <p class="text-xs text-slate-500">Pelanggan</p>
                </div>
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-400 rounded-full flex items-center justify-center text-white font-bold">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <a href="../logout.php" class="text-sm font-medium text-slate-600 hover:text-red-600 transition duration-300">
                    Keluar
                </a>
            </div>
        </div>
    </div>
</nav>
