<?php
// ============================================================
// NAVBAR partial — floating dock
// Nav structure per plans/investment-platform-structure.txt:
//   Home · About Us · Investment Plans · How It Works · FAQ · Contact
//   + Login / Register
// Placeholder logo (inline mark + wordmark) until final art lands.
// $nav_variant kept for backwards-compat; the dock styles itself.
// ============================================================
$nav_variant = $nav_variant ?? 'default';
?>
<nav id="navbar" class="navbar navbar--dock" data-navbar>
  <div class="container">
    <div class="navbar__inner">
      <a href="/" class="navbar__brand" aria-label="Solace Mining home">
        <span class="navbar__mark" aria-hidden="true">
          <img src="/assets/images/logo/solacewhitelogo.png" alt="Solace Mining">
        </span>
        <span class="navbar__wordmark">Solace<em>Mining</em></span>
      </a>

      <ul class="navbar__links">
        <li><a href="/about" class="navbar__link">About</a></li>
        <li><a href="/#plans" class="navbar__link">Investment Plans</a></li>
        <li><a href="/#how-it-works" class="navbar__link">How It Works</a></li>
        <li><a href="/contact" class="navbar__link">Contact</a></li>
      </ul>

      <div class="navbar__actions">
        <a href="/login" class="btn btn--ghost">Login</a>
        <a href="/register" class="btn btn--nav">Register</a>
      </div>

      <button class="navbar__toggle" aria-label="Toggle menu" aria-expanded="false" data-nav-toggler>
        <span class="navbar__burger" aria-hidden="true"><i></i><i></i><i></i></span>
      </button>
    </div>
  </div>
</nav>
