<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';
require_role('business');
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
    <div class="tagline">Business Portal</div>
  </div>
  <nav>
    <div class="nav-section-label">Campaigns</div>
    <a href="/linkra/business/dashboard.php"  class="nav-item <?=$cur==='dashboard.php'?'active':''?>"><i class="ti ti-layout-dashboard"></i>Dashboard</a>
    <a href="/linkra/business/create.php"     class="nav-item <?=$cur==='create.php'?'active':''?>"><i class="ti ti-plus"></i>Create Campaign</a>
    <a href="/linkra/business/campaigns.php"  class="nav-item <?=$cur==='campaigns.php'?'active':''?>"><i class="ti ti-briefcase"></i>My Campaigns</a>
    <a href="/linkra/business/submissions.php" class="nav-item <?=$cur==='submissions.php'?'active':''?>"><i class="ti ti-inbox"></i>All Submissions</a>
    <div class="nav-section-label">Community</div>
    <a href="/linkra/community/feed.php"      class="nav-item <?=$cur==='feed.php'?'active':''?>"><i class="ti ti-users"></i>Community Feed</a>
    <div class="nav-section-label">Account</div>
    <a href="/linkra/business/payments.php"   class="nav-item <?=$cur==='payments.php'?'active':''?>"><i class="ti ti-credit-card"></i>Payments</a>
    <a href="/linkra/business/profile.php"    class="nav-item <?=$cur==='profile.php'?'active':''?>"><i class="ti ti-user"></i>Profile</a>
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
    <a href="/linkra/business/notifications.php" class="notif-bell">
      <i class="ti ti-bell"></i><?php if($notif>0):?><span class="notif-dot"></span><?php endif;?>
    </a>
    <div class="topbar-user">
      <?php echo avatar_html($user,'sm'); ?>
      <div><div class="user-name"><?=htmlspecialchars($user['name'])?></div><div class="user-role">Business</div></div>
    </div>
  </div>
</div>
