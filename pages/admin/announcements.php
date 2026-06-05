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
  $page_title = "Announcements | TitanXHoldings Admin";
  include __DIR__ . "/_partials/head.php";
?>
<body class="counter-scroll txh-dash">
<div id="wrapper">
    <div id="page" class="">
        <div class="layout-wrap loader-off">
            <div id="preload" class="preload-container">
                <div class="preloading"><span></span></div>
            </div>

            <!-- Sidebar -->
            <?php $active = "announcements"; include __DIR__ . "/_partials/sidebar.php"; ?>
            <!-- /Sidebar -->

            <!-- Main Content -->
            <div class="section-content-right">
                <?php $page_heading = "Announcements"; include __DIR__ . "/_partials/topbar.php"; ?>

                <div class="main-content">
                    <div class="main-content-inner">
                        <div class="main-content-wrap">
                            <div class="tf-container">

                                <div class="mb-32">
                                    <div class="d-flex justify-between items-center mb-16">
                                        <h5 class="label-01">Member Announcements</h5>
                                        <button id="add-announcement-btn" class="tf-button bg-Primary text-White f12-bold">
                                            <span class="iconify" data-icon="mdi:plus"></span> New Announcement
                                        </button>
                                    </div>

                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Title</div>
                                            <div class="f12-bold text-White">Category</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Published</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="announcements-body">
                                                <tr><td colspan="5" class="text-center text-Primary f14-regular">Loading announcements...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add/Edit Announcement Modal -->
                <div class="modal" id="announcement-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content" style="max-width: 700px;">
                        <div class="modal-header">
                            <h2 id="announcement-title">New Announcement</h2>
                            <button class="button-close-modal">×</button>
                        </div>
                        <div class="modal-body">
                            <form id="announcement-form">
                                <input type="hidden" id="announcement-id">
                                <div class="form-group mb-3">
                                    <label>Title <span class="text-Red">*</span></label>
                                    <input type="text" class="form-control" id="announcement-title-input" maxlength="255" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Body <span class="text-Red">*</span></label>
                                    <textarea class="form-control" id="announcement-body" rows="6" required></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Category</label>
                                            <select class="form-control" id="announcement-category">
                                                <option value="general">General</option>
                                                <option value="product">Product Update</option>
                                                <option value="maintenance">Maintenance</option>
                                                <option value="regulatory">Regulatory</option>
                                                <option value="security">Security</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Status</label>
                                            <select class="form-control" id="announcement-status">
                                                <option value="published">Published</option>
                                                <option value="draft">Draft</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                    <button type="submit" class="modal-confirm-btn">Save Announcement</button>
                                </div>
                            </form>
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

<script src="<?= txh_asset('../../assets/js/api.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/jquery.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/bootstrap.min.js') ?>"></script>
<script src="<?= txh_asset('../../assets/js/bootstrap-select.min.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/admin/admin.js') ?>" defer></script>
<script src="<?= txh_asset('../../assets/js/admin/announcements.js') ?>" defer></script>
<script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</body>
</html>
