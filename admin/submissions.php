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
    $sid    = intval($_POST['sid']??0);
    $action = $_POST['action']??'';
    if ($sid && $action==='delete') $conn->query("DELETE FROM submissions WHERE id=$sid");
    header('Location:/linkra/admin/submissions.php'); exit();
}

$filter = clean($conn, $_GET['filter']??'all');
$where  = '1=1';
if ($filter!=='all') $where .= " AND s.status='$filter'";

$subs = $conn->query("SELECT s.*,u.name cname,u.avatar cavatar,p.title ptitle,p.id pid,b.company_name FROM submissions s JOIN users u ON u.id=s.creator_id JOIN projects p ON p.id=s.project_id LEFT JOIN businesses b ON b.user_id=p.business_id WHERE $where ORDER BY s.submitted_at DESC");
?>
<div class="page-header">
  <div>
    <div class="page-heading">Manage Submissions</div>
    <div class="page-sub">All content submissions across the platform.</div>
  </div>
</div>

<div class="filter-bar">
  <div class="chips">
    <?php foreach(['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$v): ?>
      <span class="chip <?=$filter===$k?'active':''?>" onclick="window.location='/linkra/admin/submissions.php?filter=<?=$k?>'"><?=$v?></span>
    <?php endforeach; ?>
  </div>
</div>

<div class="table-wrap">
  <div class="table-header">
    <div class="card-title">Submissions <span style="color:var(--text3);font-weight:400">(<?=$subs->num_rows?>)</span></div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr><th>Creator</th><th>Campaign</th><th>Business</th><th>Video</th><th>Views</th><th>Payout</th><th>Status</th><th>Submitted</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if($subs->num_rows===0): ?>
        <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text3)">No submissions found.</td></tr>
        <?php else: while($s=$subs->fetch_assoc()): $cu=['name'=>$s['cname'],'avatar'=>$s['cavatar']]; ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:8px">
              <?=avatar_html($cu,'xs')?>
              <span style="font-weight:500"><?=htmlspecialchars($s['cname'])?></span>
            </div>
          </td>
          <td style="max-width:160px;color:var(--text2)">
            <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($s['ptitle'])?></div>
          </td>
          <td style="color:var(--text2)"><?=htmlspecialchars($s['company_name']??'—')?></td>
          <td><a href="<?=htmlspecialchars($s['video_url'])?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm"><i class="ti ti-external-link"></i>View</a></td>
          <td><?=$s['view_count']>0?number_format($s['view_count']):'—'?></td>
          <td style="font-weight:600;color:var(--purple)"><?=$s['payout_amount']>0?peso($s['payout_amount']):'—'?></td>
          <td><span class="badge badge-<?=$s['status']?>"><?=ucfirst($s['status'])?></span></td>
          <td style="color:var(--text3);white-space:nowrap"><?=time_ago($s['submitted_at'])?></td>
          <td>
            <div class="td-actions">
              <a href="/linkra/business/review-submission.php?id=<?=$s['id']?>" class="btn btn-secondary btn-sm"><i class="ti ti-eye"></i>Detail</a>
              <form method="POST" onsubmit="return confirm('Delete this submission?')">
                <input type="hidden" name="sid" value="<?=$s['id']?>">
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