<?php
// ============================================================
// FOOTER partial — Crestmark C.12 adapted
// Includes the navbar scroll-toggle JS so every page picks it up.
// ============================================================
?>
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
      <span>&copy; <?= date('Y') ?> Solace Mining Ltd. All rights reserved.</span>
    </div>
  </div>
</footer>

<?php include __DIR__ . '/support-widget.php'; ?>

<script>
  // Navbar scroll toggle (transparent on hero, solid on scroll)
  (function () {
    const nav = document.querySelector('.txh-redesign .navbar');
    if (!nav || nav.classList.contains('navbar--solid')) return;
    const threshold = 80;
    const update = () => nav.classList.toggle('is-scrolled', window.scrollY > threshold);
    update();
    window.addEventListener('scroll', update, { passive: true });
  })();
</script>
