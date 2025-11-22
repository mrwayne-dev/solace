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
    <meta name="description" content="HealthRunCare Admin - Maintenance Fund">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>HealthRunCare Admin - Maintenance Fund</title>
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
                                <li class="menu-item has-children">
                                    <a href="javascript:void(0);" class="menu-item-button">
                                        <div class="icon"><span class="iconify" data-icon="mdi:view-dashboard-outline"></span></div>
                                        <div class="text">Dashboard</div>
                                    </a>
                                    <ul class="sub-menu">
                                        <li class="sub-menu-item"><a href="/admin"><div class="text">Overview</div></a></li>
                                    </ul>
                                </li>
                                <li class="menu-item">
                                    <a href="/admin.users" class="menu-item-button">
                                        <div class="icon"><span class="iconify" data-icon="mdi:account-group-outline"></span></div>
                                        <div class="text">Users</div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="/admin.transactions" class="menu-item-button">
                                        <div class="icon"><span class="iconify" data-icon="mdi:receipt-text"></span></div>
                                        <div class="text">Transactions</div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="/admin.wallets" class="menu-item-button">
                                        <div class="icon"><span class="iconify" data-icon="mdi:wallet-outline"></span></div>
                                        <div class="text">Wallet Management</div>
                                    </a>
                                </li>
                                <li class="menu-item">
                                    <a href="/admin.donations" class="menu-item-button">
                                        <div class="icon"><span class="iconify" data-icon="mdi:hand-heart-outline"></span></div>
                                        <div class="text">Donations</div>
                                    </a>
                                </li>
                                <li class="menu-item has-children active">
                                    <a href="javascript:void(0);" class="menu-item-button active">
                                        <div class="icon"><span class="iconify" data-icon="mdi:money"></span></div>
                                        <div class="text">Fund Management</div>
                                    </a>
                                    <ul class="sub-menu">
                                        <li class="sub-menu-item"><a href="/admin.funds"><div class="text">Investments</div></a></li>
                                        <li class="sub-menu-item"><a href="/admin.funds/holdlock"><div class="text">Holdlock</div></a></li>
                                        <li class="sub-menu-item"><a href="/admin.funds/trustfund"><div class="text">Trustfund</div></a></li>
                                        <li class="sub-menu-item"><a href="/admin.funds/infrastructure"><div class="text">Infrastructure</div></a></li>
                                        <li class="sub-menu-item active"><a href="/admin.funds/maintenance"><div class="text">Maintenance</div></a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section-content-right">
                <div class="header-dashboard">
                    <div class="wrap">
                        <div class="header-left">
                            <div class="button-show-hide"><i class="icon-menu"></i></div>
                            <h6>Maintenance Fund</h6>
                        </div>
                        <div class="header-grid">
                            <div class="line1"></div>
                            <div class="popup-wrap user type-header">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown">
                                        <span class="header-user wg-user">
                                            <span class="image">
                                                <img src="/assets/images/avatar/admin_default.png" alt="Admin">
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
                <div class="main-content">
                    <div class="main-content-inner">
                        <div class="main-content-wrap">
                            <div class="tf-container">
                                <div class="row mb-32">
                                    <div class="col-12">
                                        <div class="wallet-cards grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap20">
                                            <div class="wallet-card wallet-main">
                                                <div class="wallet-card-header">Total Maintenance Fund</div>
                                                <div class="wallet-card-balance">$<span id="total-maintenance">0.00</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:wrench"></span> Reserved</div>
                                            </div>
                                            <div class="wallet-card wallet-green">
                                                <div class="wallet-card-header">Active Tasks</div>
                                                <div class="wallet-card-balance"><span id="active-tasks">0</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:clipboard-check"></span> In Progress</div>
                                            </div>
                                            <div class="wallet-card wallet-accent">
                                                <div class="wallet-card-header">Total Spent</div>
                                                <div class="wallet-card-balance">$<span id="total-spent">0.00</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:currency-usd-off"></span> Used</div>
                                            </div>
                                            <div class="wallet-card wallet-purple">
                                                <div class="wallet-card-header">Next Scheduled</div>
                                                <div class="wallet-card-balance"><span id="next-scheduled">—</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:calendar-clock"></span> Due</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-32">
                                    <div class="d-flex justify-between items-center mb-16">
                                        <h5 class="label-01">Maintenance Plans</h5>
                                        <button id="add-maintenance-plan-btn" class="tf-button bg-Primary text-White f12-bold">
                                            <span class="iconify" data-icon="mdi:plus"></span> Add Plan
                                        </button>
                                    </div>
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Plan Name</div>
                                            <div class="f12-bold text-White">Min Amount</div>
                                            <div class="f12-bold text-White">Duration (Days)</div>
                                            <div class="f12-bold text-White">ROI %</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="maintenance-plans-body">
                                                </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="mb-32">
                                    <h5 class="label-01 mb-16">Active Maintenance Tasks</h5>
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Plan</div>
                                            <div class="f12-bold text-White">User</div>
                                            <div class="f12-bold text-White">Amount</div>
                                            <div class="f12-bold text-White">Status</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="active-maintenance-body">
                                                </tbody>
                                        </table>
                                    </div>
                                    <div id="active-maintenance-pagination" class="pagination mt-3 flex gap-2 justify-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal" id="maintenance-plan-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content" style="max-width: 600px;">
                        <div class="modal-header">
                            <h2 id="maintenance-plan-title">Add Maintenance Plan</h2>
                            <button class="button-close-modal">×</button>
                        </div>
                        <div class="modal-body">
                            <form id="maintenance-plan-form">
                                <input type="hidden" id="maintenance-plan-id">
                                <div class="form-group mb-3">
                                    <label>Plan Name <span class="text-Red">*</span></label>
                                    <input type="text" class="form-control" id="plan-name" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Min Amount (USD) <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="plan-min-amount" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Max Amount (USD)</label>
                                            <input type="number" step="0.01" class="form-control" id="plan-max-amount">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Duration (Days) <span class="text-Red">*</span></label>
                                            <input type="number" class="form-control" id="plan-duration-days" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>ROI Percent (%) <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="plan-roi-percent" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Summary/Purpose</label>
                                    <textarea class="form-control" id="plan-summary"></textarea>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                    <button type="submit" class="modal-confirm-btn">Save Plan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

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
<script src="../../assets/js/admin/admin.js" defer></script>
<script src="../../assets/js/admin/funds_maintenance.js" defer></script> 
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>