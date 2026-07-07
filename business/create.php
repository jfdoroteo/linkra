<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-business.php'; ?>
<div class="page-body" style="max-width:820px">
<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = clean($conn,$_POST['title']??'');
    $desc     = clean($conn,$_POST['description']??'');
    $req      = clean($conn,$_POST['requirements']??'');
    $platform = clean($conn,$_POST['platform']??'all');
    $ctype    = clean($conn,$_POST['content_type']??'both');
    $cat      = clean($conn,$_POST['category']??'');
    $budget   = floatval($_POST['overall_budget']??0);
    $per1k    = floatval($_POST['payout_per_1k']??0);
    $maxpay   = floatval($_POST['max_payout_per_creator']??0);
    $dead     = clean($conn,$_POST['deadline']??'');
    if (!$title||!$desc||$budget<=0) { $error='Title, description, and overall budget are required.'; }
    else {
        $thumb = upload_file('thumbnail','thumb',['jpg','jpeg','png','webp']);
        $instr = upload_file('instruction','instr',['pdf','doc','docx','jpg','jpeg','png']);
        $thumb_val = $thumb?"'$thumb'":'NULL';
        $instr_val = $instr?"'$instr'":'NULL';
        $dead_val  = $dead?"'$dead'":'NULL';
        $conn->query("INSERT INTO projects (business_id,title,description,requirements,platform,content_type,category,overall_budget,budget_remaining,payout_per_1k,max_payout_per_creator,deadline,thumbnail,instruction)
            VALUES ({$user['id']},'$title','$desc','$req','$platform','$ctype','$cat',$budget,$budget,$per1k,$maxpay,$dead_val,$thumb_val,$instr_val)");
        $pid = $conn->insert_id;
        header("Location:/linkra/business/campaign.php?id=$pid&created=1"); exit();
    }
}
?>
<div class="page-header">
  <div><div class="page-heading">Create Campaign</div><div class="page-sub">Fill in all details. Creators will be able to submit content once it's live.</div></div>
  <a href="/linkra/business/dashboard.php" class="btn btn-secondary"><i class="ti ti-arrow-left"></i>Back</a>
</div>
<?php if($error): ?><div class="alert alert-danger"><i class="ti ti-alert-circle"></i><?=htmlspecialchars($error)?></div><?php endif; ?>
<div class="card">
<form method="POST" enctype="multipart/form-data">
  <h5 style="margin-bottom:16px;color:var(--text)">Campaign details</h5>
  <div class="form-group">
    <label class="form-label">Campaign title <span class="req">*</span></label>
    <input type="text" name="title" class="form-control" placeholder="e.g. TikTok promo for new menu launch" value="<?=htmlspecialchars($_POST['title']??'')?>" required>
  </div>
  <div class="form-group">
    <label class="form-label">Description <span class="req">*</span></label>
    <div class="field-wrap">
      <textarea name="description" class="form-control" rows="4" maxlength="1000" placeholder="What is this campaign about? What are you promoting?" required><?=htmlspecialchars($_POST['description']??'')?></textarea>
      <div class="char-counter">0/1000</div>
    </div>
  </div>
  <div class="form-group">
    <label class="form-label">Content requirements</label>
    <div class="field-wrap">
      <textarea name="requirements" class="form-control" rows="3" maxlength="800" placeholder="Specific instructions for creators — style, tone, duration, dos and don'ts..."><?=htmlspecialchars($_POST['requirements']??'')?></textarea>
      <div class="char-counter">0/800</div>
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">Target platform</label>
      <select name="platform" class="form-control">
        <option value="all">All platforms</option>
        <option value="tiktok">TikTok</option>
        <option value="reels">Instagram Reels</option>
        <option value="shorts">YouTube Shorts</option>
        <option value="facebook">Facebook</option>
        <option value="x">X (Twitter)</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Content type</label>
      <select name="content_type" class="form-control">
        <option value="both">Both short &amp; long-form</option>
        <option value="short-form">Short-form only (under 60s)</option>
        <option value="long-form">Long-form only (60s+)</option>
      </select>
    </div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">Category</label>
      <select name="category" class="form-control">
        <option value="">Select category</option>
        <?php foreach(['Food','Fashion','Technology','Lifestyle','Fitness','Beauty','Travel','Entertainment','Other'] as $c): ?>
        <option value="<?=$c?>"><?=$c?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Campaign deadline</label>
      <input type="date" name="deadline" class="form-control" min="<?=date('Y-m-d')?>">
    </div>
  </div>

  <div class="divider"></div>
  <h5 style="margin-bottom:6px;color:var(--text)">Payout structure</h5>
  <p style="font-size:13px;color:var(--text2);margin-bottom:16px">Set how creators earn and how much you're willing to spend in total.</p>
  <div class="form-row-3">
    <div class="form-group">
      <label class="form-label">Overall budget (₱) <span class="req">*</span></label>
      <input type="number" name="overall_budget" class="form-control" placeholder="e.g. 50000" min="1" step="0.01" value="<?=htmlspecialchars($_POST['overall_budget']??'')?>" required>
      <div class="form-hint">Total ₱ available across all submissions.</div>
    </div>
    <div class="form-group">
      <label class="form-label">Payout per 1K views (₱)</label>
      <input type="number" name="payout_per_1k" class="form-control" placeholder="e.g. 150" min="0" step="0.01" value="<?=htmlspecialchars($_POST['payout_per_1k']??'')?>">
      <div class="form-hint">How much to pay per 1,000 video views.</div>
    </div>
    <div class="form-group">
      <label class="form-label">Max payout per creator (₱)</label>
      <input type="number" name="max_payout_per_creator" class="form-control" placeholder="e.g. 5000" min="0" step="0.01" value="<?=htmlspecialchars($_POST['max_payout_per_creator']??'')?>">
      <div class="form-hint">Cap per creator submission.</div>
    </div>
  </div>

  <div class="divider"></div>
  <h5 style="margin-bottom:16px;color:var(--text)">Media</h5>
  <div class="form-row">
    <div class="form-group">
      <label class="form-label">Campaign thumbnail</label>
      <label class="file-upload-area" for="thumb-input">
        <i class="ti ti-photo"></i>
        <p>Click to upload thumbnail<br><span style="font-size:11px;color:var(--text3)">JPG, PNG, WebP — recommended 1200×630px</span></p>
        <input type="file" id="thumb-input" name="thumbnail" accept="image/jpeg,image/png,image/webp" data-preview="thumb-preview">
      </label>
      <img id="thumb-preview" class="upload-preview" alt="Thumbnail preview">
    </div>
    <div class="form-group">
      <label class="form-label">Instruction file <span style="font-weight:400;color:var(--text3)">(optional)</span></label>
      <label class="file-upload-area" for="instr-input">
        <i class="ti ti-file-description"></i>
        <p>Click to upload brief<br><span style="font-size:11px;color:var(--text3)">PDF, Word, or image</span></p>
        <input type="file" id="instr-input" name="instruction" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" data-preview="instr-preview">
      </label>
      <img id="instr-preview" class="upload-preview" alt="Instruction preview">
    </div>
  </div>
  <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
    <a href="/linkra/business/dashboard.php" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary btn-lg"><i class="ti ti-rocket"></i>Launch Campaign</button>
  </div>
</form>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
