<!DOCTYPE html>
<html lang="en">
<head>
        <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="description" content="HealthRunCare Charity - Support health initiatives through donations and community contributions.">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://healthruncare.com/login">
    <title>HealthRunCare – Login</title>

    <!-- Preload + Apply (critical CSS) -->
    <link rel="preload" href="../../assets/css/bootstrap.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/css/dashboard.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/css/main.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/icon/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">

    <!-- Load Non-critical CSS Normally -->
    <link rel="stylesheet" href="../../assets/css/animation.min.css">
    <link rel="stylesheet" href="../../assets/css/animation.css">
    <link rel="stylesheet" href="../../assets/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font.css">
    <link rel="stylesheet" href="../../assets/icon/style.css">

    <!-- Fallback for browsers without preload support -->
    <noscript>
        <link rel="stylesheet" href="../../assets/css/bootstrap.css">
        <link rel="stylesheet" href="../../assets/css/dashboard.css">
        <link rel="stylesheet" href="../../assets/css/main.css">
    </noscript>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="../../assets/favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicon/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="HRC">
    <link rel="manifest" href="../../assets/favicon/site.webmanifest">

</head>

<body>
<div id="wrapper">
    <div id="page">
        <div class="sign-in-wrap">
            <div class="sign-in-box">
                <!-- LEFT SIDE -->
                <div class="left">
                    <div class="content">
                        <h3 class="heading text-Primary mb-8 text-center">Forgot Password?</h3>
                        <div class="sub f14-regular text-GrayDark mb-24 text-center">
                            Enter your registered email and we’ll send an OTP to reset your password.
                        </div>

                        <div class="sign-in-inner">
                            <!-- STEP 1: Enter Email -->
                            <form id="forgot-step1" class="auth-form flex flex-column gap24" autocomplete="off">
                                <fieldset>
                                    <div class="f14-regular mb-6">Email Address</div>
                                    <input id="forgot-email" class="form-control" type="email" placeholder="you@example.com" required>
                                </fieldset>
                                <button type="submit" class="tf-button style-1 w-100 bg-Primary text-White">Send OTP</button>
                            </form>

                            <!-- STEP 2: Enter OTP -->
                            <form id="forgot-step2" class="auth-form hidden flex flex-column gap24" autocomplete="off">
                                <fieldset>
                                    <div class="f14-regular mb-6">Enter OTP</div>
                                    <input id="otp" class="form-control" type="text" placeholder="Enter 6-digit code" maxlength="6" required>
                                </fieldset>
                                <button type="submit" class="tf-button style-1 w-100 bg-Primary text-White">Verify OTP</button>
                            </form>

                            <!-- STEP 3: Reset Password -->
                            <form id="forgot-step3" class="auth-form hidden flex flex-column gap24" autocomplete="off">
                                <fieldset>
                                    <div class="f14-regular mb-6">New Password</div>
                                    <input id="new_password" class="form-control" type="password" placeholder="Enter new password" required>
                                </fieldset>
                                <button type="submit" class="tf-button style-1 w-100 bg-Primary text-White">Reset Password</button>
                            </form>

                            <div class="f14-regular text-center mt-4">
                                Remember your password? 
                                <a href="/login" class="f14-bold text-Primary">Sign In</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE -->
                <div class="right">
                    <img src="../../assets/images/forgotpasssword.png" alt="AI Health Illustration">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loader -->
<div id="loader" class="hidden">
    <div class="line-loader"><div></div><div></div><div></div><div></div><div></div></div>
</div>

<!-- Toast Notifications -->
<div id="toast-container"></div>

<!-- Scripts -->
<script src="../../assets/js/api.js" defer></script>
</body>
</html>
