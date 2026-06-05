<?php
$page_title = 'Contact | TitanXHoldings';
$page_description = 'Get in touch with TitanXHoldings — London-based FCA-authorised investment platform. Open an account, ask about regulatory permissions, or discuss a partnership.';
$page_path = '/contact';
$nav_variant = 'solid';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">

<?php include __DIR__ . '/_partials/navbar.php'; ?>

<!-- =================== INTRO =================== -->
<section class="section section--warm" style="padding-top: var(--space-20);">
  <div class="container">
    <div class="section-header section-header--center" style="margin: 0 auto;">
      <p class="eyebrow"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>Contact</p>
      <h1 style="font-size: var(--text-h1); line-height: var(--lh-h1); letter-spacing: var(--tracking-h1); font-weight: var(--fw-regular);">Let's talk.</h1>
      <p class="section-header__body" style="max-width: 600px;">
        Our team is based in London and works alongside compliance, banking, and audit partners across the UK. Whether you're looking to open an account, discuss a partnership, or ask about our regulatory permissions — we're happy to help.
      </p>
    </div>
  </div>
</section>

<!-- =================== FORM + INFO =================== -->
<section class="section section--white">
  <div class="container">
    <div class="grid-2" style="gap: var(--space-10); align-items: flex-start;">
      <!-- Form -->
      <div class="form-card form-card--wide" style="margin: 0;">
        <form class="form-stack" action="/contact/submit" method="POST" enctype="multipart/form-data">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-3);">
            <div class="form-field">
              <label class="form-field__label" for="name">Full name</label>
              <input id="name" name="name" type="text" class="form-field__input" placeholder="Jane Doe" required>
            </div>
            <div class="form-field">
              <label class="form-field__label" for="email">Email</label>
              <input id="email" name="email" type="email" class="form-field__input" placeholder="you@example.com" required>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-3);">
            <div class="form-field">
              <label class="form-field__label" for="type">Message type</label>
              <select id="type" name="type" class="form-field__input" required>
                <option value="" disabled selected>Select...</option>
                <option value="general">General enquiry</option>
                <option value="services">Services</option>
                <option value="support">Support</option>
                <option value="feedback">Feedback</option>
                <option value="partnership">Partnership</option>
              </select>
            </div>
            <div class="form-field">
              <label class="form-field__label" for="service">Role / interest <span style="color: var(--color-ink-muted); font-weight: 400;">(optional)</span></label>
              <select id="service" name="service" class="form-field__input">
                <option value="">Select...</option>
                <option value="prospect">Prospective member</option>
                <option value="member">Existing member</option>
                <option value="press">Press / media</option>
                <option value="partner">Partner / integration</option>
                <option value="compliance">Compliance / legal</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>

          <div class="form-field">
            <label class="form-field__label" for="subject">Subject</label>
            <input id="subject" name="subject" type="text" class="form-field__input" placeholder="What's this about?" required>
          </div>

          <div class="form-field">
            <label class="form-field__label" for="message">Message</label>
            <textarea id="message" name="message" rows="6" class="form-field__input" placeholder="Tell us more..." required></textarea>
          </div>

          <div class="form-field">
            <label class="form-field__label" for="attachment">Attachment <span style="color: var(--color-ink-muted); font-weight: 400;">(optional, PDF/JPG/PNG/DOC)</span></label>
            <input id="attachment" name="attachment" type="file" class="form-field__input" accept=".pdf,.jpg,.png,.doc,.docx" style="padding: 12px; height: auto;">
          </div>

          <button type="submit" class="btn btn--primary" style="width: 100%;">Send message</button>
        </form>
      </div>

      <!-- Info sidebar -->
      <aside style="position: sticky; top: 96px;">
        <p class="eyebrow" style="margin-bottom: var(--space-4);"><span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg></span>Direct lines</p>
        <h2 style="margin-bottom: var(--space-5);">Talk to a human.</h2>
        <p style="color: var(--color-ink-muted); margin-bottom: var(--space-6);">
          Existing members can also reach support directly from inside the app — typical response in under 2 working hours.
        </p>

        <ul style="display: flex; flex-direction: column; gap: var(--space-4);">
          <li style="display: flex; gap: var(--space-3); align-items: flex-start;">
            <span style="width: 32px; height: 32px; border-radius: var(--radius-circle); background: var(--color-surface-warm); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 6h16v12H4z"/><path d="M4 6l8 7 8-7"/></svg>
            </span>
            <div>
              <p style="font-weight: var(--fw-medium); color: var(--color-ink-primary);">support@titanxholdings.com</p>
              <p style="font-size: var(--text-sm); color: var(--color-ink-muted);">General enquiries &amp; member support</p>
            </div>
          </li>
          <li style="display: flex; gap: var(--space-3); align-items: flex-start;">
            <span style="width: 32px; height: 32px; border-radius: var(--radius-circle); background: var(--color-surface-warm); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </span>
            <div>
              <p style="font-weight: var(--fw-medium); color: var(--color-ink-primary);">+44 20 4574 0000</p>
              <p style="font-size: var(--text-sm); color: var(--color-ink-muted);">Mon–Fri, 09:00–18:00 GMT</p>
            </div>
          </li>
          <li style="display: flex; gap: var(--space-3); align-items: flex-start;">
            <span style="width: 32px; height: 32px; border-radius: var(--radius-circle); background: var(--color-surface-warm); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </span>
            <div>
              <p style="font-weight: var(--fw-medium); color: var(--color-ink-primary);">London, United Kingdom</p>
              <p style="font-size: var(--text-sm); color: var(--color-ink-muted);">Registered in England &amp; Wales</p>
            </div>
          </li>
        </ul>
      </aside>
    </div>
  </div>
</section>

<div id="loader" class="loader hidden"><div class="loader-spinner"></div></div>
<div id="successModal" class="modal-message hidden"><div class="modal-content"><p>Message sent! Please check your email.</p></div></div>

<?php include __DIR__ . '/_partials/footer.php'; ?>

<script src="<?= txh_asset('/assets/js/main.js') ?>" defer></script>
</body>
</html>
