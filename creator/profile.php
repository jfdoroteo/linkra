<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-creator.php'; ?>
<div class="page-body" style="max-width:680px">
<?php
refresh_session($conn);
$cr = $conn->query("SELECT * FROM creators WHERE user_id={$user['id']} LIMIT 1")->fetch_assoc();
$success=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name  = clean($conn,$_POST['name']??'');
    $bio   = clean($conn,$_POST['bio']??'');
    $niche = clean($conn,$_POST['niche']??'');
    $tt    = clean($conn,$_POST['tiktok_url']??'');
    $ig    = clean($conn,$_POST['instagram_url']??'');
    $yt    = clean($conn,$_POST['youtube_url']??'');
    $fb    = clean($conn,$_POST['facebook_url']??'');
    $xurl  = clean($conn,$_POST['x_url']??'');
    $rmin  = floatval($_POST['rate_min']??0);
    $rmax  = floatval($_POST['rate_max']??0);
    if (!$name) { $error='Name is required.'; }
    else {
        // Avatar upload
        if (isset($_FILES['avatar'])&&$_FILES['avatar']['error']===0) {
            $av=upload_file('avatar','avatar',['jpg','jpeg','png','webp']);
            if ($av) { $conn->query("UPDATE users SET avatar='$av' WHERE id={$user['id']}"); $_SESSION['user']['avatar']=$av; }
        }
        $conn->query("UPDATE users SET name='$name' WHERE id={$user['id']}");
        if ($cr) {
            $conn->query("UPDATE creators SET bio='$bio',niche='$niche',tiktok_url='$tt',instagram_url='$ig',youtube_url='$yt',facebook_url='$fb',x_url='$xurl',rate_min=$rmin,rate_max=$rmax WHERE user_id={$user['id']}");
        } else {
            $conn->query("INSERT INTO creators (user_id,bio,niche,tiktok_url,instagram_url,youtube_url,facebook_url,x_url,rate_min,rate_max) VALUES ({$user['id']},'$bio','$niche','$tt','$ig','$yt','$fb','$xurl',$rmin,$rmax)");
        }
        $_SESSION['user']['name']=$name;
        $success='Profile updated!';
        $cr=array_merge($cr??[],['bio'=>$bio,'niche'=>$niche,'tiktok_url'=>$tt,'instagram_url'=>$ig,'youtube_url'=>$yt,'facebook_url'=>$fb,'x_url'=>$xurl,'rate_min'=>$rmin,'rate_max'=>$rmax]);
        $user=$_SESSION['user'];
    }
}
?>
<div class="page-header"><div><div class="page-heading">Creator Profile</div></div></div>
<?php if($success): ?><div class="alert alert-success" data-auto-dismiss><i class="ti ti-check"></i><?=$success?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><i class="ti ti-alert-circle"></i><?=$error?></div><?php endif; ?>
<div class="card">
<form method="POST" enctype="multipart/form-data">
  <!-- Avatar upload -->
  <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border)">
    <label class="avatar-upload" for="avatar-input" title="Change profile photo">
      <?=avatar_html($user,'xl')?>
      <div class="avatar-upload-overlay"><i class="ti ti-camera"></i></div>
      <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp" onchange="previewAvatar(this)">
    </label>
    <div>
      <div style="font-size:18px;font-weight:600"><?=htmlspecialchars($user['name'])?></div>
      <div style="font-size:13px;color:var(--text2)"><?=htmlspecialchars($user['email'])?></div>
      <div style="font-size:12px;color:var(--text3);margin-top:4px">Click photo to change</div>
    </div>
  </div>
  <div id="avatar-preview-wrap" style="display:none;margin-bottom:16px">
    <img id="avatar-prev" style="width:80px;height:80px;border-radius:50%;object-fit:cover" alt="Preview">
    <div style="font-size:12px;color:var(--text3);margin-top:4px">New photo preview</div>
  </div>

  <div class="form-group"><label class="form-label">Display name <span class="req">*</span></label><input type="text" name="name" class="form-control" value="<?=htmlspecialchars($user['name']??'')?>" required></div>
  <div class="form-group"><label class="form-label">Bio</label><div class="field-wrap"><textarea name="bio" class="form-control" rows="3" maxlength="300" placeholder="Tell businesses about yourself and your content style..."><?=htmlspecialchars($cr['bio']??'')?></textarea><div class="char-counter"></div></div></div>
  <div class="form-group"><label class="form-label">Content niche</label><select name="niche" class="form-control"><option value="">Select your niche</option><?php foreach(['Food','Fashion','Technology','Lifestyle','Fitness','Beauty','Travel','Entertainment','Other'] as $n): ?><option value="<?=$n?>" <?=($cr['niche']??'')===$n?'selected':''?>><?=$n?></option><?php endforeach; ?></select></div>

  <div class="divider"></div>
  <h5 style="margin-bottom:14px">Platform links</h5>
  <div class="form-group"><label class="form-label"><i class="ti ti-brand-tiktok" style="vertical-align:-2px;margin-right:4px"></i>TikTok URL</label><input type="url" name="tiktok_url" class="form-control" placeholder="https://tiktok.com/@yourhandle" value="<?=htmlspecialchars($cr['tiktok_url']??'')?>"></div>
  <div class="form-group"><label class="form-label"><i class="ti ti-brand-instagram" style="vertical-align:-2px;margin-right:4px"></i>Instagram URL</label><input type="url" name="instagram_url" class="form-control" placeholder="https://instagram.com/yourhandle" value="<?=htmlspecialchars($cr['instagram_url']??'')?>"></div>
  <div class="form-group"><label class="form-label"><i class="ti ti-brand-youtube" style="vertical-align:-2px;margin-right:4px"></i>YouTube URL</label><input type="url" name="youtube_url" class="form-control" placeholder="https://youtube.com/@yourchannel" value="<?=htmlspecialchars($cr['youtube_url']??'')?>"></div>
  <div class="form-group"><label class="form-label"><i class="ti ti-brand-facebook" style="vertical-align:-2px;margin-right:4px"></i>Facebook URL</label><input type="url" name="facebook_url" class="form-control" placeholder="https://facebook.com/yourpage" value="<?=htmlspecialchars($cr['facebook_url']??'')?>"></div>
  <div class="form-group"><label class="form-label"><i class="ti ti-brand-x" style="vertical-align:-2px;margin-right:4px"></i>X (Twitter) URL</label><input type="url" name="x_url" class="form-control" placeholder="https://x.com/yourhandle" value="<?=htmlspecialchars($cr['x_url']??'')?>"></div>

  <div class="divider"></div>
  <h5 style="margin-bottom:14px">Rate range (₱)</h5>
  <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:10px;align-items:center">
    <input type="number" name="rate_min" class="form-control" placeholder="Min e.g. 1500" value="<?=$cr['rate_min']??''?>" min="0">
    <span style="color:var(--text3)">to</span>
    <input type="number" name="rate_max" class="form-control" placeholder="Max e.g. 8000" value="<?=$cr['rate_max']??''?>" min="0">
  </div>
  <div class="form-group" style="margin-top:18px"><label class="form-label">Email</label><input type="email" class="form-control" value="<?=htmlspecialchars($user['email']??'')?>" disabled><div class="form-hint">Email cannot be changed.</div></div>
  <div style="display:flex;justify-content:flex-end;margin-top:8px"><button type="submit" class="btn btn-primary"><i class="ti ti-check"></i>Save profile</button></div>
</form>
</div>
<script>
function previewAvatar(input) {
  if (!input.files[0]) return;
  const wrap = document.getElementById('avatar-preview-wrap');
  const prev = document.getElementById('avatar-prev');
  const reader = new FileReader();
  reader.onload = e => { prev.src = e.target.result; wrap.style.display = 'block'; };
  reader.readAsDataURL(input.files[0]);
}
</script>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
