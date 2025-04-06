<?php
// Include the mailer file
include 'mailer.php';

// Test sending an email
$to_email = 'test@example.com'; // Replace with your test email
$to_name = 'Test User';
$subject = 'Test Email from MSWDO System';
$html_message = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #0c5c2f; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { padding: 20px; }
        .footer { background-color: #f5f5f5; padding: 10px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Test Email</h2>
        </div>
        <div class="content">
            <p>Dear Test User,</p>
            <p>This is a test email from the MSWDO System.</p>
            <p>If you received this email, the email functionality is working correctly.</p>
            <p>Best regards,<br>MSWDO Gloria Team</p>
        </div>
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>© 2024 MSWDO Gloria. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
';

// Send the test email
$result = sendEmail($to_email, $to_name, $subject, $html_message);

// Display the result
echo '<h1>Email Test Result</h1>';
echo '<pre>';
print_r($result);
echo '</pre>';

if ($result['success']) {
    echo '<div style="color: green; font-weight: bold;">Email sent successfully!</div>';
} else {
    echo '<div style="color: red; font-weight: bold;">Failed to send email. Check the error message above.</div>';
}
?>