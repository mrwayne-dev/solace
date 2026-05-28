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
                                                        <div id="plans-grid" class="row g-4">
                                                            <!-- JS will populate available investment plans here -->
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
                                                                <select class="form-select custom-select" id="plan-select">
                                                                    <option value="">Select a Plan</option>
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
                                                        <!-- JS (loadActiveInvestments) will populate rows here -->
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
                    <!-- JS (loadMaturedInvestments) will populate rows here -->
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