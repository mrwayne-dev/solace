<?php
$page_title = 'About TitanXHoldings | FCA-Authorised Investment Platform';
$page_description = 'TitanXHoldings — London-based FCA-authorised investment platform giving everyday investors access to institutional-grade yield products, transparency, and FSCS-backed protections.';
$page_path = '/about';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<!-- =================== HERO =================== -->
<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <picture>
      <source type="image/avif" srcset="/assets/images/about.avif">
      <source type="image/webp" srcset="/assets/images/about.webp">
      <img src="/assets/images/about.webp" alt="" width="1800" height="1080" loading="eager" fetchpriority="high">
    </picture>
  </div>
  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>About TitanXHoldings</p>
      <h1 class="hero__title">We make wealth-building boring.</h1>
      <p class="hero__subtitle">
        We give everyday investors the same yield products, transparency, and protections institutions take for granted — in one regulated wallet.
      </p>
      <div class="hero__cta-row">
        <a href="/contact" class="btn btn--primary">Get in touch</a>
        <a href="/platform" class="btn btn--ghost">See the platform</a>
      </div>
    </div>
  </div>
</section>

<!-- =================== STORY =================== -->
<section class="section section--white" id="story">
  <div class="container">
    <div class="section-header section-header--wide" style="max-width: 760px; margin-bottom: var(--space-10);">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>Our story</p>
      <h2 class="section-header__title">Built to close the gap between retail and institutional investing.</h2>
      <p class="section-header__body">
        Founded in 2020 by a team of ex-fintech and investment-banking operators, TitanXHoldings was born from a simple frustration — retail investors were being offered ISAs and a few index funds, while institutions had access to fixed-income, fractional equity, infrastructure deals, and structured products that compounded quietly for decades. We built TXH to close that gap.
      </p>
    </div>

    <div class="grid-3">
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg></span>
        <h3 class="card-feature__title">Authorisation &amp; protection</h3>
        <p class="card-feature__desc">Authorised and regulated by the Financial Conduct Authority. Client money in segregated UK bank accounts. Eligible deposits FSCS-protected up to £85,000 per client.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 12c0 5-4 9-9 9s-9-4-9-9 4-9 9-9 9 4 9 9z"/><path d="M9 12l2 2 4-4"/></svg></span>
        <h3 class="card-feature__title">Our values</h3>
        <p class="card-feature__desc">Transparency, suitability, simplicity. No hidden tiers, no jargon-walled disclosures — every product shows yield, risk, and lock-up before you commit a pound.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12h4l3-9 4 18 3-9h4"/></svg></span>
        <h3 class="card-feature__title">Our vision</h3>
        <p class="card-feature__desc">Make institutional-grade investing the default. Where compounding works in the background, statements export in one click, and boring is easy.</p>
      </article>
    </div>
  </div>
</section>

<!-- =================== HOW IT STARTED — pull-quote =================== -->
<section class="section section--warm">
  <div class="container">
    <div class="testimonial">
      <h2 class="testimonial__quote">From a frustration, a new kind of platform was built.</h2>
      <div>
        <p class="eyebrow" style="margin-bottom: var(--space-4);"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>How it started</p>
        <p style="color: var(--color-ink-muted);">
          TitanXHoldings began in 2020, when the founding team — alumni of major UK banks, fintech start-ups, and asset managers — kept hearing the same complaint: <em>"Why are the only options my bank gives me a 0.5% saver and a cash ISA?"</em> With backgrounds in regulated financial services and investment operations, they set out to build a single platform that delivered institutional-grade yield products to retail savers, under the same FCA framework that governs the high street.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- =================== PRIVACY =================== -->
<section class="section section--white">
  <div class="container">
    <div class="testimonial">
      <h2 class="testimonial__quote">Your data is yours. Your money is yours. Always.</h2>
      <div>
        <p class="eyebrow" style="margin-bottom: var(--space-4);"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>Privacy policy</p>
        <p style="color: var(--color-ink-muted);">
          At TitanXHoldings, privacy is a core design principle. As a London-based, FCA-authorised investment firm, we operate under UK GDPR, the Data Protection Act 2018, and the FCA's SYSC and SUP requirements. Every customer's information is stored using end-to-end encryption and processed only on the lawful bases set out in our Privacy Notice. We never sell identifiable personal data, and our internal analytics run on de-identified datasets.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- =================== TERMS =================== -->
<section class="section section--warm" id="terms-of-use">
  <div class="container">
    <div class="testimonial">
      <h2 class="testimonial__quote">Fair use. Transparent impact.</h2>
      <div>
        <p class="eyebrow" style="margin-bottom: var(--space-4);"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>Terms of use</p>
        <p style="color: var(--color-ink-muted); margin-bottom: var(--space-3);">
          By opening an account you agree to our suitability process, fee schedule, and the FCA-mandated disclosures presented before each allocation. Customers may invest across X-Lock, X-Weekly, X-Shares, X-Grid, and X-Rewards — subject to per-product minimums, lock-up periods, and risk disclosures.
        </p>
        <p style="color: var(--color-ink-muted);">
          Past performance does not guarantee future returns; capital is at risk on non-FSCS-protected products. Accounts must be used only by the registered individual. Misuse may result in account suspension and referral to relevant authorities.
        </p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_partials/footer.php'; ?>

<script src="<?= txh_asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>
