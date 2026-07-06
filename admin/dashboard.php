<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-admin.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';
?>
<div class="page-body">
<?php
$total_users    = $conn->query("SELECT COUNT(*) c FROM users WHERE role!='admin'")->fetch_assoc()['c'];
$total_biz      = $conn->query("SELECT COUNT(*) c FROM users WHERE role='business'")->fetch_assoc()['c'];
$total_creators = $conn->query("SELECT COUNT(*) c FROM users WHERE role='creator'")->fetch_assoc()['c'];
$total_camps    = $conn->query("SELECT COUNT(*) c FROM projects")->fetch_assoc()['c'];
$open_camps     = $conn->query("SELECT COUNT(*) c FROM projects WHERE status='open'")->fetch_assoc()['c'];
$total_subs     = $conn->query("SELECT COUNT(*) c FROM submissions")->fetch_assoc()['c'];
$pending_subs   = $conn->query("SELECT COUNT(*) c FROM submissions WHERE status='pending'")->fetch_assoc()['c'];
$total_paid     = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE status='released'")->fetch_assoc()['s'];

$recent_users   = $conn->query("SELECT * FROM users WHERE role!='admin' ORDER BY created_at DESC LIMIT 6");
$recent_camps   = $conn->query("SELECT p.*,u.name bname FROM projects p JOIN users u ON u.id=p.business_id ORDER BY p.created_at DESC LIMIT 6");
$recent_subs    = $conn->query("SELECT s.*,u.name cname,p.title ptitle FROM submissions s JOIN users u ON u.id=s.creator_id JOIN projects p ON p.id=s.project_id ORDER BY s.submitted_at DESC LIMIT 5");
?>
<div class="page-header">
  <div>
    <div class="page-heading">Platform Overview</div>
    <div class="page-sub">LINKRA admin dashboard — <?=date('F j, Y')?></div>
  </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
  <div class="stat-card">
    <div class="stat-icon si-purple"><i class="ti ti-users"></i></div>
    <div class="stat-label">Total users</div>
    <div class="stat-value"><?=$total_users?></div>
    <div class="stat-sub"><?=$total_biz?> businesses · <?=$total_creators?> creators</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-blue"><i class="ti ti-briefcase"></i></div>
    <div class="stat-label">Campaigns</div>
    <div class="stat-value"><?=$total_camps?></div>
    <div class="stat-sub"><?=$open_camps?> open</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-warning"><i class="ti ti-inbox"></i></div>
    <div class="stat-label">Submissions</div>
    <div class="stat-value"><?=$total_subs?></div>
    <div class="stat-sub"><?=$pending_subs?> pending review</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-green"><i class="ti ti-coin"></i></div>
    <div class="stat-label">Total paid out</div>
    <div class="stat-value"><?=peso($total_paid)?></div>
  </div>
</div>

<?php if($pending_subs>0): ?>
  <div class="alert alert-warning mb-20">
    <i class="ti ti-alert-triangle"></i><?=$pending_subs?> submission<?=$pending_subs!=1?'s':''?> pending review. <a href="/linkra/admin/submissions.php?filter=pending">View all</a>
  </div>
<?php endif; ?>

<div class="two-col">
  <div>
    <div class="table-wrap mb-16">
      <div class="table-header">
        <div class="card-title">Recent users</div>
        <a href="/linkra/admin/users.php" class="see-all">Manage all</a>
      </div>
      <div class="table-scroll">
        <table class="data-table">
          <thead>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr>
          </thead>
          <tbody>
            <?php while($u=$recent_users->fetch_assoc()): $ud=['name'=>$u['name'],'avatar'=>$u['avatar']]; ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <?=avatar_html($ud,'xs')?>
                  <span style="font-weight:500"><?=htmlspecialchars($u['name'])?></span>
                </div>
              </td>
              <td style="color:var(--text2)"><?=htmlspecialchars($u['email'])?></td>
              <td><span class="badge badge-<?=$u['role']?>"><?=ucfirst($u['role'])?></span></td>
              <td style="color:var(--text3)"><?=date('M j',strtotime($u['created_at']))?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="table-wrap">
      <div class="table-header">
        <div class="card-title">Recent campaigns</div>
        <a href="/linkra/admin/campaigns.php" class="see-all">Manage all</a>
      </div>
      <div class="table-scroll">
        <table class="data-table">
          <thead>
            <tr><th>Title</th><th>Business</th><th>Budget</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php while($p=$recent_camps->fetch_assoc()): ?>
            <tr>
              <td style="font-weight:500;max-width:180px">
                <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($p['title'])?></div>
              </td>
              <td style="color:var(--text2)"><?=htmlspecialchars($p['bname'])?></td>
              <td style="font-size:12px;font-weight:600;color:var(--purple)"><?=peso($p['budget_remaining'])?> / <?=peso($p['overall_budget'])?></td>
              <td><span class="badge badge-<?=$p['status']?>"><?=ucfirst($p['status'])?></span></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div>
    <div class="table-wrap">
      <div class="table-header">
        <div class="card-title">Recent submissions</div>
        <a href="/linkra/admin/submissions.php" class="see-all">View all</a>
      </div>
      <div class="table-scroll">
        <table class="data-table">
          <thead>
            <tr><th>Creator</th><th>Campaign</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php while($s=$recent_subs->fetch_assoc()): ?>
            <tr>
              <td style="font-weight:500"><?=htmlspecialchars($s['cname'])?></td>
              <td style="color:var(--text2);max-width:140px">
                <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($s['ptitle'])?></div>
              </td>
              <td><span class="badge badge-<?=$s['status']?>"><?=ucfirst($s['status'])?></span></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>