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
 * Function to send email using PHPMailer with Gmail SMTP
 */
function sendEmail($to_email, $to_name, $subject, $html_message, $plain_message = '') {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'alexandersiasatmain@gmail.com'; // Your Gmail
        $mail->Password = 'zwzs oguu fsjh xkmh'; // Use App Password, not regular password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 465;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;// Enable verbose debug output
        
        // Bypass SSL verification (for localhost only)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        // Sender (must match your Gmail)
        $mail->setFrom('alexandersiasatmain@gmail.com', 'MSWDO Gloria');
        $mail->addReplyTo('noreply@mswdo-gloria.com', 'MSWDO Do Not Reply');
        
        // Recipient
        $mail->addAddress($to_email, $to_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_message;
        $mail->AltBody = $plain_message ?: strip_tags($html_message);
        
        // Add DKIM signing (helps with delivery)
        $mail->DKIM_domain = 'gmail.com';
        $mail->DKIM_selector = 'google';
        $mail->DKIM_passphrase = '';
        
        if (!$mail->send()) {
            throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
        }
        
        return ['success' => true, 'message' => 'Email sent successfully'];
        
    } catch (Exception $e) {
        // Log the complete error for debugging
        error_log("Email failed to $to_email. Error: " . $e->getMessage());
        
        return [
            'success' => false,
            'message' => "Email could not be sent. Please try again later.",
            'error' => $e->getMessage() // Only show this in development
        ];
    }
}

/**
 * Test function - uncomment to verify your setup
 */
/*
function testEmail() {
    $test_email = 'recipient@example.com'; // Change to a real email
    $result = sendEmail(
        $test_email,
        'Test User',
        'MSWDO System Test',
        '<h1>Test Email</h1><p>This confirms your email settings are working.</p>',
        'Test Email - Text Version'
    );
    
    echo '<pre>';
    print_r($result);
    echo '</pre>';
    
    if ($result['success']) {
        echo "Check the inbox of $test_email for the test message.";
    } else {
        echo "Failed to send. Error: " . $result['error'];
    }
}

testEmail(); // Run the test
*/
?>