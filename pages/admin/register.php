<?php require_once __DIR__ . '/../../config/assets.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="description" content="TitanXHoldings Admin — create a new administrator account.">
  <meta name="author" content="TitanXHoldings">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <link rel="canonical" href="https://titanxholdings.com/admin.register">
  <title>Admin Register | TitanXHoldings</title>

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
        <h1>Create an admin account</h1>
        <p>Restricted — requires authorisation.</p>
      </div>

      <form id="register-form" class="form-stack" autocomplete="off">
        <div class="form-field">
          <label class="form-field__label" for="username">Username</label>
          <input id="username" name="username" type="text" class="form-field__input" placeholder="jane.admin" required>
        </div>

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
          <p class="form-field__hint">Minimum 8 characters. Include a number or symbol.</p>
        </div>

        <div class="form-field">
          <label class="form-field__label" for="invite_code">Admin invite code</label>
          <input id="invite_code" name="invite_code" type="text" class="form-field__input" placeholder="Enter your invite code" autocomplete="off" required>
          <p class="form-field__hint">Required — provided by a platform administrator.</p>
        </div>

        <label style="display: inline-flex; align-items: flex-start; gap: var(--space-2); font-size: var(--text-sm); color: var(--color-ink-muted); cursor: pointer; line-height: 1.5;">
          <input type="checkbox" id="terms" checked style="margin-top: 3px;">
          <span>I confirm I am authorised to administer the TitanXHoldings platform.</span>
        </label>

        <button type="submit" class="btn btn--primary" style="width: 100%;">Create admin account</button>
      </form>

      <div class="form-footer">
        Already have an admin account? <a href="/admin.login">Sign in</a>
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
