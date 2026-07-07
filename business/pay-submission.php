<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-business.php'; ?>
<div class="page-body" style="max-width:680px">
<?php
$sid = intval($_GET['id']??0);
$sub = $conn->query("SELECT s.*,u.name cname,p.title ptitle,p.business_id,p.budget_remaining FROM submissions s JOIN users u ON u.id=s.creator_id JOIN projects p ON p.id=s.project_id WHERE s.id=$sid AND s.status='approved' AND p.business_id={$user['id']} LIMIT 1")->fetch_assoc();
if (!$sub) { echo '<div class="alert alert-danger">Submission not found or not yet approved.</div>'; require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; exit(); }
$existing = $conn->query("SELECT * FROM payments WHERE submission_id=$sid LIMIT 1")->fetch_assoc();
$success='';
if ($_SERVER['REQUEST_METHOD']==='POST' && !$existing) {
    $amount = floatval($_POST['amount']??$sub['payout_amount']);
    $conn->query("INSERT INTO payments (submission_id,project_id,business_id,creator_id,amount,status,released_at) VALUES ($sid,{$sub['project_id']},{$user['id']},{$sub['creator_id']},$amount,'released',NOW())");
    notify($conn,$sub['creator_id'],"Payment of ".peso($amount)." released for \"{$sub['ptitle']}\"!","/linkra/creator/earnings.php");
    $success='Payment released!'; $existing=['amount'=>$amount,'status'=>'released'];
}
?>
<div class="page-header">
  <div><div class="page-heading">Release Payment</div><div class="page-sub"><?=htmlspecialchars($sub['ptitle'])?> · <?=htmlspecialchars($sub['cname'])?></div></div>
  <a href="/linkra/business/campaign.php?id=<?=$sub['project_id']?>" class="btn btn-secondary"><i class="ti ti-arrow-left"></i>Back</a>
</div>
<?php if($success): ?><div class="alert alert-success" data-auto-dismiss><i class="ti ti-check"></i><?=htmlspecialchars($success)?></div><?php endif; ?>
<div class="card">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
    <div class="stat-card"><div class="stat-label">Creator</div><div style="font-size:18px;font-weight:600"><?=htmlspecialchars($sub['cname'])?></div></div>
    <div class="stat-card"><div class="stat-label">Approved payout</div><div style="font-size:18px;font-weight:600;color:var(--purple)"><?=peso($sub['payout_amount'])?></div></div>
  </div>
  <?php if($existing): ?>
  <div class="alert alert-success"><i class="ti ti-check"></i>Payment of <?=peso($existing['amount'])?> already released.</div>
  <?php else: ?>
  <form method="POST">
    <div class="form-group">
      <label class="form-label">Final amount to release (₱)</label>
      <input type="number" name="amount" class="form-control" value="<?=$sub['payout_amount']?>" min="0.01" step="0.01" required>
      <div class="form-hint">Budget remaining: <strong><?=peso($sub['budget_remaining'])?></strong></div>
    </div>
    <div class="alert alert-info"><i class="ti ti-info-circle"></i>This marks payment as released and notifies the creator.</div>
    <button type="submit" class="btn btn-success btn-lg btn-full" onclick="return confirm('Release this payment?')"><i class="ti ti-coin"></i>Confirm &amp; Release Payment</button>
  </form>
  <?php endif; ?>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
