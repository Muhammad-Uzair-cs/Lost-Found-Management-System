<?php
// includes/header.php — Shared page header
if (!defined('PAGE_TITLE')) define('PAGE_TITLE', 'Lost & Found');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= sanitize(PAGE_TITLE) ?> | Lost & Found — University</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
</head>
<body>
<?php if (isLoggedIn()): ?>
<nav class="navbar">
  <a href="<?= SITE_URL ?>/dashboard.php" class="nav-brand">
    <span class="brand-icon"><i class="fa-solid fa-magnifying-glass-location"></i></span>
    <span>Lost<strong>&Found</strong></span>
  </a>
  <ul class="nav-links">
    <li><a href="<?= SITE_URL ?>/dashboard.php"  class="<?= basename($_SERVER['PHP_SELF'])==='dashboard.php'  ?'active':''?>"><i class="fa fa-gauge-high"></i> Dashboard</a></li>
    <li><a href="<?= SITE_URL ?>/lost-items.php"  class="<?= basename($_SERVER['PHP_SELF'])==='lost-items.php'  ?'active':''?>"><i class="fa fa-circle-question"></i> Lost</a></li>
    <li><a href="<?= SITE_URL ?>/found-items.php" class="<?= basename($_SERVER['PHP_SELF'])==='found-items.php' ?'active':''?>"><i class="fa fa-box-open"></i> Found</a></li>
    <li><a href="<?= SITE_URL ?>/matches.php"     class="<?= basename($_SERVER['PHP_SELF'])==='matches.php'     ?'active':''?>"><i class="fa fa-link"></i> Matches</a></li>
    <li><a href="<?= SITE_URL ?>/search.php"      class="<?= basename($_SERVER['PHP_SELF'])==='search.php'      ?'active':''?>"><i class="fa fa-search"></i> Search</a></li>
    <li><a href="<?= SITE_URL ?>/map.php"         class="<?= basename($_SERVER['PHP_SELF'])==='map.php'           ?'active':''?>"><i class="fa fa-map"></i> Map</a></li>
    <?php if (isAdmin()): ?>
    <li><a href="<?= SITE_URL ?>/admin/index.php"   class="<?= strpos($_SERVER['PHP_SELF'],'admin')!==false ?'active':''?>"><i class="fa fa-shield-halved"></i> Admin</a></li>
    <li><a href="<?= SITE_URL ?>/admin/reports.php" class="<?= basename($_SERVER['PHP_SELF'])==='reports.php'    ?'active':''?>"><i class="fa fa-chart-bar"></i> Reports</a></li>
    <?php endif; ?>
  </ul>
  <div class="nav-right">
    <a href="<?= SITE_URL ?>/notifications.php" class="notif-btn">
      <i class="fa fa-bell"></i>
      <?php $cnt = getUnreadCount(); if ($cnt > 0): ?><span class="notif-badge"><?= $cnt ?></span><?php endif; ?>
    </a>
    <div class="user-menu">
      <span class="user-avatar"><i class="fa fa-user-circle"></i></span>
      <span class="user-name"><?= sanitize($_SESSION['full_name']) ?></span>
      <div class="dropdown">
        <a href="<?= SITE_URL ?>/profile.php"><i class="fa fa-user"></i> Profile</a>
        <a href="<?= SITE_URL ?>/logout.php"><i class="fa fa-right-from-bracket"></i> Logout</a>
      </div>
    </div>
  </div>
</nav>
<?php endif; ?>
<main class="page-main">
