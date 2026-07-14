<?php
$page_title = 'Sign in | Solace Mining';
$page_description = 'Sign in to your Solace Mining account to manage your investments and portfolio.';
$page_path = '/login';
$nav_variant = 'solid';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<main class="auth-page">
  <div class="container">
    <div class="form-card">
      <div class="form-card__header">
        <p class="eyebrow">
          <span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>
          Welcome back
        </p>
        <h1>Sign in to Solace Mining</h1>
        <p>Manage your investments, savings, and portfolio.</p>
      </div>

      <form id="login-form" class="form-stack" autocomplete="off">
        <div class="form-field">
          <label class="form-field__label" for="email">Email address</label>
          <input id="email" name="email" type="email" class="form-field__input" placeholder="you@example.com" required>
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
            <input type="checkbox" id="terms" checked> Stay signed in
          </label>
          <a href="/forgotpassword" class="form-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn--primary" style="width: 100%;">Sign in</button>
      </form>

      <div class="form-footer">
        Don't have an account? <a href="/register">Create one</a>
      </div>
    </div>
  </div>
</main>

<!-- Toast container preserved for api.js compatibility -->
<div id="toast-container"></div>
<div id="loader" class="hidden"><div class="line-loader"><div></div><div></div><div></div><div></div><div></div></div></div>

<?php include __DIR__ . '/_partials/footer.php'; ?>

<script src="<?= txh_asset('/assets/js/api.js') ?>" defer></script>
<script src="<?= txh_asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>
