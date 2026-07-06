<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-admin.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';

if (!function_exists('clean')) {
    function clean($conn, $str) {
        return $conn->real_escape_string($str);
    }
}
?>
<div class="page-body">
<?php
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid    = intval($_POST['pid']??0);
    $action = $_POST['action']??'';
    if ($pid) {
        if ($action==='delete') $conn->query("DELETE FROM projects WHERE id=$pid");
        if ($action==='close')  $conn->query("UPDATE projects SET status='closed' WHERE id=$pid");
        if ($action==='open')   $conn->query("UPDATE projects SET status='open' WHERE id=$pid");
    }
    header('Location:/linkra/admin/campaigns.php'); exit();
}

$q      = clean($conn, $_GET['q']??'');
$status = clean($conn, $_GET['status']??'');
$where  = '1=1';
if ($q)      $where .= " AND p.title LIKE '%$q%'";
if ($status) $where .= " AND p.status='$status'";

$camps = $conn->query("SELECT p.*,u.name bname,(SELECT COUNT(*) FROM submissions WHERE project_id=p.id) subs FROM projects p JOIN users u ON u.id=p.business_id WHERE $where ORDER BY p.created_at DESC");
?>
<div class="page-header">
  <div>
    <div class="page-heading">Manage Campaigns</div>
    <div class="page-sub">View and moderate all platform campaigns.</div>
  </div>
</div>

<form method="GET">
  <div class="filter-bar">
    <div class="search-box" style="flex:1">
      <i class="ti ti-search"></i>
      <input type="text" name="q" placeholder="Search campaigns..." value="<?=htmlspecialchars($q)?>">
    </div>
    <select name="status" class="form-control" style="width:160px">
      <option value="">All statuses</option>
      <option value="open"      <?=$status==='open'?'selected':''?>>Open</option>
      <option value="closed"    <?=$status==='closed'?'selected':''?>>Closed</option>
      <option value="completed" <?=$status==='completed'?'selected':''?>>Completed</option>
    </select>
    <button type="submit" class="btn btn-primary"><i class="ti ti-search"></i>Search</button>
    <?php if($q||$status): ?>
      <a href="/linkra/admin/campaigns.php" class="btn btn-ghost"><i class="ti ti-x"></i>Clear</a>
    <?php endif; ?>
  </div>
</form>

<div class="table-wrap">
  <div class="table-header">
    <div class="card-title">Campaigns <span style="color:var(--text3);font-weight:400">(<?=$camps->num_rows?>)</span></div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr><th>Campaign</th><th>Business</th><th>Budget</th><th>Remaining</th><th>Subs</th><th>Status</th><th>Created</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if($camps->num_rows===0): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text3)">No campaigns found.</td></tr>
        <?php else: while($p=$camps->fetch_assoc()):
          $remaining_color = $p['budget_remaining'] <= 0 ? 'var(--danger)' : 'var(--success)';
        ?>
        <tr>
          <td style="font-weight:500;max-width:200px">
            <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?=htmlspecialchars($p['title'])?>"><?=htmlspecialchars($p['title'])?></div>
          </td>
          <td style="color:var(--text2)"><?=htmlspecialchars($p['bname'])?></td>
          <td style="font-weight:600"><?=peso($p['overall_budget'])?></td>
          <td style="font-weight:600;color:<?=$remaining_color?>"><?=peso($p['budget_remaining'])?></td>
          <td style="text-align:center"><?=$p['subs']?></td>
          <td><span class="badge badge-<?=$p['status']?>"><?=ucfirst($p['status'])?></span></td>
          <td style="color:var(--text3)"><?=date('M j, Y',strtotime($p['created_at']))?></td>
          <td>
            <div class="td-actions">
              <?php if($p['status']==='open'): ?>
                <form method="POST" onsubmit="return confirm('Close this campaign?')">
                  <input type="hidden" name="pid" value="<?=$p['id']?>">
                  <input type="hidden" name="action" value="close">
                  <button type="submit" class="btn btn-warning btn-sm"><i class="ti ti-lock"></i>Close</button>
                </form>
              <?php elseif($p['status']==='closed'): ?>
                <form method="POST">
                  <input type="hidden" name="pid" value="<?=$p['id']?>">
                  <input type="hidden" name="action" value="open">
                  <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-lock-open"></i>Reopen</button>
                </form>
              <?php endif; ?>
              <form method="POST" onsubmit="return confirm('Permanently delete this campaign?')">
                <input type="hidden" name="pid" value="<?=$p['id']?>">
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