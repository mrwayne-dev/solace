<?php
$page_title = 'Why TitanXHoldings | The Platform Difference';
$page_description = 'Why investors choose TitanXHoldings: one wallet, honest pricing, audit-grade reporting, accessible minimums, FCA-authorised, FSCS-protected.';
$page_path = '/whytx';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<!-- =================== HERO =================== -->
<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <picture>
      <source type="image/avif" srcset="/assets/images/why-txh.avif">
      <source type="image/webp" srcset="/assets/images/why-txh.webp">
      <img src="/assets/images/why-txh.webp" alt="" width="1800" height="1080" loading="eager" fetchpriority="high">
    </picture>
  </div>
  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow">
        <span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>
        Why TitanXHoldings
      </p>
      <h1 class="hero__title">No reinvention. Just less friction.</h1>
      <p class="hero__subtitle">
        We don't reinvent investing. We remove the markup, the fragmentation, and the fine print — and return the difference to the people putting in the capital.
      </p>
      <div class="hero__cta-row">
        <a href="/register" class="btn btn--primary">Open an account</a>
        <a href="/platform" class="btn btn--ghost">See the products</a>
      </div>
    </div>
  </div>
</section>

<!-- =================== 6 DIFFERENTIATORS =================== -->
<section class="section section--white" id="diff">
  <div class="container">
    <div class="section-header" style="margin-bottom: var(--space-10);">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>The difference</p>
      <h2 class="section-header__title">Six reasons members switch.</h2>
      <p class="section-header__body">Each one is something we made non-negotiable from day one — not a marketing claim retrofitted to existing infrastructure.</p>
    </div>

    <div class="grid-2">
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="6" width="18" height="14" rx="2"/><path d="M7 6V4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/></svg></span>
        <h3 class="card-feature__title">One wallet</h3>
        <p class="card-feature__desc">Six regulated products, one balance, one statement. No bouncing between apps to manage savings, shares, and infrastructure.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 1v22M5 5h10a4 4 0 0 1 0 8H7a4 4 0 0 0 0 8h12"/></svg></span>
        <h3 class="card-feature__title">Honest pricing</h3>
        <p class="card-feature__desc">The rate quoted is the rate paid. No spread games, no hidden margin, no headline-rate footnotes you discover at maturity.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 13h6M9 17h4"/></svg></span>
        <h3 class="card-feature__title">Audit-grade ledger</h3>
        <p class="card-feature__desc">Every transaction has a reference. Every reference reconciles to your bank. Export six months of activity in one click for your accountant.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
        <h3 class="card-feature__title">Accessible minimums</h3>
        <p class="card-feature__desc">Start from £50 on X-Weekly, scale up over time. The minimums that lock out retail investors elsewhere don't exist here.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg></span>
        <h3 class="card-feature__title">FCA + FSCS</h3>
        <p class="card-feature__desc">Authorised by the FCA. Eligible deposits FSCS-protected up to £85,000. Client money held in segregated accounts at a UK-regulated banking partner.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12h4l3-9 4 18 3-9h4"/></svg></span>
        <h3 class="card-feature__title">Compounding by default</h3>
        <p class="card-feature__desc">ROI is reinvested into your chosen strategy unless you tell us otherwise. The earlier you start, the more your future self thanks you.</p>
      </article>
    </div>
  </div>
</section>

<!-- =================== PULL QUOTE =================== -->
<section class="section section--warm">
  <div class="container">
    <div class="testimonial">
      <h2 class="testimonial__quote">
        We democratise yield. The products institutions use, available from £50 — under the same FCA framework that governs the high street.
      </h2>
      <div>
        <p class="eyebrow" style="margin-bottom: var(--space-4);">
          <span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>
          Our mission
        </p>
        <p style="color: var(--color-ink-muted);">For decades, the best yields, terms, and reporting were reserved for clients with seven-figure balances. We built TitanXHoldings to flatten that.</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
