<?php
// pages/user/investments.php

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
        'title' => 'Healthy Future Bond Plan',
        'description' => 'Build Community Diagnostic Centers',
        'details' => 'Supporting local health screenings and medical supplies for underserved communities.',
        'range' => '$500 – $100,000',
        'term' => '18 months',
        'roi' => '10–12%',
        'risk' => 'Low',
        'income' => 'Fees from diagnostic tests and partnerships with hospitals & insurance providers',
        'payout' => 'Quarterly or at maturity',
        'summary' => 'Your money helps build diagnostic centers that earn from medical tests. As these centers generate consistent service income, you earn up to 12% in 18 months — safely and with social impact.',
        'icon' => 'mdi:factory',
        'color' => 'Green'
    ],
    [
        'id' => 2,
        'title' => 'Wellness Growth Real Estate Plan',
        'description' => 'Build and Lease Wellness & Rehabilitation Facilities',
        'details' => 'Your investment funds the construction of modern wellness centers leased to physiotherapy clinics, fitness brands, and recovery operators.',
        'range' => '$5,000 – $250,000',
        'term' => '2 years',
        'roi' => '15–18%',
        'risk' => 'Moderate',
        'income' => 'Rental income from wellness centers and long-term lease agreements',
        'payout' => 'Bi-annual or lump sum at maturity',
        'summary' => 'You help build wellness facilities that lease to health operators. Rental payments provide steady income that\'s shared with you as up to 18% return in 2 years.',
        'icon' => 'mdi:home-building',
        'color' => 'Blue'
    ],
    [
        'id' => 3,
        'title' => 'Health Innovation Venture Fund',
        'description' => 'Support High-Growth Health-Tech Startups',
        'details' => 'Your capital helps scale innovative startups working on medical devices, biotech research, and digital health technologies.',
        'range' => '$10,000 – $500,000',
        'term' => '3 years',
        'roi' => '25–35%',
        'risk' => 'High',
        'income' => 'Equity profit from startup growth, technology licensing, and company buyouts',
        'payout' => 'At maturity (end of term)',
        'summary' => 'You back new health-tech companies. When they grow or get acquired, you share in their success — earning up to 35% within 3 years.',
        'icon' => 'mdi:lightning-bolt',
        'color' => 'Orange'
    ],
    [
        'id' => 4,
        'title' => 'Community Health Microfinance Plan',
        'description' => 'Empower Small Health Businesses',
        'details' => 'This plan provides microloans to rural pharmacies, small clinics, and health workers who repay with fair interest.',
        'range' => '$300 – $20,000',
        'term' => '12 months',
        'roi' => '8–10%',
        'risk' => 'Low',
        'income' => 'Loan interest payments from local health entrepreneurs',
        'payout' => 'At maturity (end of 12 months)',
        'summary' => 'Your investment gives small loans to trusted healthcare providers. They repay with interest, and you earn up to 10% in just one year — while supporting community care.',
        'icon' => 'mdi:hand-extend',
        'color' => 'Green'
    ],
    [
        'id' => 5,
        'title' => 'Green Hospital Infrastructure Plan',
        'description' => 'Finance Eco-Friendly Hospital Upgrades',
        'details' => 'Your investment enables hospitals to install solar systems, energy-saving equipment, and water recycling units.',
        'range' => '$2,000 – $200,000',
        'term' => '2 years',
        'roi' => '14–16%',
        'risk' => 'Moderate',
        'income' => 'Revenue-sharing from hospitals\' reduced energy costs and green subsidies',
        'payout' => 'Annual or at maturity',
        'summary' => 'Hospitals save thousands on electricity and maintenance after green upgrades. Part of those savings is paid back to investors — giving you up to 16% return in 2 years.',
        'icon' => 'mdi:leaf',
        'color' => 'Blue'
    ],
    [
        'id' => 6,
        'title' => 'Healthy Food Systems Plan',
        'description' => 'Strengthen Nutrition and Food Security',
        'details' => 'This plan funds farm-to-health programs and healthy meal suppliers for hospitals, schools, and wellness institutions.',
        'range' => '$1,000 – $50,000',
        'term' => '18 months',
        'roi' => '12–15%',
        'risk' => 'Moderate',
        'income' => 'Profits from produce sales, supply contracts, and wholesale distribution partnerships',
        'payout' => 'Quarterly or at maturity',
        'summary' => 'Your money supports healthy food producers who sell to hospitals and schools. As they make profits, you earn up to 15% in 18 months.',
        'icon' => 'mdi:food',
        'color' => 'Blue'
    ],
    [
        'id' => 7,
        'title' => 'Digital Health Access Plan',
        'description' => 'Expand Online Health Platforms & Telemedicine',
        'details' => 'Invest in digital platforms offering remote doctor consultations, e-prescriptions, and mobile diagnostics.',
        'range' => '$2,000 – $100,000',
        'term' => '2 years',
        'roi' => '18–22%',
        'risk' => 'Moderate to High',
        'income' => 'Subscription fees, teleconsultation charges, data partnerships, and health service commissions',
        'payout' => 'Annual or at maturity',
        'summary' => 'You invest in the future of digital healthcare. As more users join and pay for services online, you earn up to 22% return in 2 years — while helping expand access to doctors worldwide.',
        'icon' => 'mdi:phone',
        'color' => 'Orange'
    ]
];

// Placeholder data for active investments
$activeInvestments = [
    [
        'plan' => 'Healthy Future Bond Plan',
        'amount' => 1200.00,
        'roi' => '12%',
        'term' => '18 months',
        'status' => 'Active',
        'date' => 'Oct 15, 2025'
    ],
    [
        'plan' => 'Wellness Growth Real Estate Plan',
        'amount' => 5000.00,
        'roi' => '16%',
        'term' => '2 years',
        'status' => 'Active',
        'date' => 'Sep 20, 2025'
    ],
    [
        'plan' => 'Community Health Microfinance Plan',
        'amount' => 1500.00,
        'roi' => '9%',
        'term' => '12 months',
        'status' => 'Completed',
        'date' => 'Aug 10, 2025'
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="description" content="HealthRunCare Investments - Grow your wealth while supporting healthcare initiatives.">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://healthruncare.com/investments">
    <title>HealthRunCare Investments</title>

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
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    </noscript>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="HRC">
    <link rel="manifest" href="/assets/favicon/site.webmanifest">
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
                                    <li class="menu-item active">
                                        <a href="/dashboard.investment" class="menu-item-button active">
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
                                <h6>Investments</h6>
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
                                                <div class="label-01 text-Primary">Investment Summary</div>
                                                <span class="f12-regular text-Gray">Your portfolio at a glance</span>
                                            </div>

                                            <div class="content">
                                                <div class="row g-3">

                                                <!-- Active Investments -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-PrimaryLight">
                                                        <span class="iconify" data-icon="mdi:chart-line" style="color: var(--color-primary);"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 id="card-active-investments" class="text-Primary">$0.00</h6>
                                                        <p class="f12-regular text-Gray">Active Investments</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                <!-- Total ROI -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-GreenLight">
                                                        <span class="iconify" data-icon="mdi:cash-multiple" style="color: var(--Green);"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 id="card-total-roi" class="text-Green">$0.00</h6>
                                                        <p class="f12-regular text-Gray">Total ROI Earned</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                <!-- Ongoing Plans -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-AccentLight">
                                                        <span class="iconify" data-icon="mdi:chart-pie" style="color: var(--color-accent);"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 id="card-ongoing-plans" class="text-Black">0</h6>
                                                        <p class="f12-regular text-Gray">Ongoing Plans</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                <!-- Next Maturity -->
                                                <div class="col-md-3 col-6">
                                                    <div class="impact-card">
                                                    <div class="icon bg-YellowLight">
                                                        <span class="iconify" data-icon="mdi:calendar-clock" style="color: #d4a017;"></span>
                                                    </div>
                                                    <div class="text">
                                                        <h6 id="card-next-maturity" class="text-Black">—</h6>
                                                        <p class="f12-regular text-Gray">Next Maturity Date</p>
                                                    </div>
                                                    </div>
                                                </div>

                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>

                                    <!-- Investment Plans Grid -->
                                    <div class="row mb-32">
                                                <div class="col-12">
                                                    <div class="wg-box investment-plans">
                                                    <div class="title mb-16 flex justify-between items-center">
                                                        <div class="label-01 text-Primary">Available Investment Plans</div>
                                                    </div>

                                                    <div class="content">
                                                        <div class="row g-4">
                                                        <?php foreach ($plans as $plan): ?>
                                                        <div class="col-lg-3 col-md-6">
                                                            <div class="plan-card">
                                                            <div class="plan-header flex justify-between items-center mb-12">
                                                                <div class="flex items-center gap-2">
                                                                <h6 class="plan-title"><?php echo htmlspecialchars($plan['title']); ?></h6>
                                                                </div>
                                                            </div>

                                                            <p class="f12-regular text-Gray mb-12"><?php echo htmlspecialchars($plan['description']); ?></p>
                                                            <p class="f12-regular text-Black mb-16"><?php echo htmlspecialchars($plan['details']); ?></p>

                                                            <table class="plan-features">
                                                                <tr>
                                                                <td>Investment Range</td>
                                                                <td><?php echo $plan['range']; ?></td>
                                                                </tr>
                                                                <tr>
                                                                <td>Term</td>
                                                                <td><?php echo $plan['term']; ?></td>
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
                                                                <td>Income Source</td>
                                                                <td><?php echo $plan['income']; ?></td>
                                                                </tr>
                                                                <tr>
                                                                <td>Payout Option</td>
                                                                <td><?php echo $plan['payout']; ?></td>
                                                                </tr>
                                                            </table>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                    </div>
                                                </div>
                                                </div>


                                    <!-- Investment Section -->
                                    <div class="row mb-32">
                                        <div class="col-lg-8 col-md-12">
                                                        <div class="wg-box investment-form">
                                                            <div class="title mb-16">
                                                            <div class="label-01">Start Your Investment</div>
                                                            </div>

                                                            <div class="content">
                                                            <form class="form-style-1" id="investment-form">
                                                                <div class="mb-20">
                                                                <label class="f14-regular text-Black mb-8">Select Plan</label>
                                                                <select class="form-select custom-select" id="plan-select" onchange="updatePlanDetails()">
                                                                    <option selected disabled>Select a Plan</option>
                                                                    <?php foreach ($plans as $plan): ?>
                                                                    <option value="<?php echo $plan['id']; ?>"
                                                                            data-term="<?php echo $plan['term']; ?>"
                                                                            data-roi="<?php echo $plan['roi']; ?>">
                                                                        <?php echo htmlspecialchars($plan['title']); ?>
                                                                    </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                </div>

                                                                <div class="mb-20 position-relative">
                                                                <label class="f14-regular text-Black mb-8">Investment Amount (USD)</label>
                                                                <div class="input-group">
                                                                    <span class="input-icon">$</span>
                                                                    <input class="wallet-input form-control" type="number" placeholder="Enter amount" min="1" id="investment-amount">
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

                                                                <div class="row mb-20">
                                                                <div class="col-md-6">
                                                                    <label class="f14-regular text-Black mb-8">Term Duration</label>
                                                                    <input class="form-control readonly-input" type="text" id="term-duration" readonly>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="f14-regular text-Black mb-8">Expected ROI</label>
                                                                    <input class="form-control readonly-input" type="text" id="expected-roi" readonly>
                                                                </div>
                                                                </div>

                                                                <button type="submit" class="tf-button style-default w-full f14-bold bg-Green text-White hover:bg-Primary transition-colors duration-300" id="invest-btn" disabled>
                                                                Invest Now
                                                                </button>
                                                            </form>
                                                            </div>
                                                        </div>
                                                        </div>

                                        <div class="col-lg-4 col-md-12">
                                            <div class="wg-box">
                                                <div class="title mb-16">
                                                    <div class="label-01">Investment Tips</div>
                                                </div>
                                                <div class="content">
                                                    <ul class="f12-regular text-Gray">
                                                        <li class="mb-8 flex items-center">
                                                            Start small with low-risk plans
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Diversify across different sectors
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Monitor your portfolio regularly
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Reinvest earnings for compound growth
                                                        </li>
                                                        <li class="mb-8 flex items-center">
                                                            Contact support for personalized advice
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Active Investments Table -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box active-investments">
                                            <div class="title mb-16 flex justify-between items-center">
                                                <div class="label-01 text-Primary">Active Investments</div>
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
                                                    <div class="btn-key-sort"><div class="f12-bold">Status</div></div>
                                                    <div class="btn-key-sort"><div class="f12-bold">Date Started</div></div>
                                                </div>

                                                <table class="list-transaction-content content-sort w-100">
                                                    <tbody>
                                                    <?php foreach ($activeInvestments as $investment): ?>
                                                    <tr class="tf-table-item">
                                                        <td data-label="Plan Name">
                                                        <div class="f12-medium key-sort"><?php echo htmlspecialchars($investment['plan']); ?></div>
                                                        </td>
                                                        <td data-label="Amount Invested">
                                                        <div class="f12-bold key-sort">$<?php echo number_format($investment['amount'], 2); ?></div>
                                                        </td>
                                                        <td data-label="ROI (%)">
                                                        <div class="f12-bold text-Green key-sort"><?php echo $investment['roi']; ?></div>
                                                        </td>
                                                        <td data-label="Term Duration">
                                                        <div class="f12-medium key-sort"><?php echo $investment['term']; ?></div>
                                                        </td>
                                                        <td data-label="Status">
                                                        <div class="box-status bg-Green">
                                                            <span class="font-poppins key-sort"><?php echo $investment['status']; ?></span>
                                                        </div>
                                                        </td>
                                                        <td data-label="Date Started">
                                                        <div class="f12-medium key-sort"><?php echo $investment['date']; ?></div>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
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
        <?php if (empty($maturePlans)): ?>
          <div class="text-center py-20">
            <p class="f14-regular text-Gray">No mature plans available for unlock at this time.</p>
          </div>
        <?php else: ?>
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
              <tbody>
                <?php foreach ($maturePlans as $mature): ?>
                <tr class="tf-table-item">
                  <td data-label="Plan Name">
                    <div class="f12-medium key-sort"><?php echo htmlspecialchars($mature['plan']); ?></div>
                  </td>
                  <td data-label="Original Amount">
                    <div class="f12-bold key-sort">$<?php echo number_format($mature['amount'], 2); ?></div>
                  </td>
                  <td data-label="ROI Earned">
                    <div class="f12-bold text-Green key-sort"><?php echo $mature['roi_earned']; ?></div>
                  </td>
                  <td data-label="Maturity Date">
                    <div class="f12-medium key-sort"><?php echo $mature['maturity_date']; ?></div>
                  </td>
                  <td data-label="Total Payout">
                    <div class="f12-bold key-sort">$<?php echo number_format($mature['total_payout'], 2); ?></div>
                  </td>
                  <td data-label="Actions">
                    <button class="unlock-btn bg-Green text-White px-4 py-2 rounded f12-regular hover:bg-Primary transition-colors" onclick="initiateUnlock(<?php echo $mature['id']; ?>)">
                      Unlock Now
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
        <?php endif; ?>
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
<script src="../../assets/js/investment.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/countto.js" defer></script>
<script src="../../assets/js/bootstrap-select.min.js" defer></script>
<script src="../../assets/js/dashboard.js" defer></script>

    <!-- Iconify CDN -->
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.selectpicker').selectpicker();
        });

        let selectedPlanId = null;
        const plans = <?php echo json_encode($plans); ?>;

        function selectPlan(id) {
            selectedPlanId = id;
            const plan = plans.find(p => p.id === id);
            if (plan) {
                $('#plan-select').val(id).trigger('change');
            }
        }

        function updatePlanDetails() {
            const selectedOption = $('#plan-select option:selected');
            const id = parseInt(selectedOption.val());
            const plan = plans.find(p => p.id === id);
            if (plan) {
                $('#term-duration').val(plan.term);
                $('#expected-roi').val(plan.roi);
                $('#invest-btn').prop('disabled', false);
            } else {
                $('#term-duration').val('');
                $('#expected-roi').val('');
                $('#invest-btn').prop('disabled', true);
            }
        }
    </script>
</body>
</html>