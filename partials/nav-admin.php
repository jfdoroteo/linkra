<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/auth.php';
require_role('admin');
$user = current_user();
$cur  = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html><html lang="en" data-theme="light"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>LINKRA Admin</title>
<link rel="stylesheet" href="/linkra/assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>(function(){const t=localStorage.getItem('linkra_theme')||'light';document.documentElement.setAttribute('data-theme',t);})()</script>
</head><body>
<div class="app-layout">
<div class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-text">LINK<span>RA</span></div>
    <div class="tagline" style="color:#EF4444">Admin Panel</div>
  </div>
  <nav>
    <div class="nav-section-label">Overview</div>
    <a href="/linkra/admin/dashboard.php"  class="nav-item <?=$cur==='dashboard.php'?'active':''?>"><i class="ti ti-layout-dashboard"></i>Dashboard</a>
    <div class="nav-section-label">Manage</div>
    <a href="/linkra/admin/users.php"      class="nav-item <?=$cur==='users.php'?'active':''?>"><i class="ti ti-users"></i>Users</a>
    <a href="/linkra/admin/campaigns.php"  class="nav-item <?=$cur==='campaigns.php'?'active':''?>"><i class="ti ti-briefcase"></i>Campaigns</a>
    <a href="/linkra/admin/submissions.php" class="nav-item <?=$cur==='submissions.php'?'active':''?>"><i class="ti ti-inbox"></i>Submissions</a>
    <a href="/linkra/admin/community.php"  class="nav-item <?=$cur==='community.php'?'active':''?>"><i class="ti ti-messages"></i>Community</a>
    <a href="/linkra/admin/reports.php"    class="nav-item <?=$cur==='reports.php'?'active':''?>"><i class="ti ti-chart-bar"></i>Reports</a>
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
    <div class="topbar-user">
      <div class="avatar-placeholder avatar-sm avatar-red"><?=strtoupper(substr($user['name'],0,2))?></div>
      <div><div class="user-name"><?=htmlspecialchars($user['name'])?></div><div class="user-role" style="color:var(--danger)">Admin</div></div>
    </div>
  </div>
</div>
