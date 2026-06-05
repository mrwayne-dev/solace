<?php require_once __DIR__ . '/../../config/assets.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="description" content="TitanXHoldings Admin — manage the platform, users, and operations securely.">
  <meta name="author" content="TitanXHoldings">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <link rel="canonical" href="https://titanxholdings.com/admin.login">
  <title>Admin Sign in | TitanXHoldings</title>

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
        <h1>Sign in to the console</h1>
        <p>Authorised administrators only.</p>
      </div>

      <form id="login-form" class="form-stack" autocomplete="off">
        <div class="form-field">
          <label class="form-field__label" for="email">Admin email</label>
          <input id="email" name="email" type="email" class="form-field__input" placeholder="admin@titanxholdings.com" required>
        </div>

        <div class="form-field form-field--with-action">
          <label class="form-field__label" for="password">Password</label>
          <input id="password" name="password" type="password" class="form-field__input" placeholder="••••••••" required>
          <button type="button" class="form-field__action" aria-label="Show / hide password" onclick="(function(b){const i=b.previousElementSibling;i.type=i.type==='password'?'text':'password';})(this)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; font-size: var(--text-sm);">
          <label style="display: inline-flex; align-items: center; gap: var(--space-2); color: var(--color-ink-muted); cursor: pointer;">
            <input type="checkbox" id="terms" checked> Trusted device
          </label>
          <a href="/admin.forgotpassword" class="form-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn--primary" style="width: 100%;">Sign in</button>
      </form>

      <div class="form-footer">
        Need an admin account? <a href="/admin.register">Register</a>
        <span style="margin: 0 var(--space-2); opacity: 0.4;">·</span>
        <a href="/">Back to site</a>
      </div>
    </div>
  </div>
</main>

<div id="toast-container"></div>
<div id="loader" class="hidden"><div class="line-loader"><div></div><div></div><div></div><div></div><div></div></div></div>

<script src="<?= txh_asset('/assets/js/api.js') ?>" defer></script>
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>
