<?php
require_once __DIR__ . '/../../../config/assets.php'; // asset cache-busting


// ============================================================
// USER DASHBOARD HEAD partial
//   $page_title (string), $page_description (string)
// ============================================================
$page_title = $page_title ?? 'TitanXHoldings Dashboard';
$page_description = $page_description ?? 'Your TitanXHoldings dashboard — wallet, allocations, and portfolio performance.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="author" content="TitanXHoldings">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- Preload + Apply (critical CSS) -->
    <link rel="preload" href="<?= txh_asset('/assets/css/bootstrap.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="<?= txh_asset('/assets/css/dashboard.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="<?= txh_asset('/assets/icon/style.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">

    <!-- Non-critical CSS -->
    <link rel="stylesheet" href="<?= txh_asset('/assets/css/animation.min.css') ?>">
    <link rel="stylesheet" href="<?= txh_asset('/assets/css/animation.css') ?>">
    <link rel="stylesheet" href="<?= txh_asset('/assets/css/bootstrap-select.min.css') ?>">
    <link rel="stylesheet" href="<?= txh_asset('/assets/fonts/font.css') ?>">
    <!-- icon/style.css already loaded async via preload-swap above; no-JS fallback in <noscript> -->

    <!-- TXH design re-skin (loads last to win the cascade) -->
    <link rel="stylesheet" href="<?= txh_asset('/assets/css/txh-dashboard.css') ?>">

    <noscript>
        <link rel="stylesheet" href="<?= txh_asset('/assets/css/bootstrap.css') ?>">
        <link rel="stylesheet" href="<?= txh_asset('/assets/css/dashboard.css') ?>">
        <link rel="stylesheet" href="<?= txh_asset('/assets/icon/style.css') ?>">
        <link rel="stylesheet" href="<?= txh_asset('/assets/css/txh-dashboard.css') ?>">
    </noscript>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="shortcut icon" href="/assets/favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="TitanXHoldings">
    <link rel="manifest" href="/assets/favicon/site.webmanifest">
</head>
