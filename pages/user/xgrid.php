<?php
// pages/user/infrastructure.php

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

// Placeholder data for infrastructure plans
$plans = [
    [
        'id' => 1,
        'name' => 'Basic Diagnostic Plan',
        'purpose' => 'To support community and mid-level hospitals with portable ultrasound diagnostic systems for early disease detection.',
        'min_deposit' => '$10,000',
        'payoff_period' => '1 year',
        'roi' => '8–10%',
        'risk' => 'Very Low',
        'repayment' => 'Quarterly payments over 12 months',
        'summary' => 'Investors fund the purchase and setup of ultrasound systems. Clinics repay from diagnostic service fees, returning your full capital plus up to 10% profit within one year.',
        'color' => 'Green'
    ],
    [
        'id' => 2,
        'name' => 'Imaging Growth Plan',
        'purpose' => 'To deploy digital X-ray imaging systems for regional hospitals and diagnostic centers.',
        'min_deposit' => '$20,000',
        'payoff_period' => '18 months',
        'roi' => '12–15%',
        'risk' => 'Low',
        'repayment' => 'Quarterly or semi-annual',
        'summary' => 'Investors help hospitals acquire X-ray systems. Hospitals repay from patient scan revenue, and investors earn 12–15% total profit within 18 months.',
        'color' => 'Green'
    ],
    [
        'id' => 3,
        'name' => 'Advanced Radiology Plan',
        'purpose' => 'To enable hospitals to install CT scanners and expand access to high-precision imaging services.',
        'min_deposit' => '$50,000',
        'payoff_period' => '2 years',
        'roi' => '15–20%',
        'risk' => 'Moderate',
        'repayment' => 'Monthly or quarterly payments',
        'summary' => 'Investors finance CT equipment. HealthRunCare manages contracts and collects hospital payments, ensuring full repayment plus up to 20% ROI over 24 months.',
        'color' => 'Blue'
    ],
    [
        'id' => 4,
        'name' => 'Dialysis Infrastructure Plan',
        'purpose' => 'To expand kidney care capacity through the installation of dialysis centers and water treatment systems.',
        'min_deposit' => '$100,000',
        'payoff_period' => '2.5 years',
        'roi' => '18–22%',
        'risk' => 'Moderate',
        'repayment' => 'Quarterly payments with inflation-adjusted escalation clause',
        'summary' => 'Your investment supports dialysis services in hospitals. Repayments come from patient treatment revenue, returning 18–22% profit over 30 months.',
        'color' => 'Blue'
    ],
    [
        'id' => 5,
        'name' => 'Complete Operating Room Equipment Plan',
        'purpose' => 'To establish modern operating theatres equipped for advanced surgical operations in partner hospitals.',
        'min_deposit' => '$150,000',
        'payoff_period' => '3 years',
        'roi' => '20–25%',
        'risk' => 'Moderate',
        'repayment' => 'Monthly or quarterly with partial early payment options',
        'summary' => 'Investors finance complete operating room setups. Hospitals repay from surgical revenues, providing up to 25% profit over three years.',
        'color' => 'Blue'
    ],
    [
        'id' => 6,
        'name' => 'Hospital Diagnostic Wing Installation Plan',
        'purpose' => 'To construct and equip an entire hospital diagnostic and imaging wing, combining MRI, CT, X-ray, ultrasound, and lab systems.',
        'min_deposit' => '$500,000',
        'payoff_period' => '3 years',
        'roi' => '28–30%',
        'risk' => 'Moderate-Low',
        'repayment' => 'Quarterly or bi-annual',
        'summary' => 'A high-value plan for institutional investors to fund full hospital diagnostic wings. Returns reach 30% over three years, backed by large-scale facility repayment contracts.',
        'color' => 'Green'
    ]
];

// Placeholder data for user's infrastructure plans
$infraPlans = [
    [
        'plan' => 'Basic Diagnostic Plan',
        'amount' => 15000.00,
        'roi' => '9%',
        'payoff_period' => '1 year',
        'repayment_mode' => 'Quarterly',
        'status' => 'Active',
        'start_date' => 'Sep 1, 2025'
    ],
    [
        'plan' => 'Imaging Growth Plan',
        'amount' => 25000.00,
        'roi' => '14%',
        'payoff_period' => '18 months',
        'repayment_mode' => 'Quarterly',
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
    <meta name="description" content="HealthRunCare Infrastructure - Invest in healthcare infrastructure for profit and social impact.">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://healthruncare.com/infrastructure">
    <title>HealthRunCare Infrastructure</title>

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
                                    <li class="menu-item active">
                                        <a href="/dashboard.infrastructure" class="menu-item-button active">
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
                                <h6>Infrastructure</h6>
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
                                                        <div class="label-01 text-Primary">Infrastructure Development Summary</div>
                                                        <span class="f12-regular text-Gray">Your contributions to structural growth</span>
                                                    </div>

                                                    <div class="content">
                                                        <div class="row g-3">

                                                        <!-- Active Projects -->
                                                        <div class="col-md-3 col-6">
                                                            <div class="impact-card">
                                                            <div class="icon bg-PrimaryLight">
                                                                <span class="iconify" data-icon="mdi:office-building-outline" style="color: var(--color-primary);"></span>
                                                            </div>
                                                            <div class="text">
                                                                <h6 class="text-Primary" id="infra-active">0</h6>
                                                                <p class="f12-regular text-Gray">Active Projects</p>
                                                            </div>
                                                            </div>
                                                        </div>

                                                        <!-- Total Funded -->
                                                        <div class="col-md-3 col-6">
                                                            <div class="impact-card">
                                                            <div class="icon bg-GreenLight">
                                                                <span class="iconify" data-icon="mdi:bank-outline" style="color: var(--Green);"></span>
                                                            </div>
                                                            <div class="text">
                                                                <h6 class="text-Green" id="infra-funded">$0.00</h6>
                                                                <p class="f12-regular text-Gray">Total Funded</p>
                                                            </div>
                                                            </div>
                                                        </div>

                                                        <!-- Completed Builds -->
                                                        <div class="col-md-3 col-6">
                                                            <div class="impact-card">
                                                            <div class="icon bg-AccentLight">
                                                                <span class="iconify" data-icon="mdi:home-modern" style="color: var(--color-accent);"></span>
                                                            </div>
                                                            <div class="text">
                                                                <h6 class="text-Black" id="infra-completed">0</h6>
                                                                <p class="f12-regular text-Gray">Completed Structures</p>
                                                            </div>
                                                            </div>
                                                        </div>

                                                        <!-- Next Inspection -->
                                                        <div class="col-md-3 col-6">
                                                            <div class="impact-card">
                                                            <div class="icon bg-YellowLight">
                                                                <span class="iconify" data-icon="mdi:calendar-check" style="color: #d4a017;"></span>
                                                            </div>
                                                            <div class="text">
                                                                <h6 class="text-Black" id="infra-next">—</h6>
                                                                <p class="f12-regular text-Gray">Next Inspection Date</p>
                                                            </div>
                                                            </div>
                                                        </div>

                                                        </div>
                                                    </div>
                                                    </div>
                                                </div>
                                                </div>

                                    <!-- Infrastructure Plans Grid -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box infrastructure-plans">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01 text-Primary">Infrastructure Pay-Off Plans</div>
                                                </div>

                                                <div class="row g-4" id="infra-plans-grid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Start Infrastructure Section -->
                                    <div class="row mb-32">
                                        <!-- Left: Start Form -->
                                        <div class="col-lg-8 col-md-12">
                                            <div class="wg-box infrastructure-form">
                                                <div class="title mb-16">
                                                    <div class="label-01 text-Primary">Start Your Infrastructure Investment</div>
                                                </div>

                                                <div class="content">
                                                    <form class="form-style-1" id="infrastructure-form">
                                                        <!-- Select Plan -->
                                                        <div class="mb-20">
                                                            <label class="f14-regular text-Black mb-8">Select Plan</label>
                                                            <select class="form-select custom-select" id="plan-select">
                                                                <option value="">Select a Plan</option>
                                                            </select>
                                                        </div>

                                                        <!-- Investment Amount -->
                                                        <div class="mb-20 position-relative">
                                                            <label class="f14-regular text-Black mb-8">Amount to Invest (USD)</label>
                                                            <div class="input-group">
                                                                <span class="input-icon">
                                                                    <span class="iconify" data-icon="mdi:office-building-outline"></span>
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
                                                                <label class="f14-regular text-Black mb-8">Pay-Off Period</label>
                                                                <input class="f12-regular text-Gray p-12 border border-Gray rounded" type="text" id="payoff-period" readonly>
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
                                                            Invest in Infrastructure
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right: Benefits -->
                                        <div class="col-lg-4 col-md-12">
                                            <div class="wg-box infrastructure-benefits">
                                                <div class="title mb-16">
                                                    <div class="label-01 text-Primary">Why Choose Infrastructure Pay-Off Plans</div>
                                                </div>

                                                <div class="content">
                                                    <ul class="f12-regular text-Gray">
                                                        <li class="mb-8 flex items-center">
                                                            Guaranteed Full Repayment
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Asset-Backed Security
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Short to Mid-Term Duration
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Social Impact + Profit
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Professional Oversight
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- My Infrastructure Plans Table -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box infrastructure-plans-table">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01 text-Primary">My Infrastructure Plans</div>
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
                                                            <div class="btn-key-sort"><div class="f12-bold">Pay-Off Period</div></div>
                                                            <div class="btn-key-sort"><div class="f12-bold">Repayment Mode</div></div>
                                                            <div class="btn-key-sort"><div class="f12-bold">Status</div></div>
                                                            <div class="btn-key-sort"><div class="f12-bold">Start Date</div></div>
                                                        </div>

                                                        <table class="list-transaction-content content-sort w-100">
                                                            <tbody id="active-infra-tbody">
                                                            </tbody>
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
                                                    <tbody id="matured-infra-tbody">
                                                    </tbody>
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
    <script src="../../assets/js/api.js" defer></script>
    <script src="../../assets/js/infrastructure.js" defer></script>
    <script src="../../assets/js/countto.js" defer></script>
    <script src="../../assets/js/bootstrap-select.min.js" defer></script>
    <script src="../../assets/js/dashboard.js" defer></script>
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>

</body>
</html>






