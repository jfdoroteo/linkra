<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-admin.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';
?>
<div class="page-body">
<?php
$ms = date('Y-m-01');
$all_users    = $conn->query("SELECT COUNT(*) c FROM users WHERE role!='admin'")->fetch_assoc()['c'];
$all_biz      = $conn->query("SELECT COUNT(*) c FROM users WHERE role='business'")->fetch_assoc()['c'];
$all_creators = $conn->query("SELECT COUNT(*) c FROM users WHERE role='creator'")->fetch_assoc()['c'];
$all_camps    = $conn->query("SELECT COUNT(*) c FROM projects")->fetch_assoc()['c'];
$open_camps   = $conn->query("SELECT COUNT(*) c FROM projects WHERE status='open'")->fetch_assoc()['c'];
$all_subs     = $conn->query("SELECT COUNT(*) c FROM submissions")->fetch_assoc()['c'];
$approved_s   = $conn->query("SELECT COUNT(*) c FROM submissions WHERE status='approved'")->fetch_assoc()['c'];
$rejected_s   = $conn->query("SELECT COUNT(*) c FROM submissions WHERE status='rejected'")->fetch_assoc()['c'];
$pending_s    = $all_subs - $approved_s - $rejected_s;
$total_paid   = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE status='released'")->fetch_assoc()['s'];
$approval_rate= $all_subs>0 ? round(($approved_s/$all_subs)*100) : 0;

$m_users      = $conn->query("SELECT COUNT(*) c FROM users WHERE role!='admin' AND created_at>='$ms'")->fetch_assoc()['c'];
$m_camps      = $conn->query("SELECT COUNT(*) c FROM projects WHERE created_at>='$ms'")->fetch_assoc()['c'];
$m_subs       = $conn->query("SELECT COUNT(*) c FROM submissions WHERE submitted_at>='$ms'")->fetch_assoc()['c'];
$m_paid       = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE status='released' AND released_at>='$ms'")->fetch_assoc()['s'];

$by_platform  = $conn->query("SELECT platform, COUNT(*) c FROM projects GROUP BY platform ORDER BY c DESC");
$by_category  = $conn->query("SELECT category, COUNT(*) c FROM projects WHERE category!='' GROUP BY category ORDER BY c DESC LIMIT 8");
$top_creators = $conn->query("SELECT u.name, u.avatar, COUNT(py.id) projs, SUM(py.amount) earned FROM payments py JOIN users u ON u.id=py.creator_id WHERE py.status='released' GROUP BY py.creator_id ORDER BY earned DESC LIMIT 6");
$top_biz      = $conn->query("SELECT u.name, u.avatar, b.company_name, COUNT(p.id) camps, SUM(p.overall_budget) total FROM projects p JOIN users u ON u.id=p.business_id LEFT JOIN businesses b ON b.user_id=p.business_id GROUP BY p.business_id ORDER BY total DESC LIMIT 6");
?>
<div class="page-header">
  <div>
    <div class="page-heading">Reports</div>
    <div class="page-sub">Platform analytics and summaries — <?=date('F Y')?></div>
  </div>
</div>

<div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:var(--text3);margin-bottom:12px">This month — <?=date('F Y')?></div>
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:28px">
  <div class="stat-card"><div class="stat-icon si-purple"><i class="ti ti-user-plus"></i></div><div class="stat-label">New users</div><div class="stat-value"><?=$m_users?></div></div>
  <div class="stat-card"><div class="stat-icon si-blue"><i class="ti ti-briefcase"></i></div><div class="stat-label">New campaigns</div><div class="stat-value"><?=$m_camps?></div></div>
  <div class="stat-card"><div class="stat-icon si-warning"><i class="ti ti-send"></i></div><div class="stat-label">Submissions</div><div class="stat-value"><?=$m_subs?></div></div>
  <div class="stat-card"><div class="stat-icon si-green"><i class="ti ti-coin"></i></div><div class="stat-label">Paid out</div><div class="stat-value"><?=peso($m_paid)?></div></div>
</div>

<div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:var(--text3);margin-bottom:12px">All time</div>
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:28px">
  <div class="stat-card"><div class="stat-label">Total users</div><div class="stat-value"><?=$all_users?></div><div class="stat-sub"><?=$all_biz?> biz · <?=$all_creators?> creators</div></div>
  <div class="stat-card"><div class="stat-label">Total campaigns</div><div class="stat-value"><?=$all_camps?></div><div class="stat-sub"><?=$open_camps?> open</div></div>
  <div class="stat-card"><div class="stat-label">Approval rate</div><div class="stat-value"><?=$approval_rate?>%</div><div class="stat-sub"><?=$approved_s?> approved · <?=$rejected_s?> rejected</div></div>
  <div class="stat-card"><div class="stat-label">Total paid out</div><div class="stat-value"><?=peso($total_paid)?></div></div>
</div>

<div class="three-col" style="margin-bottom:24px">
  <div class="card">
    <div class="card-header"><div class="card-title">Campaigns by platform</div></div>
    <?php while($r=$by_platform->fetch_assoc()): $pct=$all_camps>0?round(($r['c']/$all_camps)*100):0; ?>
    <div style="margin-bottom:12px">
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:5px">
        <span class="badge badge-<?=$r['platform']?>"><?=strtoupper($r['platform'])==='ALL'?'All platforms':strtoupper($r['platform'])?></span>
        <span style="font-weight:600"><?=$r['c']?> (<?=$pct?>%)</span>
      </div>
      <div class="progress-wrap"><div class="progress-bar" style="width:<?=$pct?>%"></div></div>
    </div>
    <?php endwhile; ?>
  </div>
  
  <div class="card">
    <div class="card-header"><div class="card-title">Top categories</div></div>
    <?php while($r=$by_category->fetch_assoc()): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border)">
      <span class="badge badge-<?=strtolower($r['category'])?>"><?=htmlspecialchars($r['category'])?></span>
      <span style="font-weight:600;font-size:13px"><?=$r['c']?> campaigns</span>
    </div>
    <?php endwhile; ?>
  </div>
  
  <div class="card">
    <div class="card-header"><div class="card-title">Submission breakdown</div></div>
    <?php
    $breakdown = [
      ['label'=>'Pending',  'count'=>$pending_s,  'color'=>'var(--warning)'],
      ['label'=>'Approved', 'count'=>$approved_s,  'color'=>'var(--success)'],
      ['label'=>'Rejected', 'count'=>$rejected_s,  'color'=>'var(--danger)'],
    ];
    foreach($breakdown as $b):
      $pct = $all_subs>0 ? round(($b['count']/$all_subs)*100) : 0;
    ?>
    <div style="margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:5px">
        <span><?=$b['label']?></span>
        <span style="font-weight:600"><?=$b['count']?> (<?=$pct?>%)</span>
      </div>
      <div class="progress-wrap"><div class="progress-bar" style="width:<?=$pct?>%;background:<?=$b['color']?>"></div></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="two-col">
  <div class="table-wrap">
    <div class="table-header"><div class="card-title">Top earning creators</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr><th>Creator</th><th>Projects</th><th>Total earned</th></tr>
        </thead>
        <tbody>
          <?php if($top_creators->num_rows===0): ?>
          <tr><td colspan="3" style="text-align:center;padding:24px;color:var(--text3)">No payment data yet.</td></tr>
          <?php else: while($r=$top_creators->fetch_assoc()): $rd=['name'=>$r['name'],'avatar'=>$r['avatar']]; ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <?=avatar_html($rd,'sm')?>
                <span style="font-weight:500"><?=htmlspecialchars($r['name'])?></span>
              </div>
            </td>
            <td style="text-align:center"><?=$r['projs']?></td>
            <td style="font-weight:700;color:var(--purple)"><?=peso($r['earned'])?></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <div class="table-wrap">
    <div class="table-header"><div class="card-title">Top businesses by spend</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr><th>Business</th><th>Campaigns</th><th>Total budget</th></tr>
        </thead>
        <tbody>
          <?php if($top_biz->num_rows===0): ?>
          <tr><td colspan="3" style="text-align:center;padding:24px;color:var(--text3)">No data yet.</td></tr>
          <?php else: while($r=$top_biz->fetch_assoc()): $bd=['name'=>$r['company_name']??$r['name'],'avatar'=>$r['avatar']]; ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <?=avatar_html($bd,'sm')?>
                <span style="font-weight:500"><?=htmlspecialchars($r['company_name']??$r['name'])?></span>
              </div>
            </td>
            <td style="text-align:center"><?=$r['camps']?></td>
            <td style="font-weight:700;color:var(--info)"><?=peso($r['total'])?></td>
          </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>