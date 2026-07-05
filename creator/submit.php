<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-creator.php'; ?>
<div class="page-body" style="max-width:740px">
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/video-embed.php';
$pid  = intval($_GET['id']??0);
$proj = $conn->query("SELECT p.*,b.company_name FROM projects p LEFT JOIN businesses b ON b.user_id=p.business_id WHERE p.id=$pid AND p.status='open' LIMIT 1")->fetch_assoc();
if (!$proj) { echo '<div class="alert alert-danger">Campaign not found or no longer open.</div>'; require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; exit(); }
if ($proj['budget_remaining'] <= 0) { echo '<div class="alert alert-warning"><i class="ti ti-alert-triangle"></i>This campaign has exhausted its budget and is no longer accepting submissions.</div>'; require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; exit(); }
$existing = $conn->query("SELECT * FROM submissions WHERE project_id=$pid AND creator_id={$user['id']} LIMIT 1")->fetch_assoc();
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $url = clean($conn,$_POST['video_url']??'');
    if (!$url) { $error='Please enter a video URL.'; }
    else {
        $type  = detect_video_type($url);
        $embed = get_embed_url($url);
        if (!$embed) { $error='Could not detect a valid TikTok, YouTube, Instagram, Facebook, or X video URL. Please check the link.'; }
        else {
            if ($existing) {
                $elapsed = time() - strtotime($existing['submitted_at']);
                if ($elapsed > 600) { $error='The 10-minute replacement window has expired. Your original submission is locked.'; }
                else {
                    $conn->query("UPDATE submissions SET video_url='$url',video_type='$type',submitted_at=NOW() WHERE id={$existing['id']}");
                    notify($conn,$proj['business_id'],"Creator updated their submission for \"{$proj['title']}\"","/linkra/business/campaign.php?id=$pid");
                    $success='Submission updated!';
                    $existing=$conn->query("SELECT * FROM submissions WHERE id={$existing['id']} LIMIT 1")->fetch_assoc();
                }
            } else {
                $conn->query("INSERT INTO submissions (project_id,creator_id,video_url,video_type,status) VALUES ($pid,{$user['id']},'$url','$type','pending')");
                notify($conn,$proj['business_id'],"New submission received for \"{$proj['title']}\"","/linkra/business/campaign.php?id=$pid");
                $success='Video submitted successfully! The business will review it soon.';
                $existing=$conn->query("SELECT * FROM submissions WHERE project_id=$pid AND creator_id={$user['id']} LIMIT 1")->fetch_assoc();
            }
        }
    }
}
$ts = $existing ? strtotime($existing['submitted_at']) : 0;
$window_open = $existing && (time()-$ts) < 600;
?>
<div class="page-header">
  <div><div class="page-heading">Submit Your Video</div><div class="page-sub"><?=htmlspecialchars($proj['title'])?> · <?=htmlspecialchars($proj['company_name']??'Business')?></div></div>
  <a href="/linkra/creator/campaign.php?id=<?=$pid?>" class="btn btn-secondary"><i class="ti ti-arrow-left"></i>Back</a>
</div>
<?php if($success): ?><div class="alert alert-success" data-auto-dismiss><i class="ti ti-check"></i><?=htmlspecialchars($success)?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><i class="ti ti-alert-circle"></i><?=htmlspecialchars($error)?></div><?php endif; ?>

<!-- Campaign brief recap -->
<div class="card mb-16">
  <div class="card-header"><div class="card-title">Campaign brief</div><a href="/linkra/creator/campaign.php?id=<?=$pid?>" class="see-all">Full brief →</a></div>
  <p style="font-size:13px;color:var(--text2);line-height:1.6;margin-bottom:12px"><?=nl2br(htmlspecialchars(substr($proj['description'],0,300)))?><?=strlen($proj['description'])>300?'...':''?></p>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <span class="badge badge-<?=$proj['platform']?>"><?=strtoupper($proj['platform'])==='ALL'?'All platforms':strtoupper($proj['platform'])?></span>
    <span class="badge badge-<?=$proj['content_type']?>"><?=ucwords(str_replace('-',' ',$proj['content_type']))?></span>
    <?php if($proj['payout_per_1k']>0): ?><span style="font-size:13px;font-weight:600;color:var(--purple)"><i class="ti ti-coin" style="vertical-align:-2px;margin-right:3px"></i><?=peso($proj['payout_per_1k'])?>/1K views</span><?php endif; ?>
    <?php if($proj['max_payout_per_creator']>0): ?><span style="font-size:13px;color:var(--text2)">Max <?=peso($proj['max_payout_per_creator'])?></span><?php endif; ?>
    <?php if($proj['instruction']): ?><a href="/linkra/assets/<?=htmlspecialchars($proj['instruction'])?>" class="btn btn-secondary btn-sm" target="_blank"><i class="ti ti-download"></i>Instructions</a><?php endif; ?>
  </div>
</div>

<!-- Submit form -->
<div class="card mb-16">
  <div class="card-header"><div class="card-title"><?=$existing?'Your submission':'Submit your video'?></div></div>
  <?php if($existing && $window_open): ?>
  <div class="countdown-wrap" id="countdown-wrap"><i class="ti ti-clock"></i><span>Replacement window: <span id="timer">10:00</span> — you can replace your link until it expires.</span></div>
  <?php elseif($existing && !$window_open): ?>
  <div class="alert alert-info mb-16"><i class="ti ti-lock"></i>The 10-minute replacement window has closed. Your submission is locked in.</div>
  <?php endif; ?>
  <?php if(!$existing || $window_open): ?>
  <form method="POST">
    <div class="form-group">
      <label class="form-label">Video URL <span class="req">*</span></label>
      <div class="input-icon-wrap"><i class="ti ti-link icon"></i>
      <input type="url" name="video_url" class="form-control" placeholder="https://www.tiktok.com/@you/video/... or youtube.com/shorts/... or instagram.com/reel/..." value="<?=$existing?htmlspecialchars($existing['video_url']):''?>" required></div>
      <div class="form-hint">Paste the public link to your video. Make sure it's publicly viewable before submitting.</div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg" id="replace-btn"><i class="ti ti-upload"></i><?=$existing?'Replace submission':'Submit video'?></button>
  </form>
  <?php endif; ?>
  <?php if($existing): ?>
  <div class="divider"></div>
  <div style="font-size:13px;font-weight:600;margin-bottom:10px">Currently submitted:</div>
  <?php render_video($existing['video_url']); ?>
  <div style="margin-top:6px;display:flex;align-items:center;gap:8px">
    <span class="badge badge-<?=$existing['status']?>"><?=ucfirst($existing['status'])?></span>
    <span style="font-size:12px;color:var(--text3)">Submitted <?=time_ago($existing['submitted_at'])?></span>
  </div>
  <?php endif; ?>
</div>
<?php if($ts && $window_open): ?>
<script>startCountdown(<?=$ts?>);</script>
<?php endif; ?>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
