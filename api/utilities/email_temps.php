<?php
// ========================================
// EMAIL TEMPLATES — TitanXHoldings (Optimized and Consistent)
// ========================================
/**
 * Returns all email templates used in the system.
 * Each template includes 'subject' and 'html' keys.
 *
 * Usage (in email.php):
 * $templates = getEmailTemplates();
 * $templates['deposit_initiated']['html'];
 */
function getEmailTemplates() {
    $year = date('Y');
    // IMPORTANT: Update this URL to your actual logo path accessible via the web
    $logoUrl = 'https://titanxholdings.com/assets/images/logo/titanx-white.png';
    $appName = 'TitanXHoldings';
    $supportEmail = 'support@titanxholdings.com'; // Define support email for easy updates
    $websiteUrl = 'https://titanxholdings.com/'; // Define main website URL
    $adminUrl = 'https://titanxholdings.com/admin'; // Define Admin Login URL

    // TXH email color palette — light mode email
    $colors = [
        'primary'           => '#CC0000',   // TXH brand red
        'primary_light'     => '#FFFFFF',   // Email outer body — white
        'surface'           => '#FFFFFF',   // Email card surface — white
        'background'        => '#F8F8F8',   // Subtle neutral for data blocks
        'text'              => '#1C2628',   // Body text
        'muted'             => '#6B7C7D',   // Muted text
        'border'            => 'rgba(28, 38, 40, 0.1)', // Hairline border
        'success'           => '#22C55E',   // Success green
        'danger'            => '#CC0000',   // TXH brand red (used for security/CTA)
        'warning_bg'        => '#FEF3C7',   // Warning block background (warm light)
        'warning_border'    => '#F59E0B',   // Warning block border (amber)
        'highlight_text'    => '#1C2628',   // Contrast text color
    ];

    // --- Reusable HTML Blocks ---

    // 1. Consistent Data Block for Amounts, References, etc.
    $dataBlockStyle = "background-color: {$colors['background']}; padding: 16px; margin: 18px 0; border-radius: 6px; border: 1px solid {$colors['border']};";

    // 2. Consistent Alert Block for Security/Cancellation/Warning
    $alertBlockStyle = "background-color: {$colors['background']}; border-left: 4px solid {{color}}; padding: 14px 18px; margin: 20px 0; border-radius: 0 6px 6px 0;";

    // Header structure — TXH red brand band
    $header = "
        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background:{$colors['primary']};'>
            <tr>
                <td style='padding: 20px 28px; border-bottom: 1px solid {$colors['primary']};'>
                    <a href='{$websiteUrl}' target='_blank'>
                        <img src='{$logoUrl}' alt='{$appName} Logo' style='max-width:150px; height:auto; vertical-align:middle; border:0;'>
                    </a>
                </td>
            </tr>
        </table>";

    // Footer structure
    $footer = "
        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background:{$colors['background']};'>
            <tr>
                <td style='padding: 20px 28px; text-align: center; font-size: 12px; color:{$colors['muted']}; border-top: 1px solid {$colors['border']}'>
                    <p style='margin: 8px 0;'>&copy; {$year} {$appName}. All rights reserved.</p>
                    <p style='margin: 8px 0; font-size: 11px;'>
                        If you have any questions, feel free to contact us at 
                        <a href='mailto:{$supportEmail}' style='color:{$colors['primary']}; text-decoration: none;'>{$supportEmail}</a>.
                    </p>
                    <p style='margin: 8px 0; font-size: 11px;'>
                        <a href='{$websiteUrl}' style='color:{$colors['primary']}; text-decoration: none;'>Visit our Website</a> |
                        <a href='{$websiteUrl}pages/public/privacy.php' style='color:{$colors['primary']}; text-decoration: none;'>Privacy Policy</a>
                    </p>
                </td>
            </tr>
        </table>";

    // Base HTML wrapper with improved structure and responsiveness
    $wrap = fn($content) => "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>" . htmlspecialchars($appName) . " Notification</title>
            <style type='text/css'>
                /* Basic reset and typography */
                body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: {$colors['primary_light']}; }
                a { color: {$colors['primary']}; text-decoration: none; }
                h2 { color: {$colors['text']}; font-size: 24px; margin-top: 0; }
                p { margin: 0 0 16px 0; line-height: 1.6; }
                /* Button styles */
                .button {
                    display: inline-block; background-color: {$colors['danger']};
                    color: white !important; padding: 12px 28px; text-decoration: none; 
                    border-radius: 6px; font-weight: 600; mso-padding-alt: 0;
                }
                .button a {
                    color: white !important; text-decoration: none; display: block;
                    padding: 12px 28px;
                }
            </style>
        </head>
        <body style='margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: {$colors['primary_light']};'>
            <center style='width: 100%; background-color: {$colors['primary_light']};'>
            <table role='presentation' cellspacing='0' cellpadding='0' border='0' align='center' style='width: 100%; max-width: 600px; background-color: {$colors['surface']}; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin: 20px auto;'>
                {$header}
                <tr>
                    <td style='padding: 32px 28px; line-height: 1.6; color: {$colors['text']};'>
                        {$content}
                    </td>
                </tr>
                {$footer}
            </table>
            </center>
        </body>
        </html>";

    // ------------------------------
    // Template definitions
    // ------------------------------
    return [
        'login_alert' => [
            'subject' => '[Security Alert] New Login Detected on Your ' . $appName . ' Account',
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0;'>New Login Detected on Your Account</h2>
                <p>Dear {{user_name}},</p>
                <p>We noticed a recent login to your <strong>{$appName}</strong> account. Please review the details below immediately.</p>

                " . str_replace(['{{color}}', '{{content}}'], [$colors['danger'], "
                    <p style='margin: 6px 0;'><strong>Date & Time:</strong> {{login_time}}</p>
                    <p style='margin: 6px 0;'><strong>IP Address:</strong> {{ip}}</p>
                    <p style='margin: 6px 0;'><strong>Browser:</strong> {{browser}}</p>
                    <p style='margin: 6px 0;'><strong>Location:</strong> {{location}}</p>
                "], "<div style='{$alertBlockStyle}'>{{content}}</div>") . "

                <p>If this was you, no further action is needed.</p>

                <p>If you <strong>did not authorize</strong> this login, please take the following immediate action:</p>
                <ul style='padding-left: 20px;'>
                    <li><strong>Immediately reset your password</strong> using the secure link below.</li>
                    <li>Review your dashboard activity for unauthorized transactions.</li>
                    <li>Contact our support team at <a href='mailto:{$supportEmail}' style='color:{$colors['primary']};'>{$supportEmail}</a>.</li>
                </ul>

                <div style='margin: 26px 0; text-align:center;'>
                    <a href='{$websiteUrl}forgotpassword' style='display:inline-block; background-color: {$colors['danger']}; color: white; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600;' target='_blank'>Reset Password</a>
                    </div>

                <p style='margin-top:24px;'>Kind regards,<br><strong>The {$appName} Security Team</strong></p>
            "),
        ],
        'admin_login_alert' => [
            'subject' => '[Admin Security Alert] New Login to Your ' . $appName . ' Admin Account',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top:0;'>Admin Login Detected</h2>
                <p>Dear {{admin_name}},</p>
                <p>A new login to your <strong>{$appName}</strong> <em>admin account</em> was detected.</p>

                " . str_replace(['{{color}}', '{{content}}'], [$colors['danger'], "
                    <p style='margin:0; color: {$colors['text']}; line-height:1.6;'>
                        <strong>Login Time:</strong> {{login_time}}<br>
                        <strong>IP Address:</strong> {{ip}}<br>
                        <strong>Browser:</strong> {{browser}}<br>
                        <strong>Location:</strong> {{location}}
                    </p>
                "], "<div style='{$alertBlockStyle}'>{{content}}</div>") . "

                <p>If this was you, no further action is needed. Due to the sensitivity of this account, please:</p>
                <ul style='padding-left:20px;'>
                    <li>Change your password immediately via the Admin Dashboard.</li>
                    <li>Review recent activity for unauthorized changes.</li>
                    <li>Contact our Security Team if the login is unfamiliar.</li>
                </ul>

                <div style='margin: 26px 0; text-align:center;'>
                    <a href='{$adminUrl}' style='display:inline-block; background-color: {$colors['primary']}; color: white; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600;' target='_blank'>Go to Admin Dashboard</a>
                </div>

                <p style='margin-top:24px;'>Kind regards,<br><strong>{$appName} Security Team</strong></p>
            "),
        ],
        'admin_user_login_notification' => [
            'subject' => '[User Login Alert] A User Has Just Logged In',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top:0;'>User Login Detected</h2>
                <p>Hello Admin,</p>
                <p>A user just logged into their {$appName} account. This notification is for your records.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 6px 0;'><strong>Email:</strong> {{user_email}}</p>
                    <p style='margin: 6px 0;'><strong>Login Time:</strong> {{login_time}}</p>
                    <p style='margin: 6px 0;'><strong>IP:</strong> {{ip}}</p>
                    <p style='margin: 6px 0;'><strong>Browser:</strong> {{browser}}</p>
                    <p style='margin: 6px 0;'><strong>Location:</strong> {{location}}</p>
                </div>

                <p style='margin-top:24px;'>Best regards,<br><strong>{$appName} Security System</strong></p>
            "),
        ],
        'welcome_user' => [
            'subject' => 'Welcome to ' . $appName . '!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Welcome Aboard!</h2>
                <p>Dear {{user_name}},</p>
                <p>Congratulations on joining the <strong>{$appName}</strong> community! We are thrilled to have you as a member.</p>
                <p>Your account has been successfully created, and your personalized digital wallet is now active and ready for you to explore.</p>
                
                <h3 style='font-size: 18px; color: {$colors['primary']}; margin-top: 30px; margin-bottom: 12px;'>What You Can Do Next:</h3>
                <ul style='padding-left: 20px;'>
                    <li><strong>Deposit Funds:</strong> Easily add money to your wallet using various secure methods.</li>
                    <li><strong>Make Donations:</strong> Contribute to meaningful causes and support those in need.</li>
                    <li><strong>Explore Investments:</strong> Discover opportunities to grow your funds while contributing positively.</li>
                </ul>
                <p>We believe that together, we can create a healthier and more caring world. Your journey with us starts now!</p>
                
                <p style='margin-top: 24px;'>If you have any questions or need assistance, our friendly support team is always here for you at <a href='mailto:{$supportEmail}' style='color:{$colors['primary']};'>{$supportEmail}</a>.</p>
                
                <p>Warmly,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'welcome_admin' => [
            'subject' => 'Welcome — Your ' . $appName . ' Admin Account is Ready',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top:0;'>Welcome to the Admin Team</h2>
                <p>Hi {{admin_name}},</p>
                <p>Your administrator account for <strong>{$appName}</strong> has been created successfully.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Account:</strong> {{admin_email}}</p>
                    <p style='margin: 6px 0;'><strong>Role:</strong> {{admin_role}}</p>
                </div>

                <p>Next steps:</p>
                <ul style='padding-left:20px;'>
                    <li>Sign in securely here: <a href='{$adminUrl}' style='color:{$colors['primary']};'>Admin Dashboard</a></li>
                    <li>Update your profile and set a strong, unique password.</li>
                </ul>

                <p>If you did not request this account, please contact <a href='mailto:{$supportEmail}' style='color:{$colors['primary']};'>{$supportEmail}</a> immediately.</p>

                <p style='margin-top:24px;'>Kind regards,<br><strong>The {$appName} Team</strong></p>
            ")
        ],
        'deposit_initiated' => [
            'subject' => 'Deposit Request Received!',
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0;'>Deposit Request Confirmed</h2>
                <p>Hi {{user_name}},</p>
                <p>Thank you for choosing {$appName} to add funds to your wallet. We have successfully received your deposit request.</p>
                
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Deposit Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Payment Method:</strong> {{method}}</p>
                    <p style='margin: 6px 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                </div>
                
                <p>Our team is currently reviewing your request. You will receive an email shortly with specific instructions and payment details (e.g., bank details or wallet address) based on your chosen method.</p>
                <p>Please follow the instructions carefully to complete your deposit. The funds will be added to your wallet once payment is confirmed.</p>
                
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'admin_deposit_notification' => [
            'subject' => 'New Deposit Request!',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New Deposit Request Awaiting Action</h2>
                <p>Hello Admin,</p>
                <p>A new deposit request has been submitted by a user and requires your attention.</p>
                
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 6px 0;'><strong>User Email:</strong> {{user_email}}</p>
                    <p style='margin: 6px 0;'><strong>Deposit Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Payment Method:</strong> {{method}}</p>
                    <p style='margin: 6px 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                </div>
                
                <p>Please log in to the admin dashboard to review this request and provide the necessary payment details to the user.</p>
                
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'deposit_details_provided' => [
            'subject' => 'Deposit Instructions Ready!',
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0;'>Deposit Instructions Ready</h2>
                <p>Hi {{user_name}},</p>
                <p>Great news! The payment details for your deposit request (Reference: <strong>{{reference}}</strong>) are now ready. Please proceed with your deposit of <strong>\${{amount}}</strong> using the information below.</p>
                
                " . str_replace(['{{color}}', '{{content}}'], [$colors['primary'], "
                    <p style='margin: 0; color: {$colors['text']};'><strong>Deposit Address/Details:</strong><br>{{deposit_address}}</p>
                "], "<div style='{$alertBlockStyle}'>{{content}}</div>") . "
                
                <p><strong>Important:</strong> Please ensure the amount sent matches exactly \${{amount}} and use the provided details precisely. Any mismatch may cause delays.</p>
                <p>After sending the payment, please return to your wallet dashboard and click the 'I Have Paid' button for this transaction to notify us so we can quickly confirm your deposit.</p>
                
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'deposit_confirmed' => [
            'subject' => 'Success! Your Deposit Has Been Approved and Credited!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Deposit Approved & Credited</h2>
                <p>Hi {{user_name}},</p>
                <p>Great news! Your deposit request with the reference <strong>{{reference}}</strong> has been **approved** and the funds have now been safely added to your {$appName} wallet.</p>

                <div style='{$dataBlockStyle}; text-align: center; border-left: 4px solid {$colors['success']};'>
                    <p style='margin: 0; font-size: 16px; color: {$colors['text']};'>
                        <strong>Amount Credited:</strong> \${{amount}}
                    </p>
                </div>

                <p>You can now use your wallet balance to make donations, explore investment plans, or participate in other rewarding programs within {$appName}.</p>
                
                <p style='margin-top: 24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'deposit_cancelled' => [
            'subject' => 'Your Deposit Request Has Been Cancelled',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>Deposit Request Cancelled</h2>
                <p>Hi {{user_name}},</p>
                <p>We regret to inform you that your pending deposit request (Reference: <strong>{{reference}}</strong>) for <strong>\${{amount}}</strong> has been **cancelled** by our team.</p>

                " . str_replace(['{{color}}', '{{content}}'], [$colors['danger'], "
                    <p style='margin: 0; color: {$colors['text']};'><strong>Reason for Cancellation:</strong> {{reason}}</p>
                "], "<div style='{$alertBlockStyle}'>{{content}}</div>") . "
                
                <p>This may occur when payment cannot be verified or requested details are incomplete. If you believe this decision was made in error or need clarification, please contact our support team at <a href='mailto:{$supportEmail}' style='color:{$colors['primary']};'>{$supportEmail}</a>.</p>
                
                <p style='margin-top: 24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'admin_payment_confirmed' => [
            'subject' => 'User Confirmed Payment for Deposit!',
            'html' => $wrap("
                <h2 style='color: {$colors['warning_border']}; margin-top: 0;'>User Payment Confirmed</h2>
                <p>Hello Admin,</p>
                <p>A user has marked a pending deposit as paid in their dashboard. **Manual verification is required.**</p>
                
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 6px 0;'><strong>User Email:</strong> {{user_email}}</p>
                    <p style='margin: 6px 0;'><strong>Deposit Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Payment Method:</strong> {{method}}</p>
                    <p style='margin: 6px 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                    <p style='margin: 6px 0;'><strong>Confirmation Details:</strong> {{details}}</p>
                </div>
                
                <p>Please log in to the admin panel to verify the payment manually and finalize the deposit process.</p>
                
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'withdrawal_initiated' => [
            'subject' => 'Withdrawal Request Submitted!',
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0;'>Withdrawal Request Received</h2>
                <p>Hi {{user_name}},</p>
                <p>Your request to withdraw funds from your {$appName} wallet has been successfully submitted. The details are below:</p>
                
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Withdrawal Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Withdrawal Method:</strong> {{method}}</p>
                    <p style='margin: 6px 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                </div>
                
                <p>Your wallet balance has been temporarily adjusted to reflect this pending request. You will receive an email notification as soon as the status of your withdrawal changes (approved or declined).</p>
                
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
'admin_withdrawal_notification' => [
            'subject' => 'New Withdrawal Request!',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New Withdrawal Request Awaiting Review</h2>
                <p>Hello Admin,</p>
                <p>A user has submitted a new withdrawal request that requires your review and action. The details are below:</p>
                
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 6px 0;'><strong>User Email:</strong> {{user_email}}</p>
                    <p style='margin: 6px 0;'><strong>Withdrawal Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Withdrawal Method:</strong> {{method}}</p>
                    <p style='margin: 6px 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                    {{details_html}} 
                </div>
                
                <p>Please log in to the admin panel to review the details and either approve or decline this request according to our policies.</p>
                
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'withdrawal_approved' => [
            'subject' => 'Great News! Your Withdrawal Has Been Approved!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Withdrawal Approved!</h2>
                <p>Hi {{user_name}},</p>
                <p>We are pleased to inform you that your withdrawal request (Reference: <strong>{{reference}}</strong>) has been successfully reviewed and **approved** by our team.</p>
                
                <div style='{$dataBlockStyle}; text-align: center; border-left: 4px solid {$colors['success']};'>
                    <p style='margin: 0; font-size: 16px; color: {$colors['text']};'>
                        <strong>Approved Amount:</strong> \${{amount}}
                    </p>
                </div>
                
                <p>The funds of \${{amount}} will be transferred to your designated account (via **{{method}}**) within the next 1-3 business days. Please allow for standard processing time.</p>
                
                <p style='margin-top: 24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'withdrawal_declined' => [
            'subject' => 'Withdrawal Request Declined',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>Withdrawal Request Status Update</h2>
                <p>Hi {{user_name}},</p>
                <p>We regret to inform you that your withdrawal request for \${{amount}} (Reference: <strong>{{reference}}</strong>) has been **declined**.</p>
                <p>The funds associated with this request have been returned to your {$appName} wallet balance. You can access them immediately.</p>
                
                " . str_replace(['{{color}}', '{{content}}'], [$colors['danger'], "
                    <p style='margin: 0; color: {$colors['text']};'><strong>Reason:</strong> {{reason}}</p>
                "], "<div style='{$alertBlockStyle}'>{{content}}</div>") . "
                
                <p>If you need further clarification, please contact our support team at <a href='mailto:{$supportEmail}' style='color:{$colors['primary']};'>{$supportEmail}</a>.</p>
                
                <p style='margin-top: 24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        // ========================== INVESTMENT EMAILS ==========================
        'investment_confirmed' => [
            'subject' => 'Your Investment Has Been Started Successfully',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Investment Confirmed</h2>
                <p>Hi {{user_name}},</p>
                <p>Your investment in <strong>{{plan_name}}</strong> has been successfully initiated.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI:</strong> {{roi_percent}}%</p>
                    <p style='margin: 6px 0;'><strong>Duration:</strong> {{duration_days}} days</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>Thank you for trusting <strong>{$appName}</strong> with your investment. You can monitor its progress anytime in your dashboard.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'admin_investment_notification' => [
            'subject' => 'New Investment Started on ' . $appName,
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New Investment Alert</h2>
                <p>Hello Admin,</p>
                <p>A new investment has been started by <strong>{{user_name}}</strong> ({{user_email}}).</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Plan:</strong> {{plan_name}}</p>
                    <p style='margin: 6px 0;'><strong>Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>Please log in to the admin dashboard to review this investment.</p>
                <p style='margin-top:24px;'>— <strong>{$appName} System</strong></p>
            "),
        ],

        // ========================== X-WEEKLY EMAILS ==========================
        'xweekly_enrolled' => [
            'subject' => 'Your X-Weekly Program Has Started',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>X-Weekly Program Confirmed</h2>
                <p>Hi {{user_name}},</p>
                <p>You're now enrolled in <strong>{{plan_name}}</strong>. Your first weekly contribution has been credited to the program.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Weekly Amount:</strong> \${{weekly_amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI:</strong> {{roi_percent}}%</p>
                    <p style='margin: 6px 0;'><strong>Next Debit:</strong> {{next_debit}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>Each week we'll automatically debit your wallet on the scheduled date. You can pause, resume, or cancel anytime from your dashboard.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'admin_xweekly_notification' => [
            'subject' => 'New X-Weekly Enrolment on ' . $appName,
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New X-Weekly Enrolment</h2>
                <p>Hello Admin,</p>
                <p><strong>{{user_name}}</strong> ({{user_email}}) has enrolled in an X-Weekly program.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Plan:</strong> {{plan_name}}</p>
                    <p style='margin: 6px 0;'><strong>Weekly Amount:</strong> \${{weekly_amount}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>Please log in to the admin dashboard to review this enrolment.</p>
                <p style='margin-top:24px;'>— <strong>{$appName} System</strong></p>
            "),
        ],

        // ========================== X-SHARES EMAILS ==========================
        'xshares_started' => [
            'subject' => 'Your X-Shares Position Has Been Opened',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>X-Shares Position Confirmed</h2>
                <p>Hi {{user_name}},</p>
                <p>Your X-Shares position in <strong>{{asset_name}} ({{ticker}})</strong> — {{company}} — has been opened successfully.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Amount Invested:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI:</strong> {{roi_percent}}%</p>
                    <p style='margin: 6px 0;'><strong>Payout Option:</strong> {{payout_option}}</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>You can track this position and your accrued ROI anytime from your {$appName} dashboard.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'admin_xshares_notification' => [
            'subject' => 'New X-Shares Position on ' . $appName,
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New X-Shares Position</h2>
                <p>Hello Admin,</p>
                <p><strong>{{user_name}}</strong> ({{user_email}}) has opened a new X-Shares position.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Asset:</strong> {{asset_name}} ({{ticker}})</p>
                    <p style='margin: 6px 0;'><strong>Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>Please log in to the admin dashboard to review this position.</p>
                <p style='margin-top:24px;'>— <strong>{$appName} System</strong></p>
            "),
        ],

        'xshares_matured' => [
            'subject' => 'Your X-Shares Position Has Been Unlocked',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>X-Shares Payout Credited</h2>
                <p>Hi {{user_name}},</p>
                <p>Your X-Shares position has been unlocked and the proceeds have been credited to your wallet.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Principal:</strong> \${{principal}}</p>
                    <p style='margin: 6px 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
                    <p style='margin: 6px 0;'><strong>Total Payout:</strong> \${{payout}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>Funds are available immediately for reinvestment or withdrawal.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        // ========================== X-REWARDS EMAILS ==========================
        'xrewards_order_placed' => [
            'subject' => 'Your X-Rewards Order Has Been Received',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Order Confirmed</h2>
                <p>Hi {{user_name}},</p>
                <p>Thanks for redeeming through X-Rewards. We've received your order and it's being prepared for fulfilment.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Product:</strong> {{product_name}}</p>
                    <p style='margin: 6px 0;'><strong>Quantity:</strong> {{quantity}}</p>
                    <p style='margin: 6px 0;'><strong>Unit Price:</strong> \${{unit_price}}</p>
                    <p style='margin: 6px 0;'><strong>Total Charged:</strong> \${{total_price}}</p>
                    <p style='margin: 6px 0;'><strong>Order Reference:</strong> {{reference}}</p>
                </div>
                <p>You'll receive another update once your order ships. You can also track the status from your {$appName} dashboard.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'admin_xrewards_order' => [
            'subject' => 'New X-Rewards Order on ' . $appName,
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New X-Rewards Order</h2>
                <p>Hello Admin,</p>
                <p><strong>{{user_name}}</strong> ({{user_email}}) has placed a new X-Rewards order awaiting fulfilment.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Product:</strong> {{product_name}}</p>
                    <p style='margin: 6px 0;'><strong>Quantity:</strong> {{quantity}}</p>
                    <p style='margin: 6px 0;'><strong>Total:</strong> \${{total_price}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Shipping Details:</strong></p>
                    <p style='margin: 6px 0; white-space: pre-wrap;'>{{shipping_details}}</p>
                </div>
                <p>Please log in to the admin dashboard to confirm and ship.</p>
                <p style='margin-top:24px;'>— <strong>{$appName} System</strong></p>
            "),
        ],

        'xrewards_order_cancelled' => [
            'subject' => 'Your X-Rewards Order Has Been Cancelled',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>Order Cancelled</h2>
                <p>Hi {{user_name}},</p>
                <p>Your X-Rewards order for <strong>{{product_name}}</strong> ({{quantity}}× units) has been cancelled and the amount refunded to your wallet.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Refund Amount:</strong> \${{refund}}</p>
                    <p style='margin: 6px 0;'><strong>Refund Reference:</strong> {{reference}}</p>
                </div>
                <p>The refund is immediately available in your wallet balance. Sorry to see this one go — we hope you'll redeem with us again soon.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        // ========================== RECURRING / ROI CRON EMAILS ==========================
        'xweekly_debit' => [
            'subject' => 'X-Weekly: This Week\'s Contribution & ROI Credit',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>Weekly X-Weekly Update</h2>
                <p>Hi {{user_name}},</p>
                <p>Your scheduled X-Weekly contribution has been processed and this week's ROI has been credited to your wallet.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Debited This Week:</strong> \${{weekly_amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI Credited:</strong> \${{roi_credit}}</p>
                    <p style='margin: 6px 0;'><strong>Total Invested To Date:</strong> \${{total_invested}}</p>
                    <p style='margin: 6px 0;'><strong>Next Debit:</strong> {{next_debit}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>Your X-Weekly program continues to compound. You can pause, resume, or cancel anytime from your dashboard.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'xshares_payout' => [
            'subject' => 'X-Shares ROI Payout Credited',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>X-Shares Payout Credited</h2>
                <p>Hi {{user_name}},</p>
                <p>Your scheduled X-Shares ROI payout has been credited to your wallet.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Amount Credited:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Schedule:</strong> {{schedule}}</p>
                    <p style='margin: 6px 0;'><strong>Periods Paid:</strong> {{periods}}</p>
                    <p style='margin: 6px 0;'><strong>Total ROI Earned:</strong> \${{roi_total}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>Funds are immediately available for reinvestment or withdrawal from your {$appName} dashboard.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        // ========================== X-REWARDS FULFILMENT EMAILS ==========================
        'xrewards_order_confirmed' => [
            'subject' => 'Your X-Rewards Order Has Been Confirmed',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Order Confirmed</h2>
                <p>Hi {{user_name}},</p>
                <p>Good news — our fulfilment team has confirmed your order and it's now being prepared for shipping.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Product:</strong> {{product_name}}</p>
                    <p style='margin: 6px 0;'><strong>Quantity:</strong> {{quantity}}</p>
                    <p style='margin: 6px 0;'><strong>Order Reference:</strong> {{reference}}</p>
                </div>
                <p>You'll receive another update the moment it ships. Track progress anytime from your {$appName} dashboard.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'xrewards_order_shipped' => [
            'subject' => 'Your X-Rewards Order Is On The Way',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>Order Shipped</h2>
                <p>Hi {{user_name}},</p>
                <p>Your X-Rewards order is on its way to the address you provided.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Product:</strong> {{product_name}}</p>
                    <p style='margin: 6px 0;'><strong>Quantity:</strong> {{quantity}}</p>
                    <p style='margin: 6px 0;'><strong>Order Reference:</strong> {{reference}}</p>
                </div>
                <p>Delivery times vary by region. You can review delivery progress from your {$appName} dashboard.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        // ========================== X-WEEKLY ADMIN ACTION EMAILS ==========================
        'xweekly_admin_paused' => [
            'subject' => 'Your X-Weekly Program Has Been Paused',
            'html' => $wrap("
                <h2 style='color: {$colors['warning_border']}; margin-top: 0;'>X-Weekly Program Paused</h2>
                <p>Hi {{user_name}},</p>
                <p>Your X-Weekly program (#{{program_id}}) has been paused by our team. While paused, no weekly debits will be taken from your wallet.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Weekly Amount:</strong> \${{weekly_amount}}</p>
                    <p style='margin: 6px 0;'><strong>Reason:</strong> {{reason}}</p>
                </div>
                <p>Your accumulated investment continues to earn ROI in the background. If you have questions, reply to this email — we're happy to help.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'xweekly_admin_cancelled' => [
            'subject' => 'Your X-Weekly Program Has Been Cancelled',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>X-Weekly Program Cancelled</h2>
                <p>Hi {{user_name}},</p>
                <p>Your X-Weekly program (#{{program_id}}) has been cancelled by our team. No further weekly debits will be processed.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Weekly Amount:</strong> \${{weekly_amount}}</p>
                    <p style='margin: 6px 0;'><strong>Total Invested To Date:</strong> \${{total_invested}}</p>
                    <p style='margin: 6px 0;'><strong>Reason:</strong> {{reason}}</p>
                </div>
                <p>Funds already invested remain active and continue to earn ROI on their original schedule. You can review them anytime from your {$appName} dashboard.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        // ========================== X-WEEKLY USER ACTION EMAILS ==========================
        'xweekly_paused' => [
            'subject' => 'X-Weekly Program Paused',
            'html' => $wrap("
                <h2 style='color: {$colors['warning_border']}; margin-top: 0;'>X-Weekly Program Paused</h2>
                <p>Hi {{user_name}},</p>
                <p>You've paused your X-Weekly program (#{{program_id}}). No further weekly debits will be taken from your wallet until you resume it.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Program ID:</strong> #{{program_id}}</p>
                    <p style='margin: 6px 0;'><strong>Total Invested To Date:</strong> \${{total_invested}}</p>
                </div>
                <p>Your accumulated investment continues to earn ROI while paused. Resume anytime from your {$appName} dashboard.</p>
                <p style='text-align:center; margin: 28px 0;'>
                    <a href='{$websiteUrl}dashboard.xweekly' style='display:inline-block; background-color: {$colors['danger']}; color: white; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600;' target='_blank'>Resume Program</a>
                </p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'xweekly_cancelled' => [
            'subject' => 'X-Weekly Program Cancelled',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>X-Weekly Program Cancelled</h2>
                <p>Hi {{user_name}},</p>
                <p>You've cancelled your X-Weekly program. No further weekly debits will be processed from your wallet.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Total Invested To Date:</strong> \${{total_invested}}</p>
                </div>
                <p>Funds already invested remain active and continue to earn ROI on their original schedule. You can review them anytime from your {$appName} dashboard, or start a new program whenever you're ready.</p>
                <p style='text-align:center; margin: 28px 0;'>
                    <a href='{$websiteUrl}dashboard.xweekly' style='display:inline-block; background-color: {$colors['danger']}; color: white; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600;' target='_blank'>Start a New Program</a>
                </p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'weekly_investment_update' => [
            'subject' => 'Weekly ROI Update — ' . $appName . ' Investment',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>Your Weekly ROI Update</h2>
                <p>Hi {{user_name}},</p>
                <p>Great news! Your investment in <strong>{{plan_name}}</strong> has earned <strong>\${{weekly_roi}}</strong> this week.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Total ROI So Far:</strong> \${{total_roi}}</p>
                    <p style='margin: 6px 0;'><strong>Next Maturity Date:</strong> {{next_maturity}}</p>
                </div>
                <p>Your investment continues to grow steadily. You can view your full performance report on your {$appName} dashboard.</p>
                <p>Keep investing and growing with confidence!</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'investment_matured' => [
            'subject' => 'Your Investment Has Matured — Funds Credited',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Investment Maturity Notice</h2>
                <p>Congratulations {{user_name}},</p>
                <p>Your investment in <strong>{{plan_name}}</strong> has successfully matured and the payout has been credited to your wallet.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Principal:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
                    <p style='margin: 6px 0;'><strong>Total Payout:</strong> \${{total_payout}}</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                </div>
                <p>Thank you for choosing <strong>{$appName}</strong>. We look forward to helping you grow your impact even further.</p>
                <p style='margin-top:24px;'>Warm regards,<br><strong>The {$appName} Investments Team</strong></p>
            "),
        ],
        // ===============================
        // 📧 HOLDLOCK EMAIL TEMPLATES
        // ===============================

        'holdlock_started' => [
            'subject' => 'Your HoldLock Plan Has Been Activated',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>HoldLock Plan Activated</h2>
                <p>Hi {{user_name}},</p>
                <p>We’re pleased to inform you that your <strong>{{plan_name}}</strong> has been successfully activated on <strong>{$appName}</strong>.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Amount Locked:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI:</strong> {{roi_percent}}%</p>
                    <p style='margin: 6px 0;'><strong>Duration:</strong> {{duration_days}} days</p>
                    <p style='margin: 6px 0;'><strong>Early Unlock Penalty:</strong> {{penalty_percent}}%</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your funds are now securely held and will begin accruing interest immediately. You’ll be notified once your plan reaches maturity or becomes eligible for early unlock.</p>

                <p style='margin-top:24px;'>Warm regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'admin_holdlock_notification' => [
            'subject' => 'New HoldLock Plan Started on ' . $appName,
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New HoldLock Plan Alert</h2>
                <p>Hello Admin,</p>
                <p>A new HoldLock plan has been initiated by a user.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 6px 0;'><strong>User Email:</strong> {{user_email}}</p>
                    <p style='margin: 6px 0;'><strong>Plan:</strong> {{plan_name}}</p>
                    <p style='margin: 6px 0;'><strong>Amount Locked:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Please review this transaction in the admin dashboard if needed. This notification is for record and tracking purposes.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>{$appName} System</strong></p>
            "),
        ],

        'holdlock_unlocked_early' => [
            'subject' => 'Early Unlock Processed for Your HoldLock Plan',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>HoldLock Early Unlock Processed</h2>
                <p>Hi {{user_name}},</p>
                <p>Your <strong>{{plan_name}}</strong> plan was unlocked early as requested.</p>

                <div style='{$dataBlockStyle}; border-left: 4px solid {$colors['danger']};'>
                    <p style='margin: 6px 0;'><strong>ROI Earned:</strong> \${{roi}}</p>
                    <p style='margin: 6px 0;'><strong>Penalty Applied:</strong> \${{penalty}}</p>
                    <p style='margin: 6px 0;'><strong>Total Payout:</strong> \${{payout}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your payout has been credited to your wallet. Please note that early unlocks include a penalty deduction as stated in the plan’s terms.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'holdlock_matured' => [
            'subject' => 'Your HoldLock Plan Has Matured — Funds Credited!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>HoldLock Plan Matured</h2>
                <p>Congratulations {{user_name}},</p>
                <p>Your <strong>{{plan_name}}</strong> plan has successfully matured, and your funds have been credited to your wallet.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Amount Locked:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
                    <p style='margin: 6px 0;'><strong>Total Payout:</strong> \${{payout}}</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your funds are now available in your {$appName} wallet for withdrawal or reinvestment. We’re delighted to have supported your journey to financial growth.</p>
                <p style='margin-top:24px;'>Warm regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        // ===============================
        // 📧 TRUSTFUND EMAIL TEMPLATES
        // ===============================

        'trustfund_started' => [
            'subject' => 'Your TrustFund Plan Has Been Activated',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>TrustFund Plan Activated</h2>
                <p>Hi {{user_name}},</p>
                <p>Your <strong>{{plan_name}}</strong> TrustFund has been successfully activated on {$appName}.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI:</strong> {{roi_percent}}%</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your funds are now securely held and will start generating ROI immediately. You’ll receive updates as your plan progresses or reaches maturity.</p>

                <p style='margin-top:24px;'>Warm regards,<br><strong>The {$appName} TrustFund Team</strong></p>
            "),
        ],

        'admin_trustfund_notification' => [
            'subject' => 'New TrustFund Plan Started on ' . $appName,
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New TrustFund Plan Alert</h2>
                <p>Hello Admin,</p>
                <p>A new TrustFund plan has been initiated by a user.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 6px 0;'><strong>User Email:</strong> {{user_email}}</p>
                    <p style='margin: 6px 0;'><strong>Plan:</strong> {{plan_name}}</p>
                    <p style='margin: 6px 0;'><strong>Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>This is an automated alert to notify you of a new TrustFund activation.</p>
                <p style='margin-top:24px;'>— <strong>{$appName} System</strong></p>
            "),
        ],

        'trustfund_unlocked_early' => [
            'subject' => 'Early Unlock Processed for Your TrustFund Plan',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>TrustFund Early Unlock Processed</h2>
                <p>Hi {{user_name}},</p>
                <p>Your <strong>{{plan_name}}</strong> TrustFund was unlocked early as requested.</p>

                <div style='{$dataBlockStyle}; border-left: 4px solid {$colors['danger']};'>
                    <p style='margin: 6px 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
                    <p style='margin: 6px 0;'><strong>Penalty Applied:</strong> \${{penalty}}</p>
                    <p style='margin: 6px 0;'><strong>Total Payout:</strong> \${{payout}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your payout has been credited to your wallet. Please note that early unlocks carry a penalty deduction as stated in your plan terms.</p>

                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],

        'trustfund_matured' => [
            'subject' => 'Your TrustFund Plan Has Matured — Funds Credited!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>TrustFund Plan Matured</h2>
                <p>Congratulations {{user_name}},</p>
                <p>Your <strong>{{plan_name}}</strong> TrustFund has successfully matured, and your funds have been credited to your wallet.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Principal:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
                    <p style='margin: 6px 0;'><strong>Total Payout:</strong> \${{payout}}</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your funds are now available in your {$appName} wallet for reinvestment or withdrawal. We are delighted to celebrate this milestone with you!</p>
                <p style='margin-top:24px;'>Warm regards,<br><strong>The {$appName} TrustFund Team</strong></p>
            "),
        ],
        // ===============================
        // 📧 INFRASTRUCTURE EMAIL TEMPLATES
        // ===============================

        'infrastructure_started' => [
            'subject' => 'Your Infrastructure Investment Has Been Activated',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Infrastructure Investment Activated</h2>
                <p>Hi {{user_name}},</p>
                <p>Your investment in <strong>{{plan_name}}</strong> has been successfully activated on {$appName}.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI:</strong> {{roi_percent}}%</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your funds are now securely committed to the selected healthcare infrastructure plan. You will receive quarterly ROI payouts, and your full capital will be returned at maturity.</p>

                <p style='margin-top:24px;'>Warm regards,<br><strong>The {$appName} Infrastructure Team</strong></p>
            "),
        ],

        'admin_infrastructure_notification' => [
            'subject' => 'New Infrastructure Investment on ' . $appName,
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New Infrastructure Investment Alert</h2>
                <p>Hello Admin,</p>
                <p>A user has started a new infrastructure investment plan on {$appName}.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 6px 0;'><strong>User Email:</strong> {{user_email}}</p>
                    <p style='margin: 6px 0;'><strong>Plan:</strong> {{plan_name}}</p>
                    <p style='margin: 6px 0;'><strong>Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>This notification is for record purposes. Please review in the admin dashboard if needed.</p>
                <p style='margin-top:24px;'>— <strong>{$appName} System</strong></p>
            "),
        ],

        'infrastructure_matured' => [
            'subject' => 'Your Infrastructure Investment Has Matured — Funds Credited!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Infrastructure Plan Matured</h2>
                <p>Congratulations {{user_name}},</p>
                <p>Your <strong>{{plan_name}}</strong> investment has matured, and your funds have been credited to your wallet.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Principal:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
                    <p style='margin: 6px 0;'><strong>Total Payout:</strong> \${{payout}}</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your investment has created meaningful impact while earning healthy returns. Thank you for supporting healthcare advancement through <strong>{$appName}</strong>.</p>
                <p style='margin-top:24px;'>Warm regards,<br><strong>The {$appName} Infrastructure Team</strong></p>
            "),
        ],

        'infrastructure_unlocked_early' => [
            'subject' => 'Early Unlock Processed for Your Infrastructure Plan',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>Infrastructure Early Unlock Processed</h2>
                <p>Hi {{user_name}},</p>
                <p>Your <strong>{{plan_name}}</strong> investment was unlocked early as requested.</p>

                <div style='{$dataBlockStyle}; border-left: 4px solid {$colors['danger']};'>
                    <p style='margin: 6px 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
                    <p style='margin: 6px 0;'><strong>Total Payout:</strong> \${{payout}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your payout has been credited to your wallet. Please note that early unlocks may include reduced ROI or penalties as per your plan terms.</p>

                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        // ===============================
        // 📧 MAINTENANCE DEVELOPMENT EMAIL TEMPLATES
        // ===============================

        'maintenance_started' => [
            'subject' => 'Your Maintenance & Development Plan Has Been Activated',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Maintenance Plan Activated</h2>
                <p>Hi {{user_name}},</p>
                <p>Your participation in the <strong>{{plan_name}}</strong> maintenance and development plan has been successfully started on {$appName}.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI:</strong> {{roi_percent}}%</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your funds are now allocated towards healthcare system maintenance and sustainability. This plan helps keep vital medical equipment functional while generating consistent returns for you.</p>

                <p style='margin-top:24px;'>Warm regards,<br><strong>The {$appName} Maintenance Team</strong></p>
            "),
        ],

        'admin_maintenance_notification' => [
            'subject' => 'New Maintenance Development Plan Activated',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New Maintenance Investment Alert</h2>
                <p>Hello Admin,</p>
                <p>A user has activated a new Maintenance & Development plan on {$appName}.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 6px 0;'><strong>User Email:</strong> {{user_email}}</p>
                    <p style='margin: 6px 0;'><strong>Plan:</strong> {{plan_name}}</p>
                    <p style='margin: 6px 0;'><strong>Amount:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>This notification confirms a new maintenance development contribution. You may verify and track the plan progress via the admin dashboard.</p>
                <p style='margin-top:24px;'>— <strong>{$appName} System</strong></p>
            "),
        ],

        'maintenance_matured' => [
            'subject' => 'Your Maintenance Development Plan Has Matured — Funds Credited!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Maintenance Plan Matured</h2>
                <p>Congratulations {{user_name}},</p>
                <p>Your <strong>{{plan_name}}</strong> maintenance plan has successfully matured, and your total payout has been credited to your wallet.</p>

                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Principal:</strong> \${{amount}}</p>
                    <p style='margin: 6px 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
                    <p style='margin: 6px 0;'><strong>Total Payout:</strong> \${{total_payout}}</p>
                    <p style='margin: 6px 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your contribution helped sustain essential healthcare facilities while earning meaningful returns. Thank you for your impact through <strong>{$appName}</strong>.</p>

                <p style='margin-top:24px;'>Warm regards,<br><strong>The {$appName} Maintenance Team</strong></p>
            "),
        ],

        'maintenance_unlocked_early' => [
            'subject' => 'Early Unlock Processed for Your Maintenance Plan',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>Early Unlock Processed</h2>
                <p>Hi {{user_name}},</p>
                <p>Your <strong>{{plan_name}}</strong> maintenance plan has been unlocked early as requested.</p>

                <div style='{$dataBlockStyle}; border-left: 4px solid {$colors['danger']};'>
                    <p style='margin: 6px 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
                    <p style='margin: 6px 0;'><strong>Total Payout:</strong> \${{total_payout}}</p>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>

                <p>Your funds have been released to your wallet. Please note that early unlocks may include ROI adjustments as per your plan terms.</p>

                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'admin_broadcast' => [
            // Subject will be dynamically set by admin input
            'subject' => '{{subject_line}}', 
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>{{subject_line}}</h2>
                <p>Dear {{user_name}},</p>
                <div style='background-color: {$colors['background']}; padding: 18px; margin: 20px 0; border-radius: 8px; border: 1px solid {$colors['border']};'>
                    {{message_body}}
                </div>
                <p>This is a direct message from the TitanXHoldings Administration team.</p>
                <p>If you have questions, reply to this email or contact <a href='mailto:{$supportEmail}' style='color:{$colors['primary']};'>{$supportEmail}</a>.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Administration</strong></p>
            "),
        ],
        'password_reset' => [
            'subject' => 'Password Reset Request - Your OTP Code for ' . $appName,
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>Password Reset Requested</h2>
                <p>Hi {{user_name}},</p>
                <p>We received a request to reset the password for your <strong>{$appName}</strong> account associated with this email address.</p>
                <p>To proceed with resetting your password, please use the following One-Time Password (OTP) code:</p>
                <div style='text-align: center; margin: 20px 0;'>
                    <span style='display: inline-block; padding: 15px 30px; font-size: 32px; font-weight: bold; letter-spacing: 5px; background-color: {$colors['primary_light']}; color: {$colors['primary']}; border-radius: 8px; border: 2px dashed {$colors['primary']};'>
                        {{otp}}
                    </span>
                </div>
                <p style='text-align: center;'><strong>This code is valid for 10 minutes.</strong></p>
                <p>If you did not request this password reset, you can safely ignore this email. Do not share this code with anyone.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Security Team</strong></p>
            "),
        ],
        'email_verification' => [
            'subject' => 'Verify your email — your ' . $appName . ' code',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0;'>Confirm Your Email Address</h2>
                <p>Hi {{user_name}},</p>
                <p>Thanks for signing up with <strong>{$appName}</strong>. To activate your account, please enter the verification code below:</p>
                <div style='text-align: center; margin: 20px 0;'>
                    <span style='display: inline-block; padding: 15px 30px; font-size: 32px; font-weight: bold; letter-spacing: 5px; background-color: {$colors['primary_light']}; color: {$colors['primary']}; border-radius: 8px; border: 2px dashed {$colors['primary']};'>
                        {{otp}}
                    </span>
                </div>
                <p style='text-align: center;'><strong>This code is valid for 10 minutes.</strong></p>
                <p>If you did not create a {$appName} account, you can safely ignore this email. Do not share this code with anyone.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Security Team</strong></p>
            "),
        ],
        'password_reset_success' => [
            'subject' => 'Success! Your ' . $appName . ' Password Has Been Changed!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Password Successfully Reset</h2>
                <p>Hi {{user_name}},</p>
                <p>This is a confirmation that the password for your <strong>{$appName}</strong> account has been successfully changed.</p>
                <div style='{$dataBlockStyle}; text-align: center; border-left: 4px solid {$colors['success']};'>
                    <p style='margin: 6px 0;'><strong>Account:</strong> {{user_email}}</p>
                    <p style='margin: 6px 0;'><strong>Status:</strong> Password Updated Successfully</p>
                </div>
                <p>If you performed this action, no further steps are needed. Your account is secure.</p>
                
                " . str_replace(['{{color}}', '{{content}}'], [$colors['danger'], "
                    <p style='margin: 0; color: {$colors['text']};'><strong>Security Alert:</strong> If you did not request this password change, your account may have been compromised. Please contact our support team immediately at <a href='mailto:{$supportEmail}' style='color:{$colors['danger']};'>{$supportEmail}</a> to secure your account.</p>
                "], "<div style='{$alertBlockStyle}'>{{content}}</div>") . "

                <p>We recommend using a strong, unique password.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Security Team</strong></p>
            "),
        ],
        'logout_notification' => [
            'subject' => 'You Have Successfully Logged Out of Your ' . $appName . ' Account',
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0;'>Logout Confirmation</h2>
                <p>Hi {{user_name}},</p>
                <p>This email confirms that you have successfully logged out of your <strong>{$appName}</strong> account.</p>
                
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Logged Out At:</strong> {{logout_time}}</p>
                    <p style='margin: 6px 0;'><strong>Session Status:</strong> Ended Securely</p>
                </div>
                
                <p>For your security, we recommend that you always log out when using shared or public devices. Please ensure that you are accessing {$appName} through our official website: <a href='{$websiteUrl}' style='color:{$colors['primary']};'>{$websiteUrl}</a>.</p>
                
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'donation_confirmed' => [
            'subject' => 'Donation Confirmation! Thank You for Supporting Us',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0;'>Thank You, {{user_name}}!</h2>
                <p>Hi {{user_name}},</p>
                <p>We’ve received your generous donation of <strong>\${{amount}}</strong> to **{{charity_name}}**.</p>
                <p>Your support helps us continue making a difference in lives across the world.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                </div>
                <p>For your records, you can view all your donations in your {$appName} dashboard.</p>
                <p style='margin-top:24px;'>Thank you for being a part of our mission to create positive impact.</p>
                <p>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        'admin_donation_notification' => [
            'subject' => 'New Donation Received on ' . $appName . '!',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0;'>New Donation Received</h2>
                <p>Hello Admin,</p>
                <p>{{user_name}} ({{user_email}}) just donated **\${{amount}}** to **{{charity_name}}**.</p>
                <div style='{$dataBlockStyle}'>
                    <p style='margin: 6px 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>Please log in to the admin dashboard to review the full details and process accordingly.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
        // Default fallback
        'generic' => [
            'subject' => $appName . " Notification",
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0;'>Notification</h2>
                <p>Dear {{user_name}},</p>
                <p>You have a new notification from <strong>{$appName}</strong>.</p>
                <p>Please log in to your account to view the full details.</p>
                <p>If you have any concerns, feel free to contact our support team.</p>
                <p style='margin-top:24px;'>Best regards,<br><strong>The {$appName} Team</strong></p>
            "),
        ],
    ];
}
?>
