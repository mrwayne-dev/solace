<?php require_once __DIR__ . '/../../config/assets.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="description" content="TitanXHoldings Admin — reset your administrator password.">
  <meta name="author" content="TitanXHoldings">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <link rel="canonical" href="https://titanxholdings.com/admin.forgotpassword">
  <title>Admin Password Reset | TitanXHoldings</title>

  <link rel="stylesheet" href="<?= txh_asset('/assets/css/txh-design.css') ?>">

  <link rel="icon" type="image/png" href="/assets/favicon/favicon-32x32.png" sizes="32x32">
  <link rel="shortcut icon" href="/assets/favicon/favicon.ico">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
  <meta name="apple-mobile-web-app-title" content="TitanXHoldings">
</head>

<body class="txh-redesign">

<main class="auth-page">
  <div class="container">
    <div class="form-card">
      <div class="form-card__header">
        <a href="/" aria-label="TitanXHoldings home" style="margin-bottom: var(--space-2);">
          <img src="/assets/images/logo/titanx-black.png" alt="TitanXHoldings" style="height: 30px;">
        </a>
        <p class="eyebrow">
          <span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>
          Admin Console
        </p>
        <h1>Reset admin password</h1>
        <p>Enter your admin email to receive a one-time code.</p>
      </div>

      <!-- Step 1 — request OTP -->
      <form id="forgot-step1" class="form-stack" autocomplete="off">
        <div class="form-field">
          <label class="form-field__label" for="forgot-email">Admin email</label>
          <input id="forgot-email" type="email" class="form-field__input" placeholder="admin@titanxholdings.com" required>
        </div>
        <button type="submit" class="btn btn--primary" style="width: 100%;">Send code</button>
      </form>

      <!-- Step 2 — verify OTP -->
      <form id="forgot-step2" class="form-stack hidden" autocomplete="off" style="margin-top: var(--space-5);">
        <div class="form-field">
          <label class="form-field__label" for="otp">Enter the 6-digit code</label>
          <input id="otp" type="text" class="form-field__input" placeholder="••••••" maxlength="6" required>
          <p class="form-field__hint">Check your inbox (and spam folder).</p>
        </div>
        <button type="submit" class="btn btn--primary" style="width: 100%;">Verify code</button>
      </form>

      <!-- Step 3 — set new password -->
      <form id="forgot-step3" class="form-stack hidden" autocomplete="off" style="margin-top: var(--space-5);">
        <div class="form-field">
          <label class="form-field__label" for="new_password">New password</label>
          <input id="new_password" type="password" class="form-field__input" placeholder="••••••••" required>
          <p class="form-field__hint">Minimum 8 characters. Include a number or symbol.</p>
        </div>
        <button type="submit" class="btn btn--primary" style="width: 100%;">Reset password</button>
      </form>

      <div class="form-footer">
        Remembered it? <a href="/admin.login">Sign in</a>
      </div>
    </div>
  </div>
</main>

<div id="toast-container"></div>
<div id="loader" class="hidden"><div class="line-loader"><div></div><div></div><div></div><div></div><div></div></div></div>

<script src="<?= txh_asset('/assets/js/api.js') ?>" defer></script>
</body>
</html>
