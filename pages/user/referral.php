<?php
// pages/user/referral.php — Referral Center

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
?>
<?php
  $page_title = "Referral Center | Solace Mining";
  include __DIR__ . "/_partials/head.php";
?>

<body class="counter-scroll txh-dash">
    <div id="wrapper">
        <div id="page" class="">
            <div class="layout-wrap loader-off">
                <div id="preload" class="preload-container">
                    <div class="preloading"><span></span></div>
                </div>
                <?php $active = "referral"; include __DIR__ . "/_partials/sidebar.php"; ?>
                <div class="section-content-right">
                    <?php $page_heading = "Referral Center"; include __DIR__ . "/_partials/topbar.php"; ?>
                    <div class="main-content">
                        <div class="main-content-inner">
                            <div class="main-content-wrap">
                                <div class="tf-container">

                                    <!-- Referral link + stats -->
                                    <div class="row mb-32">
                                        <div class="col-lg-7 col-md-12 mb-24">
                                            <div class="wallet-card wallet-main wallet-hero">
                                                <div class="wallet-hero-top">
                                                    <div class="title-box flex items-center gap-2">
                                                        <i class="iconify ph ph-user-plus"></i>
                                                        <span class="f12-medium text-White">Your referral link</span>
                                                    </div>
                                                    <span class="box-status bg-Green f12-medium flex items-center gap-2">
                                                        <i class="iconify ph ph-percent"></i> 10% Commission
                                                    </span>
                                                </div>
                                                <div class="wallet-hero-balance">
                                                    <div class="input-group" style="margin-top:8px;">
                                                        <input class="form-control readonly-input" type="text" id="referral-link" readonly value="Loading...">
                                                    </div>
                                                </div>
                                                <div class="wallet-hero-actions">
                                                    <button type="button" class="tf-button bg-Accent f14-bold" id="copy-referral-btn">
                                                        <i class="iconify ph ph-copy"></i> Copy Link
                                                    </button>
                                                    <span class="f14-regular text-White">Code: <strong id="referral-code">—</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-5 col-md-12">
                                            <div class="wg-box">
                                                <div class="title mb-16"><div class="label-01 text-Primary">Referral Stats</div></div>
                                                <div class="content">
                                                    <div class="flex justify-between mb-12"><span class="f14-regular text-Gray">Total Referrals</span><strong id="stat-total-referrals">0</strong></div>
                                                    <div class="flex justify-between mb-12"><span class="f14-regular text-Gray">Total Earnings</span><strong class="text-Green" id="stat-total-earnings">$0.00</strong></div>
                                                    <div class="flex justify-between"><span class="f14-regular text-Gray">Commission Payouts</span><strong id="stat-payouts">0</strong></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Referred users -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box">
                                                <div class="title mb-16"><div class="label-01 text-Primary">Your Referrals</div></div>
                                                <div class="content">
                                                    <div class="txh-scroll-table mt-3">
                                                        <table class="txh-table">
                                                            <thead><tr>
                                                                <th>Name</th><th>Email</th><th>Joined</th><th>Earned From</th>
                                                            </tr></thead>
                                                            <tbody id="referrals-table-body"></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Earnings history -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box">
                                                <div class="title mb-16"><div class="label-01 text-Primary">Commission History</div></div>
                                                <div class="content">
                                                    <div class="txh-scroll-table mt-3">
                                                        <table class="txh-table">
                                                            <thead><tr>
                                                                <th>Referred User</th><th>Commission</th><th>Amount</th><th>Status</th><th>Date</th>
                                                            </tr></thead>
                                                            <tbody id="referral-history-body"></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="loader" class="hidden"><div class="line-loader"><div></div><div></div><div></div><div></div><div></div></div></div>
    <div id="toast-container"></div>

<script src="<?= txh_asset('../../assets/js/jquery.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/api.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/referral.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/bootstrap.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/dashboard.js') ?>" defer></script>
</body>
</html>
