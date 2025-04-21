<?php
/**
 * Vehicle Form Page
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';
// Include email functionality
require_once ROOT_PATH . '/app/config/email.php';

// Process form submission
$error = '';
$success = '';
$makes = getAllVehicleMakes();
$models = [];
$series = [];
$selectedMake = null;
$selectedModel = null;
$selectedSeries = null;
$noModelsFound = false;
$noSeriesFound = false;

// Process GET parameters for initial form state (if coming back to the form)
if (isset($_GET['make']) && !empty($_GET['make'])) {
    $selectedMake = (int)$_GET['make'];
    $models = getVehicleModelsByMake($selectedMake);
    
    // Check if no models were found
    if (empty($models)) {
        $noModelsFound = true;
    } else {
        if (isset($_GET['model']) && !empty($_GET['model'])) {
            $selectedModel = (int)$_GET['model'];
            $series = getVehicleSeriesByModel($selectedModel);
            
            // Check if no series were found
            if (empty($series)) {
                $noSeriesFound = true;
            }
        }
    }
}

// Process form submission - require login to submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_vehicle'])) {
        // Check if user is logged in before processing submission
        if (!isLoggedIn()) {
            $error = 'You must be logged in to submit the form. <a href="index.php">Login here</a>';
        } else {
            $makeId = $_POST['make'] ?? '';
            $modelId = $_POST['model'] ?? '';
            $seriesId = $_POST['series'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            
            // Validate submission
            if (empty($makeId)) {
                $error = 'Please select a vehicle make.';
            } elseif (empty($modelId)) {
                $error = 'Please select a vehicle model.';
            } elseif (empty($seriesId)) {
                $error = 'Please select a vehicle series.';
            } else {
                $result = saveVehicleSubmission($_SESSION['user_id'], $makeId, $modelId, $seriesId, $phone, $email);
                
                if ($result['success']) {
                    $success = $result['message'];
                    
                    // Set selected values for the form
                    $selectedMake = (int)$makeId;
                    $selectedModel = (int)$modelId;
                    $selectedSeries = (int)$seriesId;
                    $models = getVehicleModelsByMake($selectedMake);
                    $series = getVehicleSeriesByModel($selectedModel);
                    
                    // Send email notification to admin
                    $makeName = getVehicleMakeName($makeId);
                    $modelName = getVehicleModelName($modelId);
                    $seriesName = getVehicleSeriesName($seriesId);
                    
                    // Get user information
                    $userId = $_SESSION['user_id'];
                    // Check if username exists in session
                    $username = isset($_SESSION['username']) ? $_SESSION['username'] : getUsernameById($userId);
                    $userEmail = $email; // Use the email from the form submission
                    
                    // Prepare email content
                    $subject = "New Vehicle Form Submission";
                    $htmlBody = "
                        <h2>New Vehicle Form Submission</h2>
                        <p><strong>User:</strong> {$username} (ID: {$userId})</p>
                        <p><strong>User Email:</strong> {$userEmail}</p>
                        <p><strong>Vehicle Details:</strong></p>
                        <p style='margin-left:20px;'><strong>Make:</strong> {$makeName}</p>
                        <p style='margin-left:20px;'><strong>Model:</strong> {$modelName}</p>
                        <p style='margin-left:20px;'><strong>Series:</strong> {$seriesName}</p>
                        <p><strong>Contact Information:</strong></p>
                        <p style='margin-left:20px;'><strong>Phone:</strong> {$phone}</p>
                        <p style='margin-left:20px;'><strong>Email:</strong> {$userEmail}</p>
                        <p><strong>Submission Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                    ";
                    
                    // Plain text alternative
                    $plainText = "
                        New Vehicle Form Submission
                        
                        User: {$username} (ID: {$userId})
                        User Email: {$userEmail}
                        
                        Vehicle Details:
                        Make: {$makeName}
                        Model: {$modelName}
                        Series: {$seriesName}
                        
                        Contact Information:
                        Phone: {$phone}
                        Email: {$userEmail}
                        
                        Submission Time: " . date('Y-m-d H:i:s') . "
                    ";
                    
                    // Send the email using the sendEmail function from email.php
                    $emailResult = sendEmail($subject, $htmlBody, $plainText);
                    
                    // Add email status to success message
                    if (strpos($emailResult, 'successfully') !== false) {
                        $success .= ' Admin has been notified about your submission.';
                    }
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
}

// Include vehicle form view
require_once ROOT_PATH . '/app/views/vehicle_form.php';
?> 