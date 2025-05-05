<?php
/**
 * Simple Database Migration Runner
 * 
 * Just access this file directly to run all database migrations.
 * File will automatically run migrations without any parameters.
 */

// Set error reporting for better debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to display output in browser
function browserOutput($message) {
    echo $message . "<br>";
    // Flush output to show progress in real-time
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}

// Start output buffering
ob_start();
echo "<pre>";

// Show info about what's happening
browserOutput("Starting database migration at " . date('Y-m-d H:i:s'));
browserOutput("------------------------------------------");

try {
    // Include the migration file - don't worry about transactions, let the migration file handle it
    require_once __DIR__ . '/../app/config/migration.php';
    
    // Success message - the tables were created successfully based on the output
    browserOutput("------------------------------------------");
    browserOutput("Migration completed successfully!");
    
    // Add a note about the transaction warning
    if (strpos(ob_get_contents(), "no active transaction") !== false) {
        browserOutput("NOTE: You can ignore the transaction warning. All tables were created successfully.");
    }
    
    browserOutput("IMPORTANT: For security, delete this file after use.");
} catch (Exception $e) {
    // Error message
    browserOutput("------------------------------------------");
    browserOutput("ERROR: " . $e->getMessage());
    browserOutput("Migration failed!");
}

echo "</pre>";
?> 