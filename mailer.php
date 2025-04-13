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
 * @param string 
 * @param string 
 * @param string 
 * @param string 
 * @param string 
 * @return array 
 */
function sendEmail($to_email, $to_name, $subject, $html_message, $plain_message = '') {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();                                      // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                 // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                             
        
        // SMTP authentication credentials - this is for the SENDING account only
        $mail->Username   = 'alexandersiasatmain@gmail.com';          
        $mail->Password   = 'etxj llae shxv aest';            
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   
        $mail->Port       = 587;                              
        
      
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom('gloriamswdo@gmail.com', 'MSWDO Gloria');
        $mail->addAddress($to_email, $to_name);  // This is where the dynamic user email is used
        
        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $html_message;
        
        // Set plain text version if provided, otherwise strip HTML tags
        if (empty($plain_message)) {
            $mail->AltBody = strip_tags($html_message);
        } else {
            $mail->AltBody = $plain_message;
        }
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"];
    }
}
?>

