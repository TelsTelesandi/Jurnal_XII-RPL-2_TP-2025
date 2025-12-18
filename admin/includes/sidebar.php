<!-- Sidebar -->
<aside class="hidden md:flex md:flex-shrink-0">
    <div class="flex flex-col w-64 border-r border-slate-200 bg-slate-900">
        <!-- Logo -->
        <div class="flex items-center h-16 flex-shrink-0 px-6 border-b border-slate-800">
            <img src="../gambar/logo pt.png" alt="Logo PT" class="h-10 w-auto">
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center px-3 py-2 text-sm font-<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'semibold text-white bg-slate-800' : 'medium text-slate-300 hover:text-white hover:bg-slate-800'; ?> rounded-lg transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
            </a>
            <a href="requests.php" class="flex items-center px-3 py-2 text-sm font-<?php echo basename($_SERVER['PHP_SELF']) === 'requests.php' ? 'semibold text-white bg-slate-800' : 'medium text-slate-300 hover:text-white hover:bg-slate-800'; ?> rounded-lg transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Request Masuk
                <?php
                $pdo = getDBConnection();
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM requests WHERE status = 'pending'");
                $pendingCount = $stmt->fetch()['count'];
                if ($pendingCount > 0):
                ?>
                <span class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-500 text-white">
                    <?php echo $pendingCount; ?>
                </span>
                <?php endif; ?>
            </a>
            <a href="purchase_orders.php" class="flex items-center px-3 py-2 text-sm font-<?php echo basename($_SERVER['PHP_SELF']) === 'purchase_orders.php' ? 'semibold text-white bg-slate-800' : 'medium text-slate-300 hover:text-white hover:bg-slate-800'; ?> rounded-lg transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Purchase Orders
            </a>
            <a href="products.php" class="flex items-center px-3 py-2 text-sm font-<?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'semibold text-white bg-slate-800' : 'medium text-slate-300 hover:text-white hover:bg-slate-800'; ?> rounded-lg transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                Produk
            </a>
            <a href="users.php" class="flex items-center px-3 py-2 text-sm font-<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'semibold text-white bg-slate-800' : 'medium text-slate-300 hover:text-white hover:bg-slate-800'; ?> rounded-lg transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Kelola User
            </a>
            <a href="reports.php" class="flex items-center px-3 py-2 text-sm font-<?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'semibold text-white bg-slate-800' : 'medium text-slate-300 hover:text-white hover:bg-slate-800'; ?> rounded-lg transition duration-150">
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
