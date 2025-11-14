<?php
// pages/user/holdlock.php

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

// Placeholder data for plans
$plans = [
    [
        'id' => 1,
        'name' => 'Flexi Health Lock Plan',
        'purpose' => 'A short-term plan designed for clients who want safe, quick returns while keeping their capital secure.',
        'min_deposit' => '$10,000',
        'max_deposit' => '$100,000',
        'lock_period' => '6 months',
        'roi' => '3–4%',
        'risk' => 'Very Low',
        'payout' => 'Full payout at maturity',
        'summary' => 'Ideal for clients seeking liquidity and short-term growth. Funds are safely held and paid out at the end of the term.',
        'icon' => 'mdi:clock-outline',
        'color' => 'Green'
    ],
    [
        'id' => 2,
        'name' => 'Standard Lock & Grow Plan',
        'purpose' => 'A one-year plan offering predictable and consistent growth with minimal risk.',
        'min_deposit' => '$20,000',
        'max_deposit' => '$300,000',
        'lock_period' => '12 months',
        'roi' => '7–9%',
        'risk' => 'Low',
        'payout' => 'Annual or full payout at maturity',
        'summary' => 'A balanced one-year growth plan for investors who prefer stability and moderate fixed returns.',
        'icon' => 'mdi:calendar-check',
        'color' => 'Green'
    ],
    [
        'id' => 3,
        'name' => 'Executive LockPlus Plan',
        'purpose' => 'A two-year plan for individuals and organizations seeking better returns from moderate-term investments.',
        'min_deposit' => '$50,000',
        'max_deposit' => '$500,000',
        'lock_period' => '24 months',
        'roi' => '14–18%',
        'risk' => 'Moderate',
        'payout' => 'Annual or full payout at maturity',
        'summary' => 'Perfect for mid- to high-level investors seeking strong, consistent growth over two years with minimal risk exposure.',
        'icon' => 'mdi:briefcase-check',
        'color' => 'Blue'
    ],
    [
        'id' => 4,
        'name' => 'Prestige Capital Hold Plan',
        'purpose' => 'A premium plan for investors with large capital who seek long-term, high-yield returns.',
        'min_deposit' => '$250,000',
        'max_deposit' => 'Unlimited',
        'lock_period' => '36 months',
        'roi' => '25–30%',
        'risk' => 'Moderate',
        'payout' => 'Annual, bi-annual, or full payout at maturity',
        'summary' => 'A long-term, asset-secure investment option that rewards patience with premium returns and stable growth.',
        'icon' => 'mdi:crown-outline',
        'color' => 'Orange'
    ],
    [
        'id' => 5,
        'name' => 'Lifetime Reserve Lock Plan',
        'purpose' => 'A lifelong plan designed for wealth preservation and consistent annual income.',
        'min_deposit' => '$1,000,000',
        'max_deposit' => 'Unlimited',
        'lock_period' => 'Lifetime (Perpetual)',
        'roi' => '6–8% annual',
        'risk' => 'Low',
        'payout' => 'Annual or quarterly lifetime payout',
        'summary' => 'An exclusive wealth preservation plan that guarantees lifetime income, ideal for estates, families, or organizations focused on long-term legacy.',
        'icon' => 'mdi:infinity',
        'color' => 'Green'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="description" content="HealthRunCare HoldLock - Secure your capital with guaranteed trust-based growth plans.">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://healthruncare.com/holdlock">
    <title>HealthRunCare HoldLock</title>

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
                                            <li class="sub-menu-item active">
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
                                    <li class="menu-item active">
                                        <a href="/dashboard.holdlock" class="menu-item-button active">
                                            <div class="icon"><span class="iconify" data-icon="mdi:lock-outline"></span></div>
                                            <div class="text">Holdlock</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.trustfund" class="menu-item-button">
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
                                <h6>HoldLock</h6>
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
                                                <div class="label-01 text-Primary">HoldLock Summary</div>
                                                <span class="f12-regular text-Gray">Your locked savings performance</span>
                                            </div>

                                            <div class="content">
                                                <div class="row g-3">

                                                <!-- Active Locks -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-PrimaryLight">
                                                        <span class="iconify" data-icon="mdi:lock-outline" style="color: var(--color-primary);"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 class="text-Primary" id="card-active-locks">0</h6>
                                                        <p class="f12-regular text-Gray">Active Locks</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                <!-- Total Locked Amount -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-GreenLight">
                                                        <span class="iconify" data-icon="mdi:cash-lock" style="color: var(--Green);"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 class="text-Green" id="card-total-locked">$0.00</h6>
                                                        <p class="f12-regular text-Gray">Total Locked</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                <!-- ROI -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-AccentLight">
                                                        <span class="iconify" data-icon="mdi:chart-areaspline" style="color: var(--color-accent);"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 class="text-Black" id="card-roi-earned">$0.00</h6>
                                                        <p class="f12-regular text-Gray">ROI Earned</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                <!-- Next Unlock Date -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-YellowLight">
                                                        <span class="iconify" data-icon="mdi:calendar-lock" style="color: #d4a017;"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 class="text-Black" id="card-next-unlock">—</h6>
                                                        <p class="f12-regular text-Gray">Next Unlock Date</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>

                                    <!-- HoldLock Plans Grid (Uniform Investment Format) -->
                                        <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box holdlock-plans">
                                            <div class="title mb-16 flex justify-between items-center">
                                                <div class="label-01 text-Primary">Available HoldLock Plans</div>
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

                                                    <p class="f12-regular text-Gray mb-12"><?php echo htmlspecialchars($plan['purpose']); ?></p>

                                                    <table class="plan-features">
                                                        <tr>
                                                        <td>Min Deposit</td>
                                                        <td><?php echo $plan['min_deposit']; ?></td>
                                                        </tr>
                                                        <tr>
                                                        <td>Lock Period</td>
                                                        <td><?php echo $plan['lock_period']; ?></td>
                                                        </tr>
                                                        <tr>
                                                        <td>ROI</td>
                                                        <td class="text-Green fw-bold"><?php echo $plan['roi']; ?></td>
                                                        </tr>
                                                        <tr>
                                                        <td>Max Deposit</td>
                                                        <td><?php echo $plan['max_deposit']; ?></td>
                                                        </tr>
                                                        <tr>
                                                        <td>Payout</td>
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




                                                        <!-- Start HoldLock Plan Section -->
                                                            <div class="row mb-32">
                                                            <!-- Left: Start Form -->
                                                            <div class="col-lg-8 col-md-12">
                                                                <div class="wg-box holdlock-form">
                                                                <div class="title mb-16">
                                                                    <div class="label-01 text-Primary">Start Your HoldLock Plan</div>
                                                                </div>

                                                                <div class="content">
                                                                    <form class="form-style-1" id="holdlock-form" autocomplete="off">
                                                                    <!-- Select Plan -->
                                                                    <div class="mb-20">
                                                                        <label class="f14-regular text-Black mb-8">Select Plan</label>
                                                                        <select class="form-select custom-select" id="plan-select" onchange="updateHoldlockDetails()"
>
                                                                        <option>Select a Plan</option>
                                                                        <?php foreach ($plans as $plan): ?>
                                                                        <option
                                                                            value="<?php echo $plan['id']; ?>"
                                                                            data-min="<?php echo str_replace('$', '', $plan['min_deposit']); ?>"
                                                                            data-max="<?php echo $plan['max_deposit'] === 'Unlimited' ? '' : str_replace('$', '', $plan['max_deposit']); ?>"
                                                                            data-lock="<?php echo $plan['lock_period']; ?>"
                                                                            data-roi="<?php echo $plan['roi']; ?>"
                                                                        >
                                                                            <?php echo htmlspecialchars($plan['name']); ?>
                                                                        </option>
                                                                        <?php endforeach; ?>
                                                                        </select>
                                                                    </div>

                                                                    <!-- Lock Amount -->
                                                                    <div class="mb-20 position-relative">
                                                                        <label class="f14-regular text-Black mb-8">Amount to Lock (USD)</label>
                                                                        <div class="input-group">
                                                                        <span class="input-icon">
                                                                            <span class="iconify" data-icon="mdi:lock"></span>
                                                                        </span>
                                                                        <input class="wallet-input form-control" type="number" placeholder="Enter amount" min="1" id="lock-amount">
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
                                                                        <label class="f14-regular text-Black mb-8">Lock Period</label>
                                                                        <input class="f12-regular text-Gray p-12 border border-Gray rounded" type="text" id="lock-period" readonly>
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
                                                                        id="lock-btn"
                                                                        disabled
                                                                    >
                                                                        Lock Funds
                                                                    </button>
                                                                    </form>
                                                                </div>
                                                                </div>
                                                            </div>

                                                            <!-- Right: Benefits -->
                                                            <div class="col-lg-4 col-md-12">
                                                                <div class="wg-box holdlock-benefits">
                                                                <div class="title mb-16">
                                                                    <div class="label-01 text-Primary">HoldLock Benefits</div>
                                                                </div>

                                                                <div class="content">
                                                                    <ul class="f12-regular text-Gray">
                                                                    <li class="mb-8 flex items-center">
                                                                        100% Capital Protection
                                                                    </li>
                                                                    <li class="mb-8 flex items-center">
                                                                        Guaranteed Fixed Returns
                                                                    </li>
                                                                    <li class="mb-8 flex items-center">
                                                                        Transparent Trust Management
                                                                    </li>
                                                                    <li class="mb-8 flex items-center">
                                                                        Flexible Lock Periods
                                                                    </li>
                                                                    <li class="mb-8 flex items-center">
                                                                        Lifetime Options Available
                                                                    </li>
                                                                    </ul>
                                                                </div>
                                                                </div>
                                                            </div>
                                                            </div>


                                    <!-- My Locked Plans Table -->
                                    <div class="row mb-32">
                                    <div class="col-12">
                                        <div class="wg-box locked-plans">
                                        <div class="title mb-16 flex justify-between items-center">
                                            <div class="label-01 text-Primary">My Locked Plans</div>
                                        </div>

                                    <div class="content">
                                        <div class="table-responsive table-list-transaction">
                                            <!-- Table Header -->
                                            <div class="list-transaction-head title-sort bg-Primary text-White">
                                            <div class="btn-key-sort"><div class="f12-bold">Plan Name</div></div>
                                            <div class="btn-key-sort"><div class="f12-bold">Amount Locked</div></div>
                                            <div class="btn-key-sort"><div class="f12-bold">ROI (%)</div></div>
                                            <div class="btn-key-sort"><div class="f12-bold">Lock Period</div></div>
                                            <div class="btn-key-sort"><div class="f12-bold">Payout Option</div></div>
                                            <div class="btn-key-sort"><div class="f12-bold">Status</div></div>
                                            <div class="btn-key-sort"><div class="f12-bold">Start Date</div></div>
                                            </div>

                                            <!-- Dynamic Table -->
                                            <table id="active-holdlocks-table" class="list-transaction-content content-sort w-100">
                                            <tbody id="active-holdlocks-table-body">
                                                <!-- Populated dynamically by holdlock.js -->
                                            </tbody>
                                            </table>

                                            <!-- Empty state message (optional) -->
                                            <div id="no-active-holdlocks" class="text-center py-20 hidden">
                                            <p class="f14-regular text-Gray">No active HoldLock plans yet. Start one above.</p>
                                            </div>
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
                                </div>
                                <div class="content">
                                
                                <div id="matured-holdlocks-table-wrapper" class="table-responsive table-list-transaction">
                                    <div class="list-transaction-head title-sort bg-Green text-White">
                                    <div class="btn-key-sort"><div class="f12-bold">Plan Name</div></div>
                                    <div class="btn-key-sort"><div class="f12-bold">Original Amount</div></div>
                                    <div class="btn-key-sort"><div class="f12-bold">ROI Earned</div></div>
                                    <div class="btn-key-sort"><div class="f12-bold">Maturity Date</div></div>
                                    <div class="btn-key-sort"><div class="f12-bold">Total Payout</div></div>  
                                    <div class="btn-key-sort"><div class="f12-bold">Actions</div></div>
                                    </div>
                                    <table id="matured-holdlocks-table" class="list-transaction-content content-sort w-100">
                                    <tbody id="matured-holdlocks-table-body">
                                        <!-- Dynamically loaded via JS -->
                                    </tbody>
                                    </table>
                                    <div id="matured-holdlocks-empty" class="text-center py-20 hidden">
                                    <p class="f14-regular text-Gray">No mature plans available for unlock at this time.</p>
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
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/api.js"></script>
<script src="../../assets/js/holdlock.js"></script>
<script src="../../assets/js/countto.js" defer></script>
<script src="../../assets/js/bootstrap-select.min.js" defer></script>
<script src="../../assets/js/dashboard.js" defer></script>

    <!-- Iconify CDN -->
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>