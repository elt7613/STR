<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer library
require ROOT_PATH . '/vendor/autoload.php';

function sendEmail($subject, $htmlBody, $plainTextBody = '') {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  
        $mail->SMTPAuth   = true;
        $mail->Username   = 'djangochatbox@gmail.com';
        $mail->Password   = 'mbmk cavq qzpv gqai'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;  

        $adminEmail = "xaioene@gmail.com";

        $mail->setFrom('djangochatbox@gmail.com', 'NetPy');

        $mail->addAddress($adminEmail, "Admin"); 

        // Email content
        $mail->isHTML(true); 
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainTextBody ?: strip_tags($htmlBody);

        // Send the email
        $mail->send();
        return 'Email sent successfully!';
    } catch (Exception $e) {
        return "Failed to send email. Error: {$mail->ErrorInfo}";
    }
}
