<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-business.php'; ?>
<div class="page-body">
<?php
$uid        = $user['id'];
$total      = $conn->query("SELECT COUNT(*) c FROM projects WHERE business_id=$uid")->fetch_assoc()['c'];
$open       = $conn->query("SELECT COUNT(*) c FROM projects WHERE business_id=$uid AND status='open'")->fetch_assoc()['c'];
$pending    = $conn->query("SELECT COUNT(*) c FROM submissions s JOIN projects p ON p.id=s.project_id WHERE p.business_id=$uid AND s.status='pending'")->fetch_assoc()['c'];
$total_paid = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments py JOIN projects p ON p.id=py.project_id WHERE p.business_id=$uid AND py.status='released'")->fetch_assoc()['s'];
$campaigns  = $conn->query("SELECT p.*,(SELECT COUNT(*) FROM submissions WHERE project_id=p.id) subs,(SELECT COUNT(*) FROM submissions WHERE project_id=p.id AND status='pending') pend FROM projects p WHERE p.business_id=$uid ORDER BY p.created_at DESC LIMIT 6");
?>
<div class="page-header">
  <div><div class="page-heading">Dashboard</div><div class="page-sub">Welcome back, <?=htmlspecialchars($user['name'])?>.</div></div>
  <div class="page-header-right"><a href="/linkra/business/create.php" class="btn btn-primary"><i class="ti ti-plus"></i>New Campaign</a></div>
</div>
<div class="stats-grid">
  <div class="stat-card"><div class="stat-icon si-purple"><i class="ti ti-briefcase"></i></div><div class="stat-label">Total campaigns</div><div class="stat-value"><?=$total?></div></div>
  <div class="stat-card"><div class="stat-icon si-green"><i class="ti ti-circle-check"></i></div><div class="stat-label">Open campaigns</div><div class="stat-value"><?=$open?></div></div>
  <div class="stat-card"><div class="stat-icon si-warning"><i class="ti ti-inbox"></i></div><div class="stat-label">Pending review</div><div class="stat-value"><?=$pending?></div><?php if($pending>0):?><div class="stat-sub"><a href="/linkra/business/submissions.php?filter=pending">Review now</a></div><?php endif;?></div>
  <div class="stat-card"><div class="stat-icon si-blue"><i class="ti ti-coin"></i></div><div class="stat-label">Total paid out</div><div class="stat-value"><?=peso($total_paid)?></div></div>
</div>
<div class="page-header" style="margin-bottom:16px">
  <div class="page-heading" style="font-size:18px">Recent campaigns</div>
  <a href="/linkra/business/campaigns.php" class="see-all">See all →</a>
</div>
<?php if($campaigns->num_rows===0): ?>
<div class="empty-state"><i class="ti ti-briefcase"></i><h3>No campaigns yet</h3><p>Create your first campaign and start getting content from creators.</p><a href="/linkra/business/create.php" class="btn btn-primary"><i class="ti ti-plus"></i>Create Campaign</a></div>
<?php else: ?>
<div class="feed-grid">
<?php while($p=$campaigns->fetch_assoc()):
  $pct = $p['overall_budget']>0 ? min(100,round((($p['overall_budget']-$p['budget_remaining'])/$p['overall_budget'])*100)) : 0;
  $bar_class = $pct>85?'danger':($pct>60?'warn':'');
?>
<a href="/linkra/business/campaign.php?id=<?=$p['id']?>" class="campaign-card">
  <div class="campaign-thumb">
    <?php if($p['thumbnail']): ?><img src="/linkra/assets/<?=htmlspecialchars($p['thumbnail'])?>" alt=""><?php else: ?><i class="ti ti-photo" style="font-size:36px"></i><?php endif; ?>
  </div>
  <div class="campaign-card-body">
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px">
      <span class="badge badge-<?=$p['status']?>"><?=ucfirst($p['status'])?></span>
      <span class="badge badge-<?=$p['platform']?>"><?=strtoupper($p['platform'])==='ALL'?'All platforms':strtoupper($p['platform'])?></span>
    </div>
    <div class="campaign-title"><?=htmlspecialchars($p['title'])?></div>
    <div class="budget-bar-wrap"><div class="budget-bar <?=$bar_class?>" style="width:<?=$pct?>%"></div></div>
    <div class="budget-labels mb-12"><span><?=peso($p['budget_remaining'])?> remaining</span><span><?=peso($p['overall_budget'])?> total</span></div>
    <div class="campaign-meta">
      <span class="meta-item"><i class="ti ti-send"></i><?=$p['subs']?> submission<?=$p['subs']!=1?'s':''?></span>
      <?php if($p['pend']>0): ?><span class="meta-item" style="color:var(--warning)"><i class="ti ti-clock"></i><?=$p['pend']?> pending</span><?php endif; ?>
      <?php if($p['deadline']): ?><span class="meta-item"><i class="ti ti-calendar"></i><?=date('M j',strtotime($p['deadline']))?></span><?php endif; ?>
    </div>
  </div>
</a>
<?php endwhile; endif; ?>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
