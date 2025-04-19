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
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        // Enable verbose debug output (set to 0 in production)
        $mail->SMTPDebug = 0;
        
        // Set mailer to use SMTP
        $mail->isSMTP();
        
        // Gmail SMTP server
        $mail->Host = 'smtp.gmail.com';
        
        // Enable SMTP authentication
        $mail->SMTPAuth = true;
        
        // SMTP username (your Gmail address)
        $mail->Username = 'itsnotalexx25@gmail.com';
        
        // SMTP password (your Gmail app password)
        $mail->Password = 'fxit obtd xjeu ezzy'; // Replace with your actual app password
        
        // Enable TLS encryption
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        
        // TCP port to connect to; use 587 for TLS
        $mail->Port = 587;
        
        // Set timeout to avoid long waiting times
        $mail->Timeout = 30;
        
        // Set the 'from' address and name
        $mail->setFrom('itsnotalexx25@gmail.com', 'MSWDO GLORIA');
        
        // Add a recipient
        $mail->addAddress($to_email, $to_name);
        
        // Set email format to HTML
        $mail->isHTML(true);
        
        // Set the subject
        $mail->Subject = $subject;
        
        // Set the HTML body
        $mail->Body = $html_message;
        
        // Set the plain text body as alternative
        if (empty($plain_message)) {
            // If no plain text is provided, create one by stripping HTML
            $plain_message = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html_message));
        }
        $mail->AltBody = $plain_message;
        
        // Send the email
        $mail->send();
        
        // Return success
        return [
            'success' => true,
            'error' => null
        ];
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Mailer Error: " . $mail->ErrorInfo);
        
        // Return failure with error message
        return [
            'success' => false,
            'error' => $mail->ErrorInfo
        ];
    }
}
