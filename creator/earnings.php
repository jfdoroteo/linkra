<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-creator.php'; ?>
<div class="page-body">
<?php
$uid      = $user['id'];
$released = $conn->query("SELECT COALESCE(SUM(py.amount),0) s FROM payments py WHERE py.creator_id=$uid AND py.status='released'")->fetch_assoc()['s'];
$pending  = $conn->query("SELECT COALESCE(SUM(s.payout_amount),0) s FROM submissions s LEFT JOIN payments py ON py.submission_id=s.id WHERE s.creator_id=$uid AND s.status='approved' AND (py.id IS NULL OR py.status!='released')")->fetch_assoc()['s'];
$total_sub= $conn->query("SELECT COUNT(*) c FROM submissions WHERE creator_id=$uid")->fetch_assoc()['c'];
$approved = $conn->query("SELECT COUNT(*) c FROM submissions WHERE creator_id=$uid AND status='approved'")->fetch_assoc()['c'];
$payments = $conn->query("SELECT py.*,p.title,b.company_name FROM payments py JOIN projects p ON p.id=py.project_id LEFT JOIN businesses b ON b.user_id=p.business_id WHERE py.creator_id=$uid ORDER BY py.released_at DESC");
$pending_list = $conn->query("SELECT s.*,p.title,b.company_name FROM submissions s JOIN projects p ON p.id=s.project_id LEFT JOIN businesses b ON b.user_id=p.business_id LEFT JOIN payments py ON py.submission_id=s.id WHERE s.creator_id=$uid AND s.status='approved' AND (py.id IS NULL OR py.status!='released') ORDER BY s.submitted_at DESC");
?>
<div class="page-header"><div><div class="page-heading">Earnings</div><div class="page-sub">Your payment history and pending payouts.</div></div></div>
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
  <div class="stat-card"><div class="stat-icon si-green"><i class="ti ti-coin"></i></div><div class="stat-label">Total earned</div><div class="stat-value"><?=peso($released)?></div></div>
  <div class="stat-card"><div class="stat-icon si-warning"><i class="ti ti-clock"></i></div><div class="stat-label">Pending release</div><div class="stat-value"><?=peso($pending)?></div></div>
  <div class="stat-card"><div class="stat-icon si-purple"><i class="ti ti-send"></i></div><div class="stat-label">Total submitted</div><div class="stat-value"><?=$total_sub?></div></div>
  <div class="stat-card"><div class="stat-icon si-blue"><i class="ti ti-check"></i></div><div class="stat-label">Approved</div><div class="stat-value"><?=$approved?></div></div>
</div>
<?php if($pending_list->num_rows>0): ?>
<div class="card mb-20">
  <div class="card-header"><div class="card-title"><i class="ti ti-clock" style="color:var(--warning);margin-right:6px"></i>Pending payments</div></div>
  <?php while($s=$pending_list->fetch_assoc()): ?>
  <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border)">
    <div><div style="font-weight:500;font-size:13px"><?=htmlspecialchars($s['title'])?></div><div style="font-size:12px;color:var(--text3)"><?=htmlspecialchars($s['company_name']??'Business')?> · Awaiting release</div></div>
    <span style="font-weight:700;color:var(--warning)"><?=peso($s['payout_amount'])?></span>
  </div>
  <?php endwhile; ?>
</div>
<?php endif; ?>
<div class="table-wrap">
  <div class="table-header"><div class="card-title">Payment history</div></div>
  <div class="table-scroll"><table class="data-table">
    <thead><tr><th>Campaign</th><th>Business</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php if($payments->num_rows===0): ?>
    <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text3)">No payments yet. Get your submissions approved to start earning.</td></tr>
    <?php else: while($py=$payments->fetch_assoc()): ?>
    <tr>
      <td style="font-weight:500"><?=htmlspecialchars($py['title'])?></td>
      <td style="color:var(--text2)"><?=htmlspecialchars($py['company_name']??'Business')?></td>
      <td style="font-weight:700;color:var(--purple)"><?=peso($py['amount'])?></td>
      <td><span class="badge badge-released">Released</span></td>
      <td style="color:var(--text3)"><?=$py['released_at']?date('M j, Y',strtotime($py['released_at'])):'—'?></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody>
  </table></div>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
