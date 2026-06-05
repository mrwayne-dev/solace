<?php
$page_title = 'X-Rewards | Member-exclusive pricing | TitanXHoldings';
$page_description = 'Redeem accumulated yield for curated rewards at a 40% member discount. Devices, vouchers, experiences, travel.';
$page_path = '/xrewards';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <img src="/assets/images/x-rewards.webp" width="1080" height="1350" alt="" loading="eager" fetchpriority="high">
  </div>
  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>X-Rewards</p>
      <h1 class="hero__title">Spend your yield. Save 40%.</h1>
      <p class="hero__subtitle">
        Redeem accumulated returns for curated rewards at a 40% member discount. Tesla products, devices, vouchers, experiences, travel — all paid from your wallet.
      </p>
      <div class="hero__cta-row">
        <a href="/register" class="btn btn--primary">Become a member</a>
        <a href="/login" class="btn btn--ghost">Sign in</a>
      </div>
    </div>
  </div>
</section>

<section class="section section--white" id="how">
  <div class="container">
    <div class="section-header" style="margin-bottom: var(--space-10);">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>Member benefits</p>
      <h2 class="section-header__title">A reward layer, not a points scheme.</h2>
      <p class="section-header__body">X-Rewards is straight cash discount on retail, redeemed from your TXH wallet. No conversion rates, no tier games, no expiry.</p>
    </div>
    <div class="grid-2">
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 12v10H4V12"/><path d="M2 7h20v5H2zM12 22V7"/></svg></span>
        <h3 class="card-feature__title">40% off retail</h3>
        <p class="card-feature__desc">Member price = retail price × 0.60. We negotiate the discount directly with suppliers — no points, no inflation games.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg></span>
        <h3 class="card-feature__title">Pay from wallet</h3>
        <p class="card-feature__desc">No card needed at checkout. Your wallet balance covers the order, refundable to your wallet if cancelled.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 4h4l-1 16-7-3-7 3-1-16h4"/><path d="M8 4a4 4 0 0 1 8 0"/></svg></span>
        <h3 class="card-feature__title">Curated catalog</h3>
        <p class="card-feature__desc">Tesla, Apple, Garmin and more — items chosen for build quality, not commodity churn. Catalog refreshes monthly.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
        <h3 class="card-feature__title">Order tracking</h3>
        <p class="card-feature__desc">Pending → Confirmed → Shipped → Delivered, all visible on your dashboard. Cancel a pending order for a full wallet refund.</p>
      </article>
    </div>
  </div>
</section>

<?php $plan_product = 'xrewards'; include __DIR__ . '/_partials/plans-section.php'; ?>

<?php include __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
