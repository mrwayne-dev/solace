<?php
// ============================================================
// FOOTER partial — Crestmark C.12 adapted
// Includes the navbar scroll-toggle JS so every page picks it up.
// ============================================================
?>
<footer class="footer">
  <div class="container">
    <div class="footer__top">
      <a href="/" aria-label="TitanXHoldings home">
        <img src="/assets/images/logo/titanx-black.png" alt="TitanXHoldings" loading="lazy" style="height: 30px;">
      </a>
      <h2 style="margin: var(--space-2) 0;">Your wealth, simplified in one place.</h2>
      <p style="color: var(--color-ink-muted); max-width: 560px;">
        Open an account in minutes. Fund the wallet. Deploy across six regulated products — and let TitanXHoldings handle the compounding, the reporting, and the protections.
      </p>
      <a href="/register" class="btn btn--primary">Start a conversation</a>
    </div>

    <nav class="footer__nav" aria-label="Footer">
      <a href="/whytx">Why TXH</a><span class="footer__sep">·</span>
      <a href="/platform">Platform</a><span class="footer__sep">·</span>
      <a href="/solutions">Solutions</a><span class="footer__sep">·</span>
      <a href="/about">About</a><span class="footer__sep">·</span>
      <a href="/contact">Contact</a><span class="footer__sep">·</span>
      <a href="/login">Sign in</a>
    </nav>

    <div class="footer__credits">
      <span>&copy; <?= date('Y') ?> TitanXHoldings Ltd. All rights reserved.</span>
      <span class="footer__socials" aria-label="Social links">
        <a href="https://www.linkedin.com/company/titanxholdings" aria-label="LinkedIn" target="_blank" rel="noopener">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 0H5C2.2 0 0 2.2 0 5v14c0 2.8 2.2 5 5 5h14c2.8 0 5-2.2 5-5V5c0-2.8-2.2-5-5-5zM8 19H5V8h3v11zM6.5 6.7a1.8 1.8 0 1 1 0-3.6 1.8 1.8 0 0 1 0 3.6zM20 19h-3v-5.6c0-3.4-4-3.1-4 0V19h-3V8h3v1.8c1.4-2.6 7-2.8 7 2.5V19z"/></svg>
        </a>
        <a href="https://twitter.com/titanxholdings" aria-label="X (Twitter)" target="_blank" rel="noopener">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2H21l-6.46 7.387L22 22h-6.828l-4.77-6.246L4.804 22H2l6.91-7.892L1.5 2h6.957l4.31 5.713L18.244 2zM17.222 20.146h1.84L7.027 3.748H5.05L17.222 20.146z"/></svg>
        </a>
        <a href="https://www.facebook.com/titanxholdings" aria-label="Facebook" target="_blank" rel="noopener">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.99 3.66 9.13 8.44 9.88V14.9H7.9V12h2.54V9.8c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.77l-.44 2.9h-2.33v6.98C18.34 21.13 22 16.99 22 12z"/></svg>
        </a>
      </span>
    </div>
  </div>
</footer>

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
