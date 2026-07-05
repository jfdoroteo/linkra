<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-business.php'; ?>
<div class="page-body" style="max-width:820px">
<?php
$pid  = intval($_GET['id']??0);
$proj = $conn->query("SELECT * FROM projects WHERE id=$pid AND business_id={$user['id']} LIMIT 1")->fetch_assoc();
if (!$proj) { echo '<div class="alert alert-danger">Campaign not found.</div>'; require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; exit(); }
$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (isset($_POST['delete'])) {
        $conn->query("DELETE FROM projects WHERE id=$pid AND business_id={$user['id']}");
        header('Location:/linkra/business/campaigns.php'); exit();
    }
    $title   = clean($conn,$_POST['title']??'');
    $desc    = clean($conn,$_POST['description']??'');
    $req     = clean($conn,$_POST['requirements']??'');
    $plat    = clean($conn,$_POST['platform']??'all');
    $ctype   = clean($conn,$_POST['content_type']??'both');
    $cat     = clean($conn,$_POST['category']??'');
    $budget  = floatval($_POST['overall_budget']??0);
    $per1k   = floatval($_POST['payout_per_1k']??0);
    $maxpay  = floatval($_POST['max_payout_per_creator']??0);
    $dead    = clean($conn,$_POST['deadline']??'');
    $status  = clean($conn,$_POST['status']??'open');
    if (!$title||!$desc) { $error='Title and description are required.'; }
    else {
        $thumb_val = "thumbnail='{$proj['thumbnail']}'";
        if (isset($_FILES['thumbnail'])&&$_FILES['thumbnail']['error']===0) {
            $t=upload_file('thumbnail','thumb',['jpg','jpeg','png','webp']);
            if ($t) $thumb_val="thumbnail='$t'";
        }
        $dead_val = $dead?"'$dead'":'NULL';
        $diff = $budget - $proj['overall_budget'];
        $new_remaining = max(0,$proj['budget_remaining']+$diff);
        $conn->query("UPDATE projects SET title='$title',description='$desc',requirements='$req',platform='$plat',content_type='$ctype',category='$cat',overall_budget=$budget,budget_remaining=$new_remaining,payout_per_1k=$per1k,max_payout_per_creator=$maxpay,deadline=$dead_val,status='$status',$thumb_val WHERE id=$pid");
        $success='Campaign updated!';
        $proj=array_merge($proj,['title'=>$title,'description'=>$desc,'requirements'=>$req,'platform'=>$plat,'content_type'=>$ctype,'category'=>$cat,'overall_budget'=>$budget,'payout_per_1k'=>$per1k,'max_payout_per_creator'=>$maxpay,'deadline'=>$dead,'status'=>$status]);
    }
}
?>
<div class="page-header">
  <div><div class="page-heading">Edit Campaign</div></div>
  <div class="page-header-right">
    <a href="/linkra/business/campaign.php?id=<?=$pid?>" class="btn btn-secondary"><i class="ti ti-arrow-left"></i>Back</a>
    <form method="POST" onsubmit="return confirm('Delete this campaign permanently?')" style="display:inline">
      <input type="hidden" name="delete" value="1">
      <button type="submit" class="btn btn-danger btn-sm"><i class="ti ti-trash"></i>Delete</button>
    </form>
  </div>
</div>
<?php if($success): ?><div class="alert alert-success" data-auto-dismiss><i class="ti ti-check"></i><?=$success?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><i class="ti ti-alert-circle"></i><?=$error?></div><?php endif; ?>
<div class="card">
<form method="POST" enctype="multipart/form-data">
  <div class="form-row">
    <div class="form-group" style="grid-column:1/-1"><label class="form-label">Title <span class="req">*</span></label><input type="text" name="title" class="form-control" value="<?=htmlspecialchars($proj['title'])?>" required></div>
  </div>
  <div class="form-group"><label class="form-label">Description <span class="req">*</span></label><div class="field-wrap"><textarea name="description" class="form-control" rows="4" maxlength="1000" required><?=htmlspecialchars($proj['description'])?></textarea><div class="char-counter"></div></div></div>
  <div class="form-group"><label class="form-label">Requirements</label><div class="field-wrap"><textarea name="requirements" class="form-control" rows="3" maxlength="800"><?=htmlspecialchars($proj['requirements']??'')?></textarea><div class="char-counter"></div></div></div>
  <div class="form-row">
    <div class="form-group"><label class="form-label">Platform</label><select name="platform" class="form-control"><option value="all" <?=$proj['platform']==='all'?'selected':''?>>All platforms</option><option value="tiktok" <?=$proj['platform']==='tiktok'?'selected':''?>>TikTok</option><option value="reels" <?=$proj['platform']==='reels'?'selected':''?>>Instagram Reels</option><option value="shorts" <?=$proj['platform']==='shorts'?'selected':''?>>YouTube Shorts</option><option value="facebook" <?=$proj['platform']==='facebook'?'selected':''?>>Facebook</option><option value="x" <?=$proj['platform']==='x'?'selected':''?>>X (Twitter)</option></select></div>
    <div class="form-group"><label class="form-label">Content type</label><select name="content_type" class="form-control"><option value="both" <?=$proj['content_type']==='both'?'selected':''?>>Both</option><option value="short-form" <?=$proj['content_type']==='short-form'?'selected':''?>>Short-form only</option><option value="long-form" <?=$proj['content_type']==='long-form'?'selected':''?>>Long-form only</option></select></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label class="form-label">Category</label><select name="category" class="form-control"><option value="">Select</option><?php foreach(['Food','Fashion','Technology','Lifestyle','Fitness','Beauty','Travel','Entertainment','Other'] as $c): ?><option value="<?=$c?>" <?=$proj['category']===$c?'selected':''?>><?=$c?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label">Status</label><select name="status" class="form-control"><option value="open" <?=$proj['status']==='open'?'selected':''?>>Open</option><option value="closed" <?=$proj['status']==='closed'?'selected':''?>>Closed</option><option value="completed" <?=$proj['status']==='completed'?'selected':''?>>Completed</option></select></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label class="form-label">Deadline</label><input type="date" name="deadline" class="form-control" value="<?=$proj['deadline']?>"></div>
    <div class="form-group"><label class="form-label">Overall budget (₱)</label><input type="number" name="overall_budget" class="form-control" value="<?=$proj['overall_budget']?>" min="0" step="0.01"></div>
  </div>
  <div class="form-row">
    <div class="form-group"><label class="form-label">Payout per 1K views (₱)</label><input type="number" name="payout_per_1k" class="form-control" value="<?=$proj['payout_per_1k']?>" min="0" step="0.01"></div>
    <div class="form-group"><label class="form-label">Max payout per creator (₱)</label><input type="number" name="max_payout_per_creator" class="form-control" value="<?=$proj['max_payout_per_creator']?>" min="0" step="0.01"></div>
  </div>
  <?php if($proj['thumbnail']): ?><div style="margin-bottom:12px"><img src="/linkra/assets/<?=htmlspecialchars($proj['thumbnail'])?>" style="max-height:120px;border-radius:var(--radius-sm)" alt="Current thumbnail"><div style="font-size:12px;color:var(--text3);margin-top:4px">Current thumbnail — upload a new one to replace it.</div></div><?php endif; ?>
  <div class="form-group"><label class="form-label">Replace thumbnail</label><input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/webp" data-preview="thumb-prev"><img id="thumb-prev" class="upload-preview" alt=""></div>
  <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
    <a href="/linkra/business/campaign.php?id=<?=$pid?>" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary"><i class="ti ti-check"></i>Save changes</button>
  </div>
</form>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
