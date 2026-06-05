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
  $page_title = "X-Rewards | TitanXHoldings Admin";
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
            <?php $active = "funds"; $active_fund = "xrewards"; include __DIR__ . "/../_partials/sidebar.php"; ?>
            <!-- /Sidebar -->

            <!-- Main Content -->
            <div class="section-content-right">
                <!-- Header -->
                <?php $page_heading = "X-Rewards Catalog"; include __DIR__ . "/../_partials/topbar.php"; ?>
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
                                                <div class="wallet-card-header">Total Revenue</div>
                                                <div class="wallet-card-balance">$<span id="total-revenue">0.00</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:cash-multiple"></span> All orders</div>
                                            </div>
                                            <div class="wallet-card wallet-green">
                                                <div class="wallet-card-header">Active Products</div>
                                                <div class="wallet-card-balance"><span id="active-products">0</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:gift"></span> Catalog</div>
                                            </div>
                                            <div class="wallet-card wallet-accent">
                                                <div class="wallet-card-header">Pending Orders</div>
                                                <div class="wallet-card-balance"><span id="pending-orders">0</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:package-variant"></span> Awaiting fulfilment</div>
                                            </div>
                                            <div class="wallet-card wallet-purple">
                                                <div class="wallet-card-header">Delivered</div>
                                                <div class="wallet-card-balance"><span id="delivered-orders">0</span></div>
                                                <div class="wallet-card-footer"><span class="iconify" data-icon="mdi:truck-check"></span> Lifetime</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. PRODUCTS MANAGER -->
                                <div class="mb-32">
                                    <div class="d-flex justify-between items-center mb-16">
                                        <h5 class="label-01">X-Rewards Products</h5>
                                        <button id="add-xrewards-product-btn" class="tf-button bg-Primary text-White f12-bold">
                                            <span class="iconify" data-icon="mdi:plus"></span> Add Product
                                        </button>
                                    </div>

                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Product Name</div>
                                            <div class="f12-bold text-White">Retail Price</div>
                                            <div class="f12-bold text-White">Member Price</div>
                                            <div class="f12-bold text-White">Stock</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="xrewards-products-body">
                                                <tr><td colspan="6" class="text-center text-Primary f14-regular">Loading products...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- 3. ORDERS -->
                                <div class="mb-32">
                                    <h5 class="label-01 mb-16">All Orders</h5>
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">User</div>
                                            <div class="f12-bold text-White">Product</div>
                                            <div class="f12-bold text-White">Qty</div>
                                            <div class="f12-bold text-White">Total</div>
                                            <div class="f12-bold text-White">Reference</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="xrewards-orders-body">
                                                <tr><td colspan="7" class="text-center text-Primary f14-regular">Loading orders...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Main Content -->

                <!-- Add/Edit Product Modal -->
                <div class="modal" id="xrewards-product-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content" style="max-width: 700px;">
                        <div class="modal-header">
                            <h2 id="xrewards-product-title">Add Reward Product</h2>
                            <button class="button-close-modal">×</button>
                        </div>
                        <div class="modal-body">
                            <form id="xrewards-product-form">
                                <input type="hidden" id="xrewards-product-id">
                                <div class="form-group mb-3">
                                    <label>Product Name <span class="text-Red">*</span></label>
                                    <input type="text" class="form-control" id="xrewards-product-name" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Description</label>
                                    <textarea class="form-control" id="xrewards-description" rows="2"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Retail Price (USD) <span class="text-Red">*</span></label>
                                            <input type="number" step="0.01" class="form-control" id="xrewards-retail-price" required>
                                            <small class="f12-regular text-Gray">Member price auto-computed at 40% discount.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Stock</label>
                                            <input type="number" class="form-control" id="xrewards-stock" placeholder="Blank for unlimited">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Product Image</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <img id="xrewards-image-preview" src="/assets/images/avatar/default.png"
                                             alt="Product image preview"
                                             style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid var(--LightGray);background:var(--Surface-Alt);"
                                             onerror="this.src='/assets/images/avatar/default.png';">
                                        <div>
                                            <input type="file" id="xrewards-image-file" accept="image/png,image/jpeg,image/webp" hidden>
                                            <button type="button" id="xrewards-image-choose" class="tf-button bg-Accent text-Black f12-bold">
                                                <span class="iconify" data-icon="mdi:image-plus"></span> Upload image
                                            </button>
                                            <div class="f12-regular text-Gray mt-1">PNG, JPG or WEBP. Max 2&nbsp;MB.</div>
                                        </div>
                                    </div>
                                    <input type="hidden" id="xrewards-image-path">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Status</label>
                                    <select class="form-control" id="xrewards-product-status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="out_of_stock">Out of Stock</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                    <button type="submit" class="modal-confirm-btn">Save Product</button>
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
<script src="<?= txh_asset('../../assets/js/admin/funds_xrewards.js') ?>" defer></script>
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>
