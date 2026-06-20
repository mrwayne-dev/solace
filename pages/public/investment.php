<?php
$page_title = 'mining contracts | Fixed-duration investment plans | Solace Mining';
$page_description = 'Solace Mining mining contracts — fixed-duration investment plans with a known ROI and a known maturity date. Pick a tier, fund it, watch it compound.';
$page_path = '/investment';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <picture>
      <source type="image/avif" srcset="/assets/images/x-yield.avif">
      <source type="image/webp" srcset="/assets/images/x-yield.webp">
      <img src="/assets/images/x-yield.webp" alt="" width="1800" height="1080" loading="eager" fetchpriority="high">
    </picture>
  </div>
  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>mining contracts</p>
      <h1 class="hero__title">Known ROI. Known maturity. Compound.</h1>
      <p class="hero__subtitle">Fixed-duration investment plans. Set the rate at enrolment, fund the plan, and watch it compound to maturity.</p>
      <div class="hero__cta-row">
        <a href="/register" class="btn btn--primary">Start a plan</a>
        <a href="/login" class="btn btn--ghost">Sign in</a>
      </div>
    </div>
  </div>
</section>

<section class="section section--white" id="how">
  <div class="container">
    <div class="section-header" style="margin-bottom: var(--space-10);">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>How it works</p>
      <h2 class="section-header__title">Fixed-duration, fixed-rate, fixed-maturity.</h2>
      <p class="section-header__body">No surprises. Every mining contracts plan locks the rate at enrolment, sets a maturity date, and pays out on schedule.</p>
    </div>
    <div class="grid-2">
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
        <h3 class="card-feature__title">Pick a duration</h3>
        <p class="card-feature__desc">From six months to 36 months. Shorter terms for liquidity, longer terms for elevated yield.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 17l6-6 4 4 8-8"/></svg></span>
        <h3 class="card-feature__title">Known annualised rate</h3>
        <p class="card-feature__desc">The rate quoted is the rate paid. No headline-rate footnotes you discover at maturity.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v4M16 2v4"/></svg></span>
        <h3 class="card-feature__title">Optional periodic payouts</h3>
        <p class="card-feature__desc">Take income annually, semi-annually, or quarterly — or stack the full payout at maturity.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg></span>
        <h3 class="card-feature__title">Same strong protections</h3>
        <p class="card-feature__desc">Funds held in segregated cold-storage wallets. Balances secured with multi-factor authentication and an audit-grade ledger.</p>
      </article>
    </div>
  </div>
</section>

<?php $plan_product = 'xyield'; include __DIR__ . '/_partials/plans-section.php'; ?>

<?php include __DIR__ . '/_partials/footer.php'; ?>

<script src="<?= txh_asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>
