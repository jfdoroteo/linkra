<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-business.php'; ?>
<div class="page-body">
<?php
$total_released = $conn->query("SELECT COALESCE(SUM(py.amount),0) s FROM payments py JOIN projects p ON p.id=py.project_id WHERE p.business_id={$user['id']} AND py.status='released'")->fetch_assoc()['s'];
$payments = $conn->query("SELECT py.*,p.title ptitle,u.name cname FROM payments py JOIN projects p ON p.id=py.project_id JOIN users u ON u.id=py.creator_id WHERE p.business_id={$user['id']} ORDER BY py.released_at DESC");
?>
<div class="page-header"><div><div class="page-heading">Payments</div><div class="page-sub">All payment transactions.</div></div></div>
<div class="stats-grid" style="grid-template-columns:repeat(2,1fr);max-width:500px">
  <div class="stat-card"><div class="stat-icon si-green"><i class="ti ti-coin"></i></div><div class="stat-label">Total released</div><div class="stat-value"><?=peso($total_released)?></div></div>
  <div class="stat-card"><div class="stat-icon si-blue"><i class="ti ti-receipt"></i></div><div class="stat-label">Transactions</div><div class="stat-value"><?=$payments->num_rows?></div></div>
</div>
<div class="table-wrap">
  <div class="table-header"><div class="card-title">Payment history</div></div>
  <div class="table-scroll"><table class="data-table">
    <thead><tr><th>Campaign</th><th>Creator</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    <?php if($payments->num_rows===0): ?>
    <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text3)">No payments yet.</td></tr>
    <?php else: while($py=$payments->fetch_assoc()): ?>
    <tr>
      <td style="font-weight:500"><?=htmlspecialchars($py['ptitle'])?></td>
      <td><?=htmlspecialchars($py['cname'])?></td>
      <td style="font-weight:600;color:var(--purple)"><?=peso($py['amount'])?></td>
      <td><span class="badge badge-released">Released</span></td>
      <td style="color:var(--text3)"><?=$py['released_at']?date('M j, Y',strtotime($py['released_at'])):'—'?></td>
    </tr>
    <?php endwhile; endif; ?>
    </tbody>
  </table></div>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
