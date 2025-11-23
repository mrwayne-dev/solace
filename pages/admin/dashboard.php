<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'cookie_samesite' => 'Strict',
]);

if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin.login');
    exit;
}

// Optional: admin variables for display
$admin_id = $_SESSION['admin_id'];
$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator');
$admin_email = $_SESSION['admin_email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="description" content="HealthRunCare Admin Dashboard - Overview">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow"> <!-- Likely for admin pages -->
    <title>HealthRunCare Admin Dashboard</title>
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
                        <a href="/admin/dashboard" id="site-logo-inner">
                            <img id="logo_header" alt="HRC Admin" src="/assets/images/healthruncarelogo.png" width="150px">
                        </a>
                        <div class="button-show-hide">
                            <span class="iconify" data-icon="mdi:chevron-left"></span>
                        </div>
                    </div>

                    <div class="section-menu-left-wrap">
                        <div class="center">

                            <!-- Navigation Label -->
                            <div class="center-item">
                                <div class="center-heading f14-regular text-Gray menu-heading mb-12">
                                    Navigation
                                </div>
                            </div>

                            <div class="center-item">
                                <ul>

                                    <!-- DASHBOARD -->
                                    <li class="menu-item has-children active">
                                        <a href="javascript:void(0);" class="menu-item-button active">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:view-dashboard-outline"></span>
                                            </div>
                                            <div class="text">Dashboard</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item active">
                                                <a href="/admin/dashboard">
                                                    <div class="text">Overview</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>

                                    <!-- USERS -->
                                    <li class="menu-item">
                                        <a href="/admin.users" class="menu-item-button">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:account-group-outline"></span>
                                            </div>
                                            <div class="text">Users</div>
                                        </a>
                                    </li>

                                    <!-- TRANSACTIONS -->
                                    <li class="menu-item">
                                        <a href="/admin.transactions" class="menu-item-button">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:receipt-text"></span>
                                            </div>
                                            <div class="text">Transactions</div>
                                        </a>
                                    </li>

                                    <!-- WALLET MANAGEMENT -->
                                    <li class="menu-item">
                                        <a href="/admin.wallets" class="menu-item-button">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:wallet-outline"></span>
                                            </div>
                                            <div class="text">Wallet Management</div>
                                        </a>
                                    </li>

                                    <!-- DONATIONS (UPDATED ICON) -->
                                    <li class="menu-item">
                                        <a href="/admin.donations" class="menu-item-button">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:hand-heart-outline"></span>
                                            </div>
                                            <div class="text">Donations</div>
                                        </a>
                                    </li>

                                    <!-- FUND MANAGEMENT (Dropdown) -->
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:money"></span>
                                            </div>
                                            <div class="text">Fund Management</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item"><a href="/admin.funds"><div class="text">Investments</div></a></li>
                                            <li class="sub-menu-item"><a href="/admin.funds/holdlock"><div class="text">Holdlock</div></a></li>
                                            <li class="sub-menu-item"><a href="/admin.funds/trustfund"><div class="text">Trustfund</div></a></li>
                                            <li class="sub-menu-item"><a href="/admin.funds/infrastructure"><div class="text">Infrastructure</div></a></li>
                                            <li class="sub-menu-item"><a href="/admin.funds/maintenance"><div class="text">Maintenance</div></a></li>
                                        </ul>
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
                                <h6>Admin Dashboard</h6>
                            </div>
                            <div class="header-grid">
                                <div class="line1"></div>
                                <div class="popup-wrap user type-header">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="header-user wg-user">
                                                <span class="image">
                                                    <img src="/assets/images/avatar/default.png" alt="Admin Avatar">
                                                </span>
                                                <span class="content flex flex-column">
                                                    <span class="label-02 text-Black name"><?= $admin_name ?></span>
                                                    <span class="f14-regular text-Gray">Admin</span>
                                                </span>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end has-content" aria-labelledby="dropdownMenuButton3" >
                                            <li>
                                                <a href="/admin/profile" class="user-item">
                                                    <div class="body-title-2">Profile</div>
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
                                        <!-- ADMIN DASHBOARD CARDS OVERVIEW SECTION (Full Width, Bigger Cards) -->
                                        <!-- ============================= -->
                                        <div class="col-12 mb-40">
                                            <div class="wallet-overview">
                                                <div class="section-header flex justify-between items-center mb-16">
                                                    <h6 class="label-01">Dashboard Overview</h6>
                                                    <a href="#" class="f14-regular flex items-center gap8 text-Primary" onclick="refreshDashboard()">
                                                        <span class="iconify" data-icon="mdi:refresh"></span> Refresh Stats
                                                    </a>
                                                </div>

                                                <div class="wallet-cards grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap20">

                                                    <!-- Total Revenue -->
                                                    <div class="wallet-card wallet-main">
                                                        <div class="wallet-card-header">Total Revenue</div>
                                                        <div class="wallet-card-balance">$<span id="total-revenue">0.00</span></div>
                                                        <div class="wallet-card-footer">
                                                            HRC-REV-<?= str_pad($admin_id, 3, '0', STR_PAD_LEFT) ?>
                                                        </div>
                                                    </div>

                                                    <!-- Total Donations -->
                                                    <div class="wallet-card wallet-donations">
                                                        <div class="wallet-card-header">Total Donations</div>
                                                        <div class="wallet-card-balance">$<span id="total-donations">0.00</span></div>
                                                        <div class="wallet-card-footer">
                                                            HRC-DON-<?= str_pad($admin_id, 3, '0', STR_PAD_LEFT) ?>
                                                        </div>
                                                    </div>

                                                    <!-- Active Investments -->
                                                    <div class="wallet-card wallet-investments">
                                                        <div class="wallet-card-header">Active Investments</div>
                                                        <div class="wallet-card-balance"><span id="active-investments">0</span></div>
                                                        <div class="wallet-card-footer">
                                                            HRC-INV-<?= str_pad($admin_id, 3, '0', STR_PAD_LEFT) ?>
                                                        </div>
                                                    </div>

                                                    <!-- Total Users -->
                                                    <div class="wallet-card wallet-holdlock">
                                                        <div class="wallet-card-header">Total Users</div>
                                                        <div class="wallet-card-balance"><span id="total-users">0</span></div>
                                                        <div class="wallet-card-footer">
                                                            HRC-USR-<?= str_pad($admin_id, 3, '0', STR_PAD_LEFT) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ============================= -->
                                        <!-- ACTIVITY OVERVIEW & CHART SECTION -->
                                        <!-- ============================= -->
                                        <div class="col-12 mb-32">
                                            <div class="wg-box card-details mb-32">
                                                <div class="title flex justify-between items-center">
                                                    <h6 class="label-01">Activity Overview</h6>
                                                </div>

                                                <hr class="divider mb-24">

                                                <div class="card-details-grid">
                                                    <!-- Left: Chart Only -->
                                                    <div class="card-chart-panel text-center">
                                                        <canvas id="activityChart" width="200" height="200"></canvas>
                                                        <ul class="chart-legend flex justify-center gap16 mt-12 flex-wrap">
                                                            <li class="flex items-center gap6">
                                                                <div class="dot bg-Primary"></div> <span>Revenue</span> <strong id="chart-revenue">0%</strong>
                                                            </li>
                                                            <li class="flex items-center gap6">
                                                                <div class="dot bg-Green"></div> <span>Donations</span> <strong id="chart-donations">0%</strong>
                                                            </li>
                                                            <li class="flex items-center gap6">
                                                                <div class="dot bg-Accent"></div> <span>Investments</span> <strong id="chart-investments">0%</strong>
                                                            </li>
                                                            <li class="flex items-center gap6">
                                                                <div class="dot bg-Purple"></div> <span>Users</span> <strong id="chart-users">0%</strong>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <!-- Right: Chart Legend / Placeholder -->
                                                    <div class="card-info-panel">
                                                        <ul class="card-info-list">
                                                            <li>
                                                                <span>Period</span>
                                                                <strong id="chart-period">Last 30 Days</strong>
                                                            </li>
                                                            <li>
                                                                <span>Peak Activity</span>
                                                                <strong id="peak-activity">N/A</strong>
                                                            </li>
                                                            <li>
                                                                <span>Avg. Daily Users</span>
                                                                <strong id="avg-daily-users">0</strong>
                                                            </li>
                                                            <li>
                                                                <span>Top Performing Feature</span>
                                                                <strong id="top-feature">N/A</strong>
                                                            </li>
                                                            <li>
                                                                <span>System Uptime</span>
                                                                <strong id="system-uptime">99.9%</strong>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ============================= -->
                                        <!-- RECENT ACTIVITY & NOTIFICATIONS/QUICK ACTIONS (Side by Side) -->
                                        <!-- ============================= -->
                                        <div class="row">
                                            <div class="col-lg-6 mb-32">
                                                <!-- Recent Activity Section -->
                                                <div class="wg-box gap16">
                                                    <div class="title mb-12 flex justify-between items-center">
                                                        <div class="label-01">Recent Activity</div>
                                                        <a href="/admin/transactions" class="f12-bold text-Primary">View All</a>
                                                    </div>
                                                    <table class="tab-sell-order">
                                                        <thead>
                                                            <tr>
                                                                <th class="f14-regular text-Gray">Date</th>
                                                                <th class="f14-regular text-Gray">User</th>
                                                                <th class="f14-regular text-Gray">Type</th>
                                                                <th class="f14-regular text-Gray">Amount</th>
                                                                <th class="f14-regular text-Gray">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="recent-activity">
                                                            <!-- Rows will be populated by JavaScript using backend data -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- /Recent Activity Section -->
                                            </div>
                                            <div class="col-lg-6 mb-32">
                                                <!-- Notifications & Quick Actions -->
                                                <div class="row">
                                                    <!-- Quick Actions Panel -->
                                                    <div class="col-md-12 mb-24">
                                                        <div class="wg-box quick-actions-box">
                                                            <div class="title mb-12">
                                                                <div class="label-01">Quick Actions</div>
                                                            </div>

                                                            <div class="quick-actions-grid">
                                                                <button id="send-email-btn" class="quick-action-btn bg-GrayLight text-Black">
                                                                    <span class="iconify" data-icon="mdi:email-send-outline"></span>
                                                                    Send Email
                                                                </button>

                                                                <button id="set-deposit-address-btn" class="quick-action-btn bg-Green text-White">
                                                                    <span class="iconify" data-icon="mdi:cog-outline"></span>
                                                                    Set Deposit Address
                                                                </button>

                                                                <button id="view-deposit-address-btn" class="quick-action-btn bg-Black text-White">
                                                                    <span class="iconify" data-icon="mdi:eye-outline"></span>
                                                                    View Deposit Addresses
                                                                </button>



                                                                <a href="/admin/transactions/pending" class="quick-action-btn bg-Accent text-Black">
                                                                    <span class="iconify" data-icon="mdi:cash-plus"></span>
                                                                    Pending Deposits
                                                                </a>

                                                                <a href="/admin/withdrawals/pending" class="quick-action-btn bg-Green text-White">
                                                                    <span class="iconify" data-icon="mdi:bank-transfer-out"></span>
                                                                    Pending Withdrawals
                                                                </a>
                                                            </div>
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

                    <!-- Modals -->

                    <!-- Send Email Modal -->
                    <div class="modal" id="email-modal">
                        <div class="modal-overlay"></div>
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Send Email</h2>
                                <button class="button-close-modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="email-form">
                                    <div class="form-group mb-3">
                                        <label for="email-recipients" class="form-label">Recipient Group</label>
                                        <select class="form-control" id="email-recipients" required>
                                            <option value="">Select...</option>
                                            <option value="all">All Users</option>
                                            <option value="active">Active Users</option>
                                            <option value="investors">Investors Only</option>
                                            <option value="donors">Donors Only</option>
                                            <option value="specific">Specific User ID</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3" id="email-user-id-group" style="display: none;">
                                        <label for="email-user-id" class="form-label">User ID</label>
                                        <input type="text" class="form-control" id="email-user-id" placeholder="Enter exact User ID">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="email-subject" class="form-label">Subject</label>
                                        <input type="text" class="form-control" id="email-subject" placeholder="Enter email subject..." required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="email-body" class="form-label">Message Body</label>
                                        <textarea class="form-control" id="email-body" rows="6" placeholder="Enter your message..." required></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="email-priority" class="form-label">Priority</label>
                                        <select class="form-control" id="email-priority">
                                            <option value="normal">Normal</option>
                                            <option value="high">High</option>
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                        <button type="submit" class="modal-confirm-btn">Send Email</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


                    <!-- =========================================================
                    VIEW DEPOSIT ADDRESSES — MODAL
                    ========================================================= -->
                    <div class="modal" id="view-deposit-address-modal">
                        <div class="modal-overlay"></div>

                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Deposit Addresses</h2>
                                <button class="button-close-modal">&times;</button>
                            </div>

                            <div class="modal-body">

                                <div class="mb-3">
                                    <label class="form-label"><strong>Cash Mailing Address</strong></label>
                                    <div id="view-cash-mailing" class="p-2 bg-GrayLight rounded text-Black"></div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><strong>Wallet Deposit Address</strong></label>
                                    <div id="view-wallet-address" class="p-2 bg-GrayLight rounded text-Black"></div>
                                </div>

                                <div class="text-right">
                                    <button class="button-close-modal tf-button bg-Accent text-Black">Close</button>
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- =========================================================
                    SET DEPOSIT ADDRESS — MODAL
                    ========================================================= -->
                    <div class="modal" id="set-deposit-address-modal">
                        <div class="modal-overlay"></div>

                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Set Deposit Address</h2>
                                <button class="button-close-modal">&times;</button>
                            </div>

                            <div class="modal-body">
                                <form id="set-deposit-address-form">

                                    <!-- Deposit Type -->
                                    <div class="form-group mb-3">
                                        <label class="form-label">Deposit Method</label>
                                        <select class="form-control" id="deposit-method" required>
                                            <option value="">Select Method...</option>
                                            <option value="cash_mailing">Cash Mailing</option>
                                            <option value="wallet_address">Wallet Address</option>
                                        </select>
                                    </div>

                                    <!-- Address input -->
                                    <div class="form-group mb-3">
                                        <label class="form-label">Deposit Address / Instructions</label>
                                        <textarea class="form-control" id="deposit-value" rows="4"
                                            placeholder="Enter wallet address or mailing instructions..."
                                            required></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                        <button type="submit" class="modal-confirm-btn">Save Address</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>



                    <!-- =========================================================
                    PENDING DEPOSITS — SIMPLIFIED MODAL
                    ========================================================= -->
                    <div class="modal" id="pending-deposits-modal">
                        <div class="modal-overlay"></div>

                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Pending Deposit Requests</h2>
                                <button class="modal-close button-close-modal">&times;</button>
                            </div>

                            <div class="modal-body">

                                <table class="tab-sell-order" id="pending-deposit-table">
                                    <thead>
                                        <tr>
                                            <th class="f14-regular text-Gray">User</th>
                                            <th class="f14-regular text-Gray">Amount</th>
                                            <th class="f14-regular text-Gray">Date</th>
                                            <th class="f14-regular text-Gray">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pending-deposits-list">
                                        <!-- Loaded via JS -->
                                    </tbody>
                                </table>

                                <div id="no-pending-deposits" class="text-center text-Gray mt-20" style="display:none;">
                                    No pending deposit requests.
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- =========================================================
                    PENDING WITHDRAWALS — MODAL
                    ========================================================= -->
                    <div class="modal" id="pending-withdrawals-modal">
                        <div class="modal-overlay"></div>

                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Pending Withdrawal Requests</h2>
                                <button class="modal-close button-close-modal">&times;</button>
                            </div>

                            <div class="modal-body">

                                <table class="tab-sell-order" id="pending-withdrawals-table">
                                    <thead>
                                        <tr>
                                            <th class="f14-regular text-Gray">User</th>
                                            <th class="f14-regular text-Gray">Amount</th>
                                            <th class="f14-regular text-Gray">Date</th>
                                            <th class="f14-regular text-Gray">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pending-withdrawals-list">
                                        <!-- Loaded via JS -->
                                    </tbody>
                                </table>

                                <div id="no-pending-withdrawals" class="text-center text-Gray mt-20" style="display:none;">
                                    No pending withdrawal requests.
                                </div>

                            </div>
                        </div>
                    </div>




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
<script src="../../assets/js/admin/admin.js" defer></script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Iconify CDN -->
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>