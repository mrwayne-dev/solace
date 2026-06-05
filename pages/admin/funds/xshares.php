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
<?php
  $page_title = "X-Shares | TitanXHoldings Admin";
  include __DIR__ . "/../_partials/head.php";
?>
<body class="counter-scroll txh-dash">
<div id="wrapper">
    <div id="page" class="">
        <div class="layout-wrap loader-off">
            <!-- Preloader -->
            <div id="preload" class="preload-container">
                <div class="preloading"><span></span></div>
            </div>

            <!-- Sidebar -->
            <?php $active = "funds"; $active_fund = "xshares"; include __DIR__ . "/../_partials/sidebar.php"; ?>
            <!-- /Sidebar -->

            <!-- Main Content -->
            <div class="section-content-right">
                <!-- Header -->
                <?php $page_heading = "X-Shares Assets"; include __DIR__ . "/../_partials/topbar.php"; ?>
                <!-- /Header -->

                <!-- Main Content -->
                <div class="main-content">
                    <div class="main-content-inner">
                        <div class="main-content-wrap">
                            <div class="tf-container">

                                <!-- 1. SUMMARY CARDS -->
                                <div class="row mb-32">
                                    <div class="col-12">
                                        <div class="wallet-cards grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap20">
                                            <div class="wallet-card wallet-main">
                                                <div class="wallet-card-header">Total Invested</div>
                                                <div class="wallet-card-balance">$<span id="total-invested">0.00</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:chart-pie"></span> Across all assets</div>
                                            </div>
                                            <div class="wallet-card wallet-green">
                                                <div class="wallet-card-header">Active Holdings</div>
                                                <div class="wallet-card-balance"><span id="active-holdings">0</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:account-multiple"></span> Open positions</div>
                                            </div>
                                            <div class="wallet-card wallet-accent">
                                                <div class="wallet-card-header">Total Paid (ROI)</div>
                                                <div class="wallet-card-balance">$<span id="total-paid">0.00</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:percent"></span> Distributed</div>
                                            </div>
                                            <div class="wallet-card wallet-purple">
                                                <div class="wallet-card-header">Next Maturity</div>
                                                <div class="wallet-card-balance"><span id="next-maturity">—</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:calendar-clock"></span> Scheduled</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. ASSETS MANAGER -->
                                <div class="mb-32">
                                    <div class="d-flex justify-between items-center mb-16">
                                        <h5 class="label-01">X-Shares Assets</h5>
                                        <button id="add-xshares-asset-btn" class="tf-button bg-Primary text-White f12-bold">
                                            <span class="iconify" data-icon="mdi:plus"></span> Add Asset
                                        </button>
                                    </div>

                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Ticker</div>
                                            <div class="f12-bold text-White">Asset Name</div>
                                            <div class="f12-bold text-White">Price</div>
                                            <div class="f12-bold text-White">ROI</div>
                                            <div class="f12-bold text-White">Min</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="xshares-assets-body">
                                                <tr><td colspan="7" class="text-center text-Primary f14-regular">Loading assets...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- 3. ACTIVE HOLDINGS -->
                                <div class="mb-32">
                                    <h5 class="label-01 mb-16">Active X-Shares Holdings</h5>
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">User</div>
                                            <div class="f12-bold text-White">Asset</div>
                                            <div class="f12-bold text-White">Amount</div>
                                            <div class="f12-bold text-White">ROI Earned</div>
                                            <div class="f12-bold text-White">Maturity</div>
                                            <div class="f12-bold text-White">Status</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="active-xshares-body">
                                                <tr><td colspan="6" class="text-center text-Primary f14-regular">Loading holdings...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Main Content -->

                <!-- Add/Edit Asset Modal -->
                <div class="modal" id="xshares-asset-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content" style="max-width: 700px;">
                        <div class="modal-header">
                            <h2 id="xshares-asset-title">Add X-Shares Asset</h2>
                            <button class="button-close-modal">×</button>
                        </div>
                        <div class="modal-body">
                            <form id="xshares-asset-form">
                                <input type="hidden" id="xshares-asset-id">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>Ticker <span class="text-Red">*</span></label>
                                            <input type="text" class="form-control" id="xshares-ticker" maxlength="10" required>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group mb-3">
                                            <label>Asset Name <span class="text-Red">*</span></label>
                                            <input type="text" class="form-control" id="xshares-asset-name" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Company / Issuer</label>
                                    <input type="text" class="form-control" id="xshares-company">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Current Price (USD) <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="xshares-current-price" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>ROI (%) <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="xshares-roi-percent" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>Duration (days)</label>
                                            <input type="number" class="form-control" id="xshares-duration-days" placeholder="Blank for open-ended">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>Min Investment <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="xshares-min-amount" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>Payout Schedule</label>
                                            <select class="form-control" id="xshares-payout-schedule">
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                                <option value="quarterly">Quarterly</option>
                                                <option value="maturity">At Maturity</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Status</label>
                                    <select class="form-control" id="xshares-asset-status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                    <button type="submit" class="modal-confirm-btn">Save Asset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Loader & Toast -->
<div id="loader" class="hidden">
    <div class="line-loader"><div></div><div></div><div></div><div></div><div></div></div>
</div>
<div id="toast-container"></div>

<!-- Scripts -->
<script src="<?= txh_asset('../../assets/js/api.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/jquery.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/bootstrap.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/bootstrap-select.min.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/admin/admin.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/admin/funds_xshares.js') ?>" defer></script>
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>
