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
  $page_title = "X-Weekly | TitanXHoldings Admin";
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
            <?php $active = "funds"; $active_fund = "xweekly"; include __DIR__ . "/../_partials/sidebar.php"; ?>
            <!-- /Sidebar -->

            <!-- Main Content -->
            <div class="section-content-right">
                <!-- Header -->
                <?php $page_heading = "X-Weekly Programs"; include __DIR__ . "/../_partials/topbar.php"; ?>
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
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:cash-multiple"></span> Across all programs</div>
                                            </div>
                                            <div class="wallet-card wallet-green">
                                                <div class="wallet-card-header">Active Programs</div>
                                                <div class="wallet-card-balance"><span id="active-programs">0</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:calendar-refresh"></span> Running</div>
                                            </div>
                                            <div class="wallet-card wallet-accent">
                                                <div class="wallet-card-header">Total Paid (ROI)</div>
                                                <div class="wallet-card-balance">$<span id="total-paid">0.00</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:percent"></span> Distributed</div>
                                            </div>
                                            <div class="wallet-card wallet-purple">
                                                <div class="wallet-card-header">Next Debit</div>
                                                <div class="wallet-card-balance"><span id="next-debit">—</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:calendar-clock"></span> Scheduled</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. PLANS MANAGER -->
                                <div class="mb-32">
                                    <div class="d-flex justify-between items-center mb-16">
                                        <h5 class="label-01">X-Weekly Plans</h5>
                                        <button id="add-xweekly-plan-btn" class="tf-button bg-Primary text-White f12-bold">
                                            <span class="iconify" data-icon="mdi:plus"></span> Add Plan
                                        </button>
                                    </div>

                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Plan Name</div>
                                            <div class="f12-bold text-White">ROI (Annualised)</div>
                                            <div class="f12-bold text-White">Min Weekly</div>
                                            <div class="f12-bold text-White">Max Weekly</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="xweekly-plans-body">
                                                <tr><td colspan="6" class="text-center text-Primary f14-regular">Loading plans...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- 3. ACTIVE PROGRAMS -->
                                <div class="mb-32">
                                    <h5 class="label-01 mb-16">Active X-Weekly Programs</h5>
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">User</div>
                                            <div class="f12-bold text-White">Weekly Amount</div>
                                            <div class="f12-bold text-White">Total Invested</div>
                                            <div class="f12-bold text-White">ROI Earned</div>
                                            <div class="f12-bold text-White">Next Debit</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="active-xweekly-body">
                                                <tr><td colspan="7" class="text-center text-Primary f14-regular">Loading programs...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Main Content -->

                <!-- Add/Edit Plan Modal -->
                <div class="modal" id="xweekly-plan-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content" style="max-width: 600px;">
                        <div class="modal-header">
                            <h2 id="xweekly-plan-title">Add X-Weekly Plan</h2>
                            <button class="button-close-modal">×</button>
                        </div>
                        <div class="modal-body">
                            <form id="xweekly-plan-form">
                                <input type="hidden" id="xweekly-plan-id">
                                <div class="form-group mb-3">
                                    <label>Plan Name <span class="text-Red">*</span></label>
                                    <input type="text" class="form-control" id="xweekly-plan-name" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>ROI (Annualised %) <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="xweekly-roi-percent" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Status</label>
                                            <select class="form-control" id="xweekly-plan-status">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Min Weekly (USD) <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="xweekly-min-weekly" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Max Weekly (USD)</label>
                                            <input type="number" step="0.01" class="form-control" id="xweekly-max-weekly" placeholder="Leave blank for unlimited">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Description</label>
                                    <textarea class="form-control" id="xweekly-description" rows="2"></textarea>
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
<script src="<?= txh_asset('../../assets/js/admin/funds_xweekly.js') ?>" defer></script>
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>
