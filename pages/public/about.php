<?php
$page_title = 'About Solace Mining | Crypto Mining Investment Platform';
$page_description = 'Solace Mining — a crypto mining investment platform giving everyday investors access to institutional-grade mining returns through simple, fixed daily-profit contracts.';
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
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>About Solace Mining</p>
      <h1 class="hero__title">We make wealth-building boring.</h1>
      <p class="hero__subtitle">
        We give everyday investors the same yield products, transparency, and protections institutions take for granted — in one regulated wallet.
      </p>
      <div class="hero__cta-row">
        <a href="/contact" class="btn btn--primary">Get in touch</a>
        <a href="/#plans" class="btn btn--ghost">See the plans</a>
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
        Founded by a team of mining engineers and fintech operators, Solace Mining was born from a simple frustration — industrial-scale crypto mining returns were locked behind the capital needed to run your own rigs. We built SLM to open that up, packaging mining into simple fixed daily-profit contracts anyone can join.
      </p>
    </div>

    <div class="grid-3">
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/></svg></span>
        <h3 class="card-feature__title">Security &amp; protection</h3>
        <p class="card-feature__desc">Funds held in segregated cold-storage wallets, never co-mingled with operating capital. Multi-factor authentication and an audit-grade ledger keep every transaction traceable end to end.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 12c0 5-4 9-9 9s-9-4-9-9 4-9 9-9 9 4 9 9z"/><path d="M9 12l2 2 4-4"/></svg></span>
        <h3 class="card-feature__title">Our values</h3>
        <p class="card-feature__desc">Transparency, fairness, simplicity. No hidden fees, no jargon — every tier shows its deposit range, daily profit, and duration before you commit a cent.</p>
      </article>
      <article class="card-feature">
        <span class="card-feature__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 12h4l3-9 4 18 3-9h4"/></svg></span>
        <h3 class="card-feature__title">Our vision</h3>
        <p class="card-feature__desc">Make institutional-grade mining the default. Where daily profit lands automatically, statements export in one click, and getting started is easy.</p>
      </article>
    </div>
  </div>
</section>

<!-- =================== REGISTERED & COMPLIANT — certificate =================== -->
<section class="section section--warm" id="registration">
  <div class="container">
    <div class="section-header section-header--center" style="max-width: 760px; margin: 0 auto var(--space-10);">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>Registered &amp; compliant</p>
      <h2 class="section-header__title">A registered Texas company.</h2>
      <p class="section-header__body">
        Solace Mining LLC is a domestic Limited Liability Company formed under the laws of the State of Texas and on file with the Texas Secretary of State.
      </p>
    </div>

    <div class="grid-2" style="gap: var(--space-10); align-items: center;">
      <!-- Certificate image -->
      <div>
        <a href="/assets/images/solace-certificate.jpeg" target="_blank" rel="noopener" aria-label="View the full Certificate of Formation">
          <img src="/assets/images/solace-certificate.jpeg" alt="Solace Mining LLC — Texas Certificate of Formation"
               loading="lazy"
               style="width: 100%; height: auto; border-radius: var(--radius-card); border: 1px solid var(--color-ink-muted); box-shadow: 0 12px 32px -12px rgba(0,0,0,.35); background: #fff;">
        </a>
        <p class="f12-regular" style="text-align: center; margin-top: var(--space-3); color: var(--color-ink-muted);">Tap the certificate to view full size.</p>
      </div>

      <!-- Formation details -->
      <div>
        <ul style="display: flex; flex-direction: column; gap: var(--space-3); margin-bottom: var(--space-5);">
          <li style="display: flex; justify-content: space-between; gap: var(--space-4); padding-bottom: var(--space-3); border-bottom: 1px solid rgba(0,0,0,.08);">
            <span style="color: var(--color-ink-muted);">Legal entity</span><span style="font-weight: var(--fw-semibold); text-align: right;">Solace Mining LLC</span>
          </li>
          <li style="display: flex; justify-content: space-between; gap: var(--space-4); padding-bottom: var(--space-3); border-bottom: 1px solid rgba(0,0,0,.08);">
            <span style="color: var(--color-ink-muted);">Entity type</span><span style="font-weight: var(--fw-semibold); text-align: right;">Limited Liability Company (LLC)</span>
          </li>
          <li style="display: flex; justify-content: space-between; gap: var(--space-4); padding-bottom: var(--space-3); border-bottom: 1px solid rgba(0,0,0,.08);">
            <span style="color: var(--color-ink-muted);">State of formation</span><span style="font-weight: var(--fw-semibold); text-align: right;">Texas, United States</span>
          </li>
          <li style="display: flex; justify-content: space-between; gap: var(--space-4); padding-bottom: var(--space-3); border-bottom: 1px solid rgba(0,0,0,.08);">
            <span style="color: var(--color-ink-muted);">File number</span><span style="font-weight: var(--fw-semibold); text-align: right;">01902920</span>
          </li>
          <li style="display: flex; justify-content: space-between; gap: var(--space-4); padding-bottom: var(--space-3); border-bottom: 1px solid rgba(0,0,0,.08);">
            <span style="color: var(--color-ink-muted);">Date of formation</span><span style="font-weight: var(--fw-semibold); text-align: right;">5 May 2024</span>
          </li>
          <li style="display: flex; justify-content: space-between; gap: var(--space-4); padding-bottom: var(--space-3); border-bottom: 1px solid rgba(0,0,0,.08);">
            <span style="color: var(--color-ink-muted);">Registered office</span><span style="font-weight: var(--fw-semibold); text-align: right;">4001 N. Central Expressway, Suite 300, Dallas, TX 75204</span>
          </li>
          <li style="display: flex; justify-content: space-between; gap: var(--space-4);">
            <span style="color: var(--color-ink-muted);">Certificate no.</span><span style="font-weight: var(--fw-semibold); text-align: right;">TX-2024-01902920</span>
          </li>
        </ul>
        <a href="https://www.sos.texas.gov/corp/index.shtml" target="_blank" rel="noopener" class="btn btn--primary">Verify with the Texas SOS</a>
      </div>
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
          Solace Mining began when the founding team — engineers and operators from mining, fintech, and infrastructure — kept hearing the same complaint: <em>"Why is real mining yield only for people who can afford their own hardware?"</em> They set out to build a single platform that delivers institutional-grade mining returns to everyday investors through transparent, fixed daily-profit contracts.
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
          At Solace Mining, privacy is a core design principle. Every customer's information is stored using end-to-end encryption and processed only on the lawful bases set out in our Privacy Notice. We never sell identifiable personal data, and our internal analytics run on de-identified datasets.
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
          By opening an account you agree to our terms, fee schedule, and the risk disclosures presented before each contract. Customers may invest across the Bronze, Silver, Gold, Platinum, and VIP tiers — subject to per-tier deposit ranges, contract durations, and risk disclosures.
        </p>
        <p style="color: var(--color-ink-muted);">
          Past performance does not guarantee future returns; capital is at risk. Accounts must be used only by the registered individual. Misuse may result in account suspension and referral to relevant authorities.
        </p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_partials/footer.php'; ?>

<script src="<?= txh_asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>
