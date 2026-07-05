<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-creator.php'; ?>
<div class="page-body">
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/video-embed.php';
$filter = clean($conn,$_GET['filter']??'all');
$where  = "s.creator_id={$user['id']}";
if ($filter!=='all') $where .= " AND s.status='$filter'";
$subs = $conn->query("SELECT s.*,p.title,p.payout_per_1k,p.max_payout_per_creator,p.platform,p.id pid,b.company_name FROM submissions s JOIN projects p ON p.id=s.project_id LEFT JOIN businesses b ON b.user_id=p.business_id WHERE $where ORDER BY s.submitted_at DESC");
$total_pending  = $conn->query("SELECT COUNT(*) c FROM submissions WHERE creator_id={$user['id']} AND status='pending'")->fetch_assoc()['c'];
$total_approved = $conn->query("SELECT COUNT(*) c FROM submissions WHERE creator_id={$user['id']} AND status='approved'")->fetch_assoc()['c'];
$total_rejected = $conn->query("SELECT COUNT(*) c FROM submissions WHERE creator_id={$user['id']} AND status='rejected'")->fetch_assoc()['c'];
?>
<div class="page-header"><div><div class="page-heading">My Submissions</div><div class="page-sub">Track all content you've submitted to campaigns.</div></div></div>
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);max-width:500px">
  <div class="stat-card"><div class="stat-label">Pending</div><div class="stat-value" style="color:var(--warning)"><?=$total_pending?></div></div>
  <div class="stat-card"><div class="stat-label">Approved</div><div class="stat-value" style="color:var(--success)"><?=$total_approved?></div></div>
  <div class="stat-card"><div class="stat-label">Rejected</div><div class="stat-value" style="color:var(--danger)"><?=$total_rejected?></div></div>
</div>
<div class="filter-bar">
  <div class="chips">
    <?php foreach(['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$v): ?>
    <span class="chip <?=$filter===$k?'active':''?>" onclick="window.location='/linkra/creator/submissions.php?filter=<?=$k?>'"><?=$v?></span>
    <?php endforeach; ?>
  </div>
</div>
<?php if($subs->num_rows===0): ?>
<div class="empty-state"><i class="ti ti-send"></i><h3>No submissions yet</h3><p>Browse campaigns and submit your first video.</p><a href="/linkra/creator/browse.php" class="btn btn-primary"><i class="ti ti-search"></i>Browse campaigns</a></div>
<?php else: ?>
<div class="list-grid">
<?php while($s=$subs->fetch_assoc()): ?>
<div class="card">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px;flex-wrap:wrap">
    <div>
      <div style="font-weight:600;font-size:15px;margin-bottom:4px"><?=htmlspecialchars($s['title'])?></div>
      <div style="font-size:13px;color:var(--text2)"><?=htmlspecialchars($s['company_name']??'Business')?></div>
    </div>
    <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;flex-shrink:0">
      <span class="badge badge-<?=$s['platform']?>"><?=strtoupper($s['platform'])==='ALL'?'All':strtoupper($s['platform'])?></span>
      <span class="badge badge-<?=$s['status']?>"><?=ucfirst($s['status'])?></span>
    </div>
  </div>
  <?php render_video($s['video_url'],300); ?>
  <div style="margin-top:10px;display:flex;gap:16px;flex-wrap:wrap;font-size:13px;align-items:center">
    <span style="color:var(--text3)"><i class="ti ti-clock" style="vertical-align:-2px;margin-right:3px"></i>Submitted <?=time_ago($s['submitted_at'])?></span>
    <?php if($s['view_count']>0): ?><span style="color:var(--text2)"><i class="ti ti-eye" style="vertical-align:-2px;margin-right:3px"></i><?=number_format($s['view_count'])?> views</span><?php endif; ?>
    <?php if($s['payout_amount']>0): ?><span style="font-weight:600;color:var(--purple)"><i class="ti ti-coin" style="vertical-align:-2px;margin-right:3px"></i><?=peso($s['payout_amount'])?></span><?php endif; ?>
  </div>
  <?php if($s['status']==='rejected' && $s['rejection_reason']): ?>
  <div class="alert alert-danger" style="margin-top:10px;margin-bottom:0"><i class="ti ti-x"></i><div><strong>Rejection reason:</strong> <?=htmlspecialchars($s['rejection_reason'])?></div></div>
  <?php endif; ?>
  <?php if($s['status']==='approved'): ?>
  <?php $pay=$conn->query("SELECT * FROM payments WHERE submission_id={$s['id']} LIMIT 1")->fetch_assoc(); ?>
  <div style="margin-top:10px">
    <?php if($pay && $pay['status']==='released'): ?>
    <div class="alert alert-success" style="margin-bottom:0"><i class="ti ti-check"></i>Payment of <strong><?=peso($pay['amount'])?></strong> released <?=time_ago($pay['released_at'])?>.</div>
    <?php else: ?>
    <div class="alert alert-info" style="margin-bottom:0"><i class="ti ti-clock"></i>Approved — waiting for business to release payment of <strong><?=peso($s['payout_amount'])?></strong>.</div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);display:flex;gap:8px">
    <a href="/linkra/creator/campaign.php?id=<?=$s['pid']?>" class="btn btn-secondary btn-sm"><i class="ti ti-eye"></i>View campaign</a>
    <?php if($s['status']==='pending'): ?>
    <?php $elapsed=time()-strtotime($s['submitted_at']); if($elapsed<600): ?>
    <a href="/linkra/creator/submit.php?id=<?=$s['pid']?>" class="btn btn-ghost btn-sm"><i class="ti ti-edit"></i>Replace video</a>
    <?php endif; endif; ?>
  </div>
</div>
<?php endwhile; endif; ?>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
