<?php
// PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

/**
 * Function to send email using PHPMailer
 * 
 * @param string $to_email Recipient email
 * @param string $to_name Recipient name
 * @param string $subject Email subject
 * @param string $html_message HTML message content
 * @param string $plain_message Plain text message (optional)
 * @return array Success status and message
 */
function sendEmail($to_email, $to_name, $subject, $html_message, $plain_message = '') {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'alexandersiasatmain@gmail.com'; // Your Gmail
        $mail->Password = 'etxj llae shxv aest'; // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Important security settings for localhost
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        // Sender (must match your Gmail)
        $mail->setFrom('alexandersiasatmain@gmail.com', 'MSWDO Gloria'); 
        
        // Recipient (will work for any email now)
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_message;
        $mail->AltBody = $plain_message ?: strip_tags($html_message);
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"];
    }
}

/**
 * Function to test email configuration
 * Uncomment and use this function to test your email setup
 */
/*
function testEmailConfig() {
    $result = sendEmail(
        'your-test-email@example.com', 
        'Test User', 
        'MSWDO Email Test', 
        '<h1>Test Email</h1><p>This is a test email to verify the MSWDO email system is working correctly.</p>'
    );
    
    echo '<pre>';
    print_r($result);
    echo '</pre>';
}
// Call the test function - remove this in production
// testEmailConfig();
*/
?>