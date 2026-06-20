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
  $page_title = "Wallets | Solace Mining Admin";
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
            <?php $active = "wallets"; include __DIR__ . "/_partials/sidebar.php"; ?>
            <!-- /Sidebar -->

            <!-- Main Content -->
            <div class="section-content-right">
                <!-- Header -->
                <?php $page_heading = "Wallet Management"; include __DIR__ . "/_partials/topbar.php"; ?>
                <!-- /Header -->

                <!-- Main Content -->
                <div class="main-content">
                    <div class="main-content-inner">
                        <div class="main-content-wrap">
                            <div class="tf-container">

                                <!-- WALLET STATS CARDS -->
                                <div class="row mb-32">
                                    <div class="col-12">
                                        <div class="wallet-cards grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap20">
                                            <!-- Total Wallets -->
                                            <div class="wallet-card wallet-main">
                                                <div class="wallet-card-header">Total Wallets</div>
                                                <div class="wallet-card-balance"><span id="total-wallets">0</span></div>
                                                <div class="wallet-card-footer"> <i class="iconify ph ph-wallet"></i> Active</div>
                                            </div>
                                            <!-- Total Balance -->
                                            <div class="wallet-card wallet-green">
                                                <div class="wallet-card-header">Total Balance</div>
                                                <div class="wallet-card-balance">$<span id="total-balance">0.00</span></div>
                                                <div class="wallet-card-footer"><i class="iconify ph ph-bank"></i> All Users</div>
                                            </div>
                                            <!-- Pending Deposits -->
                                            <div class="wallet-card wallet-accent">
                                                <div class="wallet-card-header">Pending Deposits</div>
                                                <div class="wallet-card-balance"><span id="pending-deposits">0</span></div>
                                                <div class="wallet-card-footer"> <i class="iconify ph ph-plus-circle"></i>  Requests</div>
                                            </div>
                                            <!-- Pending Withdrawals -->
                                            <div class="wallet-card wallet-purple">
                                                <div class="wallet-card-header">Pending Withdrawals</div>
                                                <div class="wallet-card-balance"><span id="pending-withdrawals">0</span></div>
                                                <div class="wallet-card-footer"><i class="iconify ph ph-minus-circle"></i> Requests</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- SEARCH + FILTERS -->
                                <div class="topbar-search mb-24">
                                    <form class="form-search flex-grow">
                                        <fieldset class="name">
                                            <input type="text" id="wallet-search" placeholder="Search by user, email, or wallet ID..." class="show-search style-1">
                                        </fieldset>
                                        <div class="button-submit">
                                            <button type="submit"><i class="icon-search-normal1"></i></button>
                                        </div>
                                    </form>
                                    <div class="right">
                                        <div class="dropdown default style-fill">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="iconify ph ph-funnel"></i> Filter
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a href="#" data-filter="all">All Wallets</a></li>
                                                <li><a href="#" data-filter="active">Active Only</a></li>
                                                <li><a href="#" data-filter="zero">Zero Balance</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- WALLETS TABLE -->
                                <div class="table-list-transaction">
                                    <div class="list-transaction-head title-sort bg-Primary">
                                        <div class="f12-bold text-White">User</div>
                                        <div class="f12-bold text-White">Wallet ID</div>
                                        <div class="f12-bold text-White">Balance</div>
                                        <div class="f12-bold text-White">Actions</div>
                                    </div>
                                    <table class="list-transaction-content content-sort w-100">
                                        <tbody id="wallets-table-body">
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

                <!-- ====================== MODALS ====================== -->

                <!-- Edit Wallet Balance Modal -->
                <div class="modal" id="edit-balance-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Edit Wallet Balance</h2>
                            <button class="button-close-modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="edit-balance-form">
                                <input type="hidden" id="edit-wallet-id">
                                <div class="form-group mb-3">
                                    <label>User</label>
                                    <input type="text" class="form-control" id="edit-wallet-user" disabled>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Current Balance</label>
                                    <input type="text" class="form-control" id="edit-current-balance" disabled>
                                </div>
                                <div class="form-group mb-3">
                                    <label>New Balance (USD)</label>
                                    <input type="number" step="0.01" class="form-control" id="edit-new-balance" required>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                    <button type="submit" class="modal-confirm-btn">Update Balance</button>
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
<script src="<?= txh_asset('../../assets/js/admin/wallet.js') ?>" defer></script>
<script src="/assets/vendor/chartjs/chart.umd.min.js"></script>
</body>
</html>