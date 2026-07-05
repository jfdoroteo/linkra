<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-creator.php'; ?>
<div class="page-body">
<?php
$pid  = intval($_GET['id']??0);
$proj = $conn->query("SELECT p.*,b.company_name,b.description bdesc,b.logo,u.name bname,u.avatar bavatar FROM projects p JOIN users u ON u.id=p.business_id LEFT JOIN businesses b ON b.user_id=p.business_id WHERE p.id=$pid AND p.status='open' LIMIT 1")->fetch_assoc();
if (!$proj) { echo '<div class="alert alert-danger">Campaign not found or no longer active.</div>'; require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; exit(); }
$my_sub  = $conn->query("SELECT * FROM submissions WHERE project_id=$pid AND creator_id={$user['id']} LIMIT 1")->fetch_assoc();
$comments= $conn->query("SELECT c.*,u.name uname,u.avatar uavatar,u.role urole FROM project_comments c JOIN users u ON u.id=c.user_id WHERE c.project_id=$pid ORDER BY c.created_at ASC");
$success=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['comment'])) {
    $body=clean($conn,$_POST['comment']??'');
    if ($body) {
        $conn->query("INSERT INTO project_comments (project_id,user_id,body) VALUES ($pid,{$user['id']},'$body')");
        notify($conn,$proj['business_id'],"Creator asked a question on \"{$proj['title']}\"","/linkra/business/campaign.php?id=$pid#comments");
        header("Location:/linkra/creator/campaign.php?id=$pid#comments"); exit();
    }
}
$pct=$proj['overall_budget']>0?min(100,round((($proj['overall_budget']-$proj['budget_remaining'])/$proj['overall_budget'])*100)):0;
$bu=['name'=>$proj['company_name']??$proj['bname'],'avatar'=>$proj['bavatar']];
?>
<div class="page-header">
  <div><div class="page-heading"><?=htmlspecialchars($proj['title'])?></div>
    <div style="display:flex;gap:6px;margin-top:6px;flex-wrap:wrap">
      <span class="badge badge-<?=$proj['platform']?>"><?=strtoupper($proj['platform'])==='ALL'?'All platforms':strtoupper($proj['platform'])?></span>
      <span class="badge badge-<?=$proj['content_type']?>"><?=ucwords(str_replace('-',' ',$proj['content_type']))?></span>
      <?php if($proj['category']): ?><span class="badge badge-<?=strtolower($proj['category'])?>"><?=$proj['category']?></span><?php endif; ?>
    </div>
  </div>
  <a href="/linkra/creator/browse.php" class="btn btn-secondary"><i class="ti ti-arrow-left"></i>Back</a>
</div>
<div class="two-col">
  <div>
    <?php if($proj['thumbnail']): ?><div style="border-radius:var(--radius);overflow:hidden;margin-bottom:16px;border:1px solid var(--border)"><img src="/linkra/assets/<?=htmlspecialchars($proj['thumbnail'])?>" style="width:100%;max-height:240px;object-fit:cover" alt=""></div><?php endif; ?>
    <div class="card mb-16">
      <div class="card-header"><div class="card-title">Campaign Brief</div></div>
      <p style="color:var(--text2);line-height:1.7;margin-bottom:14px"><?=nl2br(htmlspecialchars($proj['description']))?></p>
      <?php if($proj['requirements']): ?>
      <div style="background:var(--bg3);border-radius:var(--radius-sm);padding:12px 14px;margin-bottom:14px">
        <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em">Requirements</div>
        <p style="font-size:13px;color:var(--text2);line-height:1.6"><?=nl2br(htmlspecialchars($proj['requirements']))?></p>
      </div>
      <?php endif; ?>
      <?php if($proj['instruction']): ?><a href="/linkra/assets/<?=htmlspecialchars($proj['instruction'])?>" class="btn btn-secondary btn-sm" target="_blank"><i class="ti ti-download"></i>Download instructions</a><?php endif; ?>
    </div>
    <?php if($my_sub): ?>
    <div class="alert alert-<?=$my_sub['status']==='approved'?'success':($my_sub['status']==='rejected'?'danger':'info')?>">
      <i class="ti ti-<?=$my_sub['status']==='approved'?'check':($my_sub['status']==='rejected'?'x':'clock')?>"></i>
      Your submission is <strong><?=ucfirst($my_sub['status'])?></strong>.
      <?php if($my_sub['status']==='rejected'&&$my_sub['rejection_reason']): ?><br><span style="font-size:12px"><?=htmlspecialchars($my_sub['rejection_reason'])?></span><?php endif; ?>
      <?php if($my_sub['status']==='approved'): ?><br><span style="font-size:12px">Payout: <strong><?=peso($my_sub['payout_amount'])?></strong></span><?php endif; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info"><i class="ti ti-info-circle"></i>No application needed — create your content, then <a href="/linkra/creator/submit.php?id=<?=$pid?>"><strong>submit your video link directly</strong></a>.</div>
    <?php endif; ?>
    <!-- Comments -->
    <div class="card" id="comments">
      <div class="card-header"><div class="card-title"><i class="ti ti-message-circle" style="margin-right:6px"></i>Campaign Q&A</div></div>
      <?php if($comments->num_rows===0): ?><div style="text-align:center;padding:20px;color:var(--text3);font-size:13px">No comments yet. Ask the business a question below.</div><?php else: while($c=$comments->fetch_assoc()): $cu2=['name'=>$c['uname'],'avatar'=>$c['uavatar']]; ?>
      <div class="comment-item"><?=avatar_html($cu2,'sm')?><div style="flex:1"><div class="comment-author"><?=htmlspecialchars($c['uname'])?> <span class="badge badge-<?=$c['urole']?>" style="font-size:10px;vertical-align:middle"><?=ucfirst($c['urole'])?></span></div><div class="comment-text"><?=nl2br(htmlspecialchars($c['body']))?></div><div class="comment-time"><?=time_ago($c['created_at'])?></div></div></div>
      <?php endwhile; endif; ?>
      <form method="POST" style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
        <textarea name="comment" class="form-control" rows="2" placeholder="Ask the business a question..." maxlength="500" required></textarea>
        <div style="display:flex;justify-content:flex-end;margin-top:8px"><button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-send"></i>Post</button></div>
      </form>
    </div>
  </div>
  <div>
    <div class="card mb-16">
      <div class="card-header"><div class="card-title">Payout info</div></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
        <?php if($proj['payout_per_1k']>0): ?><div style="text-align:center;padding:12px;background:var(--bg3);border-radius:var(--radius-sm)"><div style="font-size:20px;font-weight:700;color:var(--purple)"><?=peso($proj['payout_per_1k'])?></div><div style="font-size:11px;color:var(--text2)">per 1K views</div></div><?php endif; ?>
        <?php if($proj['max_payout_per_creator']>0): ?><div style="text-align:center;padding:12px;background:var(--bg3);border-radius:var(--radius-sm)"><div style="font-size:20px;font-weight:700"><?=peso($proj['max_payout_per_creator'])?></div><div style="font-size:11px;color:var(--text2)">max per creator</div></div><?php endif; ?>
      </div>
      <div style="font-size:13px;font-weight:600;margin-bottom:6px">Remaining budget</div>
      <div style="font-size:24px;font-weight:700;color:var(--success);margin-bottom:6px"><?=peso($proj['budget_remaining'])?></div>
      <div style="background:var(--bg3);border-radius:4px;height:6px;overflow:hidden;margin-bottom:4px"><div style="height:100%;width:<?=100-$pct?>%;background:var(--success);border-radius:4px"></div></div>
      <div style="font-size:11px;color:var(--text3)"><?=100-$pct?>% budget remaining</div>
      <?php if($proj['deadline']): ?><div class="divider"></div><div style="font-size:13px;color:var(--text2)">Deadline: <strong><?=date('M j, Y',strtotime($proj['deadline']))?></strong></div><?php endif; ?>
    </div>
    <div class="card mb-16">
      <div class="card-header"><div class="card-title">About the business</div></div>
      <div style="text-align:center;padding:12px 0">
        <?php $biz_disp=['name'=>$proj['company_name']??$proj['bname'],'avatar'=>$proj['logo']??$proj['bavatar']]; ?>
        <?=avatar_html($biz_disp,'lg')?>
        <div style="font-weight:600;font-size:16px;margin-top:10px"><?=htmlspecialchars($proj['company_name']??$proj['bname'])?></div>
      </div>
      <?php if($proj['bdesc']): ?><p style="font-size:13px;color:var(--text2);line-height:1.6"><?=htmlspecialchars($proj['bdesc'])?></p><?php endif; ?>
    </div>
    <?php if(!$my_sub && $proj['budget_remaining']>0): ?>
    <a href="/linkra/creator/submit.php?id=<?=$pid?>" class="btn btn-primary btn-lg btn-full"><i class="ti ti-upload"></i>Submit your video</a>
    <?php endif; ?>
  </div>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
