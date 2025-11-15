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
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="description" content="HealthRunCare is a unified healthcare platform connecting patients, doctors, pharmacies, and employers through AI-driven, secure solutions.">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://healthruncare.com/ ">
    <title>HealthRunCare User Dashboard</title>
   <!-- Preload + Apply (critical CSS) -->
    <link rel="preload" href="../../assets/css/bootstrap.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/css/dashboard.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/icon/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <!-- Load Non-critical CSS Normally -->
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
                <!-- preload -->=
                <div id="preload" class="preload-container">
                    <div class="preloading">
                        <span></span>
                    </div>
                </div>
                <!-- /preload -->
                <!-- section-menu-left -->
                 <!-- testing my commit -->
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
                                    <li class="menu-item active has-children">
                                        <a href="javascript:void(0);" class="menu-item-button active">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:view-dashboard-outline"></span>
                                            </div>
                                            <div class="text">Dashboard</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item active">
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
                                <h6>User Dashboard</h6>
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
                                    
                                    <div class="row">
                                        <!-- ============================= -->
                                        <!-- BIG WALLET CARDS OVERVIEW SECTION (Full Width, Bigger Cards) -->
                                        <!-- ============================= -->
                                   <div class="col-12 mb-40">
  <div class="wallet-overview">
    <div class="section-header flex justify-between items-center mb-16">
      <h6 class="label-01">Wallet Overview</h6>
      <a href="#" class="f14-regular flex items-center gap8 text-Primary" onclick="refreshDashboard()">
        <span class="iconify" data-icon="mdi:refresh"></span> Refresh Balances
      </a>
    </div>

    <div class="wallet-cards grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap20">

      <!-- Main Wallet -->
      <div class="wallet-card wallet-main">
        <div class="wallet-card-header">Main Wallet</div>
        <div class="wallet-card-balance">$<span id="total-balance">0.00</span></div>
        <div class="wallet-card-footer">
          HRC-MAIN-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?>
        </div>
      </div>

      <!-- Donations Wallet -->
      <div class="wallet-card wallet-donations">
        <div class="wallet-card-header">Donations Wallet</div>
        <div class="wallet-card-balance">$<span id="total-donations">0.00</span></div>
        <div class="wallet-card-footer">
          HRC-DON-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?>
        </div>
      </div>

      <!-- Investments -->
      <div class="wallet-card wallet-investments">
        <div class="wallet-card-header">Investments</div>
        <div class="wallet-card-balance">$<span id="total-investments">0.00</span></div>
        <div class="wallet-card-footer">
          HRC-INV-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?>
        </div>
      </div>

      <!-- HoldLock Savings -->
      <div class="wallet-card wallet-holdlock">
        <div class="wallet-card-header">HoldLock Savings</div>
        <div class="wallet-card-balance">$<span id="total-holdlock">0.00</span></div>
        <div class="wallet-card-footer">
          HRC-HLD-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?>
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
            <span>HRC ID</span>
            <strong id="card-id">HRC-<?= str_pad($user_id, 4, '0', STR_PAD_LEFT) ?>-9011-3298</strong>
            </li>
          <li>
            <span>Card Holder</span>
            <strong id="card-holder"><?= $user_name ?></strong>
          </li>
          <li>
            <span>Bank Name</span>
            <strong id="card-bank">HealthRunCare Bank</strong>
          </li>
        </ul>
      </div>

      <!-- Right: Chart Only -->
      <div class="card-chart-panel text-center">
        <canvas id="cardUsageChart" width="200" height="200"></canvas>
        <ul class="chart-legend flex justify-center gap16 mt-12 flex-wrap">
          <li class="flex items-center gap6">
            <div class="dot bg-Primary"></div> <span>Charity</span> <strong>40%</strong>
          </li>
          <li class="flex items-center gap6">
            <div class="dot bg-Accent"></div> <span>Investments</span> <strong>25%</strong>
          </li>
          <li class="flex items-center gap6">
            <div class="dot bg-Purple"></div> <span>HoldLock</span> <strong>20%</strong>
          </li>
          <li class="flex items-center gap6">
            <div class="dot bg-Gainsboro"></div> <span>Donations</span> <strong>15%</strong>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>



                                        <!-- ============================= -->
                                        <!-- HEALTH IMPACT SECTION (Full Width) -->
                                        <!-- ============================= -->
                                        <div class="col-12 mb-32">
                                            <div class="wg-box style-1 bg-Gainsboro shadow-none widget-tabs mb-32">
                                                <div>
                                                    <div class="title mb-16">
                                                        <div class="label-01">Health Impact</div>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <div class="flex gap16 items-center flex-wrap">
                                                            <div class="block-legend">
                                                                <div class="dot bg-Green"></div>
                                                                <div class="f12-medium"> <span class="text-Gray">Total Contributions</span> <span class="f12-bold" id="total-contributions">$0</span></div>
                                                            </div>
                                                            <div class="block-legend">
                                                                <div class="dot bg-Primary"></div>
                                                                <div class="f12-medium"> <span class="text-Gray">People Helped</span> <span class="f12-bold" id="people-helped">0</span></div>
                                                            </div>
                                                            <div class="block-legend">
                                                                <div class="dot bg-LimeGreen"></div>
                                                                <div class="f12-medium"> <span class="text-Gray">Impact Score</span> <span class="f12-bold" id="impact-score">0%</span></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="widget-content-tab">
                                                    <div class="widget-content-inner active">
                                                        <!-- sStats -->
                                                        <div class="flex gap24 mb-0 flex-md-row flex-column">
                                                            <div class="w-full">
                                                                <div class="wg-card style-1 bg-White mb-0">
                                                                    <div class="f12-medium text-GrayDark">Communites Helped</div>
                                                                    <div class="content">
                                                                        <h6 class="counter text-Primary" id="communities-helped">0</h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="w-full">
                                                                <div class="wg-card style-1 bg-White mb-0">
                                                                    <div class="f12-medium text-GrayDark">Packages Funded</div>
                                                                    <div class="content">
                                                                        <h6 class="counter text-Primary" id="packages-funded">0</h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
                                                                    <div class="f14-bold">Community Spotlight</div>
                                                                    <div class="f12-regular text-Gainsboro">HRC partnered with local health organizations for free screenings.</div>
                                                                    <div class="f12-regular text-LightGray mt-4">3 days ago</div>
                                                                </div>
                                                            </div>
                                                            <div class="update-item flex gap16 items-start mb-0">
                                                                <div class="update-content">
                                                                    <div class="f14-bold">New Partnership</div>
                                                                    <div class="f12-regular text-Gainsboro">HealthRunCare partners with Wales Public Health for rural outreach.</div>
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
    
<script src="../../assets/js/api.js" defer></script>
<script src="../../assets/js/jquery.min.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/countto.js" defer></script>
<script src="../../assets/js/bootstrap-select.min.js" defer></script>
<script src="../../assets/js/dashboard.js" defer></script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Iconify CDN -->
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>

<script>
async function renderCardUsageChart() {
  const ctx = document.getElementById("cardUsageChart");
  if (!ctx) return;

  try {
    const res = await fetch("/api/backend/card_usage.php");
    const result = await res.json();

    if (!result.success) throw new Error(result.message);

    // 🟢 Use the correct structure from your PHP output
    const data = result.percentages; // not result.data

    const labels = ["Charity", "Investments", "HoldLock", "Trustfund", "Infrastructure", "Development"];
    const datasetValues = [
      data.charity || 0,
      data.investment || 0,
      data.holdlock || 0,
      data.trustfund || 0,
      data.infrastructure || 0,
      data.maintenance || 0
    ];

    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels,
        datasets: [{
          data: datasetValues,
          backgroundColor: [
            getComputedStyle(document.documentElement).getPropertyValue("--Primary").trim(),
            "#CADEDE",
            "#9FB8B8",
            "#8EA8A8",
            "#A9C1C1",
            "#FEFAE0"
          ],
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
          <div class="dot" style="background-color:${[
            getComputedStyle(document.documentElement).getPropertyValue("--Primary").trim(),
            "#CADEDE", "#9FB8B8", "#8EA8A8", "#A9C1C1", "#FEFAE0"
          ][i]}"></div>
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