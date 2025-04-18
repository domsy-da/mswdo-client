<?php
require 'PHPMailer/PHPMailerAutoload.php';

function sendEmail($to_email, $to_name, $subject, $html_message, $plain_message = '') {
    $mail = new PHPMailer;
    
    // Enable detailed debug output for troubleshooting
    // 0 = off, 1 = client messages, 2 = client and server messages
    $mail->SMTPDebug = 0; // Change to 2 temporarily to see detailed error messages
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'itsnotalexx25@gmail.com'; // Your Gmail
    $mail->Password = 'zmhq ckmn lsyh pjce'; // Replace with your new app password
    $mail->SMTPSecure = 'ssl'; // Change from 'tls' to 'ssl'
    $mail->Port = 465; // Change from 587 to 465
    
    // Set timeout values to avoid connection issues
    $mail->Timeout = 60; // seconds
    $mail->SMTPKeepAlive = true;
    
    // Connection options - these help bypass some certificate verification issues
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Sender info (must match your Gmail)
    $mail->setFrom('itsnotalexx25@gmail.com', 'MSWDO Gloria');
    $mail->addReplyTo('noreply@mswdo-gloria.com', 'Do Not Reply');
    
    // Recipient
    $mail->addAddress($to_email, $to_name);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $html_message;
    $mail->AltBody = $plain_message ?: strip_tags($html_message);
    
    // Attempt to send the email
    if(!$mail->send()) {
        error_log("Email failed to $to_email. Error: " . $mail->ErrorInfo);
        return [
            'success' => false,
            'message' => 'Email could not be sent',
            'error' => $mail->ErrorInfo
        ];
    }
    
    return ['success' => true, 'message' => 'Email sent successfully'];
}