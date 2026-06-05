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
<?php
  $page_title = "X-Yield | TitanXHoldings";
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
                <?php $active = "investment"; include __DIR__ . "/_partials/sidebar.php"; ?>
                <!-- section-content-right -->
                <div class="section-content-right">
                    <!-- header-dashboard -->
                    <?php $page_heading = "X-Yield"; include __DIR__ . "/_partials/topbar.php"; ?>
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
                                              <span class="iconify" data-icon="mdi:chart-timeline-variant"></span>
                                              <span class="f12-medium text-White">Active X-Yields (USD)</span>
                                            </div>
                                            <span class="box-status bg-Green f12-medium flex items-center gap-2">
                                              <span class="iconify" data-icon="mdi:shield-check"></span> Active
                                            </span>
                                          </div>
                                          <div class="wallet-hero-balance">
                                            <h2 class="counter text-White" id="card-active-investments">$0.00</h2>
                                            <div class="wallet-hero-change f14-regular">
                                              <span class="iconify" data-icon="mdi:trending-up"></span>
                                              <span id="card-total-roi">$0.00</span>&nbsp;ROI earned to date
                                            </div>
                                          </div>
                                          <div class="wallet-hero-substats">
                                            <div class="wallet-substat">
                                              <div class="f12-regular">Ongoing plans</div>
                                              <div class="f14-bold text-White" id="card-ongoing-plans">0</div>
                                            </div>
                                            <div class="wallet-substat">
                                              <div class="f12-regular">Next maturity</div>
                                              <div class="f14-bold text-White" id="card-next-maturity">&mdash;</div>
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

                                    <!-- X-Yield Section -->
                                    <div class="row mb-32">
                                        <div class="col-lg-7 col-md-12">
                                                        <div class="wg-box investment-form">
                                                            <div class="title mb-16">
                                                            <div class="label-01">Start Your X-Yield</div>
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
                                                                <label class="f14-regular text-Black mb-8">X-Yield Amount (USD)</label>
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

                                    <!-- Active X-Yields Table -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box active-investments">
                                            <div class="title mb-16 flex justify-between items-center">
                                                <div class="label-01 text-Primary">Active X-Yields</div>
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
                                                        <th>Term Duration</th>
                                                        <th>Status</th>
                                                        <th>Date Started</th>
                                                    </tr></thead>
                                                    <tbody id="active-investments-table-body">
                                                        <!-- JS (loadActiveX-Yields) will populate rows here -->
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
            <tbody>
              <!-- JS (loadMaturedXYields) will populate rows here -->
            </tbody>
          </table>
        </div>
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
<script src="<?= txh_asset('../../assets/js/api.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/investment.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/bootstrap.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/countto.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/bootstrap-select.min.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/dashboard.js') ?>" defer></script>

    <!-- Iconify CDN -->
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.selectpicker').selectpicker();
        });
    </script>
</body>
</html>