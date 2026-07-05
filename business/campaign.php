<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-business.php'; ?>
<div class="page-body">
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/video-embed.php';
$pid  = intval($_GET['id']??0);
$proj = $conn->query("SELECT * FROM projects WHERE id=$pid AND business_id={$user['id']} LIMIT 1")->fetch_assoc();
if (!$proj) { echo '<div class="alert alert-danger">Campaign not found.</div>'; require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; exit(); }

// Handle comment
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['comment'])) {
    $body = clean($conn,$_POST['comment']);
    if ($body) { $conn->query("INSERT INTO project_comments (project_id,user_id,body) VALUES ($pid,{$user['id']},'$body')"); }
    header("Location:/linkra/business/campaign.php?id=$pid#comments"); exit();
}
// Handle close/reopen
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['toggle_status'])) {
    $ns = $proj['status']==='open' ? 'closed' : 'open';
    $conn->query("UPDATE projects SET status='$ns' WHERE id=$pid");
    header("Location:/linkra/business/campaign.php?id=$pid"); exit();
}

$subs     = $conn->query("SELECT s.*,u.name cname,u.avatar cavatar FROM submissions s JOIN users u ON u.id=s.creator_id WHERE s.project_id=$pid ORDER BY s.submitted_at DESC");
$comments = $conn->query("SELECT c.*,u.name uname,u.avatar uavatar,u.role urole FROM project_comments c JOIN users u ON u.id=c.user_id WHERE c.project_id=$pid ORDER BY c.created_at ASC");
$paid_out = $conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE project_id=$pid AND status='released'")->fetch_assoc()['s'];
$pct = $proj['overall_budget']>0 ? min(100,round((($proj['overall_budget']-$proj['budget_remaining'])/$proj['overall_budget'])*100)) : 0;
$status_map = ['open'=>'Open','closed'=>'Closed','completed'=>'Completed'];
?>
<?php if(isset($_GET['created'])): ?><div class="alert alert-success" data-auto-dismiss><i class="ti ti-check"></i>Campaign created and now live! Creators can start submitting.</div><?php endif; ?>
<div class="page-header">
  <div>
    <div class="page-heading"><?=htmlspecialchars($proj['title'])?></div>
    <div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap">
      <span class="badge badge-<?=$proj['status']?>"><?=$status_map[$proj['status']]?></span>
      <span class="badge badge-<?=$proj['platform']?>"><?=strtoupper($proj['platform'])==='ALL'?'All platforms':strtoupper($proj['platform'])?></span>
      <?php if($proj['content_type']): ?><span class="badge badge-<?=$proj['content_type']?>"><?=ucwords(str_replace('-',' ',$proj['content_type']))?></span><?php endif; ?>
      <?php if($proj['category']): ?><span class="badge badge-<?=strtolower($proj['category'])?>"><?=$proj['category']?></span><?php endif; ?>
    </div>
  </div>
  <div class="page-header-right">
    <a href="/linkra/business/edit-campaign.php?id=<?=$pid?>" class="btn btn-secondary btn-sm"><i class="ti ti-edit"></i>Edit</a>
    <form method="POST" style="display:inline"><input type="hidden" name="toggle_status" value="1">
      <button type="submit" class="btn <?=$proj['status']==='open'?'btn-warning':'btn-success'?> btn-sm">
        <i class="ti ti-<?=$proj['status']==='open'?'lock':'lock-open'?>"></i><?=$proj['status']==='open'?'Close campaign':'Reopen'?>
      </button>
    </form>
    <a href="/linkra/business/dashboard.php" class="btn btn-ghost btn-sm"><i class="ti ti-arrow-left"></i>Back</a>
  </div>
</div>

<div class="two-col">
  <div>
    <!-- Campaign thumbnail -->
    <?php if($proj['thumbnail']): ?>
    <div style="border-radius:var(--radius);overflow:hidden;margin-bottom:16px;border:1px solid var(--border)">
      <img src="/linkra/assets/<?=htmlspecialchars($proj['thumbnail'])?>" style="width:100%;max-height:260px;object-fit:cover" alt="Campaign thumbnail">
    </div>
    <?php endif; ?>

    <!-- Brief -->
    <div class="card mb-16">
      <div class="card-header"><div class="card-title">Campaign Brief</div></div>
      <p style="color:var(--text2);line-height:1.7;margin-bottom:14px"><?=nl2br(htmlspecialchars($proj['description']))?></p>
      <?php if($proj['requirements']): ?>
      <div style="background:var(--bg3);border-radius:var(--radius-sm);padding:12px 14px;margin-bottom:14px">
        <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em">Requirements</div>
        <p style="font-size:13px;color:var(--text2);line-height:1.6"><?=nl2br(htmlspecialchars($proj['requirements']))?></p>
      </div>
      <?php endif; ?>
      <?php if($proj['instruction']): ?>
      <a href="/linkra/assets/<?=htmlspecialchars($proj['instruction'])?>" class="btn btn-secondary btn-sm" target="_blank"><i class="ti ti-download"></i>Download instruction file</a>
      <?php endif; ?>
    </div>

    <!-- Submissions -->
    <div class="card mb-16">
      <div class="card-header"><div class="card-title">Submissions (<?=$subs->num_rows?>)</div>
        <a href="/linkra/business/submissions.php?campaign=<?=$pid?>" class="see-all">View all →</a>
      </div>
      <?php if($subs->num_rows===0): ?>
      <div class="empty-state" style="padding:30px"><i class="ti ti-inbox"></i><h3>No submissions yet</h3><p>Creators will submit their content here once they see your campaign.</p></div>
      <?php else: while($s=$subs->fetch_assoc()): $cu=['name'=>$s['cname'],'avatar'=>$s['cavatar']]; ?>
      <div style="padding:14px 0;border-bottom:1px solid var(--border)">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px">
          <div style="display:flex;align-items:center;gap:8px">
            <?=avatar_html($cu,'sm')?>
            <div><div style="font-weight:600;font-size:13px"><?=htmlspecialchars($s['cname'])?></div>
              <div style="font-size:11px;color:var(--text3)"><?=time_ago($s['submitted_at'])?></div>
            </div>
          </div>
          <span class="badge badge-<?=$s['status']?>"><?=ucfirst($s['status'])?></span>
        </div>
        <?php render_video($s['video_url'],300); ?>
        <?php if($s['view_count']>0): ?><div style="font-size:12px;color:var(--text2);margin:6px 0"><i class="ti ti-eye" style="vertical-align:-2px;margin-right:3px"></i><?=number_format($s['view_count'])?> views · Payout: <?=peso($s['payout_amount'])?></div><?php endif; ?>
        <?php if($s['status']==='pending'): ?>
        <div style="display:flex;gap:8px;margin-top:10px">
          <a href="/linkra/business/review-submission.php?id=<?=$s['id']?>" class="btn btn-success btn-sm"><i class="ti ti-check"></i>Review &amp; approve</a>
          <a href="/linkra/business/review-submission.php?id=<?=$s['id']?>" class="btn btn-danger btn-sm"><i class="ti ti-x"></i>Reject</a>
        </div>
        <?php elseif($s['status']==='approved'): ?>
        <div style="margin-top:8px">
          <?php $pay=$conn->query("SELECT * FROM payments WHERE submission_id={$s['id']} LIMIT 1")->fetch_assoc(); ?>
          <?php if($pay && $pay['status']==='released'): ?>
          <span class="badge badge-released"><i class="ti ti-check"></i>Paid <?=peso($pay['amount'])?></span>
          <?php else: ?>
          <a href="/linkra/business/pay-submission.php?id=<?=$s['id']?>" class="btn btn-primary btn-sm"><i class="ti ti-coin"></i>Release payment</a>
          <?php endif; ?>
        </div>
        <?php elseif($s['status']==='rejected'): ?>
        <div style="margin-top:6px;font-size:12px;color:var(--danger)"><i class="ti ti-x" style="margin-right:3px"></i>Rejected<?=$s['rejection_reason']?': '.htmlspecialchars($s['rejection_reason']):'.'?></div>
        <?php endif; ?>
      </div>
      <?php endwhile; endif; ?>
    </div>

    <!-- Comments -->
    <div class="card" id="comments">
      <div class="card-header"><div class="card-title"><i class="ti ti-message-circle" style="margin-right:6px"></i>Campaign Q&A</div></div>
      <?php if($comments->num_rows===0): ?><div style="text-align:center;padding:20px;color:var(--text3);font-size:13px">No comments yet.</div><?php else: while($c=$comments->fetch_assoc()): $cu2=['name'=>$c['uname'],'avatar'=>$c['uavatar']]; ?>
      <div class="comment-item">
        <?=avatar_html($cu2,'sm')?>
        <div style="flex:1">
          <div class="comment-author"><?=htmlspecialchars($c['uname'])?> <span class="badge badge-<?=$c['urole']?>" style="font-size:10px;vertical-align:middle"><?=ucfirst($c['urole'])?></span></div>
          <div class="comment-text"><?=nl2br(htmlspecialchars($c['body']))?></div>
          <div class="comment-time"><?=time_ago($c['created_at'])?></div>
        </div>
      </div>
      <?php endwhile; endif; ?>
      <form method="POST" style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
        <textarea name="comment" class="form-control" rows="2" placeholder="Post a comment or answer creator questions..." maxlength="500" required></textarea>
        <div style="display:flex;justify-content:flex-end;margin-top:8px"><button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-send"></i>Post</button></div>
      </form>
    </div>
  </div>

  <!-- Right panel -->
  <div>
    <div class="card mb-16">
      <div class="card-header"><div class="card-title">Budget tracker</div></div>
      <div style="text-align:center;margin-bottom:16px">
        <div style="font-size:32px;font-weight:700;color:var(--purple)"><?=peso($proj['budget_remaining'])?></div>
        <div style="font-size:13px;color:var(--text2)">remaining of <?=peso($proj['overall_budget'])?></div>
      </div>
      <div class="budget-bar-wrap"><div class="budget-bar <?=$pct>85?'danger':($pct>60?'warn':'')?>" style="width:<?=$pct?>%"></div></div>
      <div class="budget-labels" style="margin-top:4px"><span><?=$pct?>% used</span><span><?=100-$pct?>% left</span></div>
      <div class="divider"></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div style="text-align:center;padding:10px;background:var(--bg3);border-radius:var(--radius-sm)">
          <div style="font-size:18px;font-weight:700"><?=peso($proj['payout_per_1k'])?></div>
          <div style="font-size:11px;color:var(--text2)">per 1K views</div>
        </div>
        <div style="text-align:center;padding:10px;background:var(--bg3);border-radius:var(--radius-sm)">
          <div style="font-size:18px;font-weight:700"><?=peso($proj['max_payout_per_creator'])?></div>
          <div style="font-size:11px;color:var(--text2)">max per creator</div>
        </div>
      </div>
      <div class="divider"></div>
      <div style="font-size:13px;color:var(--text2);display:flex;justify-content:space-between"><span>Total paid out</span><strong><?=peso($paid_out)?></strong></div>
    </div>
    <div class="card mb-16">
      <div class="card-header"><div class="card-title">Campaign info</div></div>
      <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
        <?php if($proj['deadline']): ?><div style="display:flex;justify-content:space-between"><span style="color:var(--text2)">Deadline</span><strong><?=date('M j, Y',strtotime($proj['deadline']))?></strong></div><?php endif; ?>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--text2)">Posted</span><strong><?=date('M j, Y',strtotime($proj['created_at']))?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--text2)">Total submissions</span><strong><?=$subs->num_rows?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--text2)">Approved</span><strong style="color:var(--success)"><?=$conn->query("SELECT COUNT(*) c FROM submissions WHERE project_id=$pid AND status='approved'")->fetch_assoc()['c']?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--text2)">Rejected</span><strong style="color:var(--danger)"><?=$conn->query("SELECT COUNT(*) c FROM submissions WHERE project_id=$pid AND status='rejected'")->fetch_assoc()['c']?></strong></div>
      </div>
    </div>
  </div>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
