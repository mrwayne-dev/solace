<?php
// ============================================================
// USER DASHBOARD SIDEBAR partial
// Pass $active = one of:
//   dashboard | wallet | transactions | investment |
//   xlock | xweekly | xshares | xgrid | xrewards
// ============================================================
$active = $active ?? 'dashboard';
$is = static fn(string $slug) => $active === $slug ? ' active' : '';
?>
<div class="section-menu-left">
    <div class="box-logo">
        <a href="/dashboard" id="site-logo-inner">
            <img class="" id="logo_header" alt="TitanXHoldings" src="/assets/images/logo/titanx-white.png" width="150px">
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
                    <li class="menu-item<?= $is('dashboard') ?>">
                        <a href="/dashboard" class="menu-item-button<?= $is('dashboard') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:view-dashboard-outline"></span></div>
                            <div class="text">Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('wallet') ?>">
                        <a href="/dashboard.wallet" class="menu-item-button<?= $is('wallet') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:wallet-outline"></span></div>
                            <div class="text">My Wallet</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('transactions') ?>">
                        <a href="/dashboard.transactions" class="menu-item-button<?= $is('transactions') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:receipt-text-outline"></span></div>
                            <div class="text">Transaction</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('investment') ?>">
                        <a href="/dashboard.investment" class="menu-item-button<?= $is('investment') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:chart-timeline-variant"></span></div>
                            <div class="text">X-Yield</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('xlock') ?>">
                        <a href="/dashboard.xlock" class="menu-item-button<?= $is('xlock') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:lock-outline"></span></div>
                            <div class="text">X-Lock</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('xweekly') ?>">
                        <a href="/dashboard.xweekly" class="menu-item-button<?= $is('xweekly') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:calendar-refresh-outline"></span></div>
                            <div class="text">X-Weekly</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('xshares') ?>">
                        <a href="/dashboard.xshares" class="menu-item-button<?= $is('xshares') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:chart-pie-outline"></span></div>
                            <div class="text">X-Shares</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('xgrid') ?>">
                        <a href="/dashboard.xgrid" class="menu-item-button<?= $is('xgrid') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:office-building-outline"></span></div>
                            <div class="text">X-Grid</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('xrewards') ?>">
                        <a href="/dashboard.xrewards" class="menu-item-button<?= $is('xrewards') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:gift-outline"></span></div>
                            <div class="text">X-Rewards</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('profile') ?>">
                        <a href="/dashboard.profile" class="menu-item-button<?= $is('profile') ?>">
                            <div class="icon"><span class="iconify" data-icon="mdi:account-circle-outline"></span></div>
                            <div class="text">Profile</div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
