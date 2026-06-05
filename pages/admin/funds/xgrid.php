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
  $page_title = "X-Grid | TitanXHoldings Admin";
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
            <?php $active = "funds"; $active_fund = "xgrid"; include __DIR__ . "/../_partials/sidebar.php"; ?>
            <!-- /Sidebar -->
            <!-- Main Content -->
            <div class="section-content-right">
                <!-- Header -->
                <?php $page_heading = "X-Grid Fund"; include __DIR__ . "/../_partials/topbar.php"; ?>
                <!-- /Header -->
                <!-- Main Content -->
                <div class="main-content">
                    <div class="main-content-inner">
                        <div class="main-content-wrap">
                            <div class="tf-container">
                                <!-- 1. INFRASTRUCTURE SUMMARY CARDS -->
                                <div class="row mb-32">
                                    <div class="col-12">
                                        <div class="wallet-cards grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap20">
                                            <!-- Total X-Grid Fund -->
                                            <div class="wallet-card wallet-main">
                                                <div class="wallet-card-header">Total X-Grid Fund</div>
                                                <div class="wallet-card-balance">$<span id="total-infra">0.00</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:city"></span> Reserved</div>
                                            </div>
                                            <!-- Active Projects -->
                                            <div class="wallet-card wallet-green">
                                                <div class="wallet-card-header">Active Projects</div>
                                                <div class="wallet-card-balance"><span id="active-projects">0</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:tools"></span> In Progress</div>
                                            </div>
                                            <!-- Total Allocated -->
                                            <div class="wallet-card wallet-accent">
                                                <div class="wallet-card-header">Total Allocated</div>
                                                <div class="wallet-card-balance">$<span id="total-allocated">0.00</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:check-circle"></span> Approved</div>
                                            </div>
                                            <!-- Next Milestone -->
                                            <div class="wallet-card wallet-purple">
                                                <div class="wallet-card-header">Next Milestone</div>
                                                <div class="wallet-card-balance"><span id="next-milestone">—</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:flag-checkered"></span> Scheduled</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- 2. INFRASTRUCTURE PROJECTS MANAGER -->
                                <div class="mb-32">
                                    <div class="d-flex justify-between items-center mb-16">
                                        <h5 class="label-01">X-Grid Projects</h5>
                                        <button id="add-infra-project-btn" class="tf-button bg-Primary text-White f12-bold">
                                            <span class="iconify" data-icon="mdi:plus"></span> Add Project
                                        </button>
                                    </div>
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Project Name</div>
                                            <div class="f12-bold text-White">Budget</div>
                                            <div class="f12-bold text-White">Location</div>
                                            <div class="f12-bold text-White">Start Date</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="infra-projects-body">
                                                <!-- JS populates -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- 3. ACTIVE FUND ALLOCATIONS -->
                                <div class="mb-32">
                                    <h5 class="label-01 mb-16">Active Fund Allocations</h5>
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Project</div>
                                            <div class="f12-bold text-White">Allocated</div>
                                            <div class="f12-bold text-White">Spent</div>
                                            <div class="f12-bold text-White">Remaining</div>
                                            <div class="f12-bold text-White">Progress</div>
                                            <div class="f12-bold text-White">Status</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="active-infra-body">
                                                <!-- JS populates -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="active-infra-pagination" class="pagination mt-3 flex gap-2 justify-center"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Main Content -->
                <!-- MODALS -->
                <!-- Add/Edit X-Grid Project Modal -->
                <div class="modal" id="infra-project-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content" style="max-width: 600px;">
                        <div class="modal-header">
                            <h2 id="infra-project-title">Add X-Grid Project</h2>
                            <button class="button-close-modal">×</button>
                        </div>
                        <div class="modal-body">
                            <form id="infra-project-form">
                                <input type="hidden" id="infra-project-id">
                                <div class="form-group mb-3">
                                    <label>Project Name <span class="text-Red">*</span></label>
                                    <input type="text" class="form-control" id="infra-project-name" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Budget (USD) <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="infra-budget" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Location <span class="text-Red">*</span></label>
                                            <input type="text" class="form-control" id="infra-location" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" id="infra-start-date">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Status</label>
                                    <select class="form-control" id="infra-project-status">
                                        <option value="planning">Planning</option>
                                        <option value="active">Active</option>
                                        <option value="completed">Completed</option>
                                        <option value="on-hold">On Hold</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                    <button type="submit" class="modal-confirm-btn">Save Project</button>
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
<script src="<?= txh_asset('../../assets/js/admin/funds_xgrid.js') ?>" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>