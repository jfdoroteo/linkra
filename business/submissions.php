<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-business.php'; ?>
<div class="page-body">
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/video-embed.php';
$filter = clean($conn,$_GET['filter']??'all');
$camp   = intval($_GET['campaign']??0);
$where  = "p.business_id={$user['id']}";
if ($filter!=='all') $where .= " AND s.status='$filter'";
if ($camp>0)         $where .= " AND s.project_id=$camp";
$subs = $conn->query("SELECT s.*,u.name cname,u.avatar cavatar,p.title ptitle,p.id pid FROM submissions s JOIN users u ON u.id=s.creator_id JOIN projects p ON p.id=s.project_id WHERE $where ORDER BY s.submitted_at DESC");
?>
<div class="page-header">
  <div><div class="page-heading">All Submissions</div><div class="page-sub">Review content submitted by creators across your campaigns.</div></div>
</div>
<div class="filter-bar" data-target="sub-row">
  <div class="chips">
    <span class="chip <?=$filter==='all'?'active':''?>" data-filter="all" onclick="window.location='/linkra/business/submissions.php?filter=all<?=$camp?"&campaign=$camp":''?>'">All</span>
    <span class="chip <?=$filter==='pending'?'active':''?>" onclick="window.location='/linkra/business/submissions.php?filter=pending<?=$camp?"&campaign=$camp":''?>'">Pending</span>
    <span class="chip <?=$filter==='approved'?'active':''?>" onclick="window.location='/linkra/business/submissions.php?filter=approved<?=$camp?"&campaign=$camp":''?>'">Approved</span>
    <span class="chip <?=$filter==='rejected'?'active':''?>" onclick="window.location='/linkra/business/submissions.php?filter=rejected<?=$camp?"&campaign=$camp":''?>'">Rejected</span>
  </div>
</div>
<div class="table-wrap">
  <div class="table-header"><div class="card-title">Submissions <span style="color:var(--text3);font-weight:400">(<?=$subs->num_rows?>)</span></div></div>
  <div class="table-scroll">
  <table class="data-table">
    <thead><tr><th>Creator</th><th>Campaign</th><th>Video</th><th>Views</th><th>Payout</th><th>Status</th><th>Submitted</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if($subs->num_rows===0): ?>
    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text3)">No submissions found.</td></tr>
    <?php else: while($s=$subs->fetch_assoc()): $cu=['name'=>$s['cname'],'avatar'=>$s['cavatar']]; ?>
    <tr class="sub-row" data-status="<?=$s['status']?>">
      <td><div style="display:flex;align-items:center;gap:8px"><?=avatar_html($cu,'xs')?><span style="font-weight:500"><?=htmlspecialchars($s['cname'])?></span></div></td>
      <td style="max-width:160px"><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($s['ptitle'])?></div></td>
      <td><a href="<?=htmlspecialchars($s['video_url'])?>" target="_blank" class="btn btn-ghost btn-sm"><i class="ti ti-external-link"></i>View</a></td>
      <td><?=$s['view_count']>0?number_format($s['view_count']):'—'?></td>
      <td style="font-weight:600;color:var(--purple)"><?=$s['payout_amount']>0?peso($s['payout_amount']):'—'?></td>
      <td><span class="badge badge-<?=$s['status']?>"><?=ucfirst($s['status'])?></span></td>
      <td style="color:var(--text3)"><?=time_ago($s['submitted_at'])?></td>
      <td>
        <div class="td-actions">
          <a href="/linkra/business/review-submission.php?id=<?=$s['id']?>" class="btn btn-secondary btn-sm"><i class="ti ti-eye"></i>Review</a>
          <?php if($s['status']==='approved'): $pay=$conn->query("SELECT status FROM payments WHERE submission_id={$s['id']} LIMIT 1")->fetch_assoc(); ?>
            <?php if(!$pay||$pay['status']!=='released'): ?><a href="/linkra/business/pay-submission.php?id=<?=$s['id']?>" class="btn btn-primary btn-sm"><i class="ti ti-coin"></i>Pay</a><?php endif; ?>
          <?php endif; ?>
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
