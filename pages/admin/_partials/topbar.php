<?php
// ============================================================
// ADMIN TOPBAR partial
// Expects $admin_name in scope. Pass $page_heading for the title.
// ============================================================
$page_heading = $page_heading ?? 'Admin';
$admin_name = $admin_name ?? 'Administrator';
?>
<div class="header-dashboard">
    <div class="wrap">
        <div class="header-left">
            <div class="button-show-hide"><i class="icon-menu"></i></div>
            <h6><?= htmlspecialchars($page_heading) ?></h6>
        </div>
        <div class="header-grid">
            <div class="line1"></div>
            <div class="popup-wrap user type-header">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="header-user wg-user">
                            <span class="image"><img src="/assets/images/avatar/default.png" alt="Admin Avatar"></span>
                            <span class="content flex flex-column">
                                <span class="label-02 text-Black name"><?= htmlspecialchars($admin_name) ?></span>
                                <span class="f14-regular text-Gray">Admin</span>
                            </span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end has-content" aria-labelledby="dropdownMenuButton3">
                        <li><a href="/admin/profile" class="user-item"><div class="body-title-2">Profile</div></a></li>
                        <li><a href="#" id="logout-btn" class="user-item"><div class="body-title-2">Log out</div></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
