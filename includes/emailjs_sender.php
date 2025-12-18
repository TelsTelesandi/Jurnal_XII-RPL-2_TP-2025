<?php
/**
 * EmailJS Sender - Send notification via EmailJS
 */

require_once __DIR__ . '/../config/email.php';

/**
 * Generate JavaScript code to send email via EmailJS
 * 
 * @param array $emailData Email data (to_email, to_name, subject, message)
 * @return string JavaScript code to execute
 */
function generateEmailJSCode($emailData) {
    $config = getEmailConfig();
    
    if (!$config['enabled'] || $config['provider'] !== 'emailjs') {
        return '';
    }
    
    $serviceId = $config['emailjs']['service_id'];
    $templateId = $config['emailjs']['template_id'];
    $publicKey = $config['emailjs']['public_key'];
    
    // Encode data untuk JavaScript
    $params = [
        'to_email' => $emailData['to_email'],
        'to_name' => $emailData['to_name'],
        'subject' => $emailData['subject'],
        'message' => $emailData['message'],
        'from_name' => 'RollMate System'
    ];
    
    $paramsJson = json_encode($params);
    
    return "
    <script src='https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js'></script>
    <script>
        (function() {
            emailjs.init('{$publicKey}');
            
            emailjs.send('{$serviceId}', '{$templateId}', {$paramsJson})
                .then(function(response) {
                    console.log('Email sent successfully!', response.status, response.text);
                }, function(error) {
                    console.error('Email failed to send:', error);
                });
        })();
    </script>
    ";
}

/**
 * Send PO completion notification via EmailJS
 */
function sendPOCompletionEmailJS($pdo, $poId) {
    $config = getEmailConfig();
    
    // Check if email is enabled
    if (!$config['enabled'] || $config['provider'] !== 'emailjs') {
        return '';
    }
    
    // Get PO and customer details
    $stmt = $pdo->prepare("
        SELECT po.*, r.request_number, r.product_name, r.quantity,
               u.full_name, u.email, u.company_name
        FROM purchase_orders po
        JOIN requests r ON po.request_id = r.id
        JOIN users u ON r.customer_id = u.id
        WHERE po.id = ?
    ");
    $stmt->execute([$poId]);
    $po = $stmt->fetch();

    if (!$po || !$po['email']) {
        error_log("PO not found or customer email missing: " . $poId);
        return '';
    }

    $subject = "✅ Pesanan #{$po['request_number']} Selesai & Siap Diambil!";
    
    $message = "Halo {$po['full_name']},\n\n";
    $message .= "🎉 Kabar gembira! Pesanan Anda telah selesai diproduksi dan siap untuk diambil.\n\n";
    $message .= "DETAIL PESANAN:\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $message .= "Nomor PO       : {$po['po_number']}\n";
    $message .= "Nomor Request  : {$po['request_number']}\n";
    $message .= "Produk         : {$po['product_name']}\n";
    $message .= "Jumlah         : {$po['quantity']} unit\n";
    $message .= "Total Nilai    : Rp " . number_format($po['total_amount'], 0, ',', '.') . "\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    $message .= "✅ STATUS: SELESAI - SIAP DIAMBIL\n\n";
    $message .= "📦 LANGKAH SELANJUTNYA:\n";
    $message .= "Silakan hubungi kami untuk mengatur jadwal pengambilan pesanan Anda.\n\n";
    $message .= "⏰ PENTING: Harap ambil pesanan Anda dalam waktu 7 hari kerja.\n\n";
    $message .= "Terima kasih telah mempercayai RollMate untuk kebutuhan laminasi industri Anda!\n\n";
    $message .= "Salam hangat,\nTim RollMate\n\n";
    $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $message .= "📧 Email ini dikirim secara otomatis dari sistem RollMate\n";
    $message .= "© 2024 RollMate Marketplace System";

    $emailData = [
        'to_email' => $po['email'],
        'to_name' => $po['full_name'],
        'subject' => $subject,
        'message' => $message
    ];

    return generateEmailJSCode($emailData);
}
