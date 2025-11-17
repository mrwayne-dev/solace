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
$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="HealthRunCare Admin - All Transactions">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>HealthRunCare Admin - Transactions</title>

    <link rel="preload" href="../../assets/css/bootstrap.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/css/dashboard.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/icon/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">

    <link rel="stylesheet" href="../../assets/css/animation.min.css">
    <link rel="stylesheet" href="../../assets/css/animation.css">
    <link rel="stylesheet" href="../../assets/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font.css">
    <link rel="stylesheet" href="../../assets/icon/style.css">

    <noscript>
        <link rel="stylesheet" href="../../assets/css/bootstrap.css">
        <link rel="stylesheet" href="../../assets/css/dashboard.css">
    </noscript>

    <link rel="icon" type="image/png" href="../../assets/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="../../assets/favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicon/apple-touch-icon.png">
    <link rel="manifest" href="../../assets/favicon/site.webmanifest">
</head>
<body class="counter-scroll">
    <div id="wrapper">
        <div id="page" class="">
            <div class="layout-wrap loader-off">
                <div id="preload" class="preload-container">
                    <div class="preloading"><span></span></div>
                </div>

                <!-- Sidebar — Transactions page is active -->
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
                            <div class="center-item">
                                <div class="center-heading f14-regular text-Gray menu-heading mb-12">Navigation</div>
                            </div>
                            <div class="center-item">
                                <ul>
                                    <!-- DASHBOARD -->
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:view-dashboard-outline"></span></div>
                                            <div class="text">Dashboard</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="/admin"><div class="text">Overview</div></a>
                                            </li>
                                        </ul>
                                    </li>

                                    <!-- USERS -->
                                    <li class="menu-item">
                                        <a href="/admin.users" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:account-group-outline"></span></div>
                                            <div class="text">Users</div>
                                        </a>
                                    </li>

                                    <!-- TRANSACTIONS - ACTIVE -->
                                    <li class="menu-item active">
                                        <a href="/admin.transactions" class="menu-item-button active">
                                            <div class="icon"><span class="iconify" data-icon="mdi:receipt-text"></span></div>
                                            <div class="text">Transactions</div>
                                        </a>
                                    </li>

                                    <!-- WALLET MANAGEMENT -->
                                    <li class="menu-item">
                                        <a href="/admin.wallets" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:wallet-outline"></span></div>
                                            <div class="text">Wallet Management</div>
                                        </a>
                                    </li>

                                    <!-- DONATIONS -->
                                    <li class="menu-item">
                                        <a href="/admin.donations" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:hand-heart-outline"></span></div>
                                            <div class="text">Donations</div>
                                        </a>
                                    </li>

                                    <!-- FUND MANAGEMENT -->
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:money"></span></div>
                                            <div class="text">Fund Management</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item"><a href="/admin.funds"><div class="text">Investments</div></a></li>
                                            <li class="sub-menu-item"><a href="/admin.funds/deposits"><div class="text">Holdlock</div></a></li>
                                            <li class="sub-menu-item"><a href="/admin.funds/withdrawals"><div class="text">Infrastructure</div></a></li>
                                            <li class="sub-menu-item"><a href="/admin.funds/activity"><div class="text">Maintenance</div></a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Sidebar -->

                <!-- Main Content -->
                <div class="section-content-right">
                    <!-- Header -->
                    <div class="header-dashboard">
                        <div class="wrap">
                            <div class="header-left">
                                <div class="button-show-hide"><i class="icon-menu"></i></div>
                                <h6>Transactions</h6>
                            </div>
                            <div class="header-grid">
                                <div class="line1"></div>
                                <div class="popup-wrap user type-header">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown">
                                            <span class="header-user wg-user">
                                                <span class="image">
                                                    <img src="/assets/images/avatar/default.png" alt="Admin">
                                                </span>
                                                <span class="content flex flex-column">
                                                    <span class="label-02 text-Black name"><?= $admin_name ?></span>
                                                    <span class="f14-regular text-Gray">Admin</span>
                                                </span>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end has-content" aria-labelledby="dropdownMenuButton3">
                                            <li><a href="/admin/profile" class="user-item"><div class="body-title-2">Profile</div></a></li>
                                            <li><a href="#" id="logout-btn" class="user-item"><div class="body-title-2">Log out</div></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Header -->

                    <!-- Main Content -->
                    <div class="main-content">
                        <div class="main-content-inner">
                            <div class="main-content-wrap">
                                <div class="tf-container">

                                    <!-- TRANSACTION STATS CARDS (same style as users page) -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wallet-cards grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap20">
                                                <div class="wallet-card wallet-main">
                                                    <div class="wallet-card-header">Total Transactions</div>
                                                    <div class="wallet-card-balance"><span id="total-transactions">0</span></div>
                                                    <div class="wallet-card-footer"> <span class="iconify" data-icon="mdi:history"></span> All Time</div>
                                                </div>
                                                <div class="wallet-card wallet-green">
                                                    <div class="wallet-card-header">Total Volume</div>
                                                    <div class="wallet-card-balance">$<span id="total-volume">0.00</span></div>
                                                    <div class="wallet-card-footer"> <span class="iconify" data-icon="mdi:chart-line"></span> Processed</div>
                                                </div>
                                                <div class="wallet-card wallet-accent">
                                                    <div class="wallet-card-header">Pending</div>
                                                    <div class="wallet-card-balance"><span id="pending-count">0</span></div>
                                                    <div class="wallet-card-footer"> <span class="iconify" data-icon="mdi:progress-clock"></span> Awaiting Action</div>
                                                </div>
                                                <div class="wallet-card wallet-purple">
                                                    <div class="wallet-card-header">Today</div>
                                                    <div class="wallet-card-balance"><span id="today-count">0</span></div>
                                                    <div class="wallet-card-footer"> <span class="iconify" data-icon="mdi:calendar-today"></span> Transactions</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SEARCH + FILTERS (exactly like user transactions page) -->
                                    <div class="topbar-search mb-24">
                                        <form class="form-search flex-grow">
                                            <fieldset class="name">
                                                <input type="text" id="transaction-search" placeholder="Search by ID, user, or reference..." class="show-search style-1">
                                            </fieldset>
                                            <div class="button-submit">
                                                <button type="submit"><i class="icon-search-normal1"></i></button>
                                            </div>
                                        </form>
                                        <div class="right">
                                            <a href="#" id="export-csv" class="tf-button style-2 f12-bold d-md-flex d-none">
                                                <span class="iconify" data-icon="mdi:file-export"></span>
                                                Export Report
                                            </a>
                                            <div class="dropdown default style-fill">
                                                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <span class="iconify" data-icon="mdi:filter"></span> Filter
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a href="#" data-filter="all">All Transactions</a></li>
                                                    <li><a href="#" data-filter="deposit">Deposits</a></li>
                                                    <li><a href="#" data-filter="withdrawal">Withdrawals</a></li>
                                                    <li><a href="#" data-filter="donation">Donations</a></li>
                                                    <li><a href="#" data-filter="investment">Investments</a></li>
                                                    <li><a href="#" data-filter="pending">Pending Only</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TRANSACTIONS TABLE (same style as user transactions page) -->
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Transaction ID</div>
                                            <div class="f12-bold text-White">Date</div>
                                            <div class="f12-bold text-White">User</div>
                                            <div class="f12-bold text-White">Type</div>
                                            <div class="f12-bold text-White">Amount (USD)</div>
                                            <div class="f12-bold text-White">Status</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="transactions-table-body">
                                                <!-- JS will populate -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- PAGINATION -->
                                    <div id="pagination" class="pagination mt-3 flex gap-2 justify-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Main Content -->

                </div>
            </div>
        </div>
    </div>

    <div id="loader" class="hidden">
        <div class="line-loader"><div></div><div></div><div></div><div></div><div></div></div>
    </div>
    <div id="toast-container"></div>

    <script src="../../assets/js/api.js" defer></script>
    <script src="../../assets/js/jquery.min.js"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script src="../../assets/js/bootstrap-select.min.js" defer></script>
    <script src="../../assets/js/dashboard.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>