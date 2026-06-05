<?php
$page_title = 'X-Grid | Institutional infrastructure co-investments | TitanXHoldings';
$page_description = 'TitanXHoldings X-Grid — fractional positions in UK infrastructure deals normally reserved for institutions. Clear minimums, projected returns, quarterly reports.';
$page_path = '/xgrid';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <picture>
      <source type="image/avif" srcset="/assets/images/xgrid-bg.avif">
      <source type="image/webp" srcset="/assets/images/xgrid-bg.webp">
      <img src="/assets/images/xgrid-bg.webp" alt="" width="1920" height="1080" loading="eager" fetchpriority="high">
    </picture>
  </div>
  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>X-Grid</p>
      <h1 class="hero__title">Institutional infrastructure, retail-friendly minimums.</h1>
      <p class="hero__subtitle">Fractional positions in operational UK infrastructure — solar, logistics, data centres, fibre — alongside the institutional LPs that normally dominate the cap table.</p>
      <div class="hero__cta-row">
        <a href="/register" class="btn btn--primary">Co-invest</a>
        <a href="/login" class="btn btn--ghost">Sign in</a>
      </div>
    </div>
  </div>
</section>

<section class="section section--white" id="how">
  <div class="container">
    <div class="section-header" style="margin-bottom: var(--space-10);">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>How it works</p>
      <h2 class="section-header__title">Real assets. Contracted revenue. Quarterly distributions.</h2>
      <p class="section-header__body">Each X-Grid deal is a real UK infrastructure asset with documented counterparty contracts. We slice it into co-investment positions, you collect a share of the contracted revenue.</p>
    </div>
    <div class="grid-2">
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21h18M5 21V9l7-5 7 5v12"/></svg></span>
        <h3 class="card-feature__title">Operational UK assets</h3>
        <p class="card-feature__desc">Solar portfolios, last-mile logistics, Tier-III data centres, FTTP roll-outs. Income-producing from day one.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v4M16 2v4"/></svg></span>
        <h3 class="card-feature__title">Quarterly distributions</h3>
        <p class="card-feature__desc">Contracted revenue → quarterly payouts to your wallet. Income tracks operational performance, with downside protected by minimum take-or-pay clauses where applicable.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 13h6M9 17h4"/></svg></span>
        <h3 class="card-feature__title">Quarterly performance report</h3>
        <p class="card-feature__desc">Per-deal report covering utilisation, revenue, distributions, and any material counterparty changes. Read it in five minutes, file it for the year-end.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg></span>
        <h3 class="card-feature__title">From £500, scale to institutional</h3>
        <p class="card-feature__desc">Most deals start at £500–£2,500. The "Institutional Slot" opens an allocation normally reserved for £25,000+ LPs.</p>
      </article>
    </div>
  </div>
</section>

<?php $plan_product = 'xgrid'; include __DIR__ . '/_partials/plans-section.php'; ?>

<?php include __DIR__ . '/_partials/footer.php'; ?>

<script src="<?= txh_asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>
