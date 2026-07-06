<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-admin.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';

// Safe fallback in case clean() isn't globally defined in db.php or nav-admin.php
if (!function_exists('clean')) {
    function clean($conn, $str) {
        if (isset($conn) && $conn instanceof mysqli) {
            return $conn->real_escape_string($str);
        }
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }
}
?>
<div class="page-body">
<?php
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $uid    = intval($_POST['uid']??0);
    $action = $_POST['action']??'';
    if ($uid && $uid !== intval($user['id'] ?? 0)) {
        if ($action==='suspend')  $conn->query("UPDATE users SET is_active=0 WHERE id=$uid");
        if ($action==='activate') $conn->query("UPDATE users SET is_active=1 WHERE id=$uid");
        if ($action==='delete')   $conn->query("DELETE FROM users WHERE id=$uid AND role!='admin'");
    }
    header('Location:/linkra/admin/users.php'); exit();
}

$q      = clean($conn, $_GET['q']??'');
$role_f = clean($conn, $_GET['role']??'');
$where  = "role!='admin'";

if ($q)      $where .= " AND (name LIKE '%$q%' OR email LIKE '%$q%')";
if ($role_f) $where .= " AND role='$role_f'";

$users = $conn->query("SELECT * FROM users WHERE $where ORDER BY created_at DESC");
?>
<div class="page-header">
  <div>
    <div class="page-heading">Manage Users</div>
    <div class="page-sub">View, suspend, or remove user accounts.</div>
  </div>
</div>

<form method="GET">
  <div class="filter-bar">
    <div class="search-box" style="flex:1">
      <i class="ti ti-search"></i>
      <input type="text" name="q" placeholder="Search by name or email..." value="<?=htmlspecialchars($q)?>">
    </div>
    <select name="role" class="form-control" style="width:160px">
      <option value="">All roles</option>
      <option value="business" <?=$role_f==='business'?'selected':''?>>Business</option>
      <option value="creator"  <?=$role_f==='creator'?'selected':''?>>Creator</option>
    </select>
    <button type="submit" class="btn btn-primary"><i class="ti ti-search"></i>Search</button>
    <?php if($q||$role_f): ?>
      <a href="/linkra/admin/users.php" class="btn btn-ghost"><i class="ti ti-x"></i>Clear</a>
    <?php endif; ?>
  </div>
</form>

<div class="table-wrap">
  <div class="table-header">
    <div class="card-title">Users <span style="color:var(--text3);font-weight:400">(<?=$users->num_rows?>)</span></div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if($users->num_rows===0): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text3)">No users found.</td></tr>
        <?php else: while($u=$users->fetch_assoc()): $ud=['name'=>$u['name'],'avatar'=>$u['avatar']]; ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:8px">
              <?=avatar_html($ud,'sm')?>
              <span style="font-weight:500"><?=htmlspecialchars($u['name'])?></span>
            </div>
          </td>
          <td style="color:var(--text2)"><?=htmlspecialchars($u['email'])?></td>
          <td><span class="badge badge-<?=$u['role']?>"><?=ucfirst($u['role'])?></span></td>
          <td><?=$u['is_active']?'<span class="badge badge-approved">Active</span>':'<span class="badge badge-rejected">Suspended</span>'?></td>
          <td style="color:var(--text3)"><?=date('M j, Y',strtotime($u['created_at']))?></td>
          <td>
            <div class="td-actions">
              <?php if($u['is_active']): ?>
                <form method="POST" onsubmit="return confirm('Suspend this user?')">
                  <input type="hidden" name="uid" value="<?=$u['id']?>">
                  <input type="hidden" name="action" value="suspend">
                  <button type="submit" class="btn btn-warning btn-sm"><i class="ti ti-ban"></i>Suspend</button>
                </form>
              <?php else: ?>
                <form method="POST">
                  <input type="hidden" name="uid" value="<?=$u['id']?>">
                  <input type="hidden" name="action" value="activate">
                  <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-check"></i>Activate</button>
                </form>
              <?php endif; ?>
              <form method="POST" onsubmit="return confirm('Permanently delete this user?')">
                <input type="hidden" name="uid" value="<?=$u['id']?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn-danger btn-sm"><i class="ti ti-trash"></i>Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>