<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-business.php'; ?>
<div class="page-body">
<?php
$status = clean($conn,$_GET['status']??'');
$where  = "business_id={$user['id']}";
if ($status) $where .= " AND status='$status'";
$camps = $conn->query("SELECT p.*,(SELECT COUNT(*) FROM submissions WHERE project_id=p.id) subs,(SELECT COUNT(*) FROM submissions WHERE project_id=p.id AND status='pending') pend FROM projects p WHERE $where ORDER BY p.created_at DESC");
?>
<div class="page-header">
  <div><div class="page-heading">My Campaigns</div><div class="page-sub">All your campaigns in one place.</div></div>
  <div class="page-header-right"><a href="/linkra/business/create.php" class="btn btn-primary"><i class="ti ti-plus"></i>New Campaign</a></div>
</div>
<div class="filter-bar">
  <div class="chips">
    <?php foreach([''=>'All','open'=>'Open','closed'=>'Closed','completed'=>'Completed'] as $k=>$v): ?>
    <span class="chip <?=$status===$k?'active':''?>" onclick="window.location='/linkra/business/campaigns.php?status=<?=$k?>'"><?=$v?></span>
    <?php endforeach; ?>
  </div>
</div>
<?php if($camps->num_rows===0): ?>
<div class="empty-state"><i class="ti ti-briefcase"></i><h3>No campaigns yet</h3><p>Create your first campaign to start getting creator content.</p><a href="/linkra/business/create.php" class="btn btn-primary"><i class="ti ti-plus"></i>Create Campaign</a></div>
<?php else: ?>
<div class="feed-grid">
<?php while($p=$camps->fetch_assoc()): $pct=$p['overall_budget']>0?min(100,round((($p['overall_budget']-$p['budget_remaining'])/$p['overall_budget'])*100)):0; ?>
<div class="campaign-card" style="cursor:default">
  <div class="campaign-thumb">
    <?php if($p['thumbnail']): ?><img src="/linkra/assets/<?=htmlspecialchars($p['thumbnail'])?>" alt=""><?php else: ?><i class="ti ti-photo" style="font-size:36px;color:var(--text3)"></i><?php endif; ?>
  </div>
  <div class="campaign-card-body">
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px">
      <span class="badge badge-<?=$p['status']?>"><?=ucfirst($p['status'])?></span>
      <span class="badge badge-<?=$p['platform']?>"><?=strtoupper($p['platform'])==='ALL'?'All':strtoupper($p['platform'])?></span>
    </div>
    <div class="campaign-title"><?=htmlspecialchars($p['title'])?></div>
    <div class="budget-bar-wrap"><div class="budget-bar" style="width:<?=$pct?>%"></div></div>
    <div class="budget-labels mb-12"><span><?=peso($p['budget_remaining'])?> left</span><span><?=peso($p['overall_budget'])?></span></div>
    <div class="campaign-meta mb-12">
      <span class="meta-item"><i class="ti ti-send"></i><?=$p['subs']?> submissions</span>
      <?php if($p['pend']>0): ?><span class="meta-item" style="color:var(--warning)"><i class="ti ti-clock"></i><?=$p['pend']?> pending</span><?php endif; ?>
    </div>
    <div class="campaign-actions">
      <a href="/linkra/business/campaign.php?id=<?=$p['id']?>" class="btn btn-primary btn-sm"><i class="ti ti-eye"></i>View</a>
      <a href="/linkra/business/edit-campaign.php?id=<?=$p['id']?>" class="btn btn-secondary btn-sm"><i class="ti ti-edit"></i>Edit</a>
      <a href="/linkra/business/submissions.php?campaign=<?=$p['id']?>" class="btn btn-ghost btn-sm"><i class="ti ti-inbox"></i>Submissions</a>
    </div>
  </div>
</div>
<?php endwhile; endif; ?>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
