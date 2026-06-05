<?php
$page_title = 'X-Shares | Fractional equity with scheduled payouts | TitanXHoldings';
$page_description = 'Own fractional positions in real companies. Weekly, monthly, or quarterly payouts settled to your wallet. From £100.';
$page_path = '/xshares';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <picture>
      <source type="image/avif" srcset="/assets/images/x-shares.avif">
      <source type="image/webp" srcset="/assets/images/x-shares.webp">
      <img src="/assets/images/x-shares.webp" alt="" width="1800" height="1080" loading="eager" fetchpriority="high">
    </picture>
  </div>
  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>X-Shares</p>
      <h1 class="hero__title">Own a slice. Earn on a schedule.</h1>
      <p class="hero__subtitle">
        Fractional positions in real companies, with payouts on a weekly, monthly, or quarterly schedule. Tesla. Meta. From £100.
      </p>
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
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>What makes it different</p>
      <h2 class="section-header__title">Equity exposure without the equity ticket.</h2>
      <p class="section-header__body">X-Shares lets you hold a meaningful position in companies you'd never afford whole — at a minimum that respects retail balances, and with payouts that hit on a clear schedule.</p>
    </div>
    <div class="grid-2">
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/></svg></span>
        <h3 class="card-feature__title">Fractional from £100</h3>
        <p class="card-feature__desc">A real slice of a real position. Hold a fractional stake; collect on the same schedule as a full holder.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 17l6-6 4 4 8-8"/></svg></span>
        <h3 class="card-feature__title">Scheduled payouts</h3>
        <p class="card-feature__desc">Choose periodic payouts (weekly / monthly / quarterly) or full settlement at maturity. Funds settle straight to your wallet.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 4h14v6l-7 9-7-9z"/></svg></span>
        <h3 class="card-feature__title">Curated asset list</h3>
        <p class="card-feature__desc">We don't list every ticker on every exchange. We curate companies with payout discipline and operational track record.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg></span>
        <h3 class="card-feature__title">Same protections</h3>
        <p class="card-feature__desc">Capital is at risk on equity products by definition — but client money is held in segregated UK accounts, with worst-case scenarios disclosed before each allocation.</p>
      </article>
    </div>
  </div>
</section>

<?php $plan_product = 'xshares'; include __DIR__ . '/_partials/plans-section.php'; ?>

<?php include __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
