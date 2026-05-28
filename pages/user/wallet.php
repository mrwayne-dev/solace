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
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta -->
    <meta charset="UTF-8">
    <meta name="description" content="HealthRunCare Wallet - Manage your contributions, donations, and investments securely.">
    <meta name="author" content="HealthRunCare">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://healthruncare.com/wallet">
    <title>HealthRunCare Wallet</title>

   <!-- Preload + Apply (critical CSS) -->
    <link rel="preload" href="../../assets/css/bootstrap.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/css/dashboard.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../../assets/icon/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">

    <!-- Load Non-critical CSS Normally -->
    <link rel="stylesheet" href="../../assets/css/animation.min.css">
    <link rel="stylesheet" href="../../assets/css/animation.css">
    <link rel="stylesheet" href="../../assets/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../../assets/fonts/font.css">
    <link rel="stylesheet" href="../../assets/icon/style.css">

    <!-- Fallback for browsers without preload support -->
    <noscript>
    <link rel="stylesheet" href="../../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    </noscript>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="../../assets/favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="../../assets/favicon/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="HRC">
    <link rel="manifest" href="../../assets/favicon/site.webmanifest">
</head>

<body class="counter-scroll">

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
                <div class="section-menu-left">
                    <div class="box-logo">
                        <a href="/dashboard" id="site-logo-inner">
                            <img class="" id="logo_header" alt="HRC" src="/assets/images/healthruncarelogo.png" width="150px">
                        </a>
                        <div class="button-show-hide">
                            <span class="iconify" data-icon="mdi:chevron-left"></span>
                        </div>
                    </div>
                    <div class="section-menu-left-wrap">
                        <div class="center">
                            <div class="center-item">
                                <div class="center-heading f14-regular text-Gray menu-heading mb-12">Navigation</div>
                            </div>
                            <div class="center-item">
                                <ul class="">
                                    <li class="menu-item has-children">
                                        <a href="javascript:void(0);" class="menu-item-button">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:view-dashboard-outline"></span>
                                            </div>
                                            <div class="text">Dashboard</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item">
                                                <a href="/dashboard" class="">
                                                    <div class="text">Overview</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="menu-item active has-children">
                                        <a href="javascript:void(0);" class="menu-item-button active">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:wallet-outline"></span>
                                            </div>
                                            <div class="text">My Wallet</div>
                                        </a>
                                        <ul class="sub-menu">
                                            <li class="sub-menu-item active">
                                                <a href="/dashboard.wallet" class="">
                                                    <div class="text">Wallet</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.transactions" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:receipt-text-outline"></span></div>
                                            <div class="text">Transaction</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.charity" class="menu-item-button">
                                            <div class="icon">
                                                <span class="iconify" data-icon="mdi:heart-outline"></span>
                                            </div>
                                            <div class="text">Charity</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.investment" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:chart-timeline-variant"></span></div>
                                            <div class="text">Investments</div>
                                        </a>
                                    </li>
                                     <li class="menu-item">
                                        <a href="/dashboard.holdlock" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:lock-outline"></span></div>
                                            <div class="text">Holdlock</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.trustfund" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:account-cash-outline"></span></div>
                                            <div class="text">Trustfund</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.infrastructure" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:office-building-outline"></span></div>
                                            <div class="text">Infrastructure</div>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="/dashboard.development" class="menu-item-button">
                                            <div class="icon"><span class="iconify" data-icon="mdi:tools"></span></div>
                                            <div class="text">Maintenance Dev</div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /section-menu-left -->
                <!-- section-content-right -->
                <div class="section-content-right">
                    <!-- header-dashboard -->
                    <div class="header-dashboard">
                        <div class="wrap">
                            <div class="header-left">
                                <div class="button-show-hide">
                                    <i class="icon-menu"></i>
                                </div>
                                <h6>Wallet</h6>
                            </div>
                            <div class="header-grid">
                                <div class="line1"></div>
                                <div class="popup-wrap user type-header">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="header-user wg-user">
                                                <span class="image">
                                                    <img src="<?= htmlspecialchars($_SESSION['profile_picture'] ?? '/assets/images/avatar/default.png') ?>" alt="">
                                                </span>
                                                <span class="content flex flex-column">
                                                    <span class="label-02 text-Black name"><?= $user_name ?></span>
                                                    <span class="f14-regular text-Gray">User</span>
                                                </span>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end has-content" aria-labelledby="dropdownMenuButton3" >
                                            <li>
                                                <a href="#" id="pending-deposits-btn" class="user-item">
                                                    <div class="body-title-2">
                                                        Pending Actions <span id="pending-count" class="text-Primary"></span>
                                                    </div>
                                                </a>
                                                </li>
                                            <li>
                                                <a href="/dashboard.transactions" class="user-item">
                                                    <div class="body-title-2">Transactions</div>
                                                </a>
                                            </li>
                                           <li>
                                            <a href="#" id="logout-btn" class="user-item">
                                                <div class="body-title-2">Log out</div>
                                            </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /header-dashboard -->
                    <!-- main-content -->
                    <div class="main-content">
                        <!-- main-content-wrap -->
                        <div class="main-content-inner">
                            <!-- main-content-wrap -->
                            <div class="main-content-wrap">
                                <div class="tf-container">
                                    <!-- Wallet Balance Summary Section -->
                                    <div class="row mb-32">
                                        <div class="col-lg-3 col-md-6 mb-24">
                                            <div class="wg-card style-1 bg-Primary">
                                                <div class="title-box">
                                                    <span class="iconify" data-icon="mdi:wallet" style="color: var(--White);"></span>
                                                    <div class="f12-medium text-White">Total Balance (USD)</div>
                                                </div>
                                                <div class="content">
                                                    <h6 class="counter text-White">$<span id="total-balance">0.00</span></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-24">
                                            <div class="wg-card style-1 bg-Primary">
                                                <div class="title-box">
                                                    <span class="iconify" data-icon="mdi:clock-outline" style="color: var(--White);"></span>
                                                    <div class="f12-medium text-White">Pending Withdrawals</div>
                                                </div>
                                                <div class="content">
                                                    <h6 class="counter text-White"><span id="pending-withdrawals">0</span></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-24">
                                            <div class="wg-card style-1 bg-Primary">
                                                <div class="title-box">
                                                    <span class="iconify" data-icon="mdi:arrow-down-bold" style="color: var(--White);"></span>
                                                    <div class="f12-medium text-White">Total Deposited</div>
                                                </div>
                                                <div class="content">
                                                    <h6 class="counter text-White">$<span id="total-deposited">0.00</span></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-24">
                                            <div class="wg-card style-1 bg-Primary">
                                                <div class="title-box">
                                                    <span class="iconify" data-icon="mdi:arrow-up-bold" style="color: var(--White);"></span>
                                                    <div class="f12-medium text-White">Total Withdrawn</div>
                                                </div>
                                                <div class="content">
                                                    <h6 class="counter text-White">$<span id="total-withdrawn">0.00</span></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-24">
                                            <div class="wg-card style-1 bg-Primary">
                                                <div class="title-box">
                                                    <span class="iconify" data-icon="mdi:heart-outline" style="color: var(--White);"></span>
                                                    <div class="f12-medium text-White">Total Donations</div>
                                                </div>
                                                <div class="content">
                                                    <h6 class="counter text-White">$<span id="total-donations">0.00</span></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-24">
                                            <div class="wg-card style-1 bg-Primary">
                                                <div class="title-box">
                                                    <span class="iconify" data-icon="mdi:swap-horizontal" style="color: var(--White);"></span>
                                                    <div class="f12-medium text-White">Total Investments</div>
                                                </div>
                                                <div class="content">
                                                    <h6 class="counter text-White">$<span id="total-investments">0.00</span></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-24">
                                            <div class="wg-card style-1 bg-Primary">
                                                <div class="title-box">
                                                    <span class="iconify" data-icon="mdi:lock-outline" style="color: var(--White);"></span>
                                                    <div class="f12-medium text-White">Holdlock Savings</div>
                                                </div>
                                                <div class="content">
                                                    <h6 class="counter text-White">$<span id="holdlock-savings">0.00</span></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-24">
                                            <div class="wg-card style-1 bg-Primary">
                                                <div class="title-box">
                                                    <span class="iconify" data-icon="mdi:trending-up" style="color: var(--White);"></span>
                                                    <div class="f12-medium text-White">Total Earnings</div>
                                                </div>
                                                <div class="content">
                                                    <h6 class="counter text-White">$<span id="total-earnings">0.00</span></h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deposit and Withdraw Sections -->
                                    <div class="row mb-32">
                                        <!-- Deposit Section -->
                                        <div class="col-lg-6 col-md-12 mb-24">
                                            <div class="wg-box deposit-form">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01 text-Primary flex items-center gap-2">
                                                        <span class="iconify" data-icon="mdi:arrow-down-bold" style="color: var(--Green);"></span>
                                                        Deposit Funds
                                                    </div>
                                                </div>

                                                <div class="content">
                                                    <form id="deposit-form" class="form-style-1">
                                                        <!-- Amount -->
                                                        <div class="mb-20 position-relative">
                                                            <label class="f14-regular text-Black mb-8">Deposit Amount (USD)</label>
                                                            <div class="input-group">
                                                                <span class="input-icon">$</span>
                                                                <input class="wallet-input form-control" type="number" placeholder="Enter amount" min="1" id="deposit-amount">
                                                            </div>
                                                        </div>

                                                        <!-- Payment Method -->
                                                        <div class="mb-20">
                                                            <label class="f14-regular text-Black mb-8">Payment Method</label>
                                                            <select class="form-select custom-select" id="deposit-method">
                                                                <option selected disabled>Select Method</option>
                                                                <option value="secure_exchange">Secure Exchange</option>
                                                                <option value="cash_mailing">Cash Mailing</option>
                                                                <option value="wire_transfer">Wire Transfer</option>
                                                            </select>
                                                        </div>

                                                        <!-- CTA -->
                                                        <button type="submit" class="tf-button style-default w-full f14-bold bg-Green text-White hover:bg-Primary transition-colors duration-300">
                                                            Deposit Now
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Withdraw Section -->
                                        <div class="col-lg-6 col-md-12 mb-24">
                                            <div class="wg-box withdraw-form">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01 text-Primary flex items-center gap-2">
                                                        <span class="iconify" data-icon="mdi:arrow-up-bold" style="color: var(--Primary);"></span>
                                                        Withdraw Funds
                                                    </div>
                                                </div>

                                                <div class="content">
                                                    <form id="withdraw-form" class="form-style-1">
                                                        <!-- Amount -->
                                                        <div class="mb-20 position-relative">
                                                            <label class="f14-regular text-Black mb-8">Withdrawal Amount (USD)</label>
                                                            <div class="input-group">
                                                                <span class="input-icon">$</span>
                                                                <input class="wallet-input form-control" type="number" placeholder="Enter amount" min="1" id="withdraw-amount">
                                                            </div>
                                                        </div>

                                                        <!-- Withdrawal Method -->
                                                        <div class="mb-20">
                                                            <label class="f14-regular text-Black mb-8">Withdrawal Method</label>
                                                            <select class="form-select custom-select" id="withdraw-method">
                                                                <option selected disabled>Select Method</option>
                                                                <option value="local_bank">Local Bank</option>
                                                                <option value="wallet_address">Wallet Address</option>
                                                                <option value="cash_mailing">Cash Mailing</option>
                                                            </select>
                                                        </div>

                                                        <!-- CTA -->
                                                        <button type="submit" class="tf-button style-default w-full f14-bold bg-Green text-White hover:bg-Primary transition-colors duration-300">
                                                            Withdraw Now
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Wallet Activity Section -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="wg-box">
                                                <div class="title mb-16 flex justify-between items-center">
                                                    <div class="label-01">Wallet Activity</div>
                                                    <div class="view-all">
                                                        <a href="/dashboard.transactions" class="f12-regular text-Primary">
                                                            View All
                                                            <span class="iconify ml-2" data-icon="mdi:chevron-right"></span>
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
                                <span class="iconify" data-icon="mdi:content-copy" data-width="20" data-height="20" style="color: var(--Primary);"></span>
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
<script src="../../assets/js/jquery.min.js"></script>
<script src="../../assets/js/bootstrap.min.js"></script>

<!-- app/network layer (deferred) -->
<script src="../../assets/js/api.js" defer></script>

<!-- plugins (deferred if they support it) -->
<script src="../../assets/js/countto.js" defer></script>
<script src="../../assets/js/bootstrap-select.min.js" defer></script>

<!-- main dashboard behaviour (deferred so it runs after DOM is parsed and after api.js) -->
<script src="../../assets/js/dashboard.js" defer></script>

    <!-- Iconify CDN -->
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>