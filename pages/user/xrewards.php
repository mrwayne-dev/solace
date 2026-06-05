<?php
// pages/user/xrewards.php

session_start([
    'cookie_lifetime' => 86400, // Example: 24 hours
    'cookie_httponly' => true,
    'cookie_secure' => true, // Ensure HTTPS is used in production
    'cookie_samesite' => 'Strict',
]);

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: /login');
    exit;
}
// Retrieve user data from session
$user_name = htmlspecialchars($_SESSION['full_name'] ?? 'User'); // Fallback to 'User' if not set
$user_id = $_SESSION['user_id'] ?? null;
$user_email = $_SESSION['email'] ?? null;
$user_role = $_SESSION['role'] ?? 'user';

?>
<?php
  $page_title = "X-Rewards | TitanXHoldings";
  include __DIR__ . "/_partials/head.php";
?>

<body class="counter-scroll txh-dash">
    <!-- #wrapper -->
    <div id="wrapper">
        <!-- #page -->
        <div id="page" class="">
            <!-- layout-wrap -->
            <div class="layout-wrap loader-off">
                <!-- preload -->
                <div id="preload" class="preload-container">
                    <div class="preloading">
                        <span></span>
                    </div>
                </div>
                <!-- /preload -->
                                <!-- section-menu-left -->
                <?php $active = "xrewards"; include __DIR__ . "/_partials/sidebar.php"; ?>
                <!-- section-content-right -->
                <div class="section-content-right">
                    <!-- header-dashboard -->
                    <?php $page_heading = "X-Rewards"; include __DIR__ . "/_partials/topbar.php"; ?>
                    <!-- main-content -->
                    <div class="main-content">
                        <!-- main-content-wrap -->
                        <div class="main-content-inner">
                            <!-- main-content-wrap -->
                            <div class="main-content-wrap">
                                <div class="tf-container">
                                    <!-- ============================================================
                                         PORTFOLIO HERO (relocated summary metrics)
                                         ============================================================ -->
                                    <div class="row mb-32">
                                      <div class="col-12 mb-24">
                                        <div class="wallet-card wallet-main wallet-hero">
                                          <div class="wallet-hero-top">
                                            <div class="title-box flex items-center gap-2">
                                              <span class="iconify" data-icon="mdi:gift-outline"></span>
                                              <span class="f12-medium text-White">Total Spent (USD)</span>
                                            </div>
                                            <span class="box-status bg-Green f12-medium flex items-center gap-2">
                                              <span class="iconify" data-icon="mdi:shield-check"></span> Active
                                            </span>
                                          </div>
                                          <div class="wallet-hero-balance">
                                            <h2 class="counter text-White" id="card-total-spent">$0.00</h2>
                                            <div class="wallet-hero-change f14-regular">
                                              <span class="iconify" data-icon="mdi:trending-up"></span>
                                              <span id="card-loyalty-saved">$0.00</span>&nbsp;saved via loyalty
                                            </div>
                                          </div>
                                          <div class="wallet-hero-substats">
                                            <div class="wallet-substat">
                                              <div class="f12-regular">Active orders</div>
                                              <div class="f14-bold text-White" id="card-active-orders">0</div>
                                            </div>
                                            <div class="wallet-substat">
                                              <div class="f12-regular">Next delivery</div>
                                              <div class="f14-bold text-White" id="card-next-delivery">&mdash;</div>
                                            </div>
                                          </div>
                                          <div class="wallet-hero-actions">
                                            <a href="#xrewards-products-grid" class="tf-button bg-Accent f14-bold">
                                              <span class="iconify" data-icon="mdi:view-grid-outline"></span> Browse products
                                            </a>
                                          </div>
                                        </div>
                                      </div>
                                    </div>

                                    <!-- Member pricing promo banner -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="reward-promo">
                                                <div class="reward-promo__main">
                                                    <span class="reward-promo__icon">
                                                        <span class="iconify" data-icon="mdi:tag-heart-outline"></span>
                                                    </span>
                                                    <div>
                                                        <p class="reward-promo__eyebrow">Member exclusive</p>
                                                        <h4 class="reward-promo__title">Up to 40% off retail pricing</h4>
                                                        <p class="reward-promo__body">Redeem your loyalty savings on premium products — checkout is paid straight from your TitanXHoldings wallet.</p>
                                                    </div>
                                                </div>
                                                <span class="reward-promo__badge">
                                                    <span class="iconify" data-icon="mdi:shield-star-outline"></span> Investors only
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Available products — modern catalogue grid -->
                                        <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="reward-section-head">
                                                <div>
                                                    <p class="reward-eyebrow">Member catalogue</p>
                                                    <h5 class="reward-section-title">Available products</h5>
                                                </div>
                                                <span class="reward-section-note">Up to 40% off retail — paid from your wallet</span>
                                            </div>
                                            <div class="reward-grid" id="xrewards-products-grid">
                                                <!-- Products dynamically injected by xrewards.js -->
                                            </div>
                                        </div>




                                    <!-- Place Order Modal -->
                                    <div class="modal" id="xrewards-order-modal" style="display: none;">
                                        <div class="modal-overlay" id="xrewards-modal-overlay"></div>
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h2>Place an Order</h2>
                                                <button type="button" class="button-close-modal" id="close-xrewards-modal" aria-label="Close">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <form class="form-style-1" id="xrewards-form" autocomplete="off">
                                                    <input type="hidden" id="order-product-id" value="">

                                                    <!-- Product (pre-populated from card click) -->
                                                    <div class="mb-20">
                                                        <div class="f12-regular text-Gray mb-6">Product</div>
                                                        <div class="label-01 text-Primary" id="order-product-name" style="margin: 0;">—</div>
                                                    </div>

                                                    <!-- Quantity -->
                                                    <div class="mb-20 position-relative">
                                                        <label class="f14-regular text-Black mb-8">Quantity</label>
                                                        <div class="input-group">
                                                            <span class="input-icon"><span class="iconify" data-icon="mdi:counter"></span></span>
                                                            <input class="wallet-input form-control" type="number" placeholder="Enter quantity" min="1" id="reward-quantity" value="1">
                                                        </div>
                                                    </div>

                                                    <!-- Shipping Address -->
                                                    <div class="mb-20">
                                                        <label class="f14-regular text-Black mb-8">Shipping Address</label>
                                                        <textarea class="form-control" id="reward-shipping" rows="4" placeholder="Recipient name, address line 1, city, postcode, phone" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit;"></textarea>
                                                    </div>

                                                    <!-- Pricing details -->
                                                    <div class="row mb-20">
                                                        <div class="col-md-6">
                                                            <label class="f14-regular text-Black mb-8">Unit Price</label>
                                                            <input class="f12-regular text-Gray p-12 border border-Gray rounded" type="text" id="reward-unit-price" readonly>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="f14-regular text-Black mb-8">Total Cost</label>
                                                            <input class="f12-regular text-Gray p-12 border border-Gray rounded" type="text" id="reward-total" readonly>
                                                        </div>
                                                    </div>

                                                    <!-- Wallet Balance -->
                                                    <div class="mb-20 position-relative">
                                                        <label class="f14-regular text-Black mb-8">Wallet Balance</label>
                                                        <div class="input-group">
                                                            <span class="input-icon"><span class="iconify" data-icon="mdi:wallet-outline"></span></span>
                                                            <span id="wallet-balance" class="form-control readonly-input">$0.00</span>
                                                        </div>
                                                    </div>

                                                    <!-- CTA -->
                                                    <button
                                                        type="submit"
                                                        class="tf-button style-default w-full f14-bold bg-Green text-White hover:bg-Primary transition-colors duration-300"
                                                        id="place-order-btn"
                                                    >
                                                        Place Order
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- My Orders Table -->
                                    <div class="row mb-32">
                                    <div class="col-12">
                                        <div class="wg-box xrewards-orders">
                                        <div class="title mb-16 flex justify-between items-center">
                                            <div class="label-01 text-Primary">My Orders</div>
                                        </div>

                                    <div class="content">
                                        <div class="txh-scroll-table mt-3">
                                            <table id="active-xrewards-table" class="txh-table">
                                            <thead><tr>
                                            <th>Plan Name</th>
                                            <th>Total Price</th>
                                            <th>ROI (%)</th>
                                            <th>Unit Price</th>
                                            <th>Status</th>
                                            <th>Status</th>
                                            <th>Start Date</th>
                                            </tr></thead>
                                            <tbody id="active-xrewards-table-body">
                                                <!-- Populated dynamically by xrewards.js -->
                                            </tbody>
                                            </table>
                                        </div>
                                        </div>
                                        </div>
                                    </div>
                                    </div>

                            <!-- Eligible Unlocks Section -->
                            <div class="row mb-32">
                            <div class="col-12">
                                <div class="wg-box xrewards-delivered">
                                <div class="title mb-16 flex justify-between items-center">
                                    <div class="label-01 text-Primary">Delivered Orders</div>
                                </div>
                                <div class="content">
                                
                                <div class="txh-scroll-table mt-3">
                                    <table id="delivered-xrewards-table" class="txh-table">
                                    <thead><tr>
                                    <th>Plan Name</th>
                                    <th>Order Total</th>
                                    <th>Loyalty Saved</th>
                                    <th>Delivered On</th>
                                    <th>Reference</th>
                                    <th>Actions</th>
                                    </tr></thead>
                                    <tbody id="delivered-xrewards-table-body">
                                        <!-- Dynamically loaded via JS -->
                                    </tbody>
                                    </table>
                                </div>
                                </div>
                                </div>
                                </div>
                                </div>
                            </div>
                            <!-- /main-content-wrap -->
                        </div>
                        <!-- /main-content-wrap -->
                        
                    </div>
                    <!-- /main-content -->
                </div>
                <!-- /section-content-right -->
            </div>
            <!-- /layout-wrap -->
        </div>
        <!-- /#page -->
    </div>
    <!-- /#wrapper -->

        <div id="loader" class="hidden">
    <div class="line-loader">
            <div></div><div></div><div></div><div></div><div></div>
        </div>
    </div>
    <!-- Toast Container -->
    <div id="toast-container"></div>

<script src="<?= txh_asset('../../assets/js/jquery.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/bootstrap.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/api.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/xrewards.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/countto.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/bootstrap-select.min.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/dashboard.js') ?>" defer></script>

    <!-- Iconify CDN -->
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>