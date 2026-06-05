<?php
/**
 * Public catalog "plans we offer" section (live from DB).
 * Usage:  <?php $plan_product = 'xlock'; include __DIR__ . '/_partials/plans-section.php'; ?>
 * Renders nothing on any DB/query error so a marketing page never breaks.
 *
 * $plan_product ∈ xlock | xyield | xweekly | xshares | xgrid | xrewards
 */

if (!isset($plan_product)) return;

require_once __DIR__ . '/../../../config/database.php';

if (!function_exists('txh_fmt_term')) {
    function txh_fmt_term($days) {
        $days = (int) $days;
        if ($days <= 0)            return 'Open-ended';
        if ($days % 365 === 0)     { $y = $days / 365; return $y . ' year'  . ($y > 1 ? 's' : ''); }
        if ($days % 30  === 0)     { $m = $days / 30;  return $m . ' month' . ($m > 1 ? 's' : ''); }
        return $days . ' days';
    }
}
if (!function_exists('txh_money')) {
    function txh_money($n) { return '£' . number_format((float) $n, 0); }
}
if (!function_exists('txh_pct')) {
    function txh_pct($n) {
        $s = rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
        return $s . '%';
    }
}
if (!function_exists('txh_nice')) {
    function txh_nice($s) { return ucwords(str_replace('_', ' ', (string) $s)); }
}

$__headings = [
    'xlock'    => ['X-Lock plans',    'Fixed-term savings tiers.'],
    'xyield'   => ['X-Yield plans',   'Fixed-duration yield plans.'],
    'xweekly'  => ['X-Weekly plans',  'Automated weekly investing.'],
    'xshares'  => ['X-Shares assets', 'Fractional equity positions.'],
    'xgrid'    => ['X-Grid plans',    'Infrastructure co-investment.'],
    'xrewards' => ['X-Rewards catalog','Redeem your yield for rewards.'],
];
if (!isset($__headings[$plan_product])) return;
[$__eyebrow, $__title] = $__headings[$plan_product];

$__cards   = [];
$__variant = 'plan';

try {
    $__pdo = getPDO();

    switch ($plan_product) {
        case 'xlock':
            $rows = $__pdo->query("SELECT name, roi_range, lock_period_text, min_amount, payout, summary
                                   FROM holdlock_plans ORDER BY min_amount ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $__cards[] = [
                'tier' => 'X-Lock', 'roi' => $r['roi_range'], 'name' => $r['name'],
                'meta' => ['Term' => $r['lock_period_text'], 'Minimum' => txh_money($r['min_amount']), 'Payout' => txh_nice($r['payout'])],
                'summary' => $r['summary'],
            ];
            break;

        case 'xyield':
            $rows = $__pdo->query("SELECT title, roi_percent, duration_days, min_amount, payout_option, summary, description
                                   FROM investment_plans WHERE status='active' ORDER BY min_amount ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $__cards[] = [
                'tier' => 'X-Yield', 'roi' => txh_pct($r['roi_percent']), 'name' => $r['title'],
                'meta' => ['Term' => txh_fmt_term($r['duration_days']), 'Minimum' => txh_money($r['min_amount']), 'Payout' => txh_nice($r['payout_option'])],
                'summary' => $r['summary'] ?: $r['description'],
            ];
            break;

        case 'xweekly':
            $rows = $__pdo->query("SELECT plan_name, roi_percent, min_weekly, max_weekly, description
                                   FROM xweekly_plans WHERE status='active' ORDER BY min_weekly ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $__cards[] = [
                'tier' => 'X-Weekly', 'roi' => txh_pct($r['roi_percent']), 'name' => $r['plan_name'],
                'meta' => ['Weekly' => txh_money($r['min_weekly']) . '–' . txh_money($r['max_weekly']), 'Frequency' => 'Every week'],
                'summary' => $r['description'],
            ];
            break;

        case 'xshares':
            $rows = $__pdo->query("SELECT asset_name, ticker, roi_percent, payout_schedule, min_amount, description
                                   FROM xshares_assets WHERE status='active' ORDER BY min_amount ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $__cards[] = [
                'tier' => $r['ticker'], 'roi' => txh_pct($r['roi_percent']), 'name' => $r['asset_name'],
                'meta' => ['Minimum' => txh_money($r['min_amount']), 'Payout' => txh_nice($r['payout_schedule'])],
                'summary' => $r['description'],
            ];
            break;

        case 'xgrid':
            $rows = $__pdo->query("SELECT name, roi_percent, duration_days, min_amount, payout_option, summary
                                   FROM infrastructure_plans ORDER BY min_amount ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $__cards[] = [
                'tier' => 'X-Grid', 'roi' => txh_pct($r['roi_percent']), 'name' => $r['name'],
                'meta' => ['Term' => txh_fmt_term($r['duration_days']), 'Minimum' => txh_money($r['min_amount']), 'Payout' => txh_nice($r['payout_option'])],
                'summary' => $r['summary'],
            ];
            break;

        case 'xrewards':
            $__variant = 'product';
            $rows = $__pdo->query("SELECT product_name, description, retail_price, reward_price, discount_pct, image_path
                                   FROM xrewards_products WHERE status='active' ORDER BY retail_price DESC")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $__cards[] = [
                'name' => $r['product_name'], 'summary' => $r['description'],
                'retail' => $r['retail_price'], 'member' => $r['reward_price'],
                'discount' => $r['discount_pct'], 'image' => $r['image_path'],
            ];
            break;
    }
} catch (Throwable $e) {
    error_log('plans-section (' . $plan_product . '): ' . $e->getMessage());
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

    <div class="grid-3">
      <?php foreach ($__cards as $c): ?>
        <?php if ($__variant === 'product'): ?>
          <article class="plan-card plan-card--product">
            <?php if (!empty($c['image'])): ?>
              <div class="plan-card__media"><img src="<?= htmlspecialchars($c['image']) ?>" alt="<?= htmlspecialchars($c['name']) ?>" loading="lazy"></div>
            <?php endif; ?>
            <div class="plan-card__body">
              <?php if (!empty($c['discount'])): ?>
                <span class="plan-card__badge"><?= (int) $c['discount'] ?>% member discount</span>
              <?php endif; ?>
              <div class="plan-card__name"><?= htmlspecialchars($c['name']) ?></div>
              <?php if (!empty($c['summary'])): ?>
                <p class="plan-card__summary"><?= htmlspecialchars($c['summary']) ?></p>
              <?php endif; ?>
              <div class="plan-card__price">
                <span class="plan-card__price-member"><?= txh_money($c['member']) ?></span>
                <span class="plan-card__price-retail"><?= txh_money($c['retail']) ?></span>
              </div>
            </div>
          </article>
        <?php else: ?>
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
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <div style="display:flex; gap:var(--space-3); justify-content:center; margin-top:var(--space-10);">
      <a href="/register" class="btn btn--primary">Open an account</a>
      <a href="/login" class="btn btn--ghost">Sign in</a>
    </div>
  </div>
</section>
<?php
unset($__cards, $__variant, $__pdo, $__eyebrow, $__title, $__headings, $rows, $r, $c, $plan_product);
?>
