<?php
http_response_code(404);
$page_title = 'Not Found | Solace Mining';
$page_description = 'The page you requested could not be found.';
$page_path = '/404';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <picture>
      <source type="image/avif" srcset="/assets/images/txh-home.avif">
      <source type="image/webp" srcset="/assets/images/txh-home.webp">
      <img src="/assets/images/txh-home.webp" alt="" width="1800" height="1080" loading="eager">
    </picture>
  </div>
  <div class="container hero__inner">
    <div class="hero__content" style="align-items: center; text-align: center; margin: 0 auto;">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>404</p>
      <h1 class="hero__title">Not found.</h1>
      <p class="hero__subtitle" style="max-width: 480px;">
        The page you're looking for doesn't exist, or has moved. Head back to the homepage or sign in to your dashboard.
      </p>
      <div class="hero__cta-row">
        <a href="/" class="btn btn--nav">Return home</a>
        <a href="/login" class="btn btn--ghost">Sign in</a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_partials/footer.php'; ?>
<script src="<?= txh_asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>
