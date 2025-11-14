<?php
// pages/user/transactions.php

session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
]);

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$user_name = htmlspecialchars($_SESSION['full_name'] ?? 'User');
$user_id = $_SESSION['user_id'] ?? null;
$user_email = $_SESSION['email'] ?? null;
$user_role = $_SESSION['role'] ?? 'user';

// Placeholder for dynamic data
$transactions = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="HealthRunCare Transactions - Track your deposits, donations, and other contributions.">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthRunCare | Transactions</title>

    <link rel="preload" href="../../assets/css/bootstrap.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/css/dashboard.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="stylesheet" href="../../assets/css/animation.min.css">
    <link rel="stylesheet" href="../../assets/css/animation.css">
    <link rel="stylesheet" href="../../assets/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font.css">
    <link rel="stylesheet" href="../../assets/icon/style.css">
    <link rel="icon" type="image/png" href="../../assets/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicon/apple-touch-icon.png">

    <style>
        .tf-button.style-2:hover {
            background-color: var(--color-primary-hover) !important;
            color: #fff !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(56, 102, 65, 0.3);
        }
        .tf-button.style-2 {
            transition: all 0.3s ease;
        }
    </style>
</head>

<body class="counter-scroll">
<div id="wrapper">
    <div id="page">
        <div class="layout-wrap loader-off">

            <!-- Sidebar -->
            <div class="section-menu-left">
                <div class="box-logo">
                    <a href="/dashboard" id="site-logo-inner">
                        <img src="/assets/images/healthruncarelogo.png" width="150px" alt="HealthRunCare">
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
                                <li class="menu-item has-children">
                                    <a href="/dashboard" class="menu-item-button">
                                        <div class="icon"><span class="iconify" data-icon="mdi:view-dashboard-outline"></span></div>
                                        <div class="text">Dashboard</div>
                                    </a>
                                </li>
                                <li class="menu-item has-children">
                                    <a href="/dashboard.wallet" class="menu-item-button">
                                        <div class="icon"><span class="iconify" data-icon="mdi:wallet-outline"></span></div>
                                        <div class="text">My Wallet</div>
                                    </a>
                                </li>
                                <li class="menu-item active">
                                    <a href="/dashboard.transactions" class="menu-item-button active">
                                        <div class="icon"><span class="iconify" data-icon="mdi:receipt-text-outline"></span></div>
                                        <div class="text">Transactions</div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="/dashboard.charity" class="menu-item-button">
                                        <div class="icon"><span class="iconify" data-icon="mdi:heart-outline"></span></div>
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
                                        <div class="text">Trust Fund</div>
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

            <!-- Main Section -->
            <div class="section-content-right">

                <!-- Header -->
                <div class="header-dashboard">
                    <div class="wrap">
                        <div class="header-left">
                            <div class="button-show-hide"><i class="icon-menu"></i></div>
                            <h6>Transactions</h6>
                        </div>
                        <div class="header-grid">
                            <div class="popup-wrap user type-header">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown">
                                        <span class="header-user wg-user">
                                            <span class="image">
                                                <img src="/assets/images/avatar/default.png" alt="">
                                            </span>
                                            <span class="content flex flex-column">
                                                <span class="label-02 text-Black name"><?= $user_name ?></span>
                                                <span class="f14-regular text-Gray"><?= ucfirst($user_role) ?></span>
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

                <!-- Main Content -->
                <div class="main-content">
                    <div class="main-content-inner">
                        <div class="main-content-wrap">
                            <div class="tf-container">

                                <div class="topbar-search">
                                    <form class="form-search flex-grow">
                                        <fieldset class="name">
                                            <input type="text" placeholder="Search transactions" class="show-search style-1" required>
                                        </fieldset>
                                        <div class="button-submit">
                                            <button type="submit"><i class="icon-search-normal1"></i></button>
                                        </div>
                                    </form>
                                    <div class="right">
                                        <a href="#" class="tf-button style-2 f12-bold d-md-flex d-none">
                                            <i class="icon icon-receive-square"></i>
                                            Export Report
                                        </a>
                                        <div class="dropdown default style-fill">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="icon icon-setting-5"></i> Filter
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a href="#">All</a></li>
                                                <li><a href="#">Completed</a></li>
                                                <li><a href="#">Pending</a></li>
                                                <li><a href="#">Failed</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction Table -->
                                <div class="table-list-transaction mt-3">
                                    <div class="list-transaction-head title-sort bg-Primary">
                                        <div class="f12-bold text-White">Transaction ID</div>
                                        <div class="f12-bold text-White">Date</div>
                                        <div class="f12-bold text-White">Type</div>
                                        <div class="f12-bold text-White">Amount (USD)</div>
                                        <div class="f12-bold text-White">Status</div>
                                    </div>

                                    <table class="list-transaction-content content-sort w-100">
                                        <tbody id="transactionList">
                                            <!-- Js will populate -->
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /table -->
                                 <div id="pagination" class="pagination mt-3 flex gap-2 justify-center"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /main-content -->
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/api.js"></script> <!-- Or move after jQuery if dependent -->
<script src="../../assets/js/jquery.min.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>
<script src="../../assets/js/dashboard.js" defer></script>
<script src="../../assets/js/transaction.js" defer></script>
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>
