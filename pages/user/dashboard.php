<?php
// pages/user/dashboard.php
session_start([
    'cookie_lifetime' => 86400, // Example: 24 hours
    'cookie_httponly' => true,
    'cookie_secure' => true, 
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
  $page_title = "Dashboard | Solace Mining";
  include __DIR__ . "/_partials/head.php";
?>
<body class="counter-scroll txh-dash">
    <!-- #wrapper -->
    <div id="wrapper">
        <!-- #page -->
        <div id="page" class="">
            <!-- layout-wrap -->
            <div class="layout-wrap loader-off">
                <!-- preload -->=
                <div id="preload" class="preload-container">
                    <div class="preloading">
                        <span></span>
                    </div>
                </div>
                <!-- /preload -->
                <!-- section-menu-left -->
                 <!-- testing my commit -->
                <?php $active = "dashboard"; include __DIR__ . "/_partials/sidebar.php"; ?>
                <!-- section-content-right -->
                <div class="section-content-right">
                    <!-- header-dashboard -->
                    <?php $page_heading = "Dashboard"; include __DIR__ . "/_partials/topbar.php"; ?>
                    <!-- main-content -->
                    <div class="main-content">
                        <!-- main-content-wrap -->
                        <div class="main-content-inner">
                            <!-- main-content-wrap -->
                            <div class="main-content-wrap">
                                <div class="tf-container">
                                    
                                    <div class="row">
                                        <!-- ============================= -->
                                        <!-- BIG WALLET CARDS OVERVIEW SECTION (Full Width, Bigger Cards) -->
                                        <!-- ============================= -->
                                   <div class="col-12 mb-40">
  <div class="wallet-overview">
    <div class="section-header flex justify-between items-center mb-16">
      <h6 class="label-01">Wallet Overview</h6>
      <a href="#" class="f14-regular flex items-center gap8 text-Primary" onclick="refreshDashboard()">
        <i class="iconify ph ph-arrows-clockwise"></i> Refresh Balances
      </a>
    </div>

    <div class="wallet-cards grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap20">

      <!-- Main Wallet -->
      <div class="wallet-card wallet-main">
        <div class="wallet-card-header">Main Wallet</div>
        <div class="wallet-card-balance">$<span id="total-balance">0.00</span></div>
        <div class="wallet-card-footer">
          SLM-MAIN-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?>
        </div>
      </div>

      <!-- Total Earnings -->
      <div class="wallet-card wallet-green">
        <div class="wallet-card-header">Total Earnings</div>
        <div class="wallet-card-balance">$<span id="total-earnings">0.00</span></div>
        <div class="wallet-card-footer">
          SLM-ERN-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?>
        </div>
      </div>

      <!-- Active Contracts -->
      <div class="wallet-card wallet-investments">
        <div class="wallet-card-header">Active Contracts</div>
        <div class="wallet-card-balance">$<span id="total-investments">0.00</span></div>
        <div class="wallet-card-footer">
          SLM-INV-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?>
        </div>
      </div>

      <!-- Referral Earnings -->
      <div class="wallet-card wallet-holdlock">
        <div class="wallet-card-header">Referral Earnings</div>
        <div class="wallet-card-balance">$<span id="referral-earnings">0.00</span></div>
        <div class="wallet-card-footer">
          SLM-REF-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?>
        </div>
      </div>

    </div>
  </div>
</div>



<!-- ============================= -->
<!-- CARD DETAILS SECTION (CLEAN VERSION) -->
<!-- ============================= -->
<div class="col-12 mb-32">
  <div class="wg-box card-details mb-32">
    <div class="title flex justify-between items-center">
      <h6 class="label-01">Card Details</h6>
    </div>

    <hr class="divider mb-24">

    <div class="card-details-grid">
      <!-- Left: Card Info -->
      <div class="card-info-panel">
        <ul class="card-info-list">
          <li>
            <span>Card Name</span>
            <strong id="card-name">Main Wallet</strong>
          </li>
          <li>
            <span>Valid Date</span>
            <strong id="card-valid">08/26</strong>
          </li>
          <li>
            <span>SLM ID</span>
            <strong id="card-id">SLM-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?>-9011-3298</strong>
            </li>
          <li>
            <span>Card Holder</span>
            <strong id="card-holder"><?= $user_name ?></strong>
          </li>
          <li>
            <span>Bank Name</span>
            <strong id="card-bank">Solace Mining Bank</strong>
          </li>
        </ul>
      </div>

      <!-- Right: Chart Only -->
      <div class="card-chart-panel text-center">
        <canvas id="cardUsageChart" width="200" height="200"></canvas>
        <ul class="chart-legend flex justify-center gap16 mt-12 flex-wrap">
          <li class="flex items-center gap6">
            <div class="dot bg-Primary"></div> <span>Balance</span> <strong>0%</strong>
          </li>
          <li class="flex items-center gap6">
            <div class="dot bg-Accent"></div> <span>Contracts</span> <strong>0%</strong>
          </li>
          <li class="flex items-center gap6">
            <div class="dot bg-Gainsboro"></div> <span>Referral</span> <strong>0%</strong>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>



                                        <!-- ============================= -->
                                        <!-- LATEST UPDATES AND RECENT ACTIVITY (Side by Side) -->
                                        <!-- ============================= -->
                                        <div class="row">
                                            <div class="col-lg-6 mb-32">
                                                <!-- Latest Updates Section -->
                                                <div class="wg-box style-1 bg-Primary shadow-none mb-32">
                                                    <div>
                                                        <div class="title mb-10">
                                                            <div class="label-01 text-White">Latest Updates</div>
                                                        </div>
                                                        <div class="updates-list text-White">
                                                            <div class="update-item flex gap16 items-start mb-20">
                                                                <div class="update-content">
                                                                    <div class="f14-bold">New mining capacity online</div>
                                                                    <div class="f12-regular text-Gainsboro">Additional hashpower has been added to our rigs — higher tiers now have priority allocation.</div>
                                                                    <div class="f12-regular text-LightGray mt-4">3 days ago</div>
                                                                </div>
                                                            </div>
                                                            <div class="update-item flex gap16 items-start mb-0">
                                                                <div class="update-content">
                                                                    <div class="f14-bold">Refer &amp; earn 10%</div>
                                                                    <div class="f12-regular text-Gainsboro">Earn a 10% commission every time someone you refer starts a mining contract.</div>
                                                                    <div class="f12-regular text-LightGray mt-4">5 days ago</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /Latest Updates Section -->
                                            </div>
                                            <div class="col-lg-6 mb-32">
                                                <div class="wg-box gap16">
                                                    <div>
                                                        <div class="title mb-12">
                                                            <div class="label-01">Recent Activity</div>
                                                        </div>
                                                    </div>
                                                    <table class="tab-sell-order">
                                                        <thead>
                                                            <tr>
                                                                <th class="f14-regular text-Gray">Date</th>
                                                                <th class="f14-regular text-Gray">Type</th>
                                                                <th class="f14-regular text-Gray">Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="recent-activity">
                                                        </tbody>
                                                    </table>
                                                    <a href="/dashboard.transactions" class="tf-button f12-bold w-100">
                                                        View All
                                                        <i class="icon icon-send"></i>
                                                    </a>
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
    <!-- Toast Notifications -->
    <div id="toast-container"></div>
    
<script src="<?= txh_asset('../../assets/js/api.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/jquery.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/bootstrap.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/countto.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/bootstrap-select.min.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/dashboard.js') ?>" defer></script>

<!-- Chart.js CDN -->
<script src="/assets/vendor/chartjs/chart.umd.min.js"></script>

<!-- Iconify CDN -->

<script>
async function renderCardUsageChart() {
  const ctx = document.getElementById("cardUsageChart");
  if (!ctx) return;

  try {
    const res = await fetch("/api/backend/card_usage.php");
    const result = await res.json();

    if (!result.success) throw new Error(result.message);

    const data = result.percentages;

    const labels = ["Balance", "Contracts", "Referral"];
    const datasetValues = [
      data.balance || 0,
      data.invested || 0,
      data.referral || 0
    ];
    const palette = ["#004DC0", "#D8ECF9", "#0E1334"];

    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels,
        datasets: [{
          data: datasetValues,
          backgroundColor: palette,
          borderWidth: 0,
          cutout: "70%"
        }]
      },
      options: {
        responsive: false,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
      }
    });

    // ✅ Update the legend dynamically
    const legend = document.querySelector(".chart-legend");
    if (legend) {
      legend.innerHTML = labels.map((label, i) => `
        <li class="flex items-center gap6">
          <div class="dot" style="background-color:${palette[i]}"></div>
          <span>${label}</span> <strong>${datasetValues[i]}%</strong>
        </li>
      `).join("");
    }

  } catch (err) {
    console.error("Chart Load Error:", err);
  }
}

document.addEventListener("DOMContentLoaded", renderCardUsageChart);

</script>

</body>
</html>