<?php
// ============================================================
// ADMIN SIDEBAR partial
// $active      = dashboard | users | transactions | wallets |
//                announcements | funds
// $active_fund = (when $active === 'funds') yield | xlock |
//                xweekly | xgrid | xshares | xrewards
// ============================================================
$active = $active ?? 'dashboard';
$active_fund = $active_fund ?? '';
$is = static fn(string $s) => $active === $s ? ' active' : '';
$isFund = static fn(string $s) => $active_fund === $s ? ' active' : '';
$fundsOpen = ($active === 'funds') ? ' active' : '';
?>
<div class="section-menu-left">
    <div class="box-logo">
        <a href="/admin.dashboard" id="site-logo-inner">
            <img id="logo_header" alt="TitanXHoldings Admin" src="/assets/images/logo/titanx-white.png" width="150px">
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
                <ul>
                    <!-- DASHBOARD -->
                    <li class="menu-item has-children<?= $is('dashboard') ?>">
                        <a href="javascript:void(0);" class="menu-item-button<?= $is('dashboard') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:view-dashboard-outline"></span></div>
                            <div class="text">Dashboard</div>
                        </a>
                        <ul class="sub-menu">
                            <li class="sub-menu-item<?= $is('dashboard') ?>"><a href="/admin.dashboard"><div class="text">Overview</div></a></li>
                        </ul>
                    </li>

                    <!-- USERS -->
                    <li class="menu-item<?= $is('users') ?>">
                        <a href="/admin.users" class="menu-item-button<?= $is('users') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:account-group-outline"></span></div>
                            <div class="text">Users</div>
                        </a>
                    </li>

                    <!-- TRANSACTIONS -->
                    <li class="menu-item<?= $is('transactions') ?>">
                        <a href="/admin.transactions" class="menu-item-button<?= $is('transactions') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:receipt-text"></span></div>
                            <div class="text">Transactions</div>
                        </a>
                    </li>

                    <!-- WALLET MANAGEMENT -->
                    <li class="menu-item<?= $is('wallets') ?>">
                        <a href="/admin.wallets" class="menu-item-button<?= $is('wallets') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:wallet-outline"></span></div>
                            <div class="text">Wallet Management</div>
                        </a>
                    </li>

                    <!-- ANNOUNCEMENTS -->
                    <li class="menu-item<?= $is('announcements') ?>">
                        <a href="/admin.announcements" class="menu-item-button<?= $is('announcements') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:bullhorn-outline"></span></div>
                            <div class="text">Announcements</div>
                        </a>
                    </li>

                    <!-- FUND MANAGEMENT -->
                    <li class="menu-item has-children<?= $fundsOpen ?>">
                        <a href="javascript:void(0);" class="menu-item-button<?= $fundsOpen ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:money"></span></div>
                            <div class="text">Fund Management</div>
                        </a>
                        <ul class="sub-menu">
                            <li class="sub-menu-item<?= $isFund('yield') ?>"><a href="/admin.funds"><div class="text">X-Yield</div></a></li>
                            <li class="sub-menu-item<?= $isFund('xlock') ?>"><a href="/admin.funds/xlock"><div class="text">X-Lock</div></a></li>
                            <li class="sub-menu-item<?= $isFund('xweekly') ?>"><a href="/admin.funds/xweekly"><div class="text">X-Weekly</div></a></li>
                            <li class="sub-menu-item<?= $isFund('xgrid') ?>"><a href="/admin.funds/xgrid"><div class="text">X-Grid</div></a></li>
                            <li class="sub-menu-item<?= $isFund('xshares') ?>"><a href="/admin.funds/xshares"><div class="text">X-Shares</div></a></li>
                            <li class="sub-menu-item<?= $isFund('xrewards') ?>"><a href="/admin.funds/xrewards"><div class="text">X-Rewards</div></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
