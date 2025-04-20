<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Absolute path to vendor autoload
require __DIR__ . '/vendor/autoload.php';

// OR for manual installation - adjust path as needed
// require __DIR__ . '/PHPMailer/src/Exception.php';
// require __DIR__ . '/PHPMailer/src/PHPMailer.php';
// require __DIR__ . '/PHPMailer/src/SMTP.php';

/**
 * Send an email using PHPMailer
 * 
 * @param string $to_email Recipient email address
 * @param string $to_name Recipient name
 * @param string $subject Email subject
 * @param string $html_message HTML content of the email
 * @param string $plain_message Plain text content of the email
 * @return array Success status and error message if any
 */
function sendEmail($to_email, $to_name, $subject, $html_message, $plain_message = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPAuth = true;
        $mail->Username = 'itsnotalexx25@gmail.com';
        $mail->Password = 'your_app_password';
        
        // Sender
        $mail->setFrom('itsnotalexx25@gmail.com', 'MSWDO Gloria');
        $mail->addReplyTo('itsnotalexx25@gmail.com', 'MSWDO Information');
        
        // Recipient
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_message;
        $mail->AltBody = $plain_message ?: strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html_message));
        
        $mail->send();
        return ['success' => true, 'error' => null];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Mailer Error: ' . $e->getMessage()
        ];
    }
}