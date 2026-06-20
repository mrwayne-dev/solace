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
  $page_title = "Users | Solace Mining Admin";
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
                <?php $active = "users"; include __DIR__ . "/_partials/sidebar.php"; ?>
                <!-- /Sidebar -->

                <!-- Main Content -->
                <div class="section-content-right">
                    <!-- Header -->
                    <?php $page_heading = "Users"; include __DIR__ . "/_partials/topbar.php"; ?>
                    <!-- /Header -->

                    <!-- Main Content -->
                    <div class="main-content">
                        <div class="main-content-inner">
                            <div class="main-content-wrap">
                                <div class="tf-container">

                                    <!-- USER STATS CARDS -->
                                    <div class="row mb-32">
                                        <div class="col-12">
                                            <div class="wallet-cards grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap20">
                                                <!-- Total Users -->
                                                <div class="wallet-card wallet-main">
                                                    <div class="wallet-card-header">Total Users</div>
                                                    <div class="wallet-card-balance"><span id="total-users">0</span></div>
                                                    <div class="wallet-card-footer"><i class="iconify ph ph-users"></i>
                                                        Registered
                                                    </div>
                                                </div>  
                                                <!-- Active Users -->
                                                <div class="wallet-card wallet-green">
                                                    <div class="wallet-card-header">Active Users</div>
                                                    <div class="wallet-card-balance"><span id="active-users">0</span></div>
                                                    <div class="wallet-card-footer"><i class="iconify ph ph-user"></i>
                                                        Online Now
                                                    </div>
                                                </div>
                                                <!-- Admins -->
                                                <div class="wallet-card wallet-accent">
                                                    <div class="wallet-card-header">Admins</div>
                                                    <div class="wallet-card-balance"><span id="admin-count">0</span></div>
                                                    <div class="wallet-card-footer"><i class="iconify ph ph-shield"></i>
                                                        Privileged
                                                    </div>
                                                </div>
                                                <!-- New Today -->
                                                <div class="wallet-card wallet-purple">
                                                    <div class="wallet-card-header">New Today</div>
                                                    <div class="wallet-card-balance"><span id="new-today">0</span></div>
                                                    <div class="wallet-card-footer"><i class="iconify ph ph-user-plus"></i>
                                                        Joined Today
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SEARCH + FILTERS -->
                                    <div class="topbar-search mb-24">
                                        <form class="form-search flex-grow">
                                            <fieldset class="name">
                                                <input type="text" id="user-search" placeholder="Search by name, email, or ID..." class="show-search style-1">
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
                                                    <li><a href="#" data-filter="all">All Users</a></li>
                                                    <li><a href = "#" data-filter="active">Active Only</a></li>
                                                    <li><a href="#" data-filter="suspended">Suspended</a></li>
                                                    <li><a href="#" data-filter="admin">Admins Only</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- USERS TABLE -->
                                    <div class="table-list-transaction">
                                        <div class="list-transaction-head title-sort bg-Primary">
                                            <div class="f12-bold text-White">Name</div>
                                            <div class="f12-bold text-White">Email</div>
                                            <div class="f12-bold text-White">Role</div>
                                            <div class="f12-bold text-White">Status</div>
                                            <div class="f12-bold text-White">Last Login</div>
                                            <div class="f12-bold text-White">Actions</div>
                                        </div>
                                        <table class="list-transaction-content content-sort w-100">
                                            <tbody id="users-table-body">
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

                    <!-- MODALS -->

                    <!-- Edit User Modal -->
                    <div class="modal" id="edit-user-modal">
                        <div class="modal-overlay"></div>
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Edit User</h2>
                                <button class="button-close-modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="edit-user-form">
                                    <input type="hidden" id="edit-user-id">
                                    <div class="form-group mb-3">
                                        <label>Name</label>
                                        <input type="text" class="form-control" id="edit-name" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Email</label>
                                        <input type="email" class="form-control" id="edit-email" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Role</label>
                                        <select class="form-control" id="edit-role">
                                            <option value="user">User</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Status</label>
                                        <select class="form-control" id="edit-status">
                                            <option value="active">Active</option>
                                            <option value="suspended">Suspended</option>
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

                    <!-- Send Email Modal -->
                    <div class="modal" id="send-email-modal">
                        <div class="modal-overlay"></div>
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Send Email</h2>
                                <button class="button-close-modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="send-email-form">
                                    <input type="hidden" id="email-user-id">
                                    <div class="form-group mb-3">
                                        <label>To</label>
                                        <input type="text" class="form-control" id="email-to" disabled>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Subject</label>
                                        <input type="text" class="form-control" id="email-subject" required>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Message</label>
                                        <textarea class="form-control" id="email-body" rows="5" required></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                        <button type="submit" class="modal-confirm-btn">Send Email</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div class="modal" id="delete-user-modal">
                        <div class="modal-overlay"></div>
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Delete User</h2>
                                <button class="button-close-modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete <strong id="delete-user-name"></strong>?</p>
                                <p class="text-Gray f14-regular">This action cannot be undone.</p>
                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button type="button" class="button-close-modal tf-button bg-GrayLight text-Black">Cancel</button>
                                    <button type="button" id="confirm-delete" class="modal-confirm-btn bg-Red text-White">Delete User</button>
                                </div>
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
    <script src="<?= txh_asset('../../assets/js/countto.js') ?>" defer></script>
    <script src="<?= txh_asset('../../assets/js/bootstrap-select.min.js') ?>" defer></script>
    <script src="<?= txh_asset('../../assets/js/admin/admin.js') ?>" defer></script>
    <script src="<?= txh_asset('../../assets/js/admin/users.js') ?>" defer></script>
    <script src="/assets/vendor/chartjs/chart.umd.min.js"></script>
</body>
</html>