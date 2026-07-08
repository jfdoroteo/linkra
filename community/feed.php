<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';
require_login();
$user = current_user();
$role = $user['role'];

// Handle post
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['body'])) {
    $body=clean($conn,$_POST['body']??'');
    if ($body && strlen($body)<=280) {
        $img='NULL';
        if (isset($_FILES['image'])&&$_FILES['image']['error']===0) {
            $p=upload_file('image','post',['jpg','jpeg','png','gif','webp']);
            if ($p) $img="'$p'";
        }
        $conn->query("INSERT INTO posts (user_id,body,image_url) VALUES ({$user['id']},'$body',$img)");
    }
    header('Location:/linkra/community/feed.php'); exit();
}
// Handle like
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['like_id'])) {
    $lid=intval($_POST['like_id']);
    $ex=$conn->query("SELECT id FROM post_likes WHERE post_id=$lid AND user_id={$user['id']} LIMIT 1")->fetch_assoc();
    if ($ex) $conn->query("DELETE FROM post_likes WHERE post_id=$lid AND user_id={$user['id']}");
    else     $conn->query("INSERT INTO post_likes (post_id,user_id) VALUES ($lid,{$user['id']})");
    header('Location:/linkra/community/feed.php#post-'.$lid); exit();
}
// Handle comment
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['cpost_id'])) {
    $cpid=intval($_POST['cpost_id']); $cb=clean($conn,$_POST['cbody']??'');
    if ($cb) $conn->query("INSERT INTO post_comments (post_id,user_id,body) VALUES ($cpid,{$user['id']},'$cb')");
    header('Location:/linkra/community/feed.php#post-'.$cpid); exit();
}
// Handle delete post
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['del_post'])) {
    $dp=intval($_POST['del_post']);
    $conn->query("DELETE FROM posts WHERE id=$dp AND user_id={$user['id']}");
    header('Location:/linkra/community/feed.php'); exit();
}
// Handle delete comment
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['del_comment'])) {
    $dc=intval($_POST['del_comment']);
    $conn->query("DELETE FROM post_comments WHERE id=$dc AND user_id={$user['id']}");
    header('Location:/linkra/community/feed.php'); exit();
}
$posts=$conn->query("SELECT p.*,u.name uname,u.avatar uavatar,u.role urole,(SELECT COUNT(*) FROM post_likes WHERE post_id=p.id) likes,(SELECT COUNT(*) FROM post_comments WHERE post_id=p.id) ccount,(SELECT COUNT(*) FROM post_likes WHERE post_id=p.id AND user_id={$user['id']}) liked FROM posts p JOIN users u ON u.id=p.user_id ORDER BY p.created_at DESC LIMIT 50");
$notif = unread_count($conn,$user['id']);
?>
<!DOCTYPE html><html lang="en" data-theme="light"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Community — LINKRA</title>
<link rel="stylesheet" href="/linkra/assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>(function(){const t=localStorage.getItem('linkra_theme')||'light';document.documentElement.setAttribute('data-theme',t);})()</script>
</head><body>
<div class="app-layout">
<!-- Dynamic sidebar based on role -->
<div class="sidebar">
  <div class="sidebar-logo"><div class="logo-text">LINK<span>RA</span></div><div class="tagline">Community</div></div>
  <nav>
    <?php if($role==='business'): ?>
    <div class="nav-section-label">Campaigns</div>
    <a href="/linkra/business/dashboard.php"   class="nav-item"><i class="ti ti-layout-dashboard"></i>Dashboard</a>
    <a href="/linkra/business/create.php"      class="nav-item"><i class="ti ti-plus"></i>Create Campaign</a>
    <a href="/linkra/business/campaigns.php"   class="nav-item"><i class="ti ti-briefcase"></i>My Campaigns</a>
    <a href="/linkra/community/feed.php"       class="nav-item active"><i class="ti ti-users"></i>Community</a>
    <div class="nav-section-label">Account</div>
    <a href="/linkra/business/payments.php"    class="nav-item"><i class="ti ti-credit-card"></i>Payments</a>
    <a href="/linkra/business/profile.php"     class="nav-item"><i class="ti ti-user"></i>Profile</a>
    <?php else: ?>
    <div class="nav-section-label">Discover</div>
    <a href="/linkra/creator/browse.php"       class="nav-item"><i class="ti ti-search"></i>Browse Campaigns</a>
    <a href="/linkra/community/feed.php"       class="nav-item active"><i class="ti ti-users"></i>Community</a>
    <div class="nav-section-label">My Work</div>
    <a href="/linkra/creator/submissions.php"  class="nav-item"><i class="ti ti-send"></i>My Submissions</a>
    <a href="/linkra/creator/earnings.php"     class="nav-item"><i class="ti ti-coin"></i>Earnings</a>
    <div class="nav-section-label">Account</div>
    <a href="/linkra/creator/profile.php"      class="nav-item"><i class="ti ti-user"></i>Profile</a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-footer"><a href="/linkra/auth/logout.php" class="nav-item"><i class="ti ti-logout"></i>Log out</a></div>
</div>
<div class="main-content">
<div class="topbar">
  <div class="topbar-left"><button class="hamburger" onclick="toggleSidebar()"><i class="ti ti-menu-2"></i></button></div>
  <div class="topbar-right">
    <?php include $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/theme-toggle.php'; ?>
    <?php $notif_url=$role==='business'?'/linkra/business/notifications.php':'/linkra/creator/notifications.php'; ?>
    <a href="<?=$notif_url?>" class="notif-bell"><i class="ti ti-bell"></i><?php if($notif>0):?><span class="notif-dot"></span><?php endif;?></a>
    <div class="topbar-user"><?=avatar_html($user,'sm')?><div><div class="user-name"><?=htmlspecialchars($user['name'])?></div><div class="user-role"><?=ucfirst($role)?></div></div></div>
  </div>
</div>
<div class="page-body">
<div class="page-header"><div><div class="page-heading">Community</div><div class="page-sub">Share thoughts, tips, and updates with the LINKRA community.</div></div></div>
<div class="two-col">
  <div>
    <!-- Composer -->
    <div class="card mb-16">
      <form method="POST" enctype="multipart/form-data">
        <div style="display:flex;gap:10px;align-items:flex-start">
          <?=avatar_html($user,'md')?>
          <div style="flex:1">
            <div class="field-wrap"><textarea name="body" class="form-control" rows="3" maxlength="280" placeholder="What's on your mind?" required></textarea><div class="char-counter">0/280</div></div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;flex-wrap:wrap;gap:8px">
              <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text2);cursor:pointer">
                <i class="ti ti-photo" style="font-size:18px"></i><span>Add image</span>
                <input type="file" name="image" accept="image/*" style="display:none">
              </label>
              <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-send"></i>Post</button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <!-- Posts -->
    <?php if($posts->num_rows===0): ?>
    <div class="empty-state"><i class="ti ti-messages"></i><h3>No posts yet</h3><p>Be the first to post!</p></div>
    <?php else: while($p=$posts->fetch_assoc()):
      $pu=['name'=>$p['uname'],'avatar'=>$p['uavatar']];
      $cmts=$conn->query("SELECT c.*,u.name un,u.avatar ua FROM post_comments c JOIN users u ON u.id=c.user_id WHERE c.post_id={$p['id']} ORDER BY c.created_at ASC");
    ?>
    <div class="post-card mb-16" id="post-<?=$p['id']?>">
      <div class="post-header">
        <?=avatar_html($pu,'sm')?>
        <div style="flex:1"><div class="post-user-name"><?=htmlspecialchars($p['uname'])?> <span class="badge badge-<?=$p['urole']?>" style="font-size:10px;vertical-align:middle"><?=ucfirst($p['urole'])?></span></div><div style="font-size:11px;color:var(--text3)"><?=time_ago($p['created_at'])?></div></div>
        <?php if($p['user_id']==$user['id']): ?><form method="POST" onsubmit="return confirm('Delete?')"><input type="hidden" name="del_post" value="<?=$p['id']?>"><button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)"><i class="ti ti-trash"></i></button></form><?php endif; ?>
      </div>
      <div class="post-body"><?=nl2br(htmlspecialchars($p['body']))?></div>
      <?php if($p['image_url']): ?><img src="/linkra/assets/<?=htmlspecialchars($p['image_url'])?>" class="post-image" alt=""><?php endif; ?>
      <div class="post-actions">
        <form method="POST" style="margin:0"><input type="hidden" name="like_id" value="<?=$p['id']?>">
          <button type="submit" class="post-btn <?=$p['liked']?'liked':''?>"><i class="ti ti-heart<?=$p['liked']?'-filled':''?>"></i><?=$p['likes']?></button>
        </form>
        <button class="post-btn" onclick="toggleComments(<?=$p['id']?>)"><i class="ti ti-message-circle"></i><?=$p['ccount']?> comment<?=$p['ccount']!=1?'s':''?></button>
      </div>
      <div id="cmts-<?=$p['id']?>" style="display:<?=$p['ccount']>0?'block':'none'?>;margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
        <?php while($c=$cmts->fetch_assoc()): $cu=['name'=>$c['un'],'avatar'=>$c['ua']]; ?>
        <div class="comment-item">
          <?=avatar_html($cu,'xs')?>
          <div style="flex:1"><div class="comment-author"><?=htmlspecialchars($c['un'])?></div><div class="comment-text"><?=nl2br(htmlspecialchars($c['body']))?></div><div class="comment-time"><?=time_ago($c['created_at'])?></div></div>
          <?php if($c['user_id']==$user['id']): ?><form method="POST"><input type="hidden" name="del_comment" value="<?=$c['id']?>"><button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger);padding:3px 6px"><i class="ti ti-x"></i></button></form><?php endif; ?>
        </div>
        <?php endwhile; ?>
        <form method="POST" style="display:flex;gap:8px;margin-top:8px;align-items:center">
          <input type="hidden" name="cpost_id" value="<?=$p['id']?>">
          <?=avatar_html($user,'xs')?>
          <input type="text" name="cbody" class="form-control" placeholder="Write a comment..." maxlength="500" required style="flex:1">
          <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-send"></i></button>
        </form>
      </div>
    </div>
    <?php endwhile; endif; ?>
  </div>
  <!-- Right panel -->
  <div>
    <div class="card mb-16">
      <div class="card-header"><div class="card-title">Your profile</div></div>
      <div style="text-align:center;padding:10px 0"><?=avatar_html($user,'lg')?><div style="font-weight:600;margin-top:10px"><?=htmlspecialchars($user['name'])?></div><span class="badge badge-<?=$role?>" style="margin-top:6px"><?=ucfirst($role)?></span></div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">Community tips</div></div>
      <ul style="display:flex;flex-direction:column;gap:8px;font-size:13px;color:var(--text2)">
        <li style="display:flex;gap:8px"><i class="ti ti-check" style="color:var(--success);flex-shrink:0;margin-top:2px"></i>Share your wins and insights</li>
        <li style="display:flex;gap:8px"><i class="ti ti-check" style="color:var(--success);flex-shrink:0;margin-top:2px"></i>Ask campaign questions</li>
        <li style="display:flex;gap:8px"><i class="ti ti-check" style="color:var(--success);flex-shrink:0;margin-top:2px"></i>Post content creation tips</li>
        <li style="display:flex;gap:8px"><i class="ti ti-check" style="color:var(--success);flex-shrink:0;margin-top:2px"></i>Be respectful to everyone</li>
      </ul>
    </div>
  </div>
</div>
</div>
</div></div>
<script src="/linkra/assets/js/main.js"></script>
</body></html>
