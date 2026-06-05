<?php
$page_title = 'X-Lock | Fixed-term savings with FSCS protection | TitanXHoldings';
$page_description = 'TitanXHoldings X-Lock — fixed-term savings with rates above the high street. FSCS-protected up to £85,000. Payout on maturity.';
$page_path = '/xlock';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <picture>
      <source type="image/avif" srcset="/assets/images/x-lock.avif">
      <source type="image/webp" srcset="/assets/images/x-lock.webp">
      <img src="/assets/images/x-lock.webp" alt="" width="1800" height="1080" loading="eager" fetchpriority="high">
    </picture>
  </div>
  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>X-Lock</p>
      <h1 class="hero__title">Lock the rate. Keep the protection.</h1>
      <p class="hero__subtitle">Fixed-term savings with rates well above the high street. Capital is FSCS-protected up to £85,000 and pays out automatically on maturity.</p>
      <div class="hero__cta-row">
        <a href="/register" class="btn btn--primary">Open a position</a>
        <a href="/login" class="btn btn--ghost">Sign in</a>
      </div>
    </div>
  </div>
</section>

<section class="section section--white" id="how">
  <div class="container">
    <div class="section-header" style="margin-bottom: var(--space-10);">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>How it works</p>
      <h2 class="section-header__title">The boring half of a real portfolio.</h2>
      <p class="section-header__body">X-Lock anchors the safe layer. Lock a term, lock a rate, sleep through the news cycle. Capital protected, maturity date visible from day one.</p>
    </div>
    <div class="grid-2">
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
        <h3 class="card-feature__title">FSCS-protected to £85,000</h3>
        <p class="card-feature__desc">Eligible deposits covered under the Financial Services Compensation Scheme. Client money sits in segregated UK bank accounts.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
        <h3 class="card-feature__title">Maturity date you can plan around</h3>
        <p class="card-feature__desc">Payout date locks at enrolment and shows on your dashboard. No "renewal trap" — funds settle to your wallet on schedule.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 17l6-6 4 4 8-8"/></svg></span>
        <h3 class="card-feature__title">Rates above the high street</h3>
        <p class="card-feature__desc">5.5%–22% annualised across our plan tiers. The rate quoted at enrolment is the rate paid — no spread games.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg></span>
        <h3 class="card-feature__title">Optional early unlock</h3>
        <p class="card-feature__desc">Need liquidity? Early unlock available with a documented penalty disclosed before you commit. No surprises.</p>
      </article>
    </div>
  </div>
</section>

<?php $plan_product = 'xlock'; include __DIR__ . '/_partials/plans-section.php'; ?>

<?php include __DIR__ . '/_partials/footer.php'; ?>

<script src="<?= txh_asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>
