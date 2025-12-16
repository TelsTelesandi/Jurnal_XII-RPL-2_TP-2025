<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

function sendMail($to, $subject, $htmlBody, $altBody = null) {
    $cfg = getMailConfig();
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $cfg['host'];
        $mail->Port = $cfg['port'];
        if (!empty($cfg['username'])) {
            $mail->SMTPAuth = true;
            $mail->Username = $cfg['username'];
            $mail->Password = $cfg['password'];
        } else {
            $mail->SMTPAuth = false;
        }
        if (!empty($cfg['secure'])) {
            $mail->SMTPSecure = $cfg['secure'];
        }

        $mail->setFrom($cfg['from'], $cfg['from_name']);
        if (!empty($cfg['reply_to'])) {
            $mail->addReplyTo($cfg['reply_to']);
        }

        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody ?? strip_tags($htmlBody);

        return $mail->send();
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $e->getMessage());
        return false;
    }
}
?>
