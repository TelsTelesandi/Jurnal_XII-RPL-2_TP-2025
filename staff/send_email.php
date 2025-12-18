<?php
/**
 * Email Sender Page - Redirect after PO completion
 * This page sends email via EmailJS and then redirects to dashboard
 */

require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/emailjs_sender.php';

requireStaff();

$poId = $_GET['po_id'] ?? 0;
$pdo = getDBConnection();

// Generate email script
$emailScript = '';
if ($poId) {
    $emailScript = sendPOCompletionEmailJS($pdo, $poId);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mengirim Email...</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">
    
    <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-purple-600 mb-4"></div>
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Mengirim Email Notifikasi...</h2>
        <p class="text-slate-600">Mohon tunggu sebentar, email sedang dikirim ke pelanggan.</p>
    </div>

    <!-- EmailJS Script -->
    <?php echo $emailScript; ?>
    
    <!-- Auto redirect after 3 seconds -->
    <script>
        setTimeout(function() {
            window.location.href = 'dashboard.php';
        }, 3000);
    </script>
    
</body>
</html>
