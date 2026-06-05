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

// Live X-Grid infrastructure co-investment deals
$plans = [
    [
        'id' => 1,
        'name' => 'UK Solar Portfolio I',
        'purpose' => 'Co-investment slot in a portfolio of operational utility-scale solar farms across the UK with index-linked PPA revenue.',
        'min_deposit' => '$10,000',
        'payoff_period' => '1 year',
        'roi' => '8–10%',
        'risk' => 'Very Low',
        'repayment' => 'Quarterly distributions over 12 months',
        'summary' => 'Stake in a pool of operational solar assets selling power under long-term PPA contracts. Distributions paid quarterly from PPA revenue; full principal returned at term.',
        'color' => 'Green'
    ],
    [
        'id' => 2,
        'name' => 'Logistics Warehousing Fund',
        'purpose' => 'Co-investment in last-mile logistics warehousing across the UK and EU, leased to investment-grade tenants.',
        'min_deposit' => '$20,000',
        'payoff_period' => '18 months',
        'roi' => '12–15%',
        'risk' => 'Low',
        'repayment' => 'Quarterly or semi-annual',
        'summary' => 'Income comes from long-lease tenants in core last-mile sites. Quarterly distributions; capital returned on disposal or refinancing event within 18 months.',
        'color' => 'Green'
    ],
    [
        'id' => 3,
        'name' => 'Tier-III Data Centre Slot',
        'purpose' => 'Slot in a Tier-III data-centre operator with anchor hyperscaler tenant and contracted power offtake.',
        'min_deposit' => '$50,000',
        'payoff_period' => '2 years',
        'roi' => '15–20%',
        'risk' => 'Moderate',
        'repayment' => 'Monthly or quarterly distributions',
        'summary' => 'Revenue from a Tier-III data-centre operator with a hyperscaler anchor tenant. Monthly or quarterly distributions; principal returned at exit window.',
        'color' => 'Blue'
    ],
    [
        'id' => 4,
        'name' => 'Wind Repower & Battery Co-Invest',
        'purpose' => 'Repowering of mid-life onshore wind assets with co-located battery storage for arbitrage revenue.',
        'min_deposit' => '$100,000',
        'payoff_period' => '2.5 years',
        'roi' => '18–22%',
        'risk' => 'Moderate',
        'repayment' => 'Quarterly distributions with inflation-linked escalator',
        'summary' => 'Combined wind generation and battery arbitrage revenue stream. Quarterly distributions with an inflation-linked escalator clause; principal at term.',
        'color' => 'Blue'
    ],
    [
        'id' => 5,
        'name' => 'Fibre-to-the-Premises Roll-Out',
        'purpose' => 'Senior co-investment in a regional FTTP build-out with contracted ISP wholesale revenue.',
        'min_deposit' => '$150,000',
        'payoff_period' => '3 years',
        'roi' => '20–25%',
        'risk' => 'Moderate',
        'repayment' => 'Monthly or quarterly with optional early redemption',
        'summary' => 'Senior position in a regional FTTP build-out with contracted wholesale revenue from ISPs. Monthly or quarterly distributions; optional early redemption windows.',
        'color' => 'Blue'
    ],
    [
        'id' => 6,
        'name' => 'Institutional Infrastructure Slot',
        'purpose' => 'Higher-minimum slot for portfolios — diversified across solar, logistics, data centres, and fibre with quarterly performance packs.',
        'min_deposit' => '$500,000',
        'payoff_period' => '3 years',
        'roi' => '28–30%',
        'risk' => 'Moderate-Low',
        'repayment' => 'Quarterly or bi-annual distributions',
        'summary' => 'Higher-minimum allocation across the four core sectors above, with consolidated quarterly performance pack and a single end-of-term capital event.',
        'color' => 'Green'
    ]
];

// Current user's X-Grid positions
$infraPlans = [
    [
        'plan' => 'UK Solar Portfolio I',
        'amount' => 15000.00,
        'roi' => '9%',
        'payoff_period' => '1 year',
        'repayment_mode' => 'Quarterly',
        'status' => 'Active',
        'start_date' => 'Sep 1, 2025'
    ],
    [
        'plan' => 'Logistics Warehousing Fund',
        'amount' => 25000.00,
        'roi' => '14%',
        'payoff_period' => '18 months',
        'repayment_mode' => 'Quarterly',
        'status' => 'Active',
        'start_date' => 'Aug 15, 2025'
    ]
];
?>
<?php
  $page_title = "X-Grid | TitanXHoldings";
  include __DIR__ . "/_partials/head.php";
?>

<body class="counter-scroll txh-dash">
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
                <?php $active = "xgrid"; include __DIR__ . "/_partials/sidebar.php"; ?>
                <!-- section-content-right -->
                <div class="section-content-right">
                    <!-- header-dashboard -->
                    <?php $page_heading = "X-Grid"; include __DIR__ . "/_partials/topbar.php"; ?>
                    <!-- main-content -->
                    <div class="main-content">
                        <!-- main-content-wrap -->
                        <div class="main-content-inner">
                            <!-- main-content-wrap -->
                            <div class="main-content-wrap">
                                <div class="tf-container">

                                <!-- ============================================================
                                     PORTFOLIO HERO (relocated summary metrics)
                                     ============================================================ -->
                                <div class="row mb-32">
                                  <div class="col-12 mb-24">
                                    <div class="wallet-card wallet-main wallet-hero">
                                      <div class="wallet-hero-top">
                                        <div class="title-box flex items-center gap-2">
                                          <span class="iconify" data-icon="mdi:office-building-outline"></span>
                                          <span class="f12-medium text-White">Total Funded (USD)</span>
                                        </div>
                                        <span class="box-status bg-Green f12-medium flex items-center gap-2">
                                          <span class="iconify" data-icon="mdi:shield-check"></span> Active
                                        </span>
                                      </div>
                                      <div class="wallet-hero-balance">
                                        <h2 class="counter text-White" id="infra-funded">$0.00</h2>
                                        <div class="wallet-hero-change f14-regular">
                                          <span class="iconify" data-icon="mdi:office-building-marker-outline"></span>
                                          <span id="infra-completed">0</span>&nbsp;completed structures
                                        </div>
                                      </div>
                                      <div class="wallet-hero-substats">
                                        <div class="wallet-substat">
                                          <div class="f12-regular">Active projects</div>
                                          <div class="f14-bold text-White" id="infra-active">0</div>
                                        </div>
                                        <div class="wallet-substat">
                                          <div class="f12-regular">Next inspection</div>
                                          <div class="f14-bold text-White" id="infra-next">&mdash;</div>
                                        </div>
                                      </div>
                                      <div class="wallet-hero-actions">
                                        <a href="/dashboard.transactions" class="tf-button bg-Accent f14-bold">
                                          <span class="iconify" data-icon="mdi:history"></span> Transactions
                                        </a>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                                    <!-- Start X-Grid Section -->
                                    <div class="row mb-32">
                                        <!-- Left: Start Form -->
                                        <div class="col-lg-7 col-md-12">
                                            <div class="wg-box infrastructure-form">
                                                <div class="title mb-16">
                                                    <div class="label-01 text-Primary">Start Your X-Grid X-Yield</div>
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

                                                        <!-- X-Yield Amount -->
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
                                                            Invest in X-Grid
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right: Plan details -->
                                        <div class="col-lg-5 col-md-12">
                                            <div class="wg-box plan-detail-panel">
                                                <div class="pdp-empty" id="pdp-empty">
                                                    <span class="iconify" data-icon="mdi:gesture-tap-button" data-width="32" data-height="32"></span>
                                                    <div class="f14-bold text-Primary">Select a plan</div>
                                                    <div class="f12-regular text-Gray">Choose a plan from the dropdown to see its full details.</div>
                                                </div>
                                                <div id="pdp-content" class="hidden">
                                                    <div class="pdp-head">
                                                        <div class="pdp-name" id="pdp-name">—</div>
                                                        <span class="pdp-badge" id="pdp-risk" style="display:none;"></span>
                                                    </div>
                                                    <div class="pdp-roi" id="pdp-roi">—</div>
                                                    <div class="pdp-roi-label" id="pdp-roi-label">Expected ROI</div>
                                                    <ul class="pdp-meta" id="pdp-meta"></ul>
                                                    <p class="pdp-summary" id="pdp-summary"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- My X-Grid Plans Table -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box infrastructure-plans-table">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01 text-Primary">My X-Grid Plans</div>
                                                    <div class="view-all">
                                                        <a href="#" class="f12-regular text-Primary hover:underline flex items-center">
                                                            View All
                                                            <span class="iconify ml-2" data-icon="mdi:chevron-right"></span>
                                                        </a>
                                                    </div>
                                                </div>

                                                <div class="content">
                                                    <div class="txh-scroll-table mt-3">
                                                        <table class="txh-table">
                                                            <thead><tr>
                                                                <th>Plan Name</th>
                                                                <th>Amount Invested</th>
                                                                <th>ROI (%)</th>
                                                                <th>Pay-Off Period</th>
                                                                <th>Repayment Mode</th>
                                                                <th>Status</th>
                                                                <th>Start Date</th>
                                                            </tr></thead>
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
                                            <div class="txh-scroll-table mt-3">
                                                <table class="txh-table">
                                                    <thead><tr>
                                                        <th>Plan Name</th>
                                                        <th>Original Amount</th>
                                                        <th>ROI Earned</th>
                                                        <th>Maturity Date</th>
                                                        <th>Total Payout</th>
                                                        <th>Actions</th>
                                                    </tr></thead>
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

    <script src="<?= txh_asset('../../assets/js/jquery.min.js') ?>"></script>
    <script src="<?= txh_asset('../../assets/js/bootstrap.min.js') ?>"></script>
    <script src="<?= txh_asset('../../assets/js/api.js') ?>" defer></script>
    <script src="<?= txh_asset('../../assets/js/infrastructure.js') ?>" defer></script>
    <script src="<?= txh_asset('../../assets/js/countto.js') ?>" defer></script>
    <script src="<?= txh_asset('../../assets/js/bootstrap-select.min.js') ?>" defer></script>
    <script src="<?= txh_asset('../../assets/js/dashboard.js') ?>" defer></script>
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>

</body>
</html>






