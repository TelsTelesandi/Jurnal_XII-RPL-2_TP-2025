<?php
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $parts = explode('/', trim($script, '/'));
    $root = isset($parts[0]) ? '/' . $parts[0] . '/' : '/';
    define('BASE_URL', $protocol . '://' . $host . rtrim($root, '/') . '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>
    <?php if(isset($_SESSION['user_id'])): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-boxes me-2"></i>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>purchase_order_system/dashboard_admin.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>purchase_order_system/admin/manage_items.php"><i class="fas fa-box me-1"></i> Items</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>purchase_order_system/admin/manage_suppliers.php"><i class="fas fa-truck me-1"></i> Suppliers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>purchase_order_system/admin/incoming_items.php"><i class="fas fa-arrow-down me-1"></i> Incoming Items</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>purchase_order_system/admin/outgoing_items.php"><i class="fas fa-arrow-up me-1"></i> Outgoing Items</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>purchase_order_system/admin/report.php"><i class="fas fa-chart-bar me-1"></i> Reports</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>purchase_order_system/dashboard_production.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>purchase_order_system/production/request_item.php"><i class="fas fa-plus-circle me-1"></i> Request Items</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>purchase_order_system/production/history.php"><i class="fas fa-history me-1"></i> Request History</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); confirmLogout();"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            <form id="logoutForm" action="<?php echo BASE_URL; ?>purchase_order_system/logout.php" method="POST" style="display: none;">
                                <input type="hidden" name="logout" value="1">
                            </form>
                            <script>
                                function confirmLogout() {
                                    if (confirm('Apakah Anda yakin ingin keluar?')) {
                                        // Tampilkan loading spinner
                                        const spinner = document.createElement('div');
                                        spinner.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center';
                                        spinner.style.background = 'rgba(0,0,0,0.5)';
                                        spinner.style.zIndex = '9999';
                                        spinner.innerHTML = `
                                            <div class="text-center text-white">
                                                <div class="spinner-border text-light mb-2" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p>Sedang memproses logout...</p>
                                            </div>
                                        `;
                                        document.body.appendChild(spinner);
                                        
                                        // Submit the logout form
                                        document.getElementById('logoutForm').submit();
                                    }
                                }
                                
                                // Tangkap semua link logout dan tambahkan event listener
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Hapus semua session storage yang tidak perlu
                                    window.addEventListener('beforeunload', function() {
                                        // Hapus item yang tidak perlu dari sessionStorage
                                        ['formData', 'unsavedChanges'].forEach(item => {
                                            if (sessionStorage.getItem(item)) {
                                                sessionStorage.removeItem(item);
                                            }
                                        });
                                    });
                                });
                            </script>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    <div class="container-fluid py-4">
