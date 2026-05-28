<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Meta -->
  <meta charset="UTF-8">
  <meta name="description" content="HealthRunCare Admin - Manage the platform, users, and operations securely.">
  <meta name="author" content="HealthRunCare">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <link rel="canonical" href="https://healthruncare.com/admin.login">
  <title>HealthRunCare – Admin Login</title>

  <!-- Preload + Apply -->
  <link rel="preload" href="../../assets/css/bootstrap.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="../../assets/css/dashboard.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="../../assets/css/main.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <link rel="preload" href="../../assets/icon/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">

  <!-- Non-critical CSS -->
  <link rel="stylesheet" href="../../assets/css/animation.min.css">
  <link rel="stylesheet" href="../../assets/css/bootstrap-select.min.css">
  <link rel="stylesheet" href="../../assets/fonts/font.css">
  <link rel="stylesheet" href="../../assets/icon/style.css">

  <!-- Fallback -->
  <noscript>
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
  </noscript>

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="../../assets/favicon/favicon-32x32.png">
  <meta name="apple-mobile-web-app-title" content="HRC">
</head>

<body style="background-color: white;">
  <div id="wrapper">
    <div id="page">
      <div class="sign-in-wrap">
        <div class="sign-in-box">

          <!-- LEFT SIDE -->
          <div class="left">
            <div class="content">
              <h3 class="heading text-Primary mb-8 text-center">Admin Login</h3>
              <div class="sub f14-regular text-GrayDark mb-24 text-center">
                Sign in to access the HealthRunCare admin panel
              </div>

              <div class="sign-in-inner">
                <form id="login-form" class="form-login flex flex-column gap24" autocomplete="off">
                  <fieldset class="email">
                    <div class="f14-regular mb-6">Admin Email</div>
                    <input id="email" class="flex-grow form-control" type="email" name="email" placeholder="admin@example.com" required>
                  </fieldset>

                  <fieldset class="password">
                    <div class="f14-regular mb-6">Password</div>
                    <div class="relative">
                      <input id="password" class="password-input form-control" type="password" name="password" placeholder="••••••••" required>
                      <span class="show-pass cursor-pointer absolute" style="right:12px;top:50%;transform:translateY(-50%);">
                        <span class="iconify view" data-icon="mdi:eye-outline" style="font-size:20px;"></span>
                        <span class="iconify hide" data-icon="mdi:eye-off-outline" style="font-size:20px;display:none;"></span>
                      </span>
                    </div>
                  </fieldset>

                  <div class="forgot-password-link">
                    <a href="/admin.forgotpassword" class="f14-regular f14-bold text-Primary">Forgot Password?</a>
                  </div>

                  <div class="tf-cart-checkbox mt-3 mb-3">
                    <div class="tf-checkbox-wrapp">
                      <input class="checkbox-item" type="checkbox" id="terms" checked>
                      <div><i class="icon-check"></i></div>
                    </div>
                    <label for="terms" class="f14-regular">
                      By signing in, you confirm your admin credentials are authorized.
                    </label>
                  </div>

                  <button type="submit" class="tf-button style-1 label-01 w-100 bg-Primary text-White">
                    Sign In
                  </button>

                  <div class="f14-regular text-center mt-4">
                    Don't have an admin account? <a href="/admin.register" class="f14-bold text-Primary">Register</a>
                  </div>

                  <div class="text-center mt-4">
                    <a href="/" class="tf-button style-1 bg-GrayLight text-Primary f14-bold" 
                      style="padding:10px 24px; border-radius:8px; display:inline-flex; align-items:center; gap:8px;">
                      <span class="iconify" data-icon="mdi:arrow-left"></span>
                      Back to Home
                    </a>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- RIGHT SIDE -->
          <div class="right">
                        <img src="../../assets/images/admin_signin.png" alt="AI Health Illustration">
            </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Loader -->
  <div id="loader" class="hidden">
    <div class="line-loader"><div></div><div></div><div></div><div></div><div></div></div>
  </div>

  <!-- Toast -->
  <div id="toast-container"></div>

  <!-- Scripts -->
  <script src="../../assets/js/api.js" defer></script>
  <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>
