<?php
// pages/user/wallet.php

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
  $page_title = "Wallet | Solace Mining";
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
                <?php $active = "wallet"; include __DIR__ . "/_partials/sidebar.php"; ?>
                <!-- section-content-right -->
                <div class="section-content-right">
                    <!-- header-dashboard -->
                    <?php $page_heading = "Wallet"; include __DIR__ . "/_partials/topbar.php"; ?>
                    <!-- main-content -->
                    <div class="main-content">
                        <!-- main-content-wrap -->
                        <div class="main-content-inner">
                            <!-- main-content-wrap -->
                            <div class="main-content-wrap">
                                <div class="tf-container">
                                    <!-- ============================================================
                                         ROW 1 · PORTFOLIO SUMMARY (hero) + LIFETIME ACTIVITY
                                         ============================================================ -->
                                    <div class="row mb-32">
                                        <!-- Hero balance -->
                                        <div class="col-lg-8 col-md-12 mb-24">
                                            <div class="wallet-card wallet-main wallet-hero">
                                                <div class="wallet-hero-top">
                                                    <div class="title-box flex items-center gap-2">
                                                        <i class="iconify ph ph-wallet"></i>
                                                        <span class="f12-medium text-White">Total Balance (USD)</span>
                                                    </div>
                                                    <span class="box-status bg-Green f12-medium flex items-center gap-2">
                                                        <i class="iconify ph ph-shield-check"></i> Active
                                                    </span>
                                                </div>

                                                <div class="wallet-hero-balance">
                                                    <h2 class="counter text-White">$<span id="total-balance">0.00</span></h2>
                                                    <div class="wallet-hero-change f14-regular">
                                                        <i class="iconify ph ph-trend-up"></i>
                                                        +$<span id="total-earnings">0.00</span> earned to date
                                                    </div>
                                                </div>

                                                <div class="wallet-hero-substats">
                                                    <div class="wallet-substat">
                                                        <div class="f12-regular">Invested across products</div>
                                                        <div class="f14-bold text-White">$<span id="wallet-total-invested">0.00</span></div>
                                                    </div>
                                                    <div class="wallet-substat">
                                                        <div class="f12-regular">Pending withdrawals</div>
                                                        <div class="f14-bold text-White">$<span id="pending-withdrawals">0.00</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Lifetime activity -->
                                        <div class="col-lg-4 col-md-12 mb-24">
                                            <div class="wg-box wallet-lifetime h-full">
                                                <div class="title mb-16">
                                                    <div class="label-01 text-Primary">Lifetime activity</div>
                                                </div>
                                                <ul class="wallet-stat-list">
                                                    <li>
                                                        <span class="wallet-stat-label f14-regular text-Gray">
                                                            <i class="iconify ph ph-arrow-down"></i> Total deposited
                                                        </span>
                                                        <span class="f14-bold text-Primary">$<span id="total-deposited">0.00</span></span>
                                                    </li>
                                                    <li>
                                                        <span class="wallet-stat-label f14-regular text-Gray">
                                                            <i class="iconify ph ph-arrow-up"></i> Total withdrawn
                                                        </span>
                                                        <span class="f14-bold text-Primary">$<span id="total-withdrawn">0.00</span></span>
                                                    </li>
                                                    <li>
                                                        <span class="wallet-stat-label f14-regular text-Gray">
                                                            <i class="iconify ph ph-trend-up"></i> Total earnings
                                                        </span>
                                                        <span class="f14-bold text-Green">$<span id="wallet-total-earnings">0.00</span></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ============================================================
                                         ROW 2 · PORTFOLIO ALLOCATION (where your money is)
                                         ============================================================ -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wg-box">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01 text-Primary">Your portfolio</div>
                                                    <a href="/dashboard.investment" class="view-all f12-regular text-Primary">
                                                        Manage <i class="iconify ph ph-caret-right ml-2"></i>
                                                    </a>
                                                </div>
                                                <ul class="wallet-alloc-list">
                                                    <li class="wallet-alloc-item">
                                                        <a href="/dashboard.investment">
                                                            <span class="wallet-alloc-icon"><i class="iconify ph ph-hammer"></i></span>
                                                            <span class="wallet-alloc-meta">
                                                                <span class="f14-bold text-Primary">Active Contracts</span>
                                                                <span class="f12-regular text-Gray">Daily-profit mining plans</span>
                                                            </span>
                                                            <span class="wallet-alloc-value f14-bold text-Primary">$<span id="total-investments">0.00</span></span>
                                                            <i class="iconify ph ph-caret-right wallet-alloc-arrow"></i>
                                                        </a>
                                                    </li>
                                                    <li class="wallet-alloc-item">
                                                        <a href="/dashboard.referral">
                                                            <span class="wallet-alloc-icon"><i class="iconify ph ph-user-plus"></i></span>
                                                            <span class="wallet-alloc-meta">
                                                                <span class="f14-bold text-Primary">Referral Earnings</span>
                                                                <span class="f12-regular text-Gray">10% commission on referrals</span>
                                                            </span>
                                                            <span class="wallet-alloc-value f14-bold text-Primary">$<span id="referral-earnings">0.00</span></span>
                                                            <i class="iconify ph ph-caret-right wallet-alloc-arrow"></i>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ============================================================
                                         ROW 3 · MOVE MONEY (deposit / withdraw)
                                         ============================================================ -->
                                    <div class="row mb-32">
                                        <!-- Deposit Section -->
                                        <div class="col-lg-6 col-md-12 mb-24" id="deposit-panel">
                                            <div class="wg-box deposit-form">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01 text-Primary flex items-center gap-2">
                                                        <i class="iconify ph ph-arrow-down" style="color: var(--Green);"></i>
                                                        Deposit Funds
                                                    </div>
                                                </div>
                                                <div class="content">
                                                    <form id="deposit-form" class="form-style-1">
                                                        <div class="mb-20 position-relative">
                                                            <label class="f14-regular text-Black mb-8">Deposit Amount (USD)</label>
                                                            <div class="input-group">
                                                                <span class="input-icon">$</span>
                                                                <input class="wallet-input form-control" type="number" placeholder="Enter amount" min="1" id="deposit-amount">
                                                            </div>
                                                        </div>
                                                        <div class="mb-20">
                                                            <label class="f14-regular text-Black mb-8">Payment Method</label>
                                                            <select class="form-select custom-select" id="deposit-method">
                                                                <option value="secure_exchange" selected>Secure Exchange</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="tf-button style-default w-full f14-bold bg-Green text-White hover:bg-Primary transition-colors duration-300">
                                                            Deposit Now
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Withdraw Section -->
                                        <div class="col-lg-6 col-md-12 mb-24" id="withdraw-panel">
                                            <div class="wg-box withdraw-form">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01 text-Primary flex items-center gap-2">
                                                        <i class="iconify ph ph-arrow-up" style="color: var(--Primary);"></i>
                                                        Withdraw Funds
                                                    </div>
                                                </div>
                                                <div class="content">
                                                    <form id="withdraw-form" class="form-style-1">
                                                        <div class="mb-20 position-relative">
                                                            <label class="f14-regular text-Black mb-8">Withdrawal Amount (USD)</label>
                                                            <div class="input-group">
                                                                <span class="input-icon">$</span>
                                                                <input class="wallet-input form-control" type="number" placeholder="Enter amount" min="1" id="withdraw-amount">
                                                            </div>
                                                        </div>
                                                        <div class="mb-20">
                                                            <label class="f14-regular text-Black mb-8">Withdrawal Method</label>
                                                            <select class="form-select custom-select" id="withdraw-method">
                                                                <option selected disabled>Select Method</option>
                                                                <option value="local_bank">Local Bank</option>
                                                                <option value="wallet_address">Wallet Address</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="tf-button style-default w-full f14-bold bg-Green text-White hover:bg-Primary transition-colors duration-300">
                                                            Withdraw Now
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ============================================================
                                         ROW 4 · WALLET ACTIVITY
                                         ============================================================ -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="wg-box">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01 text-Primary">Wallet Activity</div>
                                                    <div class="view-all">
                                                        <a href="/dashboard.transactions" class="f12-regular text-Primary">
                                                            View All
                                                            <i class="iconify ph ph-caret-right ml-2"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="content">
                                                    <ul class="list-wallet-activity" id="wallet-activity">
                                                        <!-- Dynamic: Loaded via JS -->
                                                    </ul>
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

    <!-- Loader -->
    <div id="loader" class="hidden">
        <div class="line-loader">
            <div></div><div></div><div></div><div></div><div></div>
        </div>
    </div>
    <!-- Toast Container -->
    <div id="toast-container"></div>

<!-- Withdrawal Modal -->
<div id="withdraw-modal" class="modal" role="dialog" aria-modal="true" aria-hidden="true" data-modal>
  <div class="modal-overlay" data-modal-close></div>
  <div class="modal-content" tabindex="-1" aria-labelledby="withdraw-modal-title">
    <header class="modal-header">
      <h2 id="withdraw-modal-title">Withdraw Funds</h2>
      <button class="modal-close" type="button" aria-label="Close modal" data-modal-close>&times;</button>
    </header>

    <div class="modal-body">
      <div class="withdrawal-method">
        <label>Selected Method: <span id="modal-method-name"></span></label>
      </div>

      <div class="form-group">
        <label>Amount to Withdraw</label>
        <input type="text" id="modal-withdraw-amount" readonly>
      </div>

      <!-- Local Bank Fields -->
      <div id="local-bank-fields" class="bank-form hidden" aria-hidden="true">
        <div class="form-group">
          <label for="modal-bank-country">Country</label>
          <select id="modal-bank-country">
            <option value="">Select Country</option>
            <option value="United States of America">United States of America</option>
            <option value="Germany">Germany</option>
            <option value="France">France</option>
            <option value="United Kingdom">United Kingdom</option>
            <option value="Italy">Italy</option>
            <option value="Spain">Spain</option>
            <option value="Netherlands">Netherlands</option>
            <option value="Sweden">Sweden</option>
            <option value="Switzerland">Switzerland</option>
            <option value="Poland">Poland</option>
            <option value="Austria">Austria</option>
            <option value="Greece">Greece</option>
            <option value="Portugal">Portugal</option>
            <option value="Norway">Norway</option>
            <option value="Denmark">Denmark</option>
            <option value="Belgium">Belgium</option>
            <option value="Finland">Finland</option>
            <option value="Ireland">Ireland</option>
            <option value="Czech Republic">Czech Republic</option>
            <option value="Hungary">Hungary</option>
            <option value="Ukraine">Ukraine</option>
          </select>
        </div>

        <div class="form-group">
          <label for="modal-bank-search">Bank</label>
          <div class="bank-search-container">
            <input type="text" id="modal-bank-search" placeholder="Search for a bank..." autocomplete="off">
            <div id="modal-bank-dropdown"></div>
            <input type="hidden" id="modal-bank-name">
          </div>
          <small class="form-error" id="modal-bank-name-error"></small>
        </div>

        <div class="form-group">
          <label for="modal-account-holder">Account Holder Name</label>
          <input type="text" id="modal-account-holder" placeholder="Full Name">
        </div>

        <div class="form-group">
          <label for="modal-iban">IBAN</label>
          <input type="text" id="modal-iban" placeholder="e.g., DE89370400440532013000">
        </div>

        <div class="form-group">
          <label for="modal-bic">BIC/SWIFT Code</label>
          <input type="text" id="modal-bic" placeholder="e.g., DEUTDEFFXXX">
        </div>

        <div class="form-group uk-only">
          <label for="modal-sort-code">Sort Code (UK only)</label>
          <input type="text" id="modal-sort-code" placeholder="e.g., 12-34-56">
        </div>

        <div class="form-group">
          <label for="modal-bank-currency">Currency</label>
          <select id="modal-bank-currency">
            <option value="EUR">EUR</option>
            <option value="USD">USD</option>
            <option value="GBP">GBP</option>
            <option value="CHF">CHF</option>
            <option value="SEK">SEK</option>
            <option value="PLN">PLN</option>
            <option value="CZK">CZK</option>
            <option value="HUF">HUF</option>
            <option value="NOK">NOK</option>
            <option value="DKK">DKK</option>
            <option value="UAH">UAH</option>
          </select>
        </div>

        <div class="form-group">
          <label for="modal-transaction-ref">Transaction Reference</label>
          <input type="text" id="modal-transaction-ref" placeholder="e.g., Withdrawal October 2025">
        </div>

        <small class="form-error" id="withdraw-general-error"></small>
        <p class="note">Local bank conversions are based on currency selected.</p>
      </div>

      <!-- Wallet Address Fields -->
      <div id="wallet-address-fields" class="hidden" aria-hidden="true">
        <div class="form-group">
          <label for="modal-coin">Select Coin</label>
          <select id="modal-coin">
            <option value="btc">Bitcoin (BTC)</option>
            <option value="eth">Ethereum (ETH)</option>
            <option value="usdt">USDT</option>
            <option value="usdc">USDC</option>
          </select>
        </div>

        <div class="form-group">
          <label for="modal-wallet-address">Wallet Address</label>
          <input type="text" id="modal-wallet-address" placeholder="Enter wallet address">
        </div>
      </div>

      <!-- Cash Mailing Fields -->
      <div id="cash-mailing-fields" class="hidden" aria-hidden="true">
        <div class="form-group">
          <label for="modal-cash-details">Cash Mailing Details</label>
          <textarea id="modal-cash-details" placeholder="Enter mailing address and instructions"></textarea>
        </div>
      </div>

      <button type="button" class="modal-confirm-btn" id="confirm-withdraw">
        Confirm Withdrawal
      </button>
    </div>
  </div>
</div>

    <!-- Pending Deposit Details Modal (Fixed & Enhanced Version) -->
    <div id="pending-actions-modal" class="modal" role="dialog" aria-modal="true" aria-hidden="true" data-modal>
        <div class="modal-overlay" data-modal-close></div>
        <div class="modal-content" tabindex="-1" aria-labelledby="pending-actions-title">
            <header class="modal-header">
                <h2 id="pending-actions-title">Pending Deposit Details</h2>
                <button class="modal-close" type="button" aria-label="Close modal" data-modal-close>&times;</button>
            </header>
            <div class="modal-body">
                <form id="pending-deposit-form" autocomplete="off">
                    <!-- Deposit Method -->
                    <div class="form-group">
                        <label for="pending-deposit-method">Deposit Method</label>
                        <select id="pending-deposit-method" name="deposit_method" required disabled> <!-- Disabled as it's loaded from data -->
                            <option value="" selected disabled>Select Method</option>
                            <option value="cash_mailing">Cash Mailing</option> 
                            <option value="wire_transfer">Wire Transfer</option>
                        </select>
                    </div>
                    <!-- Deposit Amount -->
                    <div class="form-group">
                        <label for="pending-deposit-amount">Amount to Deposit (USD)</label>
                        <input type="number" id="pending-deposit-amount" name="amount" placeholder="Enter amount" min="10" step="0.01" required disabled /> <!-- Disabled as it's loaded from data -->
                    </div>
                    <!-- Deposit Address / Details (dynamic) -->
                    <div class="form-group hidden" id="pending-deposit-address-group">
                        <label for="pending-deposit-address">Deposit Address / Details</label>
                        <div style="position: relative;">
                            <input type="text" id="pending-deposit-address" readonly />
                            <button type="button" class="copy-btn" data-target="pending-deposit-address" aria-label="Copy address"
                                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                                <i class="iconify ph ph-copy" style="color: var(--Primary); font-size:20px;"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Instruction Message -->
                    <div id="pending-deposit-instruction" class="note hidden"></div>
                </form>
                <!-- Confirm Button -->
                <button type="button" class="modal-confirm-btn" id="pending-confirm-paid-btn">I Have Paid</button>
            </div>
        </div>
    </div>




<!-- core libs: jquery then bootstrap -->
<script src="<?= txh_asset('../../assets/js/jquery.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/bootstrap.min.js') ?>"></script>

<!-- app/network layer (deferred) -->
<script src="<?= txh_asset('../../assets/js/api.js') ?>" defer></script>

<!-- plugins (deferred if they support it) -->
<script src="<?= txh_asset('../../assets/js/countto.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/bootstrap-select.min.js') ?>" defer></script>

<!-- main dashboard behaviour (deferred so it runs after DOM is parsed and after api.js) -->
<script src="<?= txh_asset('../../assets/js/dashboard.js') ?>" defer></script>

    <!-- Iconify CDN -->
</body>
</html>