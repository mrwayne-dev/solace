<?php
require_once __DIR__ . '/../../../config/assets.php'; // asset cache-busting


// ============================================================
// HEAD partial — shared by every public page
// Usage:
//   $page_title       (string)  e.g. "Solace Mining — Build Wealth"
//   $page_description (string)
//   $page_path        (string)  e.g. "/about" (for canonical)
// ============================================================
$page_title       = $page_title       ?? 'Solace Mining';
$page_description = $page_description ?? 'crypto mining platform — high-yield savings, fractional shares, automated investing, all in one regulated wallet.';
$page_path        = $page_path        ?? '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
  <meta name="author" content="Solace Mining">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://solacemining.org<?= htmlspecialchars($page_path) ?>">

  <title><?= htmlspecialchars($page_title) ?></title>

  <!-- Preload critical assets -->
  <link rel="preload" href="<?= txh_asset('/assets/css/txh-design.css') ?>" as="style">

  <!-- Stylesheets — legacy first, design system overrides last -->
  <link rel="stylesheet" href="<?= txh_asset('/assets/css/main.css') ?>">
  <link rel="stylesheet" href="<?= txh_asset('/assets/css/responsive.css') ?>">
  <link rel="stylesheet" href="<?= txh_asset('/assets/css/txh-design.css') ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/assets/favicon/favicon-32x32.png" sizes="32x32">
  <link rel="shortcut icon" href="/assets/favicon/favicon.ico">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
  <meta name="apple-mobile-web-app-title" content="Solace Mining">
  <link rel="manifest" href="/assets/favicon/site.webmanifest">
</head>
