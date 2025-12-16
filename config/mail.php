<?php
function getMailConfig() {
    $localPath = __DIR__ . '/mail.local.php';
    if (file_exists($localPath)) {
        $local = include $localPath;
        if (is_array($local) && !empty($local['host'])) {
            return $local;
        }
    }

    $host = 'smtp.gmail.com';
    $port = 587;
    $username = 'raadeveloperz@gmail.com';
    $password = 'jljiijlrggmsadqd';
    $secure = 'tls';
    $from = getenv('MAIL_FROM') ?: 'noreply@cvjurnal.com';
    $fromName = getenv('MAIL_FROM_NAME') ?: 'Admin CV Jurnal';
    $replyTo = getenv('MAIL_REPLY_TO') ?: '';

    return [
        'host' => $host,
        'port' => $port,
        'username' => $username,
        'password' => $password,
        'secure' => $secure,
        'from' => $from,
        'from_name' => $fromName,
        'reply_to' => $replyTo,
    ];
}
?>
