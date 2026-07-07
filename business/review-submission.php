<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-business.php'; ?>
<div class="page-body" style="max-width:800px">
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/video-embed.php';
$sid = intval($_GET['id']??0);
$sub = $conn->query("SELECT s.*,u.name cname,u.avatar cavatar,p.title ptitle,p.business_id,p.payout_per_1k,p.max_payout_per_creator,p.budget_remaining,p.overall_budget FROM submissions s JOIN users u ON u.id=s.creator_id JOIN projects p ON p.id=s.project_id WHERE s.id=$sid AND p.business_id={$user['id']} LIMIT 1")->fetch_assoc();
if (!$sub) { echo '<div class="alert alert-danger">Submission not found.</div>'; require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; exit(); }

$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action = $_POST['action']??'';
    if ($action==='approve') {
        $views  = intval($_POST['view_count']??0);
        $amount = floatval($_POST['payout_amount']??0);
        $amount = min($amount, $sub['max_payout_per_creator']>0 ? $sub['max_payout_per_creator'] : $amount);
        $amount = min($amount, $sub['budget_remaining']);
        $conn->query("UPDATE submissions SET status='approved',view_count=$views,payout_amount=$amount,reviewed_at=NOW() WHERE id=$sid");
        $conn->query("UPDATE projects SET budget_remaining=budget_remaining-$amount WHERE id={$sub['project_id']}");
        notify($conn,$sub['creator_id'],"Your submission for \"{$sub['ptitle']}\" was approved! Payment of ".peso($amount)." will be released soon.","/linkra/creator/submissions.php");
        $success='Submission approved. You can now release payment.';
        $sub['status']='approved'; $sub['payout_amount']=$amount;
    } elseif ($action==='reject') {
        $reason = clean($conn,$_POST['rejection_reason']??'');
        $conn->query("UPDATE submissions SET status='rejected',rejection_reason='$reason',reviewed_at=NOW() WHERE id=$sid");
        notify($conn,$sub['creator_id'],"Your submission for \"{$sub['ptitle']}\" was not approved.".($reason?" Reason: $reason":''),"/linkra/creator/submissions.php");
        $success='Submission rejected.';
        $sub['status']='rejected';
    }
}
$cu = ['name'=>$sub['cname'],'avatar'=>$sub['cavatar']];
?>
<div class="page-header">
  <div><div class="page-heading">Review Submission</div><div class="page-sub"><?=htmlspecialchars($sub['ptitle'])?></div></div>
  <a href="/linkra/business/campaign.php?id=<?=$sub['project_id']?>" class="btn btn-secondary"><i class="ti ti-arrow-left"></i>Back to campaign</a>
</div>
<?php if($success): ?><div class="alert alert-success" data-auto-dismiss><i class="ti ti-check"></i><?=htmlspecialchars($success)?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><i class="ti ti-alert-circle"></i><?=htmlspecialchars($error)?></div><?php endif; ?>

<div class="card mb-16">
  <div class="card-header">
    <div style="display:flex;align-items:center;gap:10px"><?=avatar_html($cu,'md')?><div><div style="font-weight:600"><?=htmlspecialchars($sub['cname'])?></div><div style="font-size:12px;color:var(--text3)"><?=time_ago($sub['submitted_at'])?></div></div></div>
    <span class="badge badge-<?=$sub['status']?>"><?=ucfirst($sub['status'])?></span>
  </div>
  <?php render_video($sub['video_url'],480); ?>
  <?php if($sub['view_count']>0): ?><div style="margin-top:8px;font-size:13px;color:var(--text2)"><i class="ti ti-eye" style="vertical-align:-2px;margin-right:4px"></i><?=number_format($sub['view_count'])?> views · Payout: <strong><?=peso($sub['payout_amount'])?></strong></div><?php endif; ?>
</div>

<?php if($sub['status']==='pending'): ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  <!-- Approve -->
  <div class="card">
    <div class="card-header"><div class="card-title" style="color:var(--success)"><i class="ti ti-circle-check" style="margin-right:6px"></i>Approve</div></div>
    <form method="POST">
      <input type="hidden" name="action" value="approve">
      <div class="form-group">
        <label class="form-label">View count (current)</label>
        <input type="number" name="view_count" class="form-control" placeholder="e.g. 15000" min="0" required>
        <div class="form-hint">Enter the current view count of the submitted video.</div>
      </div>
      <div class="form-group">
        <label class="form-label">Payout amount (₱)</label>
        <?php
        $suggested = $sub['payout_per_1k']>0 ? 0 : ($sub['max_payout_per_creator']>0 ? $sub['max_payout_per_creator'] : 0);
        ?>
        <input type="number" name="payout_amount" class="form-control" placeholder="e.g. 2500" min="0" step="0.01" value="<?=$suggested?:''?>" required>
        <div class="form-hint">
          <?php if($sub['payout_per_1k']>0): ?>Per 1K views: <?=peso($sub['payout_per_1k'])?>. <?php endif; ?>
          <?php if($sub['max_payout_per_creator']>0): ?>Max per creator: <?=peso($sub['max_payout_per_creator'])?>. <?php endif; ?>
          Budget remaining: <strong><?=peso($sub['budget_remaining'])?></strong>
        </div>
      </div>
      <button type="submit" class="btn btn-success btn-full" onclick="return confirm('Approve this submission?')"><i class="ti ti-check"></i>Approve &amp; set payout</button>
    </form>
  </div>
  <!-- Reject -->
  <div class="card">
    <div class="card-header"><div class="card-title" style="color:var(--danger)"><i class="ti ti-circle-x" style="margin-right:6px"></i>Reject</div></div>
    <form method="POST">
      <input type="hidden" name="action" value="reject">
      <div class="form-group">
        <label class="form-label">Reason for rejection <span class="req">*</span></label>
        <div class="field-wrap">
          <textarea name="rejection_reason" class="form-control" rows="5" maxlength="400" placeholder="Explain why this submission doesn't meet requirements..." required></textarea>
          <div class="char-counter">0/400</div>
        </div>
        <div class="form-hint">This will be sent to the creator as feedback.</div>
      </div>
      <button type="submit" class="btn btn-danger btn-full" onclick="return confirm('Reject this submission?')"><i class="ti ti-x"></i>Reject submission</button>
    </form>
  </div>
</div>
<?php elseif($sub['status']==='approved'): ?>
<div class="alert alert-success"><i class="ti ti-check"></i>Submission approved — payout: <strong><?=peso($sub['payout_amount'])?></strong>
  <?php $pay=$conn->query("SELECT * FROM payments WHERE submission_id=$sid LIMIT 1")->fetch_assoc(); ?>
  <?php if($pay && $pay['status']==='released'): ?> · <span class="badge badge-released">Paid</span>
  <?php else: ?><a href="/linkra/business/pay-submission.php?id=<?=$sid?>" class="btn btn-primary btn-sm" style="margin-left:10px"><i class="ti ti-coin"></i>Release payment</a><?php endif; ?>
</div>
<?php elseif($sub['status']==='rejected'): ?>
<div class="alert alert-danger"><i class="ti ti-x"></i>Rejected — <?=htmlspecialchars($sub['rejection_reason'])?></div>
<?php endif; ?>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
