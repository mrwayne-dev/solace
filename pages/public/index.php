<?php require_once __DIR__ . '/../../config/assets.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Meta -->
  <meta charset="UTF-8">
  <meta name="description" content="Solace Mining is a crypto mining investment platform — choose a tier, earn a fixed daily profit for 5 days, and get your principal back, all from one secure wallet with a 10% referral commission.">
  <meta name="author" content="Solace Mining">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://solacemining.org/">


  <!-- Title -->
  <title>Solace Mining – Build Wealth on Autopilot</title>

  <!-- Preload CSS -->
  <link rel="preload" href="<?= txh_asset('../../assets/css/main.css') ?>" as="style">

  <!-- Preload Fonts -->
  <link rel="preload" href="../../assets/fonts/HostGrotesk-Regular.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="../../assets/fonts/HostGrotesk-Bold.woff2" as="font" type="font/woff2" crossorigin>
  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?= txh_asset('../../assets/css/main.css') ?>">
  <link rel="stylesheet" href="<?= txh_asset('../../assets/css/responsive.css') ?>">
  <link rel="stylesheet" href="<?= txh_asset('../../assets/css/txh-design.css') ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="../../assets/favicon/favicon-32x32.png" sizes="32x32">
  <link rel="shortcut icon" href="../../assets/favicon/favicon.ico">
  <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicon/apple-touch-icon.png">
  <meta name="apple-mobile-web-app-title" content="Solace Mining">
  <link rel="manifest" href="../../assets/favicon/site.webmanifest">
  
    <!-- Smartsupp Live Chat script -->
  <script type="text/javascript">
  var _smartsupp = _smartsupp || {};
  _smartsupp.key = '3c2dbbfc4e90eff8ecbbe0a2f4936d2be60ccec7';
  window.smartsupp||(function(d) {
    var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
    s=d.getElementsByTagName('script')[0];c=d.createElement('script');
    c.type='text/javascript';c.charset='utf-8';c.async=true;
    c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
  })(document);
  </script>
  <noscript>Powered by <a href="https://www.smartsupp.com" target="_blank" rel="noopener noreferrer">Smartsupp</a></noscript>

</head>
<body class="txh-redesign">

<!-- =================== NAVBAR (floating dock) =================== -->
<?php include __DIR__ . '/_partials/navbar.php'; ?>

<!-- =================== HERO (Crestmark C.2 adapted) =================== -->
<section class="hero hero--center" id="hero">
  <div class="hero__bg" aria-hidden="true">
    <picture>
      <source type="image/avif" srcset="/assets/images/txh-home.avif">
      <source type="image/webp" srcset="/assets/images/txh-home.webp">
      <img src="/assets/images/txh-home.webp" alt="" width="1800" height="1080" loading="eager" fetchpriority="high">
    </picture>
  </div>

  <div class="container hero__inner">
    <div class="hero__content">
      <p class="eyebrow">
        <span class="eyebrow__icon">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
        </span>
        Daily payouts · Principal returned
      </p>

      <h1 class="hero__title">Mine smarter. Earn daily.</h1>

      <p class="hero__subtitle">
        Choose a mining tier, earn a fixed daily profit for five days, and get your principal back when the contract completes — all from one secure wallet, with a 10% referral commission on every miner you bring.
      </p>

      <div class="hero__cta-row">
        <a href="/#plans" class="btn btn--primary">See the plans</a>
        <a href="/register" class="btn btn--ghost">Open an account</a>
      </div>
    </div>
  </div>

</section>


<!-- =================== SMARTER INVESTING (Crestmark D.2 + 2-col) =================== -->
<section class="section section--white" id="smarter-investing">
  <div class="container">
    <div class="grid-2" style="gap: var(--space-10); align-items: center;">
      <div class="section-header section-header--wide">
        <p class="eyebrow">
          <span class="eyebrow__icon">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
          </span>
          The Platform
        </p>
        <h2 class="section-header__title">Smarter mining.</h2>
        <p class="section-header__body">
          Solace Mining packages institutional-grade mining into simple, fixed-profit contracts. Pick a tier from Bronze to VIP, fund your wallet, and earn a set daily return for five days — your principal returns when the contract completes.
        </p>
        <div class="section-header__cta">
          <a href="/#how-it-works" class="btn btn--primary">See how it works</a>
        </div>
      </div>

      <div>
        <img src="../../assets/images/smarter.webp" width="1200" height="900" alt="Modern investment platform illustration" loading="lazy" style="border-radius: var(--radius-card); width: 100%;">
      </div>
    </div>
  </div>
</section>


<!-- =================== ONE WALLET (Crestmark D.2 centered + D.5 icon grid) =================== -->
<section class="section section--warm">
  <div class="container">
    <div class="section-header section-header--center" style="margin-bottom: var(--space-10);">
      <p class="eyebrow">
        <span class="eyebrow__icon">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
        </span>
        Architecture
      </p>
      <h2 class="section-header__title">One wallet. Every way to grow it.</h2>
      <p class="section-header__body">
        Solace Mining is built around five mining tiers — Bronze, Silver, Gold, Platinum, and VIP. Fund your wallet once, choose the tier that fits your deposit, and route your capital wherever your strategy needs it next.
      </p>
    </div>

    <div class="grid-2">
      <article class="card-feature">
        <span class="card-feature__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>
          </svg>
        </span>
        <h3 class="card-feature__title">Fixed daily profit</h3>
        <p class="card-feature__desc">Lock in a tier and earn a fixed daily rate for the full five-day contract — credited to your wallet every single day.</p>
      </article>

      <article class="card-feature">
        <span class="card-feature__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M3 17l6-6 4 4 8-8"/><path d="M14 7h7v7"/>
          </svg>
        </span>
        <h3 class="card-feature__title">Principal returned in full</h3>
        <p class="card-feature__desc">When your contract completes, your original deposit returns to your wallet. The rate quoted is the rate paid.</p>
      </article>

      <article class="card-feature">
        <span class="card-feature__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M3 12a9 9 0 1 1 9 9"/><path d="M3 12l4-4M3 12l4 4"/>
          </svg>
        </span>
        <h3 class="card-feature__title">Reinvest and compound</h3>
        <p class="card-feature__desc">Roll a completed contract straight into a new tier to compound your earnings — or withdraw at any time.</p>
      </article>

      <article class="card-feature">
        <span class="card-feature__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/>
          </svg>
        </span>
        <h3 class="card-feature__title">Secure by design</h3>
        <p class="card-feature__desc">Funds sit in a secured wallet with segregated cold storage and an audit-grade reference on every transaction.</p>
      </article>
    </div>
  </div>
</section>



<!-- =================== HOW IT WORKS (doc nav) =================== -->
<section class="section section--white" id="how-it-works">
  <div class="container">
    <div class="section-header section-header--center" style="margin-bottom: var(--space-10);">
      <p class="eyebrow">
        <span class="eyebrow__icon">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
        </span>
        How it works
      </p>
      <h2 class="section-header__title">From sign-up to payout in six steps.</h2>
      <p class="section-header__body">
        No jargon, no lock-in surprises. Create an account, fund your wallet, pick a tier, and watch your profit accrue every day.
      </p>
    </div>

    <div class="steps">
      <article class="step">
        <span class="step__num">1</span>
        <h3 class="step__title">Create account</h3>
        <p class="step__desc">Register in under a minute and verify your email to unlock your secure wallet.</p>
      </article>
      <article class="step">
        <span class="step__num">2</span>
        <h3 class="step__title">Fund your wallet</h3>
        <p class="step__desc">Deposit crypto to your balance — funds are confirmed and ready to mine instantly.</p>
      </article>
      <article class="step">
        <span class="step__num">3</span>
        <h3 class="step__title">Choose a plan</h3>
        <p class="step__desc">Pick a mining tier from Bronze to VIP to match your deposit and target daily profit.</p>
      </article>
      <article class="step">
        <span class="step__num">4</span>
        <h3 class="step__title">Earn daily profit</h3>
        <p class="step__desc">Your fixed daily rate is credited to your wallet every single day of the contract.</p>
      </article>
      <article class="step">
        <span class="step__num">5</span>
        <h3 class="step__title">Withdraw earnings</h3>
        <p class="step__desc">Principal returns at completion. Withdraw anytime, or reinvest to compound your returns.</p>
      </article>
      <article class="step">
        <span class="step__num">6</span>
        <h3 class="step__title">Refer &amp; earn</h3>
        <p class="step__desc">Share your referral link and earn a 10% commission on every miner you bring on board.</p>
      </article>
    </div>
  </div>
</section>

<!-- =================== INVESTMENT PLANS (live tiers from DB) =================== -->
<?php include __DIR__ . '/_partials/plans-section.php'; ?>


<!-- =================== VISION 1 — Democratise Yield (Crestmark D.13 pull-quote) =================== -->
<section class="section section--warm" id="vision">
  <div class="container">
    <div class="testimonial">
      <div>
        <p class="eyebrow" style="margin-bottom: var(--space-5);">
          <span class="eyebrow__icon">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
          </span>
          Vision
        </p>
        <p class="testimonial__quote">We democratise mining. Institutional-grade hashpower, available from $100.</p>
      </div>
      <div>
        <p style="font-size: var(--text-body); line-height: var(--lh-body); color: var(--color-ink-muted);">
          For decades, the most profitable mining operations were reserved for those with the capital to run their own rigs. We built Solace Mining to flatten that — the same industrial mining returns, packaged into fixed daily-profit contracts, available from $100 upwards.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- =================== VISION 2 — Beat Idle Cash =================== -->
<section class="section section--white">
  <div class="container">
    <div class="testimonial">
      <div>
        <p class="eyebrow" style="margin-bottom: var(--space-5);">
          <span class="eyebrow__icon">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
          </span>
          Vision
        </p>
        <p class="testimonial__quote">We beat idle cash. Whatever's sitting still goes to work the moment it lands.</p>
      </div>
      <div>
        <p style="font-size: var(--text-body); line-height: var(--lh-body); color: var(--color-ink-muted);">
          Idle capital is a slow tax. Solace Mining turns a deposit — payday surplus, an unspent bonus, an emergency-fund overflow — into a yield-generating mining contract the moment it lands in your wallet, with the option to withdraw or reinvest on your schedule.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- =================== STATS (Crestmark C.5 / D.7 counter cards) =================== -->
<section class="section section--warm">
  <div class="container">
    <div class="section-header" style="margin-bottom: var(--space-10);">
      <p class="eyebrow">
        <span class="eyebrow__icon">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
        </span>
        Our Numbers
      </p>
      <h2 class="section-header__title">Built for real capital. Measured in trust.</h2>
      <p class="section-header__body">
        Solace Mining was built for real people deploying real capital. These figures reflect what our members have built on the platform — and the trust that's let us keep building.
      </p>
    </div>

    <div class="grid-3">
      <article class="card-stat">
        <span class="card-stat__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <circle cx="9" cy="8" r="4"/><path d="M2 22a7 7 0 0 1 14 0M17 11a3 3 0 1 0 0-6M22 22a5 5 0 0 0-7-5"/>
          </svg>
        </span>
        <p class="card-stat__desc">Miners actively earning</p>
        <p class="card-stat__value"><span data-count="12000">0</span>+</p>
      </article>

      <article class="card-stat">
        <span class="card-stat__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M3 17l6-6 4 4 8-8"/><path d="M14 7h7v7"/>
          </svg>
        </span>
        <p class="card-stat__desc">Daily payouts settled on time</p>
        <p class="card-stat__value"><span data-count="6800">0</span>+</p>
      </article>

      <article class="card-stat">
        <span class="card-stat__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/>
          </svg>
        </span>
        <p class="card-stat__desc">Days of clean audit trail</p>
        <p class="card-stat__value"><span data-count="320">0</span></p>
      </article>
    </div>
  </div>
</section>

<!-- =================== WHY SLM (Crestmark C.8 6-feature grid) =================== -->
<section class="section section--white">
  <div class="container">
    <div class="section-header" style="margin-bottom: var(--space-10);">
      <p class="eyebrow">
        <span class="eyebrow__icon">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
        </span>
        Why Solace Mining
      </p>
      <h2 class="section-header__title">No reinvention. Just less friction.</h2>
      <p class="section-header__body">
        We don't reinvent investing — we remove the markup, the fragmentation, and the fine print, then return the difference to the people putting in the capital.
      </p>
      <div class="section-header__cta">
        <a href="/contact" class="btn btn--primary">Start a conversation</a>
      </div>
    </div>

    <div class="grid-2">
      <article class="card-feature">
        <span class="card-feature__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="6" width="18" height="14" rx="2"/><path d="M7 6V4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/>
          </svg>
        </span>
        <h3 class="card-feature__title">One wallet</h3>
        <p class="card-feature__desc">Every tier, every contract, one balance and one statement. No juggling rigs, pools, or dashboards.</p>
      </article>

      <article class="card-feature">
        <span class="card-feature__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M12 1v22M5 5h10a4 4 0 0 1 0 8H7a4 4 0 0 0 0 8h12"/>
          </svg>
        </span>
        <h3 class="card-feature__title">Honest pricing</h3>
        <p class="card-feature__desc">The rate quoted is the rate paid. No spread games, no hidden margin, no "headline rate" footnotes you discover at maturity.</p>
      </article>

      <article class="card-feature">
        <span class="card-feature__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 13h6M9 17h4"/>
          </svg>
        </span>
        <h3 class="card-feature__title">Audit-grade ledger</h3>
        <p class="card-feature__desc">Every transaction has a reference. Every reference reconciles to your bank. Export six months of activity in one click for your accountant.</p>
      </article>

      <article class="card-feature">
        <span class="card-feature__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
          </svg>
        </span>
        <h3 class="card-feature__title">Accessible minimums</h3>
        <p class="card-feature__desc">Start from $100 on the Bronze tier and scale up to VIP. The minimums that lock retail miners out elsewhere don't exist here.</p>
      </article>

      <article class="card-feature">
        <span class="card-feature__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M12 2l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V6l8-4z"/><path d="M9 12l2 2 4-4"/>
          </svg>
        </span>
        <h3 class="card-feature__title">Secure &amp; transparent</h3>
        <p class="card-feature__desc">Segregated cold-storage wallets, multi-factor authentication, and an audit-grade ledger reviewed regularly. Every payout is traceable end to end.</p>
      </article>

      <article class="card-feature">
        <span class="card-feature__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M3 12h4l3-9 4 18 3-9h4"/>
          </svg>
        </span>
        <h3 class="card-feature__title">Compounding by default</h3>
        <p class="card-feature__desc">Completed contracts can roll straight into a new tier to compound your daily profit. The earlier you start, the more it adds up.</p>
      </article>
    </div>
  </div>
</section>

<!-- =================== TESTIMONIAL (Crestmark C.4 single pull-quote) =================== -->
<section class="section section--warm">
  <div class="container">
    <div class="testimonial">
      <h2 class="testimonial__quote">
        I'd left my crypto sitting in a wallet doing nothing for years. Setting up a Gold contract took ten minutes — and now a fixed profit lands in my balance every single day, with my principal returned at the end.
      </h2>
      <div>
        <p class="eyebrow" style="margin-bottom: var(--space-4);">
          <span class="eyebrow__icon">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
          </span>
          Member story
        </p>
        <div class="testimonial__attribution">
          <img src="../../assets/images/avatar/default.png" width="2000" height="2000" alt="" class="testimonial__portrait" loading="lazy">
          <div>
            <p class="testimonial__name">Sarah Johnson</p>
            <p class="testimonial__role">Gold tier miner, Solace Mining</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- =================== FAQ (Crestmark C.11 accordion) =================== -->
<section class="section section--warm">
  <div class="container">
    <div class="section-header section-header--center" style="margin-bottom: var(--space-10);">
      <p class="eyebrow">
        <span class="eyebrow__icon">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg>
        </span>
        FAQs
      </p>
      <h2 class="section-header__title">Frequently asked questions.</h2>
      <p class="section-header__body">Quick answers to the questions members ask most often.</p>
      <div class="section-header__cta">
        <a href="/contact" class="btn btn--primary">Connect with our team</a>
      </div>
    </div>

    <div class="accordion">
      <details class="accordion__item">
        <summary class="accordion__trigger">
          What is Solace Mining?
          <span class="accordion__icon" aria-hidden="true"></span>
        </summary>
        <div class="accordion__body">
          Solace Mining is a crypto mining investment platform. You choose a tier — Bronze, Silver, Gold, Platinum, or VIP — fund your wallet, and earn a fixed daily profit for a five-day contract. Your principal is returned when the contract completes, and you earn a 10% commission on every miner you refer.
        </div>
      </details>

      <details class="accordion__item">
        <summary class="accordion__trigger">
          Is my money safe with Solace Mining?
          <span class="accordion__icon" aria-hidden="true"></span>
        </summary>
        <div class="accordion__body">
          Funds are held in segregated cold-storage wallets and never co-mingled with operating capital. We use multi-factor authentication and an audit-grade ledger, and every transaction carries a traceable reference. As with any investment, returns are never guaranteed and capital is at risk.
        </div>
      </details>

      <details class="accordion__item">
        <summary class="accordion__trigger">
          Is my personal data secure?
          <span class="accordion__icon" aria-hidden="true"></span>
        </summary>
        <div class="accordion__body">
          Yes. We use end-to-end AES-256 encryption, multi-factor authentication, and security controls reviewed regularly. Your personal data is handled under a transparent Privacy Notice and is never sold.
        </div>
      </details>

      <details class="accordion__item">
        <summary class="accordion__trigger">
          What's the minimum to get started?
          <span class="accordion__icon" aria-hidden="true"></span>
        </summary>
        <div class="accordion__body">
          The Bronze tier starts at $100. Silver runs from $500, Gold from $2,500, Platinum from $5,000, and VIP from $10,000 upwards. Each tier sets its own deposit range and fixed daily profit rate.
        </div>
      </details>

      <details class="accordion__item">
        <summary class="accordion__trigger">
          Can I withdraw my money any time?
          <span class="accordion__icon" aria-hidden="true"></span>
        </summary>
        <div class="accordion__body">
          Your available wallet balance can be withdrawn on demand. Funds committed to an active contract are released — with your daily profit and returned principal — when the five-day term completes. You can then withdraw or reinvest into a new tier.
        </div>
      </details>
    </div>
  </div>
</section>


<!-- =================== FOOTER (Crestmark C.12) =================== -->
<footer class="footer">
  <div class="container">
    <div class="footer__top">
      <a href="/" aria-label="Solace Mining home">
        <span class="footer__wordmark">Solace<em>Mining</em></span>
      </a>
      <h2 style="margin: var(--space-2) 0;">Your mining, simplified in one place.</h2>
      <p style="color: var(--color-ink-muted); max-width: 560px;">
        Open an account in minutes. Fund your wallet. Choose a mining tier — and let Solace Mining handle the daily payouts, the reporting, and the security.
      </p>
      <a href="/register" class="btn btn--primary">Open an account</a>
    </div>

    <nav class="footer__nav" aria-label="Footer">
      <a href="/about">About</a><span class="footer__sep">·</span>
      <a href="/#plans">Investment Plans</a><span class="footer__sep">·</span>
      <a href="/#how-it-works">How It Works</a><span class="footer__sep">·</span>
      <a href="/contact">Contact</a><span class="footer__sep">·</span>
      <a href="/login">Login</a>
    </nav>

    <nav class="footer__legal" aria-label="Legal">
      <a href="/privacy">Privacy Policy</a><span class="footer__sep">·</span>
      <a href="/terms">Terms of Service</a><span class="footer__sep">·</span>
      <a href="/risk-disclosure">Risk Disclosure</a><span class="footer__sep">·</span>
      <a href="/aml-policy">AML Policy</a><span class="footer__sep">·</span>
      <a href="/cookies">Cookie Policy</a>
    </nav>

    <div class="footer__credits">
      <span>© <?= date('Y') ?> Solace Mining Ltd. All rights reserved.</span>
    </div>
  </div>
</footer>

<?php $support_widget_paired = true; // homepage also shows Smartsupp — place them side by side
include __DIR__ . '/_partials/support-widget.php'; ?>


  <!-- Scripts -->
  <script src="<?= txh_asset('../../assets/js/main.js') ?>" defer></script>
  <script>
    // SLM Redesign: toggle .is-scrolled on navbar once past hero
    (function () {
      const nav = document.querySelector('.txh-redesign .navbar');
      if (!nav) return;
      const threshold = 80;
      const update = () => nav.classList.toggle('is-scrolled', window.scrollY > threshold);
      update();
      window.addEventListener('scroll', update, { passive: true });
    })();
  </script>
  <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Solace Mining",
  "url": "https://solacemining.org",
  "logo": "https://solacemining.org/assets/favicon/favicon-32x32.png",
  "telephone": "+1 317 366 1701",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Dallas",
    "addressRegion": "TX",
    "addressCountry": "US"
  }
}
</script>

<noscript>
  Powered by <a href="https://www.smartsupp.com" target="_blank" rel="noopener noreferrer">Smartsupp</a>
</noscript>


</body>
</html>
