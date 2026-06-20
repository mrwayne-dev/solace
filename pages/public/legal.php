<?php
// ============================================================
// LEGAL PAGES — shared template
// Routed for: /privacy /terms /cookies /risk-disclosure /aml-policy
// Slug is derived from the request path; content lives in $legal map.
// ============================================================
$__uri  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$__slug = trim($__uri, '/') ?: 'privacy';

$company  = 'Solace Mining Ltd';
$support  = 'support@solacemining.org';
$updated  = 'June 16, 2026';

$legal = [
  'privacy' => [
    'title' => 'Privacy Policy',
    'intro' => 'This Privacy Policy explains how ' . $company . ' ("Solace Mining", "we", "us") collects, uses, and protects your information when you use our platform.',
    'sections' => [
      ['Information we collect', 'We collect the details you provide when you register and use your account — your name, email address, and authentication credentials — along with transaction records (deposits, mining contracts, withdrawals, and referral activity) and technical data such as IP address, device, and browser for security and fraud prevention.'],
      ['How we use your information', 'We use your information to operate your account, process deposits and withdrawals, credit daily mining profit and referral commissions, secure the platform, comply with legal obligations, and contact you about your account or important service changes.'],
      ['How we protect it', 'Passwords are stored using one-way hashing. Sensitive data is encrypted in transit and at rest, access is restricted on a need-to-know basis, and funds are held in segregated cold-storage wallets. No method of transmission is perfectly secure, but we work continuously to safeguard your data.'],
      ['Sharing', 'We do not sell your personal data. We share information only with service providers who help us operate (for example payment and email providers), or where required by law, regulation, or a valid legal request.'],
      ['Your rights', 'You may request access to, correction of, or deletion of your personal data, subject to records we are legally required to retain. To exercise these rights, contact us at ' . $support . '.'],
      ['Retention', 'We keep your information for as long as your account is active and as required to meet legal, accounting, and regulatory obligations.'],
    ],
  ],
  'terms' => [
    'title' => 'Terms of Service',
    'intro' => 'These Terms of Service govern your access to and use of the Solace Mining platform. By creating an account you agree to these terms.',
    'sections' => [
      ['Eligibility', 'You must be at least 18 years old and legally permitted to use this service in your jurisdiction. Each account is for the registered individual only and must not be shared.'],
      ['Accounts', 'You are responsible for keeping your login credentials secure and for all activity under your account. Notify us immediately of any unauthorised use.'],
      ['Mining contracts', 'Each tier (Bronze, Silver, Gold, Platinum, VIP) sets a deposit range, a fixed daily profit rate, and a contract duration. Profit accrues to your wallet daily and your principal is returned when the contract completes, as described on the platform at the time of purchase.'],
      ['Deposits & withdrawals', 'Deposits are made in supported cryptocurrencies. Available wallet balances can be withdrawn on request; funds committed to an active contract are released on completion. Network and processing conditions may affect timing.'],
      ['Referrals', 'You may earn a 10% commission on qualifying activity from miners you refer. Abuse of the referral program — including self-referral or fraudulent sign-ups — may result in forfeiture of commissions and account suspension.'],
      ['Acceptable use', 'You agree not to use the platform for unlawful activity, to attempt to disrupt or compromise it, or to provide false information. We may suspend or terminate accounts that breach these terms.'],
      ['No guarantee & liability', 'All investment carries risk and returns are never guaranteed. To the maximum extent permitted by law, Solace Mining is not liable for indirect or consequential losses arising from your use of the platform.'],
      ['Changes', 'We may update these terms from time to time. Continued use after changes take effect constitutes acceptance.'],
    ],
  ],
  'risk-disclosure' => [
    'title' => 'Risk Disclosure',
    'intro' => 'Please read this Risk Disclosure carefully. Investing in cryptocurrency mining contracts involves significant risk.',
    'sections' => [
      ['Capital at risk', 'The value of cryptocurrency is volatile and can move sharply. You should never invest more than you can afford to lose, and past performance is not a reliable indicator of future results.'],
      ['No guaranteed returns', 'While each tier quotes a fixed daily profit rate, all returns are subject to platform terms and market and operational conditions. Returns are not guaranteed.'],
      ['Liquidity', 'Funds committed to an active mining contract are not available for withdrawal until the contract completes.'],
      ['Regulatory & tax', 'Cryptocurrency regulation varies by jurisdiction and may change. You are responsible for understanding and meeting any tax obligations that apply to you.'],
      ['Your responsibility', 'You are solely responsible for your investment decisions. If you are unsure, seek independent financial advice before investing.'],
    ],
  ],
  'aml-policy' => [
    'title' => 'AML Policy',
    'intro' => 'Solace Mining is committed to preventing money laundering and the financing of illegal activity. This summary outlines our approach.',
    'sections' => [
      ['Our commitment', 'We maintain controls designed to detect and prevent the use of our platform for money laundering, terrorist financing, or other financial crime.'],
      ['Verification', 'We may require identity verification (KYC) and additional information before processing certain deposits or withdrawals, particularly for larger amounts or where activity appears unusual.'],
      ['Monitoring', 'Transactions are monitored for suspicious patterns. We reserve the right to delay, decline, or reverse transactions, and to suspend accounts, where we identify potential risk.'],
      ['Reporting', 'Where required by law, we report suspicious activity to the relevant authorities and cooperate with lawful investigations.'],
      ['Prohibited use', 'Using the platform with proceeds of crime, or to disguise the origin of funds, is strictly prohibited and will result in account termination.'],
    ],
  ],
  'cookies' => [
    'title' => 'Cookie Policy',
    'intro' => 'This Cookie Policy explains how Solace Mining uses cookies and similar technologies.',
    'sections' => [
      ['What cookies are', 'Cookies are small text files stored on your device that help websites function and remember your preferences.'],
      ['How we use them', 'We use essential cookies to keep you signed in and secure your session, and limited analytics to understand how the platform is used so we can improve it.'],
      ['Third parties', 'Some features, such as live chat, may set their own cookies governed by the provider\'s policies.'],
      ['Managing cookies', 'You can control or delete cookies through your browser settings. Disabling essential cookies may prevent parts of the platform — including sign-in — from working.'],
    ],
  ],
];

if (!isset($legal[$__slug])) { $__slug = 'privacy'; }
$doc = $legal[$__slug];

$page_title = $doc['title'] . ' | Solace Mining';
$page_description = $doc['intro'];
$page_path = '/' . $__slug;
$nav_variant = 'solid';
include __DIR__ . '/_partials/head.php';
?>
<body class="txh-redesign">
<?php include __DIR__ . '/_partials/navbar.php'; ?>

<section class="section section--white">
  <div class="container">
    <div class="legal-doc">
      <header class="legal-doc__head">
        <p class="eyebrow">
          <span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg></span>
          Legal
        </p>
        <h1 class="legal-doc__title"><?= htmlspecialchars($doc['title']) ?></h1>
        <p class="legal-doc__meta">Last updated: <?= htmlspecialchars($updated) ?></p>
        <p class="legal-doc__intro"><?= htmlspecialchars($doc['intro']) ?></p>
      </header>

      <?php foreach ($doc['sections'] as $i => $s): ?>
        <section class="legal-doc__section">
          <h2><?= ($i + 1) . '. ' . htmlspecialchars($s[0]) ?></h2>
          <p><?= htmlspecialchars($s[1]) ?></p>
        </section>
      <?php endforeach; ?>

      <section class="legal-doc__section">
        <h2><?= (count($doc['sections']) + 1) . '. Contact' ?></h2>
        <p>Questions about this document? Email us at <a href="mailto:<?= $support ?>"><?= $support ?></a>, message us on Telegram at <a href="https://t.me/+13173661701" target="_blank" rel="noopener">+1 317 366 1701</a>, or call the same number. <?= htmlspecialchars($company) ?>, Dallas, United States of America.</p>
      </section>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_partials/footer.php'; ?>
</body>
</html>
