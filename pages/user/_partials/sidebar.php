<?php
// ============================================================
// USER DASHBOARD SIDEBAR partial
// Pass $active = one of:
//   dashboard | wallet | transactions | investment |
//   referral | profile
// ============================================================
$active = $active ?? 'dashboard';
$is = static fn(string $slug) => $active === $slug ? ' active' : '';
?>
<div class="section-menu-left">
    <div class="box-logo">
        <a href="/dashboard" id="site-logo-inner" class="dash-brand">
            <span class="dash-brand__mark"><img src="/assets/images/logo/solacewhitelogo.png" alt="Solace Mining"></span>
            <span class="dash-brand__word">Solace<em>Mining</em></span>
        </a>
        <div class="button-show-hide">
            <i class="ph ph-caret-left"></i>
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
                            <div class="icon"><i class="ph ph-squares-four"></i></div>
                            <div class="text">Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('wallet') ?>">
                        <a href="/dashboard.wallet" class="menu-item-button<?= $is('wallet') ?>">
                            <div class="icon"><i class="ph ph-wallet"></i></div>
                            <div class="text">My Wallet</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('transactions') ?>">
                        <a href="/dashboard.transactions" class="menu-item-button<?= $is('transactions') ?>">
                            <div class="icon"><i class="ph ph-receipt"></i></div>
                            <div class="text">Transaction</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('investment') ?>">
                        <a href="/dashboard.investment" class="menu-item-button<?= $is('investment') ?>">
                            <div class="icon"><i class="ph ph-coins"></i></div>
                            <div class="text">Mining Plans</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('referral') ?>">
                        <a href="/dashboard.referral" class="menu-item-button<?= $is('referral') ?>">
                            <div class="icon"><i class="ph ph-user-plus"></i></div>
                            <div class="text">Referral Center</div>
                        </a>
                    </li>
                    <li class="menu-item<?= $is('profile') ?>">
                        <a href="/dashboard.profile" class="menu-item-button<?= $is('profile') ?>">
                            <div class="icon"><i class="ph ph-user-circle"></i></div>
                            <div class="text">Profile</div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
