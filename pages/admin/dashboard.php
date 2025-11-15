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
    <style>
        /* Additional specific styles for the admin dashboard if needed */
        .status.text-Green { background: var(--Primary); color: var(--White); border: 1px solid var(--Primary-Hover); }
        .status.text-Orange { background: #FD7E14; color: var(--White); border: 1px solid #C65A00; }
        .status.text-Red { background: #dc3545; color: var(--White); border: 1px solid #bd2130; }
        .status.text-Gray { background: var(--Gray); color: var(--White); border: 1px solid var(--GrayDark); }
    </style>
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
                            <img class="" id="logo_header" alt="HRC Admin" src="/assets/images/healthruncarelogo.png" width="150px">
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
                                                <a href="/admin/dashboard" class="">
                                                    <div class="text">Overview</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/admin/users" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:account-group-outline"></span></div>
                                            <div class="text">Users</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/admin/transactions" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:receipt-text-outline"></span></div>
                                            <div class="text">Transactions</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/admin/wallets" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:wallet-outline"></span></div>
                                            <div class="text">Wallets</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/admin/investments" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:chart-timeline-variant"></span></div>
                                            <div class="text">Investments</div>
                                        </a>
                                    </li>
                                     <li class="menu-item">
                                        <a href="/admin/holdlock" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:lock-outline"></span></div>
                                            <div class="text">Holdlock</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/admin/charity" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:heart-outline"></span></div>
                                            <div class="text">Charity</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/admin/trustfund" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:account-cash-outline"></span></div>
                                            <div class="text">Trustfund</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/admin/infrastructure" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:office-building-outline"></span></div>
                                            <div class="text">Infrastructure</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/admin/maintenance" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:tools"></span></div>
                                            <div class="text">Maintenance</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/admin/settings" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:cog-outline"></span></div>
                                            <div class="text">Settings</div>
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
                                <h6>Admin Dashboard</h6>
                            </div>
                            <div class="header-grid">
                                <div class="line1"></div>
                                <div class="popup-wrap user type-header">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="header-user wg-user">
                                                <span class="image">
                                                    <img src="/assets/images/avatar/admin_default.png" alt="Admin Avatar">
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
                                                            <span class="iconify" data-icon="mdi:cash-multiple"></span> All Sources
                                                        </div>
                                                    </div>

                                                    <!-- Total Donations -->
                                                    <div class="wallet-card wallet-donations">
                                                        <div class="wallet-card-header">Total Donations</div>
                                                        <div class="wallet-card-balance">$<span id="total-donations">0.00</span></div>
                                                        <div class="wallet-card-footer">
                                                            <span class="iconify" data-icon="mdi:heart"></span> Charity & Impact
                                                        </div>
                                                    </div>

                                                    <!-- Active Investments -->
                                                    <div class="wallet-card wallet-investments">
                                                        <div class="wallet-card-header">Active Investments</div>
                                                        <div class="wallet-card-balance"><span id="active-investments">0</span></div>
                                                        <div class="wallet-card-footer">
                                                            <span class="iconify" data-icon="mdi:trending-up"></span> Currently Active
                                                        </div>
                                                    </div>

                                                    <!-- Total Users -->
                                                    <div class="wallet-card wallet-holdlock">
                                                        <div class="wallet-card-header">Total Users</div>
                                                        <div class="wallet-card-balance"><span id="total-users">0</span></div>
                                                        <div class="wallet-card-footer">
                                                            <span class="iconify" data-icon="mdi:account-group"></span> Registered
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
                                                    <!-- Notifications Panel -->
                                                    <div class="col-md-6 mb-24">
                                                        <div class="wg-box style-1 bg-Primary shadow-none">
                                                            <div class="title mb-10">
                                                                <div class="label-01 text-White">Notifications</div>
                                                            </div>
                                                            <div class="updates-list text-White">
                                                                <!-- Notifications will be populated by JavaScript using backend data -->
                                                                <div id="notifications-list">
                                                                    <!-- Notification items go here -->
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Quick Actions Panel -->
                                                    <div class="col-md-6 mb-24">
                                                        <div class="wg-box">
                                                            <div class="title mb-12">
                                                                <div class="label-01">Quick Actions</div>
                                                            </div>
                                                            <div class="flex flex-column gap12">
                                                                <button id="post-announcement-btn" class="tf-button bg-Primary text-White w-100 f12-bold">
                                                                    Post Announcement
                                                                </button>
                                                                <button id="send-email-btn" class="tf-button bg-GrayLight text-Black w-100 f12-bold">
                                                                    Send Email
                                                                </button>
                                                                <a href="/admin/transactions/pending" class="tf-button bg-Accent text-White w-100 f12-bold">
                                                                    Pending Deposits
                                                                </a>
                                                                <a href="/admin/withdrawals/pending" class="tf-button bg-Green text-White w-100 f12-bold">
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
                    <!-- Post Announcement Modal -->
                    <div class="modal" id="announcement-modal">
                        <div class="modal-overlay"></div>
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Post Announcement</h2>
                                <button class="button-close-modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="announcement-form">
                                    <div class="form-group mb-3">
                                        <label for="announcement-title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="announcement-title" placeholder="Enter announcement title..." required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="announcement-content" class="form-label">Content</label>
                                        <textarea class="form-control" id="announcement-content" rows="5" placeholder="Enter announcement content..." required></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="announcement-type" class="form-label">Type</label>
                                        <select class="form-control" id="announcement-type">
                                            <option value="info">Info</option>
                                            <option value="warning">Warning</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="announcement-target" class="form-label">Target Audience</label>
                                        <select class="form-control" id="announcement-target">
                                            <option value="all">All Users</option>
                                            <option value="investors">Investors</option>
                                            <option value="donors">Donors</option>
                                            <option value="active">Active Users</option>
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                        <button type="submit" class="modal-confirm-btn">Post Announcement</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

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
    // --- DOM Ready and Initial Load ---
    document.addEventListener("DOMContentLoaded", function() {
        // Initial load - fetches data from backend
        loadDashboardStats();
        loadRecentActivity();
        loadNotifications(); // New function for notifications
        renderActivityChart();
        setupModalTriggers();
        setupFormSubmissions();
    });

    // --- Function to load dashboard stats from backend ---
    async function loadDashboardStats() {
        try {
            const response = await fetch('/api/admin/dashboard/overview'); // Example API endpoint
            const data = await response.json();
            if (data.success) {
                // Update the DOM elements with backend data
                document.getElementById('total-revenue').textContent = data.data.total_revenue.toFixed(2);
                document.getElementById('total-donations').textContent = data.data.total_donations.toFixed(2);
                document.getElementById('active-investments').textContent = data.data.active_investments;
                document.getElementById('total-users').textContent = data.data.total_users;
            } else {
                console.error('API Error:', data.message);
                showToast('Failed to load dashboard stats.', 'error');
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            showToast('Network error loading dashboard stats.', 'error');
        }
    }

    // --- Function to load recent activity from backend ---
    async function loadRecentActivity() {
        const tbody = document.getElementById('recent-activity');
        // Clear existing rows before populating
        tbody.innerHTML = '';

        try {
            const response = await fetch('/api/admin/recent_transactions?limit=5'); // Example API endpoint
            const data = await response.json();
            if (data.success) {
                data.data.forEach(item => {
                    // Create a new row element
                    const row = document.createElement('tr');

                    // Format date using JavaScript's Date object if needed
                    const formattedDate = new Date(item.date).toLocaleString(); // Adjust format as needed

                    // Determine positive/negative class for amount
                    const amountClass = item.amount >= 0 ? 'positive' : 'negative';

                    // Determine status class (assuming status values like 'completed', 'pending', 'failed')
                    const statusClass = item.status === 'completed' ? 'text-Green' :
                                        item.status === 'pending' ? 'text-Orange' :
                                        item.status === 'failed' ? 'text-Red' : 'text-Gray';

                    // Populate the row with data
                    row.innerHTML = `
                        <td class="date-activity">${formattedDate}</td>
                        <td class="body-title-2">${item.user_id}</td>
                        <td class="f12-bold">${item.type}</td>
                        <td class="f12-bold ${amountClass}">${item.amount > 0 ? '+' : ''}${item.amount.toFixed(2)}</td>
                        <td><span class="status ${statusClass} f10-bold">${item.status}</span></td>
                    `;
                    // Append the row to the table body
                    tbody.appendChild(row);
                });
            } else {
                console.error('API Error:', data.message);
                showToast('Failed to load recent activity.', 'error');
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            showToast('Network error loading recent activity.', 'error');
        }
    }

    // --- Function to load notifications from backend ---
    async function loadNotifications() {
        const container = document.getElementById('notifications-list');
        // Clear existing notifications
        container.innerHTML = '';

         try {
            const response = await fetch('/api/admin/notifications'); // Example API endpoint
            const data = await response.json();
            if (data.success) {
                data.data.forEach(notification => {
                    // Format date for notification
                    const formattedDate = new Date(notification.timestamp).toLocaleTimeString(); // Or toLocaleDateString() for date only

                    // Create notification item element
                    const item = document.createElement('div');
                    item.className = 'update-item flex gap16 items-start mb-12';

                    item.innerHTML = `
                        <div class="update-content">
                            <div class="f14-bold">${notification.title}</div>
                            <div class="f12-regular text-Gainsboro">${notification.message}</div>
                            <div class="f12-regular text-LightGray mt-2">${formattedDate}</div>
                        </div>
                    `;
                    // Append the item to the container
                    container.appendChild(item);
                });
            } else {
                console.error('API Error:', data.message);
                showToast('Failed to load notifications.', 'error');
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            showToast('Network error loading notifications.', 'error');
        }
    }


    // --- Function to render the activity chart from backend data ---
    async function renderActivityChart() {
        const ctx = document.getElementById("activityChart");
        if (!ctx) return;

        try {
            const response = await fetch('/api/admin/dashboard/chart_data'); // Example API endpoint
            const data = await response.json();
            if (data.success) {
                const chartData = data.data; // Assuming data.data contains the necessary arrays
                const labels = chartData.labels; // e.g., ["Revenue", "Donations", "Investments", "Users"]
                const values = chartData.values; // e.g., [35, 25, 20, 20]
                const colors = chartData.colors || [ // Default colors if not provided by backend
                    getComputedStyle(document.documentElement).getPropertyValue("--Primary").trim(),
                    "#22C55E", // Green
                    "#CADEDE", // Accent
                    "#9FB8B8"  // Purple
                ];

                // Update legend text based on fetched values
                if (values.length >= 4) {
                    document.getElementById('chart-revenue').textContent = `${values[0]}%`;
                    document.getElementById('chart-donations').textContent = `${values[1]}%`;
                    document.getElementById('chart-investments').textContent = `${values[2]}%`;
                    document.getElementById('chart-users').textContent = `${values[3]}%`;
                }

                // Destroy existing chart instance if it exists to prevent conflicts
                if (window.activityChartInstance) {
                    window.activityChartInstance.destroy();
                }

                // Create new chart
                window.activityChartInstance = new Chart(ctx, {
                    type: "doughnut",
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colors,
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
            } else {
                console.error('API Error:', data.message);
                showToast('Failed to load chart data.', 'error');
            }
        } catch (error) {
            console.error('Fetch Error:', error);
            showToast('Network error loading chart data.', 'error');
        }
    }


    // --- Function to setup modal triggers ---
    function setupModalTriggers() {
        // Post Announcement Button
        document.getElementById('post-announcement-btn').addEventListener('click', function() {
            openModal('#announcement-modal');
        });

        // Send Email Button
        document.getElementById('send-email-btn').addEventListener('click', function() {
            openModal('#email-modal');
        });

        // Recipient Type Change for Email Modal
        document.getElementById('email-recipients').addEventListener('change', function(e) {
            const specificGroup = document.getElementById('email-user-id-group');
            if (e.target.value === 'specific') {
                specificGroup.style.display = 'block';
            } else {
                specificGroup.style.display = 'none';
            }
        });

        // Close Modal Buttons (using event delegation for efficiency)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('button-close-modal')) {
                const modal = e.target.closest('.modal');
                if (modal) closeModal(modal);
            }
            // Close modal when clicking the overlay
            if (e.target.classList.contains('modal-overlay')) {
                closeModal(e.target.parentElement);
            }
        });
    }

    // --- Function to open modal ---
    function openModal(modalId) {
        const modal = document.querySelector(modalId);
        if (modal) {
            modal.classList.add('is-open');
        }
    }

    // --- Function to close modal ---
    function closeModal(modalElement) {
        if (modalElement) {
            modalElement.classList.remove('is-open');
        }
    }

    // --- Function to setup form submissions (calls backend API) ---
    function setupFormSubmissions() {
        document.getElementById('announcement-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = {
                title: document.getElementById('announcement-title').value,
                content: document.getElementById('announcement-content').value,
                type: document.getElementById('announcement-type').value,
                target: document.getElementById('announcement-target').value
            };

            try {
                const response = await fetch('/api/admin/announcements', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${getAdminToken()}` // Example token
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                if (result.success) {
                    showToast("Announcement posted successfully!", "success");
                    closeModal(document.querySelector('#announcement-modal'));
                    this.reset(); // Reset form
                    // Optionally reload notifications or activity to show the new announcement
                    // loadNotifications();
                } else {
                    showToast(`Error: ${result.message}`, "error");
                }
            } catch (error) {
                console.error('Submit Error:', error);
                showToast("Network error posting announcement.", "error");
            }
        });

        document.getElementById('email-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = {
                recipients: document.getElementById('email-recipients').value,
                userId: document.getElementById('email-user-id').value, // Only relevant if recipients is 'specific'
                subject: document.getElementById('email-subject').value,
                body: document.getElementById('email-body').value,
                priority: document.getElementById('email-priority').value
            };

            try {
                const response = await fetch('/api/admin/send_email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${getAdminToken()}` // Example token
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                if (result.success) {
                    showToast("Email sent successfully!", "success");
                    closeModal(document.querySelector('#email-modal'));
                    this.reset(); // Reset form
                } else {
                    showToast(`Error: ${result.message}`, "error");
                }
            } catch (error) {
                console.error('Submit Error:', error);
                showToast("Network error sending email.", "error");
            }
        });
    }

    // --- Helper function to get admin token (implement as needed) ---
    function getAdminToken() {
        // Example: return token from session storage or a hidden input
        return localStorage.getItem('admin_token') || document.getElementById('admin-token-input')?.value;
    }

    // --- Function to refresh dashboard (calls backend) ---
    async function refreshDashboard() {
        // Show loader
        document.getElementById('loader').classList.remove('hidden');
        try {
            // Wait for all data loading promises to resolve
            await Promise.all([
                loadDashboardStats(),
                loadRecentActivity(),
                loadNotifications(),
                renderActivityChart()
            ]);
            // Show success toast
            showToast("Dashboard refreshed successfully!", "success");
        } catch (error) {
            console.error("Refresh Error:", error);
            showToast("Error refreshing dashboard.", "error");
        } finally {
            // Hide loader regardless of success/failure
            document.getElementById('loader').classList.add('hidden');
        }
    }

    // --- Toast Notification Helper (basic) ---
    function showToast(message, type = "info") {
        const toastContainer = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000); // Remove after 3 seconds
    }

    // --- Logout Button Handler (example) ---
    document.getElementById('logout-btn').addEventListener('click', function(e) {
        e.preventDefault();
        // Perform logout logic (e.g., call API, clear session)
        // alert('Logout initiated!'); // Replace with actual logic
        window.location.href = '/admin/logout'; // Redirect to logout page
    });

</script>

</body>
</html>