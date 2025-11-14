<?php
// pages/user/development.php

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

// Placeholder data for development plans
$plans = [
    [
        'id' => 1,
        'name' => 'Maintenance Support Starter Plan',
        'purpose' => 'An entry-level plan for individuals seeking both community impact and short-term, reliable returns.',
        'min_deposit' => '$10,000',
        'max_deposit' => '$50,000',
        'duration' => '9 months',
        'roi' => '5–6%',
        'risk' => 'Very Low',
        'payout' => 'Full payout at maturity',
        'summary' => 'A short-term plan ideal for investors looking to earn modest returns while supporting the upkeep of vital community healthcare assets.',
        'color' => 'Green'
    ],
    [
        'id' => 2,
        'name' => 'Standard Equipment Care Plan',
        'purpose' => 'A one-year plan designed to maintain essential hospital assets and ensure continuous medical operations.',
        'min_deposit' => '$25,000',
        'max_deposit' => '$300,000',
        'duration' => '12 months',
        'roi' => '8–10%',
        'risk' => 'Low',
        'payout' => 'Annual or full payout at maturity',
        'summary' => 'Investors in this plan help healthcare centers maintain key operational tools, earning steady returns from predictable maintenance and renewal income streams.',
        'color' => 'Green'
    ],
    [
        'id' => 3,
        'name' => 'Infrastructure Development Plan',
        'purpose' => 'A mid-term investment for clients supporting the maintenance and modernization of healthcare infrastructure and utilities.',
        'min_deposit' => '$50,000',
        'max_deposit' => '$500,000',
        'duration' => '24 months',
        'roi' => '15–18%',
        'risk' => 'Moderate',
        'payout' => 'Annual or full payout at maturity',
        'summary' => 'This plan supports large-scale repair and development projects with measurable social and financial benefits.',
        'color' => 'Blue'
    ],
    [
        'id' => 4,
        'name' => 'Premium Equipment Sustainability Plan',
        'purpose' => 'A premium, long-term plan focusing on advanced medical system upkeep and modernization.',
        'min_deposit' => '$250,000',
        'max_deposit' => 'Unlimited',
        'duration' => '36 months',
        'roi' => '22–28%',
        'risk' => 'Moderate',
        'payout' => 'Annual, bi-annual, or full payout at maturity',
        'summary' => 'Designed for investors seeking high-value, long-term impact and returns from advanced healthcare sustainability programs.',
        'color' => 'Blue'
    ],
    [
        'id' => 5,
        'name' => 'Lifetime Equipment Trust Plan',
        'purpose' => 'A perpetual plan offering lifetime income while funding continuous maintenance operations for healthcare facilities.',
        'min_deposit' => '$1,000,000',
        'max_deposit' => 'Unlimited',
        'duration' => 'Lifetime (Perpetual)',
        'roi' => '6–8% annual',
        'risk' => 'Low',
        'payout' => 'Annual or quarterly lifetime payout',
        'summary' => 'A legacy plan ensuring long-term financial returns while maintaining the efficiency and reliability of healthcare systems for generations.',
        'color' => 'Green'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="description" content="HealthRunCare Development - Invest in healthcare maintenance for sustainable returns.">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://healthruncare.com/development">
    <title>HealthRunCare Development</title>

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
                                    <li class="menu-item active">
                                        <a href="/dashboard.development" class="menu-item-button active">
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
                                <h6>Development</h6>
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
                                                <div class="label-01 text-Primary">Maintenance Development Summary</div>
                                                <span class="f12-regular text-Gray">System upkeep and progress</span>
                                            </div>

                                            <div class="content">
                                                <div class="row g-3">

                                                <!-- Active Projects -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-PrimaryLight">
                                                        <span class="iconify" data-icon="mdi:tools" style="color: var(--color-primary);"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 class="text-Primary" id="dev-active">0</h6>
                                                        <p class="f12-regular text-Gray">Active Projects</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                <!-- Total Spent -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-GreenLight">
                                                        <span class="iconify" data-icon="mdi:currency-usd" style="color: var(--Green);"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 class="text-Green" id="dev-spent">$0.00</h6>
                                                        <p class="f12-regular text-Gray">Total Spent</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                <!-- Completed Tasks -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-AccentLight">
                                                        <span class="iconify" data-icon="mdi:check-circle-outline" style="color: var(--color-accent);"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 class="text-Black" id="dev-completed">0</h6>
                                                        <p class="f12-regular text-Gray">Completed Tasks</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                <!-- Next Maintenance -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-YellowLight">
                                                        <span class="iconify" data-icon="mdi:calendar-check" style="color: #d4a017;"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 class="text-Black" id="dev-next">—</h6>
                                                        <p class="f12-regular text-Gray">Next Maintenance</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>


                                    <!-- Development Plans Grid -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box development-plans">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01 text-Primary">Maintenance & Development Plans</div>
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
                                                                        <td>Duration</td>
                                                                        <td><?php echo $plan['duration']; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>ROI</td>
                                                                        <td class="text-Green fw-bold"><?php echo $plan['roi']; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Risk Level</td>
                                                                        <td class="text-<?php echo $plan['color']; ?> fw-bold"><?php echo $plan['risk']; ?></td>
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

                                    <!-- Start Development Section -->
                                    <div class="row mb-32">
                                        <!-- Left: Start Form -->
                                        <div class="col-lg-8 col-md-12">
                                            <div class="wg-box development-form">
                                                <div class="title mb-16">
                                                    <div class="label-01 text-Primary">Start Your Development Plan</div>
                                                </div>

                                                <div class="content">
                                                    <form class="form-style-1" id="development-form">
                                                        <!-- Select Plan -->
                                                        <div class="mb-20">
                                                            <label class="f14-regular text-Black mb-8">Select Plan</label>
                                                            <select class="form-select custom-select" id="plan-select" onchange="updatePlanDetails()">
                                                                <option>Select a Plan</option>
                                                                <?php foreach ($plans as $plan): ?>
                                                                <option
                                                                    value="<?php echo $plan['id']; ?>"
                                                                    data-min="<?php echo str_replace('$', '', $plan['min_deposit']); ?>"
                                                                    data-max="<?php echo $plan['max_deposit'] === 'Unlimited' ? '' : str_replace('$', '', $plan['max_deposit']); ?>"
                                                                    data-duration="<?php echo $plan['duration']; ?>"
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
                                                                    <span class="iconify" data-icon="mdi:tools"></span>
                                                                </span>
                                                                <input class="wallet-input form-control" type="number" placeholder="Enter amount" min="1" id="invest-amount">
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
                                                                <label class="f14-regular text-Black mb-8">Duration</label>
                                                                <input class="f12-regular text-Gray p-12 border border-Gray rounded" type="text" id="duration" readonly>
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
                                                            Start Development
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right: Benefits -->
                                        <div class="col-lg-4 col-md-12">
                                            <div class="wg-box development-benefits">
                                                <div class="title mb-16">
                                                    <div class="label-01 text-Primary">Why Choose Maintenance & Development</div>
                                                </div>
                                                <div class="content">
                                                    <ul class="f12-regular text-Gray">
                                                        <li class="mb-8 flex items-center">
                                                            Steady Asset-Backed Returns
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Long-Term Service Contracts
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Community Healthcare Impact
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Predictable Income Streams
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Professional Management
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- My Development Plans Table -->
                                    <div class="row mb-32">
                                    <div class="col-12">
                                        <div class="wg-box development-plans-table">
                                        <div class="title mb-16 flex justify-between items-center">
                                            <div class="label-01 text-Primary">My Development Plans</div>
                                            <div class="view-all">
                                            <a href="#" class="f12-regular text-Primary hover:underline flex items-center">
                                                View All
                                                <span class="iconify ml-2" data-icon="mdi:chevron-right"></span>
                                            </a>
                                            </div>
                                        </div>

                                        <div class="content">
                                            <div class="table-responsive table-list-transaction">
                                            <!-- Table Header -->
                                            <div class="list-transaction-head title-sort bg-Primary text-White">
                                                <div class="btn-key-sort"><div class="f12-bold">Plan Name</div></div>
                                                <div class="btn-key-sort"><div class="f12-bold">Amount Invested</div></div>
                                                <div class="btn-key-sort"><div class="f12-bold">ROI (%)</div></div>
                                                <div class="btn-key-sort"><div class="f12-bold">Duration</div></div>
                                                <div class="btn-key-sort"><div class="f12-bold">Payout Option</div></div>
                                                <div class="btn-key-sort"><div class="f12-bold">Status</div></div>
                                                <div class="btn-key-sort"><div class="f12-bold">Start Date</div></div>
                                            </div>

                                            

                                            <!-- Data Table -->
                                            <table class="list-transaction-content content-sort w-100">
                                                <tbody id="active-dev-tbody"></tbody>
                                                    <div id="empty-active-dev" class="text-center text-muted py-3" style="display:none;">
                                                    </div>
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
                                            <!-- Table Header -->
                                            <div class="list-transaction-head title-sort bg-Green text-White">
                                                <div class="btn-key-sort"><div class="f12-bold">Plan Name</div></div>
                                                <div class="btn-key-sort"><div class="f12-bold">Original Amount</div></div>
                                                <div class="btn-key-sort"><div class="f12-bold">ROI Earned</div></div>
                                                <div class="btn-key-sort"><div class="f12-bold">Maturity Date</div></div>
                                                <div class="btn-key-sort"><div class="f12-bold">Total Payout</div></div>  
                                                <div class="btn-key-sort"><div class="f12-bold">Actions</div></div>
                                            </div>

                                            

                                            <!-- Table -->
                                            <table class="list-transaction-content content-sort w-100">
                                                <tbody id="matured-dev-tbody"></tbody>
                                                <div id="empty-matured-dev" class="text-center text-muted py-3" style="display:none;">
                                                </div>
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
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script src="../../assets/js/api.js"></script>
    <script src="../../assets/js/maintenance.js" defer></script>
    <script src="../../assets/js/countto.js" defer></script>
    <script src="../../assets/js/bootstrap-select.min.js" defer></script>
    <script src="../../assets/js/dashboard.js" defer></script>
    <!-- Iconify CDN -->
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>