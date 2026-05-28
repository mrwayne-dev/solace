<?php
// ========================================
// EMAIL HANDLER — HealthRunCare (Finalized v2)
// ========================================

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/env.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../utilities/email_temps.php';
require_once __DIR__ . '/../utilities/helpers.php'; // ✅ helpers now available (getUserIP, getUserBrowser, etc.)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * sendEmail()
 * Sends an HTML email using PHPMailer and a chosen template.
 *
 * @param array $params [
 * 'to' => recipient email,
 * 'template' => key from getEmailTemplates(),
 * 'variables' => array('placeholder' => 'value'),
 * 'subject' => optional override,
 * 'body' => optional raw HTML override,
 * 'debug' => optional true to preview in browser,
 * 'cc_admin' => optional true to auto-send to admin,
 * 'admin_template' => optional template key for admin notification
 * ]
 * @return bool|array
 */
function sendEmail($params)
{
    if (empty($params['to']) || !filter_var($params['to'], FILTER_VALIDATE_EMAIL)) {
        error_log('sendEmail: Invalid recipient email');
        return false;
    }

    $templates = getEmailTemplates();
    $templateKey = $params['template'] ?? 'generic';
    $variables = $params['variables'] ?? [];

    // Template fallback
    $template = $templates[$templateKey] ?? $templates['generic'];
    $subject = $params['subject'] ?? $template['subject'] ?? (APP_NAME . ' Notification');
    $bodyHtml = $params['body'] ?? $template['html'];

    // Replace template variables safely
    foreach ($variables as $key => $value) {
        // 🚨 CRITICAL FIX: Do not escape pre-formatted HTML variables.
        if ($key === 'details_html' || $key === 'message_body') {
            $safeValue = (string)$value;
        } else {
            // Escape all other dynamic variables
            $safeValue = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        }
        $bodyHtml = str_replace('{{' . $key . '}}', $safeValue, $bodyHtml);
        $subject = str_replace('{{' . $key . '}}', $safeValue, $subject);
    }

    // Replace global placeholders if any are left
    $bodyHtml = str_replace(
        ['{{year}}', '{{app_name}}', '{{support_email}}', '{{website_url}}'],
        [date('Y'), APP_NAME, ADMIN_CONTACT_EMAIL, APP_URL],
        $bodyHtml
    );

    // Debug mode (template preview)
    if (!empty($params['debug'])) {
        header('Content-Type: text/html; charset=UTF-8');
        echo $bodyHtml;
        exit;
    }

    // --- Initialize PHPMailer ---
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host      = SMTP_HOST;
        $mail->SMTPAuth  = true;
        $mail->Username  = SMTP_USER;
        $mail->Password  = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port      = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';
        $mail->isHTML(true);

        // Sender / Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addReplyTo(ADMIN_CONTACT_EMAIL, APP_NAME . ' Support');
        $mail->addAddress($params['to']);

        // Message
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody = html_entity_decode(strip_tags($bodyHtml), ENT_QUOTES, 'UTF-8');

        // Send
        $mail->send();

        // Optional: Send admin copy
        if (!empty($params['cc_admin'])) {
            $adminTemplate = $params['admin_template'] ?? 'admin_user_login_notification';
            $adminData = [
                'to' => ADMIN_CONTACT_EMAIL,
                'template' => $adminTemplate,
                'variables' => array_merge($variables, [
                    'admin_name' => 'Admin',
                    'admin_email' => ADMIN_CONTACT_EMAIL,
                ])
            ];
            sendEmail($adminData);
        }

        // Log success
        $log = sprintf("[%s] SENT → %s | Template: %s | Subject: %s\n",
            date('Y-m-d H:i:s'), $params['to'], $templateKey, $subject
        );
        file_put_contents(__DIR__ . '/../../logs/email.log', $log, FILE_APPEND);

        return [
            'success' => true,
            'recipient' => $params['to'],
            'subject' => $subject,
            'template' => $templateKey
        ];

    } catch (Exception $e) {
        $errorLog = sprintf("[%s] ERROR → %s | %s | Mailer: %s\n",
            date('Y-m-d H:i:s'),
            $params['to'],
            $e->getMessage(),
            $mail->ErrorInfo
        );
        file_put_contents(__DIR__ . '/../../logs/email.log', $errorLog, FILE_APPEND);
        error_log($errorLog);

        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
?>