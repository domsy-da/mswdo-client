<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// If not using Composer, manually include the PHPMailer files
// Make sure these paths are correct for your server
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

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
        // Debugging - set to 0 in production after testing
        $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->SMTPAuth = true;
        
        // Credentials - USE ACTUAL VALUES OR ENVIRONMENT VARIABLES
        $mail->Username = 'itsnotalexx25@gmail.com'; // Must match authenticated account
        $mail->Password = 'qhowygajsdahqajj'; // Replace with actual app password
        
        // Sender must match authenticated account or be properly delegated
        $mail->setFrom('itsnotalexx25@gmail.com', 'MSWDO Gloria');
        $mail->addReplyTo('itsnotalexx25@gmail.com', 'MSWDO Information');
        
        // Recipient
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_message;
        $mail->AltBody = $plain_message ?: strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html_message));
        
        // Reduce timeout for faster failure
        $mail->Timeout = 10;
        
        if (!$mail->send()) {
            throw new Exception('Send failed');
        }
        
        return ['success' => true, 'error' => null];
        
    } catch (Exception $e) {
        $error = $mail->ErrorInfo ?: $e->getMessage();
        error_log("[Email Error] " . date('Y-m-d H:i:s') . " - Error: $error");
        
        return [
            'success' => false,
            'error' => 'Email service temporarily unavailable'
        ];
    }
}