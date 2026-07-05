<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';
require_role('creator');
$user  = current_user();
$notif = unread_count($conn, $user['id']);
$cur   = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html><html lang="en" data-theme="light"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>LINKRA</title>
<link rel="stylesheet" href="/linkra/assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>(function(){const t=localStorage.getItem('linkra_theme')||'light';document.documentElement.setAttribute('data-theme',t);})()</script>
</head><body>
<div class="app-layout">
<div class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-text">LINK<span>RA</span></div>
    <div class="tagline">Creator Portal</div>
  </div>
  <nav>
    <div class="nav-section-label">Discover</div>
    <a href="/linkra/creator/browse.php"       class="nav-item <?=$cur==='browse.php'?'active':''?>"><i class="ti ti-search"></i>Browse Campaigns</a>
    <a href="/linkra/community/feed.php"       class="nav-item <?=$cur==='feed.php'?'active':''?>"><i class="ti ti-users"></i>Community</a>
    <div class="nav-section-label">My Work</div>
    <a href="/linkra/creator/submissions.php"  class="nav-item <?=$cur==='submissions.php'?'active':''?>"><i class="ti ti-send"></i>My Submissions</a>
    <a href="/linkra/creator/earnings.php"     class="nav-item <?=$cur==='earnings.php'?'active':''?>"><i class="ti ti-coin"></i>Earnings</a>
    <div class="nav-section-label">Account</div>
    <a href="/linkra/creator/profile.php"      class="nav-item <?=$cur==='profile.php'?'active':''?>"><i class="ti ti-user"></i>Profile</a>
  </nav>
  <div class="sidebar-footer">
    <a href="/linkra/auth/logout.php" class="nav-item"><i class="ti ti-logout"></i>Log out</a>
  </div>
</div>
<div class="main-content">
<div class="topbar">
  <div class="topbar-left">
    <button class="hamburger" onclick="toggleSidebar()"><i class="ti ti-menu-2"></i></button>
  </div>
  <div class="topbar-right">
    <?php include $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/theme-toggle.php'; ?>
    <a href="/linkra/creator/notifications.php" class="notif-bell">
      <i class="ti ti-bell"></i><?php if($notif>0):?><span class="notif-dot"></span><?php endif;?>
    </a>
    <div class="topbar-user">
      <?php echo avatar_html($user,'sm'); ?>
      <div><div class="user-name"><?=htmlspecialchars($user['name'])?></div><div class="user-role">Creator</div></div>
    </div>
  </div>
</div>
