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
  $page_title = "Mining Plans | Solace Mining Admin";
  include __DIR__ . "/_partials/head.php";
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
            <?php $active = "funds"; include __DIR__ . "/_partials/sidebar.php"; ?>
            <!-- /Sidebar -->

            <!-- Main Content -->
            <div class="section-content-right">
                <!-- Header -->
                <?php $page_heading = "Mining Plans"; include __DIR__ . "/_partials/topbar.php"; ?>
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
                                            <!-- Total Active Contracts -->
                                            <div class="wallet-card wallet-main">
                                                <div class="wallet-card-header">Total Active Contracts</div>
                                                <div class="wallet-card-balance">$<span id="total-active-invest">0.00</span></div>
                                                <div class="wallet-card-footer"><i class="iconify ph ph-trend-up"></i> Locked</div>
                                            </div>
                                            <!-- Total ROI Paid Out -->
                                            <div class="wallet-card wallet-green">
                                                <div class="wallet-card-header">Total ROI Paid</div>
                                                <div class="wallet-card-balance">$<span id="total-roi-paid">0.00</span></div>
                                                <div class="wallet-card-footer"><i class="iconify ph ph-check-circle"></i> Distributed</div>
                                            </div>
                                            <!-- Ongoing Plans -->
                                            <div class="wallet-card wallet-accent">
                                                <div class="wallet-card-header">Ongoing Plans</div>
                                                <div class="wallet-card-balance"><span id="ongoing-plans">0</span></div>
                                                <div class="wallet-card-footer"><i class="iconify ph ph-user"></i> Users</div>
                                            </div>
                                            <!-- Next Maturity -->
                                            <div class="wallet-card wallet-purple">
                                                <div class="wallet-card-header">Next Maturity</div>
                                                <div class="wallet-card-balance"><span id="next-maturity">—</span></div>
                                                <div class="wallet-card-footer"><i class="iconify ph ph-calendar-x"></i> Upcoming</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. INVESTMENT PLANS MANAGER -->
                                <div class="mb-32">
                                    <div class="d-flex justify-between items-center mb-16">
                                        <h5 class="label-01">Mining Contract Tiers</h5>
                                        <button id="add-plan-btn" class="tf-button bg-Primary text-White f12-bold">
                                            <i class="iconify ph ph-plus"></i> Add New Plan
                                        </button>
                                    </div>

                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Plan Name</div>
                                            <div class="f12-bold text-White">Daily Profit</div>
                                            <div class="f12-bold text-White">Duration</div>
                                            <div class="f12-bold text-White">Deposit Range</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="plans-table-body">
                                                <!-- JS populates -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- 4. ACTIVE INVESTMENTS TABLE -->
                                <div class="mb-32">
                                    <h5 class="label-01 mb-16">All Active Contracts</h5>
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">User</div>
                                            <div class="f12-bold text-White">Plan</div>
                                            <div class="f12-bold text-White">Amount</div>
                                            <div class="f12-bold text-White">Daily Profit</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Start Date</div>
                                            <div class="f12-bold text-White">End Date</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="active-investments-body">
                                                <!-- JS populates -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="active-pagination" class="pagination mt-3 flex gap-2 justify-center"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Main Content -->

                <!-- MODALS -->

                <!-- Add/Edit Plan Modal -->
                <div class="modal" id="plan-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content" style="max-width: 600px;">
                        <div class="modal-header">
                            <h2 id="plan-modal-title">Add New Plan</h2>
                            <button class="button-close-modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="plan-form">
                                <input type="hidden" id="plan-id">
                                <div class="form-group mb-3">
                                    <label>Plan Name <span class="text-Red">*</span></label>
                                    <input type="text" class="form-control" id="plan-name" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Min Amount (USD)</label>
                                            <input type="number" step="0.01" class="form-control" id="plan-min">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Max Amount (USD)</label>
                                            <input type="number" step="0.01" class="form-control" id="plan-max">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Daily Profit (%) <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="plan-daily" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Referral Commission (%)</label>
                                            <input type="number" step="0.01" class="form-control" id="plan-referral" value="10">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Duration (days) <span class="text-Red">*</span></label>
                                    <input type="number" class="form-control" id="plan-duration" value="5" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Status</label>
                                    <select class="form-control" id="plan-status">
                                        <option value="active">Active</option>
                                        <option value="hidden">Hidden</option>
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

                <!-- Edit Contract Modal -->
                <div class="modal" id="edit-investment-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Edit Contract</h2>
                            <button class="button-close-modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="edit-investment-form">
                                <input type="hidden" id="inv-id">
                                <div class="form-group mb-3">
                                    <label>User</label>
                                    <input type="text" class="form-control" id="inv-user" disabled>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Plan</label>
                                    <input type="text" class="form-control" id="inv-plan" disabled>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Amount (USD)</label>
                                            <input type="number" step="0.01" class="form-control" id="inv-amount">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Daily Profit (%)</label>
                                            <input type="number" step="0.01" class="form-control" id="inv-roi">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Status</label>
                                    <select class="form-control" id="inv-status">
                                        <option value="active">Active</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                    <button type="submit" class="modal-confirm-btn">Save Changes</button>
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
<script src="<?= txh_asset('../../assets/js/admin/funds.js') ?>" defer></script>
<script src="/assets/vendor/chartjs/chart.umd.min.js"></script>
</body>
</html>