<?php
// pages/public/logout.php

session_start();

// Store user data before destroying the session
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'User'; // Fallback to 'User' if names aren't set
$user_email = null; // Initialize

if ($user_id) {
    // Include database configuration
    require_once __DIR__ . '/../../config/database.php'; // Adjust path if necessary
    require_once __DIR__ . '/../../config/constants.php'; // Adjust path if necessary
    require_once __DIR__ . '/../../config/env.php'; // Adjust path if necessary

    try {
        $pdo = getPDO();
        // Fetch user email using the stored user_id
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?"); // Adjust 'id' and 'email' column names if different
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $user_email = $user_data['email'];
        } else {
            error_log("Logout: User data not found for ID: $user_id");
        }
    } catch (Exception $e) {
        error_log("Logout: Database error fetching user: " . $e->getMessage());
    }
}

// --- Send Logout Notification Email (only if user data was found) ---
if ($user_email) {
    // Include the email sending function
    require_once __DIR__ . '/../../api/backend/email.php';


    // Prepare email data using the 'logout_notification' template
    $emailData = [
        'to' => $user_email,
        'template' => 'logout_notification', // Use the specific template
        'variables' => [
            'user_name' => $user_name,
            // No specific variable for logout time in the template, but you could add it if desired
            // 'logout_time' => date('Y-m-d H:i:s T'), // Example variable
        ]
    ];

    // Attempt to send the email
    try {
        $emailResult = sendEmail($emailData);
        if ($emailResult['status'] !== 'success') {
            error_log("Logout: Failed to send notification email to {$user_email}. Error: " . ($emailResult['message'] ?? 'Unknown error'));
            // Decide: Log error, show message to user (if applicable), or just continue logout
            // For now, we'll just log the error and continue with logout.
        }
        // If successful, $emailResult['status'] will be 'success', but we don't need its output here.
    } catch (Exception $e) {
        error_log("Logout: Exception sending notification email to {$user_email}. Error: " . $e->getMessage());
        // Similar to above, log and continue logout.
    }
} else {
    error_log("Logout: Could not determine user email for ID: $user_id. Email notification skipped.");
    // Session destruction will still proceed.
}

// --- Destroy Session ---
// Unset all session variables
session_unset();

// Delete session cookie if it exists
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'], // Ensure secure flag matches session config
        $params['httponly'] // Ensure httponly flag matches session config
    );
}

// Destroy the session data
session_destroy();

// --- Redirect to login ---
$loginPage = '/login'; // Define the login page path, adjust if necessary
header("Location: $loginPage");
exit; // Always exit after a redirect
?>