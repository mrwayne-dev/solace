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
  $page_title = "X-Lock | TitanXHoldings Admin";
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
            <?php $active = "funds"; $active_fund = "xlock"; include __DIR__ . "/../_partials/sidebar.php"; ?>
            <!-- /Sidebar -->

            <!-- Main Content -->
            <div class="section-content-right">
                <!-- Header -->
                <?php $page_heading = "X-Lock Savings"; include __DIR__ . "/../_partials/topbar.php"; ?>
                <!-- /Header -->

                <!-- Main Content -->
                <div class="main-content">
                    <div class="main-content-inner">
                        <div class="main-content-wrap">
                            <div class="tf-container">

                                <!-- 1. HOLDLOCK SUMMARY CARDS -->
                                <div class="row mb-32">
                                    <div class="col-12">
                                        <div class="wallet-cards grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap20">
                                            <!-- Total X-Lock Balance -->
                                            <div class="wallet-card wallet-main">
                                                <div class="wallet-card-header">Total X-Lock Balance</div>
                                                <div class="wallet-card-balance">$<span id="total-holdlock">0.00</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:lock"></span> Locked</div>
                                            </div>
                                            <!-- Active X-Lock Users -->
                                            <div class="wallet-card wallet-green">
                                                <div class="wallet-card-header">Active X-Lock Users</div>
                                                <div class="wallet-card-balance"><span id="holdlock-users">0</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:account-lock"></span> Saving</div>
                                            </div>
                                            <!-- Total Interest Earned -->
                                            <div class="wallet-card wallet-accent">
                                                <div class="wallet-card-header">Interest Earned</div>
                                                <div class="wallet-card-balance">$<span id="total-interest">0.00</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:percent"></span> Distributed</div>
                                            </div>
                                            <!-- Next Unlock Date -->
                                            <div class="wallet-card wallet-purple">
                                                <div class="wallet-card-header">Next Unlock</div>
                                                <div class="wallet-card-balance"><span id="next-unlock">—</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:calendar-clock"></span> Scheduled</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. HOLDLOCK PLANS MANAGER -->
                                <div class="mb-32">
                                    <div class="d-flex justify-between items-center mb-16">
                                        <h5 class="label-01">X-Lock Plans</h5>
                                        <button id="add-holdlock-plan-btn" class="tf-button bg-Primary text-White f12-bold">
                                            <span class="iconify" data-icon="mdi:plus"></span> Add Plan
                                        </button>
                                    </div>

                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Plan Name</div>
                                            <div class="f12-bold text-White">Lock Period</div>
                                            <div class="f12-bold text-White">Interest Rate</div>
                                            <div class="f12-bold text-White">Min Amount</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="holdlock-plans-body">
                                                <!-- JS populates -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- 3. ACTIVE HOLDLOCK SAVINGS -->
                                <div class="mb-32">
                                    <h5 class="label-01 mb-16">Active X-Lock Savings</h5>
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">User</div>
                                            <div class="f12-bold text-White">Plan</div>
                                            <div class="f12-bold text-White">Amount</div>
                                            <div class="f12-bold text-White">Interest</div>
                                            <div class="f12-bold text-White">Lock Until</div>
                                            <div class="f12-bold text-White">Status</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="active-holdlock-body">
                                                <!-- JS populates -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="active-holdlock-pagination" class="pagination mt-3 flex gap-2 justify-center"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Main Content -->

                <!-- MODALS -->

                <!-- Add/Edit X-Lock Plan Modal -->
                <div class="modal" id="holdlock-plan-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content" style="max-width: 600px;">
                        <div class="modal-header">
                            <h2 id="holdlock-plan-title">Add X-Lock Plan</h2>
                            <button class="button-close-modal">×</button>
                        </div>
                        <div class="modal-body">
                            <form id="holdlock-plan-form">
                                <input type="hidden" id="holdlock-plan-id">
                                <div class="form-group mb-3">
                                    <label>Plan Name <span class="text-Red">*</span></label>
                                    <input type="text" class="form-control" id="holdlock-plan-name" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Lock Period (days) <span class="text-Red">*</span></label>
                                            <input type="number" class="form-control" id="holdlock-lock-days" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Interest Rate (%) <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="holdlock-interest-rate" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Minimum Amount (USD)</label>
                                    <input type="number" step="0.01" class="form-control" id="holdlock-min-amount">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Status</label>
                                    <select class="form-control" id="holdlock-plan-status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
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
<script src="<?= txh_asset('../../assets/js/admin/funds_xlock.js') ?>" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>