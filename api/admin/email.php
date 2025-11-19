<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Start session to check admin status
session_start();

// --- DEPENDENCY INCLUSION ---
// 1. Include the file that defines getEmailTemplates()
// Corrected path for email_temps.php (located in api/utilities/)
require_once '../utilities/email_temps.php'; 

// 2. Mock functions for admin and user data. These MUST be replaced 
// with real database logic in a production environment.

function is_admin_logged_in() {
    // Replace with your actual admin login check logic
    return isset($_SESSION['admin_id']);
}

function getRecipientsByGroup($group, $userId = null) {
    // --- MOCK DATABASE FETCH ---
    // Replace this with real database query logic to fetch user emails/names.
    // NOTE: For testing, ensure these emails are valid and accessible by you.
    $allUsers = [
        ['email' => 'user1@example.com', 'name' => 'Alice Smith', 'is_investor' => false, 'id' => 1],
        ['email' => 'user2@example.com', 'name' => 'Bob Johnson', 'is_investor' => true, 'is_donor' => false, 'id' => 2],
        ['email' => 'user3@example.com', 'name' => 'Charlie Brown', 'is_donor' => true, 'is_investor' => true, 'id' => 3],
        // IMPORTANT: Use a real, personal email here for successful testing
        ['email' => 'admin_test@healthruncare.com', 'name' => 'Admin Test User', 'is_investor' => true, 'is_donor' => true, 'id' => 4],
    ];

    $recipients = [];

    switch ($group) {
        case 'all':
            $recipients = $allUsers;
            break;
        case 'active':
            // In a real app, this would query for users with recent activity
            $recipients = $allUsers; 
            break;
        case 'investors':
            $recipients = array_filter($allUsers, fn($u) => $u['is_investor'] ?? false);
            break;
        case 'donors':
            $recipients = array_filter($allUsers, fn($u) => $u['is_donor'] ?? false);
            break;
        case 'specific':
            if ($userId) {
                // Find user by ID (using mock data: ID is 1-based index + 1)
                $targetUser = array_values(array_filter($allUsers, fn($u) => ($u['id'] ?? 0) == $userId))[0] ?? null; 
                if ($targetUser) {
                    $recipients = [$targetUser];
                }
            }
            break;
        default:
            $recipients = [];
    }
    return $recipients;
}

// ====================================================================
// FIX: IMPLEMENT ACTUAL EMAIL SENDING USING PHP'S mail() FUNCTION
// ====================================================================
function sendMail($toEmail, $subject, $htmlBody, $priority = 'normal') {
    // IMPORTANT: For this to work, your local PHP environment (e.g., Laragon/XAMPP) 
    // must be configured to send mail (e.g., via fake sendmail or an actual SMTP service).
    
    $fromEmail = 'support@healthruncare.com';
    $appName = 'HealthRunCare Administration';
    
    // Set headers for HTML content, UTF-8, and Sender details
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    
    // Set From and Reply-To headers
    $headers .= "From: {$appName} <{$fromEmail}>" . "\r\n";
    $headers .= "Reply-To: {$fromEmail}" . "\r\n";

    // Handle Priority header
    if ($priority === 'high') {
        $headers .= "X-Priority: 1 (Highest)\r\n";
        $headers .= "X-MSMail-Priority: High\r\n";
        $headers .= "Importance: High\r\n";
    }

    // Use PHP's built-in mail function
    $mail_sent = mail($toEmail, $subject, $htmlBody, $headers);

    if ($mail_sent) {
        error_log("REAL EMAIL SENT: To: {$toEmail}, Subject: {$subject}, Priority: {$priority}");
        return true;
    } else {
        error_log("EMAIL SEND FAILED: To: {$toEmail}, Subject: {$subject}, Priority: {$priority}. Check PHP mail configuration!");
        return false;
    }
}
// ====================================================================

// --- INITIAL VALIDATION ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed.']);
    exit;
}

if (!is_admin_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in as admin.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Get and sanitize inputs
$recipient_group = $input['recipient_group'] ?? '';
$user_id = $input['user_id'] ?? null; // Only for 'specific' group
$subject = htmlspecialchars($input['subject'] ?? '');
// Convert newlines (\n) to <br> tags so the message body respects line breaks in the HTML email
$body = nl2br(htmlspecialchars($input['body'] ?? '')); 
$priority = $input['priority'] ?? 'normal';


if (empty($recipient_group) || empty($subject) || empty($body)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields (Recipient Group, Subject, or Message Body).']);
    exit;
}

if ($recipient_group === 'specific' && (empty($user_id) || !is_numeric($user_id))) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'A valid numeric User ID is required for a specific recipient.']);
    exit;
}

// --- CORE LOGIC ---

try {
    $templates = getEmailTemplates();
    $template = $templates['admin_broadcast'] ?? null;

    if (!$template) {
        throw new Exception("Email template 'admin_broadcast' not found. Ensure it is defined in email_temps.php.");
    }
    
    // 1. Get recipients
    $recipients = getRecipientsByGroup($recipient_group, $user_id);
    $total_recipients = count($recipients);

    if ($total_recipients === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No recipients found for the selected group/ID.']);
        exit;
    }

    $emails_sent_count = 0;
    
    // 2. Prepare and send email to each recipient
    foreach ($recipients as $recipient) {
        $user_name = htmlspecialchars($recipient['name'] ?? 'Valued Customer');
        $to_email = $recipient['email'];

        // Apply placeholders to the template subject and body
        $final_subject = str_replace(
            ['{{subject_line}}'],
            [$subject],
            $template['subject']
        );
        
        $final_html_body = str_replace(
            ['{{subject_line}}', '{{message_body}}', '{{user_name}}'],
            [$subject, $body, $user_name],
            $template['html']
        );

        // Send the email (now calling the actual mail function)
        if (sendMail($to_email, $final_subject, $final_html_body, $priority)) {
            $emails_sent_count++;
        }
    }

    // --- FINAL RESPONSE ---
    if ($emails_sent_count > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => "Email broadcast successfully sent to {$emails_sent_count} of {$total_recipients} recipients. (Check your email client!)",
            'recipients' => $emails_sent_count
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => "Failed to send email to any recipient. Check server error logs and PHP mail configuration.",
            'recipients' => 0
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>