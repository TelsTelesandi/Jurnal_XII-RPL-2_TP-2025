<?php
/**
 * Email Configuration for RollMate
 * 
 * OPTION 1: EmailJS (Recommended - Easy Setup)
 * OPTION 2: PHPMailer with SMTP
 */

function getEmailConfig() {
    return [
        // Email Provider: 'emailjs' or 'phpmailer'
        'provider' => 'emailjs',
        
        // EmailJS Configuration (https://www.emailjs.com/)
        'emailjs' => [
            'service_id' => 'service_t6s67s3',      // Dari EmailJS dashboard
            'template_id' => 'template_16ih3ld',    // Dari EmailJS dashboard
            'public_key' => 'fm9vRIEm_8B6Opv6W',      // Dari EmailJS dashboard
        ],
        
        // PHPMailer SMTP Configuration (Alternative)
        'smtp' => [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'your-email@gmail.com',
            'password' => 'your-app-password',
            'from_email' => 'noreply@rollmate.com',
            'from_name' => 'RollMate System'
        ],
        
        'enabled' => true,  // Set false untuk disable email sementara
    ];
}
