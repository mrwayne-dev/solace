<?php
/**
 * Public "mining contract tiers" section (live from DB).
 * Usage:  <?php include __DIR__ . '/_partials/plans-section.php'; ?>
 * Renders nothing on any DB/query error so a marketing page never breaks.
 */

require_once __DIR__ . '/../../../config/database.php';

if (!function_exists('slm_money')) {
    function slm_money($n) { return $n === null ? 'Unlimited' : '$' . number_format((float) $n, 0); }
}
if (!function_exists('slm_pct')) {
    function slm_pct($n) {
        $s = rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
        return $s . '%';
    }
}

$__eyebrow = 'Mining contracts';
$__title   = 'Choose your mining tier';
$__cards   = [];

try {
    $__pdo = getPDO();
    $rows = $__pdo->query("SELECT name, daily_profit_percent, duration_days, min_amount, max_amount, referral_commission_percent, summary
                           FROM investment_plans WHERE status='active' ORDER BY min_amount ASC")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) $__cards[] = [
        'tier' => $r['name'],
        'roi'  => slm_pct($r['daily_profit_percent']) . ' daily',
        'name' => $r['name'] . ' Contract',
        'meta' => [
            'Deposit' => slm_money($r['min_amount']) . ' – ' . slm_money($r['max_amount']),
            'Duration' => ((int)$r['duration_days']) . ' days',
            'Referral' => slm_pct($r['referral_commission_percent']),
        ],
        'summary' => $r['summary'],
    ];
} catch (Throwable $e) {
    error_log('plans-section: ' . $e->getMessage());
    return; // never break the marketing page
}

if (empty($__cards)) return;
?>
<section class="section section--warm" id="plans">
  <div class="container">
    <div class="section-header section-header--center" style="margin-bottom: var(--space-10);">
      <p class="eyebrow">
        <span class="eyebrow__icon"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="6"/></svg></span>
        <?= htmlspecialchars($__eyebrow) ?>
      </p>
      <h2 class="section-header__title"><?= htmlspecialchars($__title) ?></h2>
    </div>

    <div class="plan-grid">
      <?php foreach ($__cards as $c): ?>
        <article class="plan-card">
          <span class="plan-card__tier"><?= htmlspecialchars($c['tier']) ?></span>
          <div class="plan-card__roi"><?= htmlspecialchars($c['roi']) ?></div>
          <div class="plan-card__name"><?= htmlspecialchars($c['name']) ?></div>
          <ul class="plan-card__meta">
            <?php foreach ($c['meta'] as $k => $v): if ($v === '' || $v === null) continue; ?>
              <li><span><?= htmlspecialchars($k) ?></span><strong><?= htmlspecialchars($v) ?></strong></li>
            <?php endforeach; ?>
          </ul>
          <?php if (!empty($c['summary'])): ?>
            <p class="plan-card__summary"><?= htmlspecialchars($c['summary']) ?></p>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>

    <div style="display:flex; gap:var(--space-3); justify-content:center; margin-top:var(--space-10);">
      <a href="/register" class="btn btn--primary">Open an account</a>
      <a href="/login" class="btn btn--line">Sign in</a>
    </div>
  </div>
</section>
<?php
unset($__cards, $__pdo, $__eyebrow, $__title, $rows, $r, $c);
if (isset($plan_product)) unset($plan_product);
?>
