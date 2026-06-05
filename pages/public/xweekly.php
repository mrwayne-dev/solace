<?php
$page_title = 'X-Weekly | Automated weekly investing | TitanXHoldings';
$page_description = 'Set a weekly contribution, pick a plan, compound on autopilot. Pause, resume, or cancel anytime — no early-exit penalty.';
$page_path = '/xweekly';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <picture>
      <source type="image/avif" srcset="/assets/images/x-weekly.avif">
      <source type="image/webp" srcset="/assets/images/x-weekly.webp">
      <img src="/assets/images/x-weekly.webp" alt="" width="1800" height="1080" loading="eager" fetchpriority="high">
    </picture>
  </div>
  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>X-Weekly</p>
      <h1 class="hero__title">Compound on autopilot.</h1>
      <p class="hero__subtitle">
        Automated weekly contributions deployed into your chosen TXH strategy. Start from £50 a week. Pause, resume, or cancel from your dashboard — no early-exit penalty.
      </p>
      <div class="hero__cta-row">
        <a href="/register" class="btn btn--primary">Start a program</a>
        <a href="/login" class="btn btn--ghost">Sign in</a>
      </div>
    </div>
  </div>
</section>

<section class="section section--white" id="how">
  <div class="container">
    <div class="section-header" style="margin-bottom: var(--space-10);">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>How it works</p>
      <h2 class="section-header__title">Set it once. Compound forever.</h2>
      <p class="section-header__body">Pick a plan tier, fund your wallet, and X-Weekly handles the rest — debit, deploy, reinvest, repeat.</p>
    </div>
    <div class="grid-2">
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
        <h3 class="card-feature__title">Weekly debits, automatic</h3>
        <p class="card-feature__desc">Choose a weekly amount from £50 upwards. Funds debit from your wallet every 7 days, on a date you can plan around.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12h4l3-9 4 18 3-9h4"/></svg></span>
        <h3 class="card-feature__title">ROI compounded weekly</h3>
        <p class="card-feature__desc">Earnings credit back into the same strategy by default. Switch to wallet payout anytime if you'd rather take the income.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg></span>
        <h3 class="card-feature__title">Pause anytime, no penalty</h3>
        <p class="card-feature__desc">Tight month? Pause the program. Funds already invested keep earning on the original schedule — only the new debits stop.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg></span>
        <h3 class="card-feature__title">Same FCA protections</h3>
        <p class="card-feature__desc">Each weekly contribution sits inside the same FCA framework as the rest of your TXH wallet. Eligible balances FSCS-protected up to £85,000.</p>
      </article>
    </div>
  </div>
</section>

<?php $plan_product = 'xweekly'; include __DIR__ . '/_partials/plans-section.php'; ?>

<section class="section section--warm">
  <div class="container">
    <div class="testimonial">
      <h2 class="testimonial__quote">
        Setting up X-Weekly took me ten minutes — and now £200 a week routes straight into a strategy that's actually compounding.
      </h2>
      <div>
        <p class="eyebrow" style="margin-bottom: var(--space-4);"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>Member story</p>
        <div class="testimonial__attribution">
          <img src="/assets/images/avatar/default.png" width="2000" height="2000" alt="" class="testimonial__portrait" loading="lazy">
          <div>
            <p class="testimonial__name">Sarah Johnson</p>
            <p class="testimonial__role">X-Weekly member</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
