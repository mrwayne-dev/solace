<?php
// pages/user/transactions.php

session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
]);

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$user_name = htmlspecialchars($_SESSION['full_name'] ?? 'User');
$user_id = $_SESSION['user_id'] ?? null;
$user_email = $_SESSION['email'] ?? null;
$user_role = $_SESSION['role'] ?? 'user';

// Placeholder for dynamic data
$transactions = [];
?>
<?php
  $page_title = "Transactions | Solace Mining";
  include __DIR__ . "/_partials/head.php";
?>

<body class="counter-scroll txh-dash">
<div id="wrapper">
    <div id="page">
        <div class="layout-wrap loader-off">

            <!-- Sidebar -->
            <?php $active = "transactions"; include __DIR__ . "/_partials/sidebar.php"; ?>

            <!-- Main Section -->
            <div class="section-content-right">

                <!-- Header -->
                <?php $page_heading = "Transactions"; include __DIR__ . "/_partials/topbar.php"; ?>

                <!-- Main Content -->
                <div class="main-content">
                    <div class="main-content-inner">
                        <div class="main-content-wrap">
                            <div class="tf-container">

                                <div class="topbar-search">
                                    <form class="form-search flex-grow">
                                        <fieldset class="name">
                                            <input type="text" placeholder="Search transactions" class="show-search style-1" required>
                                        </fieldset>
                                        <div class="button-submit">
                                            <button type="submit"><i class="icon-search-normal1"></i></button>
                                        </div>
                                    </form>
                                    <div class="right">
                                        <a href="#" class="tf-button style-2 f12-bold d-md-flex d-none">
                                            <i class="icon icon-receive-square"></i>
                                            Export Report
                                        </a>
                                        <div class="dropdown default style-fill">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="icon icon-setting-5"></i> Filter
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a href="#">All</a></li>
                                                <li><a href="#">Completed</a></li>
                                                <li><a href="#">Pending</a></li>
                                                <li><a href="#">Failed</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction Table (real table, horizontal scroll on mobile) -->
                                <div class="txh-scroll-table mt-3">
                                    <table class="txh-table">
                                        <thead>
                                            <tr>
                                                <th>Transaction ID</th>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Amount (USD)</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="transactionList">
                                            <!-- Js will populate -->
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /table -->
                                 <div id="pagination" class="pagination mt-3 flex gap-2 justify-center"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /main-content -->
            </div>
        </div>
    </div>
</div>

<script src="<?= txh_asset('../../assets/js/api.js') ?>"></script> <!-- Or move after jQuery if dependent -->
<script src="<?= txh_asset('../../assets/js/jquery.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/bootstrap.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/dashboard.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/transaction.js') ?>" defer></script>
</body>
</html>
