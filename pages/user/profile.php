<?php
// pages/user/profile.php

session_start([
    'cookie_lifetime' => 86400,
    'cookie_httponly' => true,
    'cookie_secure'   => true,
    'cookie_samesite' => 'Strict',
]);

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$user_name = htmlspecialchars($_SESSION['full_name'] ?? 'User');
$user_id   = $_SESSION['user_id'] ?? null;
$avatar    = htmlspecialchars($_SESSION['profile_picture'] ?? '/assets/images/avatar/default.png');
?>
<?php
  $page_title = "Profile | TitanXHoldings";
  include __DIR__ . "/_partials/head.php";
?>

<body class="counter-scroll txh-dash">
    <div id="wrapper">
        <div id="page" class="">
            <div class="layout-wrap loader-off">
                <div id="preload" class="preload-container">
                    <div class="preloading"><span></span></div>
                </div>

                <?php $active = "profile"; include __DIR__ . "/_partials/sidebar.php"; ?>

                <div class="section-content-right">
                    <?php $page_heading = "Profile"; include __DIR__ . "/_partials/topbar.php"; ?>

                    <div class="main-content">
                        <div class="main-content-inner">
                            <div class="main-content-wrap">
                                <div class="tf-container">

                                    <!-- ROW 1: Avatar + Details -->
                                    <div class="row mb-32">
                                        <!-- Avatar card -->
                                        <div class="col-lg-4 col-md-12 mb-24">
                                            <div class="wg-box profile-avatar-card">
                                                <div class="title mb-16">
                                                    <div class="label-01 text-Primary">Profile photo</div>
                                                </div>
                                                <div class="profile-avatar-wrap">
                                                    <img id="avatar-preview" src="<?= $avatar ?>" alt="Profile photo"
                                                         onerror="this.src='/assets/images/avatar/default.png';">
                                                </div>
                                                <form id="avatar-form" enctype="multipart/form-data">
                                                    <input type="file" id="avatar-input" name="avatar" accept="image/png,image/jpeg,image/webp" hidden>
                                                    <div class="profile-avatar-actions">
                                                        <button type="button" id="avatar-choose" class="tf-button bg-Accent f14-bold w-full">
                                                            <span class="iconify" data-icon="mdi:image-edit-outline"></span> Choose photo
                                                        </button>
                                                        <button type="submit" id="avatar-upload" class="tf-button bg-Primary text-White f14-bold w-full" disabled>
                                                            Upload photo
                                                        </button>
                                                    </div>
                                                    <p class="f12-regular text-Gray profile-avatar-hint">PNG, JPG or WEBP. Max 2&nbsp;MB.</p>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Details form -->
                                        <div class="col-lg-8 col-md-12 mb-24">
                                            <div class="wg-box">
                                                <div class="title mb-16">
                                                    <div class="label-01 text-Primary">Personal details</div>
                                                </div>
                                                <div class="content">
                                                    <form id="profile-form" class="form-style-1" autocomplete="off">
                                                        <div class="row">
                                                            <div class="col-md-12 mb-20">
                                                                <label class="f14-regular text-Black mb-8">Full name</label>
                                                                <input class="form-control" type="text" id="pf-full-name" placeholder="Your full name">
                                                            </div>
                                                            <div class="col-md-6 mb-20">
                                                                <label class="f14-regular text-Black mb-8">Email address</label>
                                                                <input class="form-control" type="email" id="pf-email" placeholder="you@example.com">
                                                            </div>
                                                            <div class="col-md-6 mb-20">
                                                                <label class="f14-regular text-Black mb-8">Phone</label>
                                                                <input class="form-control" type="text" id="pf-phone" placeholder="+44 …">
                                                            </div>
                                                            <div class="col-md-6 mb-20">
                                                                <label class="f14-regular text-Black mb-8">Country</label>
                                                                <input class="form-control" type="text" id="pf-country" placeholder="United Kingdom">
                                                            </div>
                                                            <div class="col-md-6 mb-20">
                                                                <label class="f14-regular text-Black mb-8">Address</label>
                                                                <input class="form-control" type="text" id="pf-address" placeholder="Street, city, postcode">
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="tf-button bg-Primary text-White f14-bold" id="profile-save-btn">
                                                            Save changes
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ROW 2: Change password -->
                                    <div class="row mb-32">
                                        <div class="col-lg-8 col-md-12">
                                            <div class="wg-box">
                                                <div class="title mb-16">
                                                    <div class="label-01 text-Primary">Change password</div>
                                                </div>
                                                <div class="content">
                                                    <form id="password-form" class="form-style-1" autocomplete="off">
                                                        <div class="row">
                                                            <div class="col-md-12 mb-20">
                                                                <label class="f14-regular text-Black mb-8">Current password</label>
                                                                <input class="form-control" type="password" id="pf-current-password" placeholder="••••••••">
                                                            </div>
                                                            <div class="col-md-6 mb-20">
                                                                <label class="f14-regular text-Black mb-8">New password</label>
                                                                <input class="form-control" type="password" id="pf-new-password" placeholder="At least 8 characters">
                                                            </div>
                                                            <div class="col-md-6 mb-20">
                                                                <label class="f14-regular text-Black mb-8">Confirm new password</label>
                                                                <input class="form-control" type="password" id="pf-confirm-password" placeholder="Re-enter new password">
                                                            </div>
                                                        </div>
                                                        <button type="submit" class="tf-button bg-Primary text-White f14-bold" id="password-save-btn">
                                                            Update password
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="loader" class="hidden">
        <div class="line-loader"><div></div><div></div><div></div><div></div><div></div></div>
    </div>
    <div id="toast-container"></div>

    <script src="<?= txh_asset('../../assets/js/jquery.min.js') ?>"></script>
    <script src="<?= txh_asset('../../assets/js/bootstrap.min.js') ?>"></script>
    <script src="<?= txh_asset('../../assets/js/api.js') ?>" defer></script>
    <script src="<?= txh_asset('../../assets/js/dashboard.js') ?>" defer></script>
    <script src="<?= txh_asset('../../assets/js/profile.js') ?>" defer></script>
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>
