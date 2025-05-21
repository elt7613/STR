<?php
/**
 * Email functions for STR Works - Vehicle module
 *
 * This file contains functions for sending vehicle-related emails from the application
 */

// Include necessary configs and the main email functionality
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email.php';

/**
 * Send a simple vehicle notification email
 *
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @return bool True if email was sent successfully, false otherwise
 */
function sendVehicleNotification($subject, $htmlBody) {
    // Use the main sendEmail function from the config/email.php file
    return sendEmail($subject, $htmlBody);
}

/**
 * Send a vehicle-related template email
 *
 * @param string $toEmail Recipient email address
 * @param string $subject Email subject
 * @param string $template Template name
 * @param array $variables Variables to replace in template
 * @return bool True if email was sent successfully, false otherwise
 */
function sendVehicleTemplateEmail($toEmail, $toName, $subject, $template, $variables = []) {
    // Path to email templates
    $templateFile = __DIR__ . '/../views/email_templates/' . $template . '.php';
    
    // Check if template exists
    if (!file_exists($templateFile)) {
        error_log("Email template not found: $templateFile");
        return false;
    }
    
    // Start output buffering to capture template content
    ob_start();
    
    // Extract variables into the current scope
    extract($variables);
    
    // Include the template
    include $templateFile;
    
    // Get the rendered content and clean the buffer
    $htmlBody = ob_get_clean();
    
    // Send the email using the main email system
    return sendEmailToCustomer($subject, $htmlBody, $toEmail, $toName);
}

/**
 * Send a vehicle submission notification email to admin
 *
 * @param int $userId User ID who submitted the vehicle info
 * @param array $vehicleData Vehicle information
 * @param int $imageCount Number of images uploaded
 * @return bool True if email was sent successfully, false otherwise
 */
function sendVehicleSubmissionEmail($userId, $vehicleData, $imageCount = 0) {
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    try {
        // Get user data
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        // Prepare email content
        $subject = "New Vehicle Information Submission";
        
        // Create an HTML email body
        $htmlBody = "<html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h2 { color: #f91c92; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                .user-info { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                .vehicle-info { margin-bottom: 20px; }
                .label { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>New Vehicle Information Submission</h2>
                
                <div class='user-info'>
                    <p><span class='label'>User:</span> {$user['username']}</p>
                    <p><span class='label'>Email:</span> {$user['email']}</p>
                </div>
                
                <div class='vehicle-info'>
                    <p><span class='label'>Vehicle Brand:</span> {$vehicleData['brand']}</p>
                    <p><span class='label'>Vehicle Model:</span> {$vehicleData['model']}</p>";
                    
        if (!empty($vehicleData['series'])) {
            $htmlBody .= "<p><span class='label'>Vehicle Series:</span> {$vehicleData['series']}</p>";
        }
        
        $htmlBody .= "<p><span class='label'>Number of Images:</span> {$imageCount}</p>
                </div>
                
                <p>You can view this submission in the admin panel.</p>
            </div>
        </body>
        </html>";
        
        // Use the main sendEmail function
        return sendEmail($subject, $htmlBody);
    } catch (PDOException $e) {
        error_log("Error sending vehicle submission email: " . $e->getMessage());
        return false;
    }
}
?>
