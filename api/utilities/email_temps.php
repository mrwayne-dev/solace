<?php
// ========================================
// EMAIL TEMPLATES — HealthRunCare
// ========================================
/**
 * Returns all email templates used in the system.
 * Each template includes 'subject' and 'html' keys.
 *
 * Usage (in email.php):
 *   $templates = getEmailTemplates();
 *   $templates['deposit_initiated']['html'];
 */
function getEmailTemplates() {
    $year = date('Y');
    // IMPORTANT: Update this URL to your actual logo path accessible via the web
    $logoUrl = 'https://healthruncare.com/assets/images/healthruncarelogo.png';
    $appName = 'HealthRunCare';
    $supportEmail = 'support@healthruncare.com'; // Define support email for easy updates
    $websiteUrl = 'https://healthruncare.com/'; // Define main website URL
    // Define color palette
    $colors = [
        'primary'           => '#386641',   // Matches --Primary
        'primary_light'     => '#CADEDE',   // Matches --Accent
        'surface'           => '#ffffff',   // Matches --Surface
        'accent'            => '#FEFAE0',   // Matches --Bg / --Gainsboro
        'text'              => '#1C2628',   // Matches --Black
        'muted'             => '#6B7C7D',   // Matches --GrayDark
        'border'            => 'rgba(28, 38, 40, 0.1)', // Matches --LightGray
        'success'           => '#386641',   // Matches --Green
        'warning'           => '#9FB8B8',   // Slight contrast tone (from --Surface-Alt)
        'danger'            => '#2f5336',   // Darker primary hover tone
        'highlight'         => '#CADEDE',   // Soft accent highlight
        'highlight_text'    => '#1C2628',   // Contrast text color
    ];
    // Header structure
    $header = "
        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background:{$colors['surface']};'>
            <tr>
                <td style='padding: 20px 28px; border-bottom: 1px solid {$colors['border']};'>
                    <img src='{$logoUrl}' alt='{$appName} Logo' style='max-width:150px; height:auto; vertical-align:middle;'>
                </td>
            </tr>
        </table>";
    // Footer structure
    $footer = "
        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background:{$colors['accent']};'>
            <tr>
                <td style='padding: 20px 28px; text-align: center; font-size: 12px; color:{$colors['muted']}; border-top: 1px solid {$colors['border']}'>
                    <p style='margin: 8px 0;'>© {$year} {$appName}. All rights reserved.</p>
                    <p style='margin: 8px 0; font-size: 11px;'>
                        You are receiving this email because you are a registered user of {$appName}.
                        If you have any questions, feel free to contact us at <a href='mailto:{$supportEmail}' style='color:{$colors['primary']}; text-decoration: none;'>{$supportEmail}</a>.
                    </p>
                    <p style='margin: 8px 0; font-size: 11px;'>
                        <a href='{$websiteUrl}' style='color:{$colors['primary']}; text-decoration: none;'>Visit our Website</a> |
                        <a href='{$websiteUrl}/pages/public/privacy.php' style='color:{$colors['primary']}; text-decoration: none;'>Privacy Policy</a>
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
        </head>
        <body style='margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: {$colors['primary_light']};'>
            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: {$colors['primary_light']}; min-height: 100vh;'>
                <tr>
                    <td style='padding: 20px 0;'>
                    <!--[if mso]>
                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' align='center' style='width: 600px;'>
                        <tr>
                            <td>
                    <![endif]-->
                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' align='center' style='width: 100%; max-width: 600px; background-color: {$colors['surface']}; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
                        {$header}
                        <tr>
                            <td style='padding: 32px 28px; line-height: 1.6; color: {$colors['text']};'>
                                {$content}
                            </td>
                        </tr>
                        {$footer}
                    </table>
                    <!--[if mso]>
                            </td>
                        </tr>
                    </table>
                    <![endif]-->
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    // ------------------------------
    // Template definitions with enhanced content
    // ------------------------------
    return [
'login_alert' => [
    'subject' => '[Security Alert] New Login Detected on Your HealthRunCare Account',
    'html' => $wrap("
        <h2 style='color: {$colors['text']}; margin-top: 0; font-size: 24px;'>New Login Detected on Your Account</h2>
        <p>Dear {{user_name}},</p>
        <p>We noticed a recent login to your <strong>{$appName}</strong> account. Below are the full details of this activity.</p>

        <div style='background-color: {$colors['accent']}; border-left: 4px solid {$colors['primary']}; padding: 14px 18px; margin: 20px 0; border-radius: 6px;'>
            <p style='margin: 6px 0;'><strong>Date & Time:</strong> {{login_time}}</p>
            <p style='margin: 6px 0;'><strong>IP Address:</strong> {{ip}}</p>
            <p style='margin: 6px 0;'><strong>Browser:</strong> {{browser}}</p>
            <p style='margin: 6px 0;'><strong>Location:</strong> {{location}}</p>
        </div>

        <p style='margin: 18px 0;'>If this was you, no further action is needed — your account remains secure.</p>

        <p>If you <strong>did not authorize</strong> this login, please take the following immediate actions:</p>
        <ol style='padding-left: 20px;'>
            <li>Reset your password using the secure link below.</li>
            <li>Review your dashboard activity and wallet transactions.</li>
            <li>Contact our support team at 
                <a href='mailto:{$supportEmail}' style='color:{$colors['primary']}; text-decoration:none;'>{$supportEmail}</a>.
            </li>
        </ol>

        <div style='margin: 26px 0; text-align:center;'>
            <a href='{$websiteUrl}forgotpassword' 
               style='display:inline-block; background-color: {$colors['danger']};
               color: white; padding: 12px 28px; text-decoration: none; 
               border-radius: 6px; font-weight: 600;'>Reset Password</a>
        </div>

        <hr style='border:none; border-top:1px solid {$colors['border']}; margin: 28px 0;'>

        <p style='margin:0;'>You can always access your dashboard securely via:</p>
        <p style='margin:8px 0;'>
            <a href='{$websiteUrl}' style='color:{$colors['primary']}; text-decoration:none;'>{$websiteUrl}</a>
        </p>

        <p>Thank you for staying vigilant — your security is our top priority at {$appName}.</p>

        <p style='margin-top:24px;'>Kind regards,<br><strong>The {$appName} Security Team</strong></p>
    "),
],
            'admin_login_alert' => [
            'subject' => '[Admin Security Alert] New Login to Your HealthRunCare Admin Account',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top:0; font-size:22px;'>Admin Login Detected</h2>
                <p>Dear {{admin_name}},</p>
                <p>A new login to your <strong>{$appName}</strong> <em>admin account</em> was detected.</p>

                <div style='background-color: {$colors['highlight']}; border-left: 4px solid {$colors['danger']}; padding: 12px 16px; margin: 18px 0; border-radius: 6px;'>
                    <p style='margin:0; color: {$colors['highlight_text']}; line-height:1.6;'>
                        <strong>Login Time:</strong> {{login_time}}<br>
                        <strong>IP Address:</strong> {{ip}}<br>
                        <strong>Browser:</strong> {{browser}}<br>
                        <strong>Location:</strong> {{location}}
                    </p>
                </div>

                <p>If this was you, no further action is needed.</p>
                <p>If you do not recognize this login, <strong>immediately secure your account</strong>:</p>
                <ul style='padding-left:20px;'>
                    <li>Change your password from the Admin Dashboard</li>
                    <li>Contact our Security Team at 
                        <a href='mailto:{$supportEmail}' style='color:{$colors['primary']}; text-decoration:none;'>{$supportEmail}</a>
                    </li>
                    <li>Check recent activity for unauthorized access</li>
                </ul>

                <p>Stay alert — admin access is privileged and must be protected at all times.</p>
                <p>Kind regards,<br><strong>{$appName} Security Team</strong></p>
            "),
        ],
        'admin_user_login_notification' => [
            'subject' => '[User Login Alert] A User Has Just Logged In',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top:0; font-size:22px;'>User Login Detected</h2>
                <p>Hello Admin,</p>
                <p>A user just logged into their {$appName} account.</p>

                <div style='background-color: {$colors['accent']}; border-left: 4px solid {$colors['primary']}; padding: 12px 16px; margin: 18px 0; border-radius: 6px;'>
                    <p style='margin:0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin:0;'><strong>Email:</strong> {{user_email}}</p>
                    <p style='margin:0;'><strong>Login Time:</strong> {{login_time}}</p>
                    <p style='margin:0;'><strong>IP:</strong> {{ip}}</p>
                    <p style='margin:0;'><strong>Browser:</strong> {{browser}}</p>
                    <p style='margin:0;'><strong>Location:</strong> {{location}}</p>
                </div>

                <p>This is an automatic notification for your records.</p>
                <p>Best regards,<br><strong>{$appName} Security System</strong></p>
            "),
        ],
        'welcome_user' => [
            'subject' => 'Welcome to HealthRunCare!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0; font-size: 24px;'>Welcome Aboard! 🎉</h2>
                <p>Dear {{user_name}},</p>
                <p>Congratulations on joining the <strong>{$appName}</strong> community! We are thrilled to have you as a member.</p>
                <p>Your account has been successfully created, and your personalized digital wallet is now active and ready for you to explore.</p>
                <h3 style='font-size: 18px; color: {$colors['primary']};'>What You Can Do Next:</h3>
                <ul>
                    <li><strong>Deposit Funds:</strong> Easily add money to your wallet using various secure methods.</li>
                    <li><strong>Make Donations:</strong> Contribute to meaningful causes and support those in need.</li>
                    <li><strong>Explore Investments:</strong> Discover opportunities to grow your funds while contributing positively.</li>
                    <li><strong>Track Impact:</strong> Monitor the positive changes your contributions are making.</li>
                </ul>
                <p>We believe that together, we can create a healthier and more caring world. Your journey with us starts now!</p>
                <p>If you have any questions or need assistance, our friendly support team is always here for you at <a href='mailto:{$supportEmail}' style='color:{$colors['primary']}; text-decoration: none;'>{$supportEmail}</a>.</p>
                <p>Thank you for choosing {$appName}. Welcome to a community focused on positive impact.</p>
                <p>Warmly,<br>The {$appName} Team</p>
            "),
        ],
        'welcome_admin' => [
            'subject' => 'Welcome — Your HealthRunCare Admin Account is Ready',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top:0; font-size:22px;'>Welcome to the Admin Team</h2>
                <p>Hi {{admin_name}},</p>
                <p>Your administrator account for <strong>{$appName}</strong> has been created successfully.</p>

                <div style='background-color: {$colors['accent']}; padding: 12px; margin: 14px 0; border-radius:6px; border:1px solid {$colors['border']}'>
                    <p style='margin: 0;'><strong>Account:</strong> {{admin_email}}</p>
                    <p style='margin: 0;'><strong>Role:</strong> {{admin_role}}</p>
                </div>

                <p>Next steps:</p>
                <ul>
                    <li>Sign in at <a href='{$websiteUrl}admin' style='color:{$colors['primary']}; text-decoration:none;'>Admin Dashboard</a></li>
                    <li>Update your profile and enable 2-factor auth if available</li>
                    <li>Review admin onboarding docs and security policies</li>
                </ul>

                <p>If you did not request this account, contact <a href='mailto:{$supportEmail}' style='color:{$colors['primary']}; text-decoration:none;'>{$supportEmail}</a> immediately.</p>

                <p>Kind regards,<br><strong>The {$appName} Team</strong></p>
            ")
        ],
        'deposit_initiated' => [
            'subject' => 'Deposit Request Received!',
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0; font-size: 24px;'>Deposit Request Confirmed</h2>
                <p>Hi {{user_name}},</p>
                <p>Thank you for choosing {$appName} to add funds to your wallet.</p>
                <p>We have successfully received your deposit request.</p>
                <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 4px; border: 1px solid {$colors['border']};'>
                    <p style='margin: 0;'><strong>Deposit Amount:</strong> \${{amount}}</p>
                    <p style='margin: 0;'><strong>Payment Method:</strong> {{method}}</p>
                    <p style='margin: 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                </div>
                <p>Our team is currently reviewing your request. You will receive an email shortly with specific instructions and payment details (e.g., bank details or wallet address) based on your chosen method.</p>
                <p>Please follow the instructions carefully to complete your deposit. Once we confirm the payment, the funds will be added to your wallet balance.</p>
                <p>If you have any questions, feel free to reach out to us.</p>
                <p>Thank you for trusting {$appName}.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'admin_deposit_notification' => [
            'subject' => 'New Deposit Request!',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0; font-size: 24px;'>New Deposit Request Awaiting Action</h2>
                <p>Hello Admin,</p>
                <p>A new deposit request has been submitted by a user and requires your attention.</p>
                <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 4px; border: 1px solid {$colors['border']};'>
                    <p style='margin: 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 0;'><strong>User Email:</strong> {{user_email}}</p>
                    <p style='margin: 0;'><strong>Deposit Amount:</strong> \${{amount}}</p>
                    <p style='margin: 0;'><strong>Payment Method:</strong> {{method}}</p>
                    <p style='margin: 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                </div>
                <p>Please log in to the admin dashboard to review this request. You will need to provide the necessary payment details (e.g., bank account information, wallet address) to the user so they can complete the deposit.</p>
                <p>Thank you for managing the {$appName} platform.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'deposit_details_provided' => [
            'subject' => 'Deposit Instructions Ready!',
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0; font-size: 24px;'>Deposit Instructions Ready</h2>
                <p>Hi {{user_name}},</p>
                <p>Great news! The payment details for your deposit request (Reference: {{reference}}) are now ready.</p>
                <p>Please proceed with your deposit of <strong>\${{amount}}</strong> using the information provided below.</p>
                <div style='background-color: {$colors['highlight']}; border-left: 4px solid {$colors['primary']}; padding: 12px; margin: 15px 0; border-radius: 0 4px 4px 0;'>
                    <p style='margin: 0; color: {$colors['highlight_text']};'><strong>Deposit Address/Details:</strong><br>{{deposit_address}}</p>
                </div>
                <p><strong>Important:</strong> Please ensure the amount sent matches exactly \${{amount}} and use the provided address or details precisely. Any mismatch may cause delays or loss of funds.</p>
                <p>After sending the payment, please return to your wallet dashboard and click the 'I Have Paid' button for this transaction to notify us. This helps us track and confirm your deposit quickly.</p>
                <p>Thank you for your patience and for using {$appName}.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'deposit_confirmed' => [
            'subject' => 'Success! Your Deposit Has Been Confirmed!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0; font-size: 24px;'>Deposit Confirmed Successfully! 🎉</h2>
                <p>Hi {{user_name}},</p>
                <p>Excellent news! We have successfully verified the payment for your deposit request (Reference: {{reference}}).</p>
                <div style='background-color: {$colors['success']}; color: white; padding: 16px; margin: 15px 0; border-radius: 4px; text-align: center;'>
                    <p style='margin: 0; font-size: 18px;'><strong>Amount Credited:</strong> \${{amount}}</p>
                </div>
                <p>The full amount of \${{amount}} has been securely added to your {$appName} wallet balance. You can now use these funds for donations, investments, or other activities within the platform.</p>
                <p>We appreciate your trust in {$appName} and thank you for contributing to our mission.</p>
                <p>Keep up the great work!</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'admin_payment_confirmed' => [
            'subject' => 'User Confirmed Payment for Deposit!',
            'html' => $wrap("
                <h2 style='color: {$colors['warning']}; margin-top: 0; font-size: 24px;'>User Payment Confirmed</h2>
                <p>Hello Admin,</p>
                <p>A user has marked a pending deposit as paid in their dashboard.</p>
                <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 4px; border: 1px solid {$colors['border']};'>
                    <p style='margin: 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 0;'><strong>User Email:</strong> {{user_email}}</p>
                    <p style='margin: 0;'><strong>Deposit Amount:</strong> \${{amount}}</p>
                    <p style='margin: 0;'><strong>Payment Method:</strong> {{method}}</p>
                    <p style='margin: 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                    <p style='margin: 0;'><strong>Confirmation Details:</strong> {{details}}</p>
                </div>
                <p>Please log in to the admin panel to verify the payment manually and finalize the deposit process by crediting the user's wallet.</p>
                <p>Thank you for managing the {$appName} platform.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'withdrawal_initiated' => [
            'subject' => 'Withdrawal Request Submitted!',
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0; font-size: 24px;'>Withdrawal Request Received</h2>
                <p>Hi {{user_name}},</p>
                <p>Your request to withdraw funds from your {$appName} wallet has been successfully submitted.</p>
                <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 4px; border: 1px solid {$colors['border']};'>
                    <p style='margin: 0;'><strong>Withdrawal Amount:</strong> \${{amount}}</p>
                    <p style='margin: 0;'><strong>Withdrawal Method:</strong> {{method}}</p>
                    <p style='margin: 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                </div>
                <p>Your wallet balance has been temporarily adjusted to reflect this pending request. The funds will be released once the request is reviewed and approved by our team.</p>
                <p>You will receive an email notification as soon as the status of your withdrawal changes (approved or declined).</p>
                <p>We appreciate your patience during the review process.</p>
                <p>Thank you for using {$appName}.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'admin_withdrawal_notification' => [
            'subject' => 'New Withdrawal Request!',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0; font-size: 24px;'>New Withdrawal Request Awaiting Review</h2>
                <p>Hello Admin,</p>
                <p>A user has submitted a new withdrawal request that requires your review and action.</p>
                <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 4px; border: 1px solid {$colors['border']};'>
                    <p style='margin: 0;'><strong>User Name:</strong> {{user_name}}</p>
                    <p style='margin: 0;'><strong>User Email:</strong> {{user_email}}</p>
                    <p style='margin: 0;'><strong>Withdrawal Amount:</strong> \${{amount}}</p>
                    <p style='margin: 0;'><strong>Withdrawal Method:</strong> {{method}}</p>
                    <p style='margin: 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                </div>
                <p>Please log in to the admin panel to review the details and either approve or decline this request according to our policies.</p>
                <p>Thank you for managing the {$appName} platform.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'withdrawal_approved' => [
            'subject' => 'Great News! Your Withdrawal Has Been Approved!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0; font-size: 24px;'>Withdrawal Approved! 🎉</h2>
                <p>Hi {{user_name}},</p>
                <p>We are pleased to inform you that your withdrawal request (Reference: {{reference}}) has been successfully reviewed and approved by our team.</p>
                <div style='background-color: {$colors['success']}; color: white; padding: 16px; margin: 15px 0; border-radius: 4px; text-align: center;'>
                    <p style='margin: 0; font-size: 18px;'><strong>Approved Amount:</strong> \${{amount}}</p>
                </div>
                <p>The funds of \${{amount}} will be transferred to your designated account (via {{method}}) within the next 1-3 business days. Please allow for standard processing time.</p>
                <p>We hope you are satisfied with your experience on {$appName}.</p>
                <p>Thank you for being a valued member of our community.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'withdrawal_declined' => [
            'subject' => 'Withdrawal Request Declined',
            'html' => $wrap("
                <h2 style='color: {$colors['danger']}; margin-top: 0; font-size: 24px;'>Withdrawal Request Status Update</h2>
                <p>Hi {{user_name}},</p>
                <p>We regret to inform you that your withdrawal request for \${{amount}} (Reference: {{reference}}) has been declined.</p>
                <p>The funds associated with this request have been returned to your {$appName} wallet balance. You can access them immediately.</p>
                <div style='background-color: {$colors['highlight']}; border-left: 4px solid {$colors['danger']}; padding: 12px; margin: 15px 0; border-radius: 0 4px 4px 0;'>
                    <p style='margin: 0; color: {$colors['highlight_text']};'><strong>Reason:</strong> Our system or team identified an issue with the request. This could be due to policy violations, account verification requirements, or other security checks.</p>
                </div>
                <p>If you believe this decision was made in error, or if you need further clarification, please do not hesitate to contact our support team at <a href='mailto:{$supportEmail}' style='color:{$colors['primary']}; text-decoration: none;'>{$supportEmail}</a>.</p>
                <p>We appreciate your understanding and continued use of {$appName}.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        // Default fallback
        'generic' => [
            'subject' => "{$appName} Notification",
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0;'>Notification</h2>
                <p>Dear {{user_name}},</p>
                <p>You have a new notification from <strong>{$appName}</strong>.</p>
                <p>Please log in to your account to view the full details.</p>
                <p>If you have any concerns, feel free to contact our support team.</p>
                <p>Thank you for using {$appName}.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'password_reset' => [
            'subject' => 'Password Reset Request - Your OTP Code for HealthRunCare',
            'html' => $wrap("
                <h2 style='color: {$colors['warning']}; margin-top: 0; font-size: 24px;'>Password Reset Requested</h2>
                <p>Hi {{user_name}},</p>
                <p>We received a request to reset the password for your <strong>{$appName}</strong> account associated with this email address.</p>
                <p>To proceed with resetting your password, please use the following One-Time Password (OTP) code:</p>
                <div style='text-align: center; margin: 20px 0;'>
                    <span style='display: inline-block; padding: 15px 30px; font-size: 32px; font-weight: bold; letter-spacing: 5px; background-color: {$colors['primary_light']}; color: {$colors['primary']}; border-radius: 8px; border: 2px dashed {$colors['primary']};'>
                        {{otp}}
                    </span>
                </div>
                <p><strong>This code is valid for 10 minutes.</strong> If you do not use it within this time, you will need to request a new one.</p>
                <p>If you did not request this password reset, you can safely ignore this email. Your account remains secure.</p>
                <p>Do not share this code with anyone.</p>
                <p>Thank you for keeping your {$appName} account secure.</p>
                <p>Best regards,<br>The {$appName} Security Team</p>
            "),
        ],
        'password_reset_success' => [
            'subject' => 'Success! Your HealthRunCare Password Has Been Changed!',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0; font-size: 24px;'>Password Successfully Reset</h2>
                <p>Hi {{user_name}},</p>
                <p>This is a confirmation that the password for your <strong>{$appName}</strong> account has been successfully changed.</p>
                <div style='background-color: {$colors['success']}; color: white; padding: 12px; margin: 15px 0; border-radius: 4px; text-align: center;'>
                    <p style='margin: 0;'><strong>Account:</strong> {{user_email}}</p>
                    <p style='margin: 0;'><strong>Status:</strong> Password Updated Successfully</p>
                </div>
                <p>If you performed this action, no further steps are needed. Your account is secure.</p>
                <div style='background-color: {$colors['highlight']}; border-left: 4px solid {$colors['danger']}; padding: 12px; margin: 15px 0; border-radius: 0 4px 4px 0;'>
                    <p style='margin: 0; color: {$colors['highlight_text']};'><strong>Security Alert:</strong> If you did not request this password change, your account may have been compromised. Please contact our support team immediately at <a href='mailto:{$supportEmail}' style='color:{$colors['danger']}; text-decoration: none;'>{$supportEmail}</a> to secure your account.</p>
                </div>
                <p>We recommend using a strong, unique password and enabling two-factor authentication if available.</p>
                <p>Thank you for keeping your {$appName} account secure.</p>
                <p>Best regards,<br>The {$appName} Security Team</p>
            "),
        ],
        'logout_notification' => [
            'subject' => 'You Have Successfully Logged Out of Your HealthRunCare Account',
            'html' => $wrap("
                <h2 style='color: {$colors['text']}; margin-top: 0; font-size: 24px;'>Logout Confirmation</h2>
                <p>Hi {{user_name}},</p>
                <p>This email confirms that you have successfully logged out of your <strong>{$appName}</strong> account.</p>
                <p>Your session has ended securely.</p>
                <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 4px; border: 1px solid {$colors['border']};'>
                    <p style='margin: 0;'><strong>Logged Out At:</strong> {{logout_time}}</p>
                    <p style='margin: 0;'><strong>Session Status:</strong> Ended</p>
                </div>
                <p>For your security, we recommend that you always log out when using shared or public devices. Additionally, please ensure that you are accessing {$appName} through our official website: <a href='{$websiteUrl}' style='color:{$colors['primary']}; text-decoration: none;'>{$websiteUrl}</a>.</p>
                <p>We look forward to seeing you again soon.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'donation_confirmed' => [
            'subject' => 'Donation Confirmation! Thank You for Supporting Us',
            'html' => $wrap("
                <h2 style='color: {$colors['success']}; margin-top: 0; font-size: 24px;'>Thank You, {{user_name}}!</h2>
                <p>Hi {{user_name}},</p>
                <p>We’ve received your generous donation of <strong>\${{amount}}</strong> to <strong>{{charity_name}}</strong>.</p>
                <p>Your support helps us continue making a difference in lives across the world.</p>
                <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 4px; border: 1px solid {$colors['border']};'>
                    <p style='margin: 0;'><strong>Transaction Reference:</strong> {{reference}}</p>
                </div>
                <p>For your records, you can view all your donations in your {$appName} dashboard.</p>
                <p>If you have any questions, feel free to reach out to us at <a href='mailto:{$supportEmail}' style='color:{$colors['primary']}; text-decoration: none;'>{$supportEmail}</a>.</p>
                <p>Thank you for being a part of our mission to create positive impact.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
        'admin_donation_notification' => [
            'subject' => 'New Donation Received on HealthRunCare!',
            'html' => $wrap("
                <h2 style='color: {$colors['primary']}; margin-top: 0; font-size: 24px;'>New Donation Received</h2>
                <p>Hello Admin,</p>
                <p>{{user_name}} ({{user_email}}) just donated <strong>\${{amount}}</strong> to <strong>{{charity_name}}</strong>.</p>
                <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 4px; border: 1px solid {$colors['border']};'>
                    <p style='margin: 0;'><strong>Reference:</strong> {{reference}}</p>
                </div>
                <p>Please log in to the admin dashboard to review the full details and process accordingly.</p>
                <p>Thank you for managing the {$appName} platform.</p>
                <p>Best regards,<br>The {$appName} Team</p>
            "),
        ],
// ========================== INVESTMENT EMAILS ==========================
'investment_confirmed' => [
    'subject' => 'Your Investment Has Been Started Successfully',
    'html' => $wrap("
        <h2 style='color: {$colors['success']}; margin-top: 0; font-size: 24px;'>Investment Confirmed</h2>
        <p>Hi {{user_name}},</p>
        <p>Your investment in <strong>{{plan_name}}</strong> has been successfully initiated.</p>
        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p style='margin: 0;'><strong>Amount:</strong> \${{amount}}</p>
            <p style='margin: 0;'><strong>ROI:</strong> {{roi_percent}}%</p>
            <p style='margin: 0;'><strong>Duration:</strong> {{duration_days}} days</p>
            <p style='margin: 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
            <p style='margin: 0;'><strong>Reference:</strong> {{reference}}</p>
        </div>
        <p>Thank you for trusting <strong>{$appName}</strong> with your investment. You can monitor its progress anytime in your dashboard.</p>
        <p>Best regards,<br>The {$appName} Team</p>
    "),
],

'admin_investment_notification' => [
    'subject' => 'New Investment Started on HealthRunCare',
    'html' => $wrap("
        <h2 style='color: {$colors['primary']}; margin-top: 0; font-size: 24px;'>New Investment Alert</h2>
        <p>Hello Admin,</p>
        <p>A new investment has been started by <strong>{{user_name}}</strong> ({{user_email}}).</p>
        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p style='margin: 0;'><strong>Plan:</strong> {{plan_name}}</p>
            <p style='margin: 0;'><strong>Amount:</strong> \${{amount}}</p>
            <p style='margin: 0;'><strong>Reference:</strong> {{reference}}</p>
        </div>
        <p>Please log in to the admin dashboard to review this investment.</p>
        <p>— {$appName} System</p>
    "),
],

'weekly_investment_update' => [
    'subject' => 'Weekly ROI Update — HealthRunCare Investment',
    'html' => $wrap("
        <h2 style='color: {$colors['primary']}; margin-top: 0; font-size: 24px;'>Your Weekly ROI Update</h2>
        <p>Hi {{user_name}},</p>
        <p>Great news! Your investment in <strong>{{plan_name}}</strong> has earned <strong>\${{weekly_roi}}</strong> this week.</p>
        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 15px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p style='margin: 0;'><strong>Total ROI So Far:</strong> \${{total_roi}}</p>
            <p style='margin: 0;'><strong>Next Maturity Date:</strong> {{next_maturity}}</p>
        </div>
        <p>Your investment continues to grow steadily. You can view your full performance report on your {$appName} dashboard.</p>
        <p>Keep investing and growing with confidence!</p>
        <p>Best regards,<br>The {$appName} Team</p>
    "),
],

'investment_matured' => [
    'subject' => 'Your Investment Has Matured — Funds Credited',
    'html' => $wrap("
        <h2 style='color: {$colors['success']}; margin-top: 0; font-size: 24px;'>Investment Maturity Notice</h2>
        <p>Congratulations {{user_name}},</p>
        <p>Your investment in <strong>{{plan_name}}</strong> has successfully matured and the payout has been credited to your wallet.</p>
        <div style='background-color: {$colors['highlight']}; padding: 16px; margin: 15px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p style='margin: 0;'><strong>Principal:</strong> \${{amount}}</p>
            <p style='margin: 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
            <p style='margin: 0;'><strong>Total Payout:</strong> \${{total_payout}}</p>
            <p style='margin: 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
        </div>
        <p>Thank you for choosing <strong>{$appName}</strong>. We look forward to helping you grow your impact even further.</p>
        <p>Warm regards,<br>The {$appName} Investments Team</p>
    "),
],
// ===============================
// 📧 HOLDLOCK EMAIL TEMPLATES
// ===============================

'holdlock_started' => [
    'subject' => 'Your HoldLock Plan Has Been Activated',
    'html' => $wrap("
        <h2 style='color: {$colors['success']}; margin-top: 0; font-size: 24px;'>HoldLock Plan Activated</h2>
        <p>Hi {{user_name}},</p>
        <p>We’re pleased to inform you that your <strong>{{plan_name}}</strong> has been successfully activated on <strong>{$appName}</strong>.</p>

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p style='margin: 0;'><strong>Amount Locked:</strong> \${{amount}}</p>
            <p style='margin: 0;'><strong>ROI:</strong> {{roi_percent}}%</p>
            <p style='margin: 0;'><strong>Duration:</strong> {{duration_days}} days</p>
            <p style='margin: 0;'><strong>Early Unlock Penalty:</strong> {{penalty_percent}}%</p>
            <p style='margin: 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
            <p style='margin: 0;'><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Your funds are now securely held and will begin accruing interest immediately. You’ll be notified once your plan reaches maturity or becomes eligible for early unlock.</p>

        <p>Thank you for trusting <strong>{$appName}</strong> with your financial growth journey.</p>
        <p>Warm regards,<br><strong>The {$appName} Team</strong></p>
    "),
],

'admin_holdlock_notification' => [
    'subject' => 'New HoldLock Plan Started on HealthRunCare',
    'html' => $wrap("
        <h2 style='color: {$colors['primary']}; margin-top: 0; font-size: 24px;'>New HoldLock Plan Alert</h2>
        <p>Hello Admin,</p>
        <p>A new HoldLock plan has been initiated by a user.</p>

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p style='margin: 0;'><strong>User Name:</strong> {{user_name}}</p>
            <p style='margin: 0;'><strong>User Email:</strong> {{user_email}}</p>
            <p style='margin: 0;'><strong>Plan:</strong> {{plan_name}}</p>
            <p style='margin: 0;'><strong>Amount Locked:</strong> \${{amount}}</p>
            <p style='margin: 0;'><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Please review this transaction in the admin dashboard if needed. This notification is for record and tracking purposes.</p>
        <p>Best regards,<br><strong>{$appName} System</strong></p>
    "),
],

'holdlock_unlocked_early' => [
    'subject' => 'Early Unlock Processed for Your HoldLock Plan',
    'html' => $wrap("
        <h2 style='color: {$colors['warning']}; margin-top: 0; font-size: 24px;'>HoldLock Early Unlock Processed</h2>
        <p>Hi {{user_name}},</p>
        <p>Your <strong>{{plan_name}}</strong> plan was unlocked early as requested.</p>

        <div style='background-color: {$colors['highlight']}; padding: 16px; margin: 18px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p style='margin: 0;'><strong>ROI Earned:</strong> \${{roi}}</p>
            <p style='margin: 0;'><strong>Penalty Applied:</strong> \${{penalty}}</p>
            <p style='margin: 0;'><strong>Total Payout:</strong> \${{payout}}</p>
        </div>

        <p>Your payout has been credited to your wallet. Please note that early unlocks include a penalty deduction as stated in the plan’s terms.</p>
        <p>Thank you for using <strong>{$appName}</strong> for your secure fund management.</p>
        <p>Best regards,<br><strong>The {$appName} Team</strong></p>
    "),
],

'holdlock_matured' => [
    'subject' => 'Your HoldLock Plan Has Matured — Funds Credited!',
    'html' => $wrap("
        <h2 style='color: {$colors['success']}; margin-top: 0; font-size: 24px;'>HoldLock Plan Matured 🎉</h2>
        <p>Congratulations {{user_name}},</p>
        <p>Your <strong>{{plan_name}}</strong> plan has successfully matured, and your funds have been credited to your wallet.</p>

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p style='margin: 0;'><strong>Amount Locked:</strong> \${{amount}}</p>
            <p style='margin: 0;'><strong>ROI Earned:</strong> \${{roi_earned}}</p>
            <p style='margin: 0;'><strong>Total Payout:</strong> \${{payout}}</p>
            <p style='margin: 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
            <p style='margin: 0;'><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Your funds are now available in your {$appName} wallet for withdrawal or reinvestment. We’re delighted to have supported your journey to financial growth.</p>
        <p>Thank you for trusting <strong>{$appName}</strong>.</p>
        <p>Warm regards,<br><strong>The {$appName} Team</strong></p>
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

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p style='margin: 0;'><strong>Amount:</strong> \${{amount}}</p>
            <p style='margin: 0;'><strong>ROI:</strong> {{roi_percent}}%</p>
            <p style='margin: 0;'><strong>Maturity Date:</strong> {{maturity_date}}</p>
            <p style='margin: 0;'><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Your funds are now securely held and will start generating ROI immediately. You’ll receive updates as your plan progresses or reaches maturity.</p>

        <p>Thank you for trusting <strong>{$appName}</strong> to safeguard and grow your future.</p>
        <p>Warm regards,<br><strong>The {$appName} TrustFund Team</strong></p>
    "),
],

'admin_trustfund_notification' => [
    'subject' => 'New TrustFund Plan Started on HealthRunCare',
    'html' => $wrap("
        <h2 style='color: {$colors['primary']}; margin-top: 0;'>New TrustFund Plan Alert</h2>
        <p>Hello Admin,</p>
        <p>A new TrustFund plan has been initiated by a user.</p>

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>User Name:</strong> {{user_name}}</p>
            <p><strong>User Email:</strong> {{user_email}}</p>
            <p><strong>Plan:</strong> {{plan_name}}</p>
            <p><strong>Amount:</strong> \${{amount}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>This is an automated alert to notify you of a new TrustFund activation.</p>
        <p>Thank you for managing the {$appName} platform.</p>
        <p>— {$appName} System</p>
    "),
],

'trustfund_unlocked_early' => [
    'subject' => 'Early Unlock Processed for Your TrustFund Plan',
    'html' => $wrap("
        <h2 style='color: {$colors['warning']}; margin-top: 0;'>TrustFund Early Unlock Processed</h2>
        <p>Hi {{user_name}},</p>
        <p>Your <strong>{{plan_name}}</strong> TrustFund was unlocked early as requested.</p>

        <div style='background-color: {$colors['highlight']}; padding: 16px; margin: 18px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>ROI Earned:</strong> \${{roi_earned}}</p>
            <p><strong>Penalty Applied:</strong> \${{penalty}}</p>
            <p><strong>Total Payout:</strong> \${{payout}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Your payout has been credited to your wallet. Please note that early unlocks carry a penalty deduction as stated in your plan terms.</p>

        <p>We appreciate your continued trust in <strong>{$appName}</strong>.</p>
        <p>Best regards,<br><strong>The {$appName} Team</strong></p>
    "),
],

'trustfund_matured' => [
    'subject' => 'Your TrustFund Plan Has Matured — Funds Credited!',
    'html' => $wrap("
        <h2 style='color: {$colors['success']}; margin-top: 0;'>TrustFund Plan Matured 🎉</h2>
        <p>Congratulations {{user_name}},</p>
        <p>Your <strong>{{plan_name}}</strong> TrustFund has successfully matured, and your funds have been credited to your wallet.</p>

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0; border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>Principal:</strong> \${{amount}}</p>
            <p><strong>ROI Earned:</strong> \${{roi_earned}}</p>
            <p><strong>Total Payout:</strong> \${{payout}}</p>
            <p><strong>Maturity Date:</strong> {{maturity_date}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Your funds are now available in your {$appName} wallet for reinvestment or withdrawal. We are delighted to celebrate this milestone with you!</p>
        <p>Thank you for growing with <strong>{$appName}</strong>.</p>
        <p>Warm regards,<br><strong>The {$appName} TrustFund Team</strong></p>
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

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0;
                    border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>Amount:</strong> \${{amount}}</p>
            <p><strong>ROI:</strong> {{roi_percent}}%</p>
            <p><strong>Maturity Date:</strong> {{maturity_date}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Your funds are now securely committed to the selected healthcare infrastructure plan.
        You will receive quarterly ROI payouts, and your full capital will be returned at maturity.</p>

        <p>Thank you for supporting healthcare advancement through <strong>{$appName}</strong>.</p>
        <p>Warm regards,<br><strong>The {$appName} Infrastructure Team</strong></p>
    "),
],

'admin_infrastructure_notification' => [
    'subject' => 'New Infrastructure Investment on HealthRunCare',
    'html' => $wrap("
        <h2 style='color: {$colors['primary']}; margin-top: 0;'>New Infrastructure Investment Alert</h2>
        <p>Hello Admin,</p>
        <p>A user has started a new infrastructure investment plan on {$appName}.</p>

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0;
                    border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>User Name:</strong> {{user_name}}</p>
            <p><strong>User Email:</strong> {{user_email}}</p>
            <p><strong>Plan:</strong> {{plan_name}}</p>
            <p><strong>Amount:</strong> \${{amount}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>This notification is for record purposes. Please review in the admin dashboard if needed.</p>
        <p>— {$appName} System</p>
    "),
],

'infrastructure_matured' => [
    'subject' => 'Your Infrastructure Investment Has Matured — Funds Credited!',
    'html' => $wrap("
        <h2 style='color: {$colors['success']}; margin-top: 0;'>Infrastructure Plan Matured 🎉</h2>
        <p>Congratulations {{user_name}},</p>
        <p>Your <strong>{{plan_name}}</strong> investment has matured, and your funds have been credited to your wallet.</p>

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0;
                    border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>Principal:</strong> \${{amount}}</p>
            <p><strong>ROI Earned:</strong> \${{roi_earned}}</p>
            <p><strong>Total Payout:</strong> \${{payout}}</p>
            <p><strong>Maturity Date:</strong> {{maturity_date}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Thank you for supporting healthcare growth through <strong>{$appName}</strong>.
        Your investment has created meaningful impact while earning healthy returns.</p>
        <p>Warm regards,<br><strong>The {$appName} Infrastructure Team</strong></p>
    "),
],

'infrastructure_unlocked_early' => [
    'subject' => 'Early Unlock Processed for Your Infrastructure Plan',
    'html' => $wrap("
        <h2 style='color: {$colors['warning']}; margin-top: 0;'>Infrastructure Early Unlock Processed</h2>
        <p>Hi {{user_name}},</p>
        <p>Your <strong>{{plan_name}}</strong> investment was unlocked early as requested.</p>

        <div style='background-color: {$colors['highlight']}; padding: 16px; margin: 18px 0;
                    border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>ROI Earned:</strong> \${{roi_earned}}</p>
            <p><strong>Total Payout:</strong> \${{payout}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Your payout has been credited to your wallet. Note that early unlocks may include
        reduced ROI or penalties as per your plan terms.</p>

        <p>We appreciate your continued trust in <strong>{$appName}</strong>.</p>
        <p>Best regards,<br><strong>The {$appName} Team</strong></p>
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

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0;
                    border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>Amount:</strong> \${{amount}}</p>
            <p><strong>ROI:</strong> {{roi_percent}}%</p>
            <p><strong>Maturity Date:</strong> {{maturity_date}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Your funds are now allocated towards healthcare system maintenance and sustainability. 
        This plan helps keep vital medical equipment functional while generating consistent returns for you.</p>

        <p>Thank you for contributing to healthcare resilience through <strong>{$appName}</strong>.</p>
        <p>Warm regards,<br><strong>The {$appName} Maintenance Team</strong></p>
    "),
],

'admin_maintenance_notification' => [
    'subject' => 'New Maintenance Development Plan Activated',
    'html' => $wrap("
        <h2 style='color: {$colors['primary']}; margin-top: 0;'>New Maintenance Investment Alert</h2>
        <p>Hello Admin,</p>
        <p>A user has activated a new Maintenance & Development plan on {$appName}.</p>

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0;
                    border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>User Name:</strong> {{user_name}}</p>
            <p><strong>User Email:</strong> {{user_email}}</p>
            <p><strong>Plan:</strong> {{plan_name}}</p>
            <p><strong>Amount:</strong> \${{amount}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>This notification confirms a new maintenance development contribution. 
        You may verify and track the plan progress via the admin dashboard.</p>

        <p>— {$appName} System</p>
    "),
],

'maintenance_matured' => [
    'subject' => 'Your Maintenance Development Plan Has Matured — Funds Credited!',
    'html' => $wrap("
        <h2 style='color: {$colors['success']}; margin-top: 0;'>Maintenance Plan Matured 🎉</h2>
        <p>Congratulations {{user_name}},</p>
        <p>Your <strong>{{plan_name}}</strong> maintenance plan has successfully matured, 
        and your total payout has been credited to your wallet.</p>

        <div style='background-color: {$colors['accent']}; padding: 16px; margin: 18px 0;
                    border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>Principal:</strong> \${{amount}}</p>
            <p><strong>ROI Earned:</strong> \${{roi_earned}}</p>
            <p><strong>Total Payout:</strong> \${{total_payout}}</p>
            <p><strong>Maturity Date:</strong> {{maturity_date}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Your contribution helped sustain essential healthcare facilities 
        while earning meaningful returns. Thank you for your impact through <strong>{$appName}</strong>.</p>

        <p>Warm regards,<br><strong>The {$appName} Maintenance Team</strong></p>
    "),
],

'maintenance_unlocked_early' => [
    'subject' => 'Early Unlock Processed for Your Maintenance Plan',
    'html' => $wrap("
        <h2 style='color: {$colors['warning']}; margin-top: 0;'>Early Unlock Processed</h2>
        <p>Hi {{user_name}},</p>
        <p>Your <strong>{{plan_name}}</strong> maintenance plan has been unlocked early as requested.</p>

        <div style='background-color: {$colors['highlight']}; padding: 16px; margin: 18px 0;
                    border-radius: 6px; border: 1px solid {$colors['border']};'>
            <p><strong>ROI Earned:</strong> \${{roi_earned}}</p>
            <p><strong>Total Payout:</strong> \${{total_payout}}</p>
            <p><strong>Reference:</strong> {{reference}}</p>
        </div>

        <p>Your funds have been released to your wallet. 
        Please note that early unlocks may include ROI adjustments as per your plan terms.</p>

        <p>We appreciate your continued trust in <strong>{$appName}</strong>.</p>
        <p>Best regards,<br><strong>The {$appName} Team</strong></p>
    "),
],
'admin_broadcast' => [
    // Subject will be dynamically set by admin input
    'subject' => '{{subject_line}}', 
    'html' => $wrap("
        <h2 style='color: {$colors['primary']}; margin-top: 0; font-size: 24px;'>{{subject_line}}</h2>
        <p>Dear {{user_name}},</p>
        <div style='background-color: {$colors['accent']}; padding: 18px; margin: 20px 0; border-radius: 8px; border: 1px solid {$colors['border']};'>
            {{message_body}}
        </div>
        <p>This is a direct message from the HealthRunCare Administration team.</p>
        <p>If you have questions, reply to this email or contact <a href='mailto:{$supportEmail}' style='color:{$colors['primary']}; text-decoration: none;'>{$supportEmail}</a>.</p>
        <p>Best regards,<br>The {$appName} Administration</p>
    "),
],

    ];
}
?>