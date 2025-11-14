<?php
// pages/user/trustfund.php

session_start([
    'cookie_lifetime' => 86400, // Example: 24 hours
    'cookie_httponly' => true,
    'cookie_secure' => true, // Ensure HTTPS is used in production
    'cookie_samesite' => 'Strict',
]);

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: /login');
    exit;
}
// Retrieve user data from session
$user_name = htmlspecialchars($_SESSION['full_name'] ?? 'User'); // Fallback to 'User' if not set
$user_id = $_SESSION['user_id'] ?? null;
$user_email = $_SESSION['email'] ?? null;
$user_role = $_SESSION['role'] ?? 'user';

// Placeholder data for trust fund plans
$plans = [
    [
        'id' => 1,
        'name' => 'Child Education Growth Plan',
        'purpose' => 'To help parents and guardians build a secure education fund for their children’s future, with guaranteed growth and flexible payout options.',
        'min_deposit' => '$500',
        'max_deposit' => '$50,000',
        'lock_period' => '3 years',
        'roi' => '20–28%',
        'risk' => 'Low',
        'payout' => 'Annual payout or at maturity',
        'summary' => 'A safe and steady plan that allows parents to prepare for their children’s education needs. Funds grow through secure, education-focused investments with short-term profitability.',
        'icon' => 'mdi:school-outline',
        'color' => 'Green'
    ],
    [
        'id' => 2,
        'name' => 'Legacy Wealth Trust Plan',
        'purpose' => 'To help high-net-worth individuals and families build long-term generational wealth through sustainable, asset-backed investments.',
        'min_deposit' => '$500,000',
        'max_deposit' => 'Unlimited',
        'lock_period' => '5 years',
        'roi' => '45–60%',
        'risk' => 'Moderate',
        'payout' => 'Annual or full payout at maturity',
        'summary' => 'A premium trust plan for investors who want to establish multi-generational wealth. Funds are allocated into stable, high-yield ventures in healthcare and infrastructure, ensuring significant compounded returns.',
        'icon' => 'mdi:family-tree',
        'color' => 'Blue'
    ],
    [
        'id' => 3,
        'name' => 'Business Succession Trust Plan',
        'purpose' => 'To help business owners secure and grow their assets while creating a smooth transition or expansion strategy.',
        'min_deposit' => '$5,000',
        'max_deposit' => '$500,000',
        'lock_period' => '4 years',
        'roi' => '40–55%',
        'risk' => 'Moderate to High',
        'payout' => 'Annual or full payout at maturity',
        'summary' => 'This plan empowers entrepreneurs to expand or stabilize their businesses while earning profitable returns through diversified investments in growth industries.',
        'icon' => 'mdi:briefcase-outline',
        'color' => 'Orange'
    ],
    [
        'id' => 4,
        'name' => 'Medical Protection Trust Plan',
        'purpose' => 'To create a secure health reserve that earns profit while providing access to emergency medical funds.',
        'min_deposit' => '$300',
        'max_deposit' => '$25,000',
        'lock_period' => '3 years',
        'roi' => '15–20%',
        'risk' => 'Low',
        'payout' => 'Quarterly, annual, or at maturity',
        'summary' => 'A health-focused savings and investment plan that offers financial security for medical needs while maintaining capital growth through health sector investments.',
        'icon' => 'mdi:heart-pulse',
        'color' => 'Green'
    ],
    [
        'id' => 5,
        'name' => 'Future Builders Business Plan',
        'purpose' => 'To help young professionals and entrepreneurs grow startup capital or long-term business project funds.',
        'min_deposit' => '$1,000',
        'max_deposit' => '$100,000',
        'lock_period' => '4 years',
        'roi' => '30–45%',
        'risk' => 'Moderate',
        'payout' => 'Annual or full payout at maturity',
        'summary' => 'This plan provides growth opportunities for future business leaders. HealthRunCare invests your capital into profitable startups and innovation projects that deliver measurable social and financial impact.',
        'icon' => 'mdi:rocket-outline',
        'color' => 'Blue'
    ],
    [
        'id' => 6,
        'name' => 'Guardian Trust Income Plan',
        'purpose' => 'To provide steady, reliable annual income for beneficiaries such as children, dependents, or retirees.',
        'min_deposit' => '$10,000',
        'max_deposit' => '$200,000',
        'lock_period' => '5 years',
        'roi' => '35% total (approx. 7% yearly)',
        'risk' => 'Low to Moderate',
        'payout' => 'Annual income distribution',
        'summary' => 'A dependable, income-generating plan that ensures steady annual payouts while preserving your trust capital — ideal for long-term dependents and family income planning.',
        'icon' => 'mdi:shield-check-outline',
        'color' => 'Green'
    ],
    [
        'id' => 7,
        'name' => 'Perpetual Legacy Trust Plan',
        'purpose' => 'To establish a lifetime trust that pays continuous annual income while keeping the principal amount permanently invested.',
        'min_deposit' => '$1,000,000',
        'max_deposit' => 'Unlimited',
        'lock_period' => 'Lifetime (Perpetual)',
        'roi' => '10–12% annual',
        'risk' => 'Low',
        'payout' => 'Annual or quarterly for life',
        'summary' => 'An elite trust for individuals or institutions seeking permanent wealth and lifetime income. The principal remains preserved within HealthRunCare’s managed assets, generating annual returns indefinitely.',
        'icon' => 'mdi:infinity',
        'color' => 'Orange'
    ]
];

// Placeholder data for user's trust fund plans
$trustPlans = [
    [
        'plan' => 'Child Education Growth Plan',
        'amount' => 2500.00,
        'roi' => '25%',
        'term_duration' => '3 years',
        'payout' => 'Annual',
        'status' => 'Active',
        'start_date' => 'Sep 1, 2025'
    ],
    [
        'plan' => 'Medical Protection Trust Plan',
        'amount' => 10000.00,
        'roi' => '18%',
        'term_duration' => '3 years',
        'payout' => 'Quarterly',
        'status' => 'Active',
        'start_date' => 'Aug 15, 2025'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="description" content="HealthRunCare TrustFund - Empower your legacy with secure, impact-driven trust investments.">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://healthruncare.com/trustfund">
    <title>HealthRunCare TrustFund</title>

    <!-- Preload + Apply (critical CSS) -->
    <link rel="preload" href="../../assets/css/bootstrap.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/css/dashboard.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/icon/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">

    <link rel="stylesheet" href="../../assets/css/animation.min.css">
    <link rel="stylesheet" href="../../assets/css/animation.css">
    <link rel="stylesheet" href="../../assets/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font.css">
    <link rel="stylesheet" href="../../assets/icon/style.css">

    <!-- Fallback for browsers without preload support -->
    <noscript>
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    </noscript>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="../../assets/favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicon/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="HRC">
    <link rel="manifest" href="../../assets/favicon/site.webmanifest">
</head>

<body class="counter-scroll">
    <!-- #wrapper -->
    <div id="wrapper">
        <!-- #page -->
        <div id="page" class="">
            <!-- layout-wrap -->
            <div class="layout-wrap loader-off">
                <!-- preload -->
                <div id="preload" class="preload-container">
                    <div class="preloading">
                        <span></span>
                    </div>
                </div>
                <!-- /preload -->
                                <!-- section-menu-left -->
                <div class="section-menu-left">
                    <div class="box-logo">
                        <a href="/dashboard" id="site-logo-inner">
                            <img class="" id="logo_header" alt="HRC" src="/assets/images/healthruncarelogo.png" width="150px">
                        </a>
                        <div class="button-show-hide">
                            <span class="iconify" data-icon="mdi:chevron-left"></span>
                        </div>
                    </div>
                    <div class="section-menu-left-wrap">
                        <div class="center">
                            <div class="center-item">
                                <div class="center-heading f14-regular text-Gray menu-heading mb-12">Navigation</div>
                            </div>
                            <div class="center-item">
                                <ul class="">
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:view-dashboard-outline"></span>
                                            </div>
                                            <div class="text">Dashboard</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="/dashboard" class="">
                                                    <div class="text">Overview</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:wallet-outline"></span>
                                            </div>
                                            <div class="text">My Wallet</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="/dashboard.wallet" class="">
                                                    <div class="text">Wallet</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.transactions" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:receipt-text-outline"></span></div>
                                            <div class="text">Transaction</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.charity" class="menu-item-button">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:heart-outline"></span>
                                            </div>
                                            <div class="text">Charity</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.investment" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:chart-timeline-variant"></span></div>
                                            <div class="text">Investments</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.holdlock" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:lock-outline"></span></div>
                                            <div class="text">Holdlock</div>
                                        </a>
                                    </li>
                                    <li class="menu-item active">
                                        <a href="/dashboard.trustfund" class="menu-item-button active">
                                            <div class="icon"><span class="iconify" data-icon="mdi:account-cash-outline"></span></div>
                                            <div class="text">Trustfund</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.infrastructure" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:office-building-outline"></span></div>
                                            <div class="text">Infrastructure</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.development" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:tools"></span></div>
                                            <div class="text">Maintenance Dev</div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /section-menu-left -->
                <!-- section-content-right -->
                <div class="section-content-right">
                    <!-- header-dashboard -->
                    <div class="header-dashboard">
                        <div class="wrap">
                            <div class="header-left">
                                <div class="button-show-hide">
                                    <i class="icon-menu"></i>
                                </div>
                                <h6>TrustFund</h6>
                            </div>
                            <div class="header-grid">
                                <div class="line1"></div>
                                <div class="popup-wrap user type-header">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="header-user wg-user">
                                                <span class="image">
                                                    <img src="/assets/images/avatar/default.png" alt="">
                                                </span>
                                                <span class="content flex flex-column">
                                                    <span class="label-02 text-Black name"><?= $user_name ?></span>
                                                    <span class="f14-regular text-Gray">User</span>
                                                </span>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end has-content" aria-labelledby="dropdownMenuButton3" >
                                            <li>
                                                <a href="/dashboard.transactions" class="user-item">
                                                    <div class="body-title-2">Transactions</div>
                                                </a>
                                            </li>
                                            <li>
                                            <a href="#" id="logout-btn" class="user-item">
                                                <div class="body-title-2">Log out</div>
                                            </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /header-dashboard -->
                    <!-- main-content -->
                    <div class="main-content">
                        <!-- main-content-wrap -->
                        <div class="main-content-inner">
                            <!-- main-content-wrap -->
                            <div class="main-content-wrap">
                                <div class="tf-container">
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box impact-overview">
                                            <div class="title mb-16 flex justify-between items-center">
                                                <div class="label-01 text-Primary">TrustFund Summary</div>
                                                <span class="f12-regular text-Gray">Your portfolio growth insight</span>
                                            </div>

                                            <div class="content">
                                                <div class="row g-3">

                                                <!-- Total Trusts -->
                                                <div class="col-md-3 col-6">
                                                <div class="impact-card">
                                                    <div class="icon bg-PrimaryLight">
                                                    <span class="iconify" data-icon="mdi:account-cash-outline" style="color: var(--color-primary);"></span>
                                                    </div>
                                                    <div class="text">
                                                    <h6 id="trust_active" class="text-Primary">0</h6>
                                                    <p class="f12-regular text-Gray">Active Trusts</p>
                                                    </div>
                                                </div>
                                                </div>

                                                <!-- Total Invested -->
                                                <div class="col-md-3 col-6">
                                                <div class="impact-card">
                                                    <div class="icon bg-GreenLight">
                                                    <span class="iconify" data-icon="mdi:cash-plus" style="color: var(--Green);"></span>
                                                    </div>
                                                    <div class="text">
                                                    <h6 id="trust_total_invested" class="text-Green">$0.00</h6>
                                                    <p class="f12-regular text-Gray">Total Invested</p>
                                                    </div>
                                                </div>
                                                </div>

                                                <!-- ROI -->
                                                <div class="col-md-3 col-6">
                                                <div class="impact-card">
                                                    <div class="icon bg-AccentLight">
                                                    <span class="iconify" data-icon="mdi:chart-bar" style="color: var(--color-accent);"></span>
                                                    </div>
                                                    <div class="text">
                                                    <h6 id="trust_total_roi" class="text-Black">$0.00</h6>
                                                    <p class="f12-regular text-Gray">ROI Earned</p>
                                                    </div>
                                                </div>
                                                </div>

                                                <!-- Next Payout -->
                                                <div class="col-md-3 col-6">
                                                <div class="impact-card">
                                                    <div class="icon bg-YellowLight">
                                                    <span class="iconify" data-icon="mdi:calendar-sync" style="color: #d4a017;"></span>
                                                    </div>
                                                    <div class="text">
                                                    <h6 id="trust_next_payout" class="text-Black">—</h6>
                                                    <p class="f12-regular text-Gray">Next Payout Date</p>
                                                    </div>
                                                </div>
                                                </div>


                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                   <!-- TrustFund Plans Grid (Unified Investment Format) -->
                                        <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box trustfund-plans">
                                            <div class="title mb-16 flex justify-between items-center">
                                                <div class="label-01 text-Primary">Available TrustFund Plans</div>
                                            </div>

                                            <div class="content">
                                                <div class="row g-4">
                                                <?php foreach ($plans as $plan): ?>
                                                <div class="col-lg-3 col-md-6">
                                                    <div class="plan-card">
                                                    <div class="plan-header flex justify-between items-center mb-12">
                                                        <div class="flex items-center gap-2">
                                                        <h6 class="plan-title"><?php echo htmlspecialchars($plan['name']); ?></h6>
                                                        </div>
                                                    </div>

                                                    <p class="f12-regular text-Gray mb-12">
                                                        <?php echo htmlspecialchars($plan['purpose']); ?>
                                                    </p>

                                                    <table class="plan-features">
                                                        <tr>
                                                        <td>Min Investment</td>
                                                        <td><?php echo $plan['min_deposit']; ?></td>
                                                        </tr>
                                                        <tr>
                                                        <td>Term</td>
                                                        <td><?php echo $plan['lock_period']; ?></td>
                                                        </tr>
                                                        <tr>
                                                        <td>ROI</td>
                                                        <td class="text-Green fw-bold"><?php echo $plan['roi']; ?></td>
                                                        </tr>
                                                        <tr>
                                                        <td>Risk Level</td>
                                                        <td class="text-<?php echo $plan['color'] ?? 'Gray'; ?> fw-bold">
                                                            <?php echo $plan['risk']; ?>
                                                        </td>
                                                        </tr>
                                                        <tr>
                                                        <td>Payout Option</td>
                                                        <td><?php echo $plan['payout']; ?></td>
                                                        </tr>
                                                    </table>

                                                    <p class="f12-regular text-Gray italic mt-12">
                                                        <?php echo htmlspecialchars($plan['summary']); ?>
                                                    </p>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                    <!-- Start Trust Fund Section -->
                                                            <div class="row mb-32">
                                                            <!-- Left: Start Form -->
                                                            <div class="col-lg-8 col-md-12">
                                                                <div class="wg-box trustfund-form">
                                                                <div class="title mb-16">
                                                                    <div class="label-01 text-Primary">Start Your TrustFund Plan</div>
                                                                </div>

                                                                <div class="content">
                                                                    <form class="form-style-1" id="trustfundForm">
                                                                        <input type="hidden" name="plan_id">
                                                                        <input type="hidden" name="plan_name">

                                                                    <!-- Select Plan -->
                                                                        <div class="mb-20">
                                                                        <label class="f14-regular text-Black mb-8">Select Plan</label>
                                                                        <select class="form-select custom-select" id="plan-select">
                                                                            <option value="">Select a Plan</option>
                                                                            <?php foreach ($plans as $plan): ?>
                                                                            <option
                                                                                value="<?php echo $plan['id']; ?>"
                                                                                data-min="<?php echo str_replace('$', '', $plan['min_deposit']); ?>"
                                                                                data-term="<?php echo $plan['lock_period']; ?>"
                                                                                data-roi="<?php echo $plan['roi']; ?>"
                                                                            >
                                                                                <?php echo htmlspecialchars($plan['name']); ?>
                                                                            </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                        </div>


                                                                    <!-- Investment Amount -->
                                                                    <div class="mb-20 position-relative">
                                                                        <label class="f14-regular text-Black mb-8">Amount to Invest (USD)</label>
                                                                        <div class="input-group">
                                                                        <span class="input-icon">
                                                                            <span class="iconify" data-icon="mdi:account-cash"></span>
                                                                        </span>
                                                                        <input class="wallet-input form-control" type="number" name="amount" placeholder="Enter amount" min="1" id="invest-amount">
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-20 position-relative">
                                                                        <label class="f14-regular text-Black mb-8">Wallet Balance</label>
                                                                        <!-- In form -->
                                                                    <div class="input-group">
                                                                        <span class="input-icon"><span class="iconify" data-icon="mdi:wallet-outline"></span></span>
                                                                        <span id="wallet-balance" class="form-control readonly-input">$0.00</span>  <!-- <span> not input -->
                                                                    </div>
                                                                    </div>

                                                                    <!-- Details -->
                                                                    <div class="row mb-20">
                                                                        <div class="col-md-6">
                                                                        <label class="f14-regular text-Black mb-8">Term Duration</label>
                                                                        <input class="f12-regular text-Gray p-12 border border-Gray rounded" type="text" id="term-duration" readonly>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                        <label class="f14-regular text-Black mb-8">Expected ROI</label>
                                                                        <input class="f12-regular text-Gray p-12 border border-Gray rounded" type="text" id="expected-roi" readonly>
                                                                        </div>
                                                                    </div>

                                                                    <!-- CTA -->
                                                                    <button
                                                                        type="submit"
                                                                        class="tf-button style-default w-full f14-bold bg-Green text-White hover:bg-Primary transition-colors duration-300"
                                                                        id="invest-btn"
                                                                        disabled
                                                                    >
                                                                        Start Trust Fund
                                                                    </button>
                                                                    </form>
                                                                </div>
                                                                </div>
                                                            </div>

                                                            <!-- Right: Benefits -->
                                                            <div class="col-lg-4 col-md-12">
                                                                <div class="wg-box trustfund-benefits">
                                                                <div class="title mb-16">
                                                                    <div class="label-01 text-Primary">Why Choose TrustFunds</div>
                                                                </div>

                                                                <div class="content">
                                                                    <ul class="f12-regular text-Gray">
                                                                    <li class="mb-8 flex items-center">
                                                                        100% Legally Registered Trust
                                                                    </li>
                                                                    <li class="mb-8 flex items-center">
                                                                        Professionally Managed by Experts
                                                                    </li>
                                                                    <li class="mb-8 flex items-center">
                                                                        Transparent Audited Reporting
                                                                    </li>
                                                                    <li class="mb-8 flex items-center">
                                                                        Flexible Payout Options
                                                                    </li>
                                                                    <li class="mb-8 flex items-center">
                                                                        Positive Social Impact
                                                                    </li>
                                                                    </ul>
                                                                </div>
                                                                </div>
                                                            </div>
                                                            </div>

                                    <!-- My TrustFund Plans Table -->
<!-- My TrustFund Plans Section -->
<div class="row mb-32">
  <div class="col-12">
    <div class="wg-box trustfund-plans-table">
      <div class="title mb-16 flex justify-between items-center">
        <div class="label-01 text-Primary">My TrustFund Plans</div>
        <div class="view-all">
          <a href="#" class="f12-regular text-Primary hover:underline flex items-center">
            View All
            <span class="iconify ml-2" data-icon="mdi:chevron-right"></span>
          </a>
        </div>
      </div>

      <div class="content">
        <div class="table-responsive table-list-transaction">
          <div class="list-transaction-head title-sort bg-Primary text-White">
            <div class="btn-key-sort"><div class="f12-bold">Plan Name</div></div>
            <div class="btn-key-sort"><div class="f12-bold">Amount Invested</div></div>
            <div class="btn-key-sort"><div class="f12-bold">ROI (%)</div></div>
            <div class="btn-key-sort"><div class="f12-bold">Term Duration</div></div>
            <div class="btn-key-sort"><div class="f12-bold">Payout Option</div></div>
            <div class="btn-key-sort"><div class="f12-bold">Status</div></div>
            <div class="btn-key-sort"><div class="f12-bold">Start Date</div></div>
          </div>

          <table class="list-transaction-content content-sort w-100">
            <tbody id="active-trusts-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Eligible Unlocks Section -->
<div class="row mb-32">
  <div class="col-12">
    <div class="wg-box unlock-plans">
      <div class="title mb-16 flex justify-between items-center">
        <div class="label-01 text-Primary">Eligible Unlocks (Mature Plans)</div>
        <div class="view-all">
          <a href="#" class="f12-regular text-Primary hover:underline flex items-center">
            View All
            <span class="iconify ml-2" data-icon="mdi:chevron-right"></span>
          </a>
        </div>
      </div>

      <div class="content">
          <div class="table-responsive table-list-transaction">
            <div class="list-transaction-head title-sort bg-Green text-White">
              <div class="btn-key-sort"><div class="f12-bold">Plan Name</div></div>
              <div class="btn-key-sort"><div class="f12-bold">Original Amount</div></div>
              <div class="btn-key-sort"><div class="f12-bold">ROI Earned</div></div>
              <div class="btn-key-sort"><div class="f12-bold">Maturity Date</div></div>
              <div class="btn-key-sort"><div class="f12-bold">Total Payout</div></div>  
              <div class="btn-key-sort"><div class="f12-bold">Actions</div></div>
            </div>

            <table class="list-transaction-content content-sort w-100">
              <tbody id="matured-trusts-tbody"></tbody>
            </table>
      </div>
    </div>
  </div>
</div>

                                </div>
                                </div>
                            </div>
                            <!-- /main-content-wrap -->
                        </div>
                        <!-- /main-content-wrap -->
                        
                    </div>
                    <!-- /main-content -->
                </div>
                <!-- /section-content-right -->
            </div>
            <!-- /layout-wrap -->
        </div>
        <!-- /#page -->
    </div>
    <!-- /#wrapper -->

        <div id="loader" class="hidden">
    <div class="line-loader">
            <div></div><div></div><div></div><div></div><div></div>
        </div>
    </div>
    <!-- Toast Container -->
    <div id="toast-container"></div>

    <script src="../../assets/js/jquery.min.js"></script>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/trustfund.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script src="../../assets/js/countto.js" defer></script>
    <script src="../../assets/js/bootstrap-select.min.js" defer></script>
    <script src="../../assets/js/dashboard.js" defer></script>
    <!-- Iconify CDN -->
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>