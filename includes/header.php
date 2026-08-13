<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Digital Studio in Somalia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>assets/images/favicon_io/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>assets/images/favicon_io/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>assets/images/favicon_io/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>assets/images/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>assets/images/favicon_io/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= BASE_URL ?>assets/images/favicon_io/android-chrome-512x512.png">
    <link rel="manifest" href="<?= BASE_URL ?>assets/images/favicon_io/site.webmanifest">
    <meta name="msapplication-TileColor" content="#25D366">
    <meta name="msapplication-TileImage" content="<?= BASE_URL ?>assets/images/favicon_io/favicon-32x32.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/animations.css">
    <style>
    .nav-link { background: #ffffff; }
    .nav-link:hover,
    .nav-link.active { background: #f0f0f0; }
    @media (max-width: 768px) {
        .nav { background: #ffffff; }
        .nav-link { background: #f8f9fb; }
        .nav-link:hover,
        .nav-link.active { background: #e8e8f0; }
    }
    </style>
    <script src="https://unpkg.com/lucide@0.460.0" integrity="sha256-GyLGwEocabdaQcZMfqmSZX6PYo2r1jJJhP/GHDdhpWo=" crossorigin="anonymous"></script>
    <script>var BASE_URL = '<?= BASE_URL ?>';</script>
<script>
(function(){
  'use strict';
  document.addEventListener('DOMContentLoaded', function(){
    var toggle = document.querySelector('.mobile-toggle');
    var nav = document.querySelector('.nav');
    var overlay = document.querySelector('.nav-overlay');
    if (toggle && nav) {
      function closeNav() {
        nav.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        var icon = toggle.querySelector('i');
        if (icon) {
          icon.setAttribute('data-lucide', 'menu');
          if (typeof lucide !== 'undefined') lucide.createIcons();
        }
      }
      toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        nav.classList.toggle('active');
        if (overlay) overlay.classList.toggle('active');
        toggle.setAttribute('aria-expanded', nav.classList.contains('active'));
        var icon = toggle.querySelector('i');
        if (icon) {
          icon.setAttribute('data-lucide', nav.classList.contains('active') ? 'x' : 'menu');
          if (typeof lucide !== 'undefined') lucide.createIcons();
        }
      });
      if (overlay) {
        overlay.addEventListener('click', closeNav);
      }
      document.addEventListener('click', function(e) {
        if (!toggle.contains(e.target) && !nav.contains(e.target)) {
          closeNav();
        }
      });
      nav.querySelectorAll('.nav-link').forEach(function(link) {
        link.addEventListener('click', closeNav);
      });
    }
  });
})();
</script>
</head>
<body>
<header class="header" id="header">
    <div class="header-inner">
        <a href="<?= BASE_URL ?>home" class="logo">
            <img src="<?= BASE_URL ?>assets/images/logo2_blackbackground.png" alt="ASAAS" class="logo-light" style="height:64px;width:auto">
            <img src="<?= BASE_URL ?>assets/images/logo1_whitebackground.png" alt="ASAAS" class="logo-dark" style="height:64px;width:auto;display:none">
        </a>
        <nav class="nav" id="main-nav">
            <a href="<?= BASE_URL ?>home" class="nav-link <?= $page === 'home' ? 'active' : '' ?>">Home</a>
            <a href="<?= BASE_URL ?>services" class="nav-link <?= $page === 'services' ? 'active' : '' ?>">Services</a>
            <a href="<?= BASE_URL ?>portfolio" class="nav-link <?= $page === 'portfolio' ? 'active' : '' ?>">Portfolio</a>
            <a href="<?= BASE_URL ?>blog" class="nav-link <?= $page === 'blog' ? 'active' : '' ?>">Blog</a>
            <a href="<?= BASE_URL ?>about" class="nav-link <?= $page === 'about' ? 'active' : '' ?>">About</a>
            <a href="<?= BASE_URL ?>contact" class="nav-link <?= $page === 'contact' ? 'active' : '' ?>">Contact</a>
        </nav>
        <div class="nav-actions">
            <?php if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>dashboard" class="btn btn-primary btn-sm">
                    <i data-lucide="layout-dashboard" size="16"></i>
                    Dashboard
                </a>
                <?php if (isAdmin()): ?>
                    <a href="<?= BASE_URL ?>admin" class="btn btn-secondary btn-sm">
                        <i data-lucide="shield" size="16"></i>
                        Admin
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= BASE_URL ?>login" class="btn btn-ghost btn-sm">Sign In</a>
                <a href="<?= BASE_URL ?>register" class="btn btn-primary btn-sm">
                    Get Started
                    <i data-lucide="arrow-right" size="16"></i>
                </a>
            <?php endif; ?>
            <button class="mobile-toggle" id="mobile-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <i data-lucide="menu" size="24"></i>
            </button>
        </div>
    </div>
    <div class="nav-overlay" id="nav-overlay"></div>
</header>
