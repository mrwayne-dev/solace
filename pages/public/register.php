<?php
$page_title = 'Create your account | Solace Mining';
$page_description = 'Open a Solace Mining account in minutes. crypto mining platform with secured savings, fractional shares, and automated investing.';
$page_path = '/register';
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
          New here
        </p>
        <h1>Open your account</h1>
        <p>Takes about three minutes. secured from your first deposit.</p>
      </div>

      <form id="register-form" class="form-stack" autocomplete="off">
        <input type="hidden" id="ref" value="<?= htmlspecialchars($_GET['ref'] ?? '', ENT_QUOTES) ?>">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-3);">
          <div class="form-field">
            <label class="form-field__label" for="first_name">First name</label>
            <input id="first_name" name="first_name" type="text" class="form-field__input" placeholder="John" required>
          </div>
          <div class="form-field">
            <label class="form-field__label" for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" class="form-field__input" placeholder="Doe" required>
          </div>
        </div>

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
          <p class="form-field__hint">Minimum 8 characters. Include a number or symbol.</p>
        </div>

        <label style="display: inline-flex; align-items: flex-start; gap: var(--space-2); font-size: var(--text-sm); color: var(--color-ink-muted); cursor: pointer; line-height: 1.5;">
          <input type="checkbox" id="terms" checked style="margin-top: 3px;">
          <span>By signing up, you agree to our <a href="/terms" class="form-link" style="text-decoration-color: var(--color-ink-primary);">Terms &amp; Conditions</a> and acknowledge our Privacy Policy.</span>
        </label>

        <button type="submit" class="btn btn--primary" style="width: 100%;">Create account</button>
      </form>

      <div class="form-footer">
        Already have an account? <a href="/login">Sign in</a>
      </div>
    </div>
  </div>
</main>

<div id="toast-container"></div>
<div id="loader" class="hidden"><div class="line-loader"><div></div><div></div><div></div><div></div><div></div></div></div>

<?php include __DIR__ . '/_partials/footer.php'; ?>

<script src="<?= txh_asset('/assets/js/api.js') ?>" defer></script>
<script src="<?= txh_asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>
