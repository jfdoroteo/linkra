<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-business.php'; ?>
<div class="page-body" style="max-width:680px">
<?php
refresh_session($conn);
$biz = $conn->query("SELECT * FROM businesses WHERE user_id={$user['id']} LIMIT 1")->fetch_assoc();
$success=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name    = clean($conn,$_POST['name']??'');
    $company = clean($conn,$_POST['company_name']??'');
    $ind     = clean($conn,$_POST['industry']??'');
    $web     = clean($conn,$_POST['website']??'');
    $desc    = clean($conn,$_POST['description']??'');
    if (!$name||!$company) { $error='Name and company name are required.'; }
    else {
        // Handle avatar upload
        $avatar_set = '';
        if (isset($_FILES['avatar'])&&$_FILES['avatar']['error']===0) {
            $av=upload_file('avatar','avatar',['jpg','jpeg','png','webp']);
            if ($av) {
                $conn->query("UPDATE users SET avatar='$av' WHERE id={$user['id']}");
                $_SESSION['user']['avatar']=$av;
                $avatar_set=$av;
            }
        }
        // Handle logo upload
        $logo_val = $biz['logo']??'';
        if (isset($_FILES['logo'])&&$_FILES['logo']['error']===0) {
            $lg=upload_file('logo','logo',['jpg','jpeg','png','webp']);
            if ($lg) $logo_val=$lg;
        }
        $conn->query("UPDATE users SET name='$name' WHERE id={$user['id']}");
        if ($biz) {
            $conn->query("UPDATE businesses SET company_name='$company',industry='$ind',website='$web',description='$desc',logo='$logo_val' WHERE user_id={$user['id']}");
        } else {
            $conn->query("INSERT INTO businesses (user_id,company_name,industry,website,description,logo) VALUES ({$user['id']},'$company','$ind','$web','$desc','$logo_val')");
        }
        $_SESSION['user']['name']=$name;
        $success='Profile updated!';
        $biz=array_merge($biz??[],['company_name'=>$company,'industry'=>$ind,'website'=>$web,'description'=>$desc,'logo'=>$logo_val]);
        $user=$_SESSION['user'];
    }
}
?>
<div class="page-header"><div><div class="page-heading">Business Profile</div></div></div>
<?php if($success): ?><div class="alert alert-success" data-auto-dismiss><i class="ti ti-check"></i><?=$success?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><i class="ti ti-alert-circle"></i><?=$error?></div><?php endif; ?>
<div class="card">
<form method="POST" enctype="multipart/form-data">
  <!-- Avatar upload -->
  <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border)">
    <label class="avatar-upload" for="avatar-input" title="Change profile photo">
      <?=avatar_html($user,'xl')?>
      <div class="avatar-upload-overlay"><i class="ti ti-camera"></i></div>
      <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp" data-preview="avatar-preview">
    </label>
    <div>
      <div style="font-size:18px;font-weight:600"><?=htmlspecialchars($user['name'])?></div>
      <div style="font-size:13px;color:var(--text2)"><?=htmlspecialchars($user['email'])?></div>
      <div style="font-size:12px;color:var(--text3);margin-top:4px">Click photo to change</div>
    </div>
  </div>
  <div id="avatar-preview-wrap" style="display:none;margin-bottom:16px"><img id="avatar-preview" style="width:80px;height:80px;border-radius:50%;object-fit:cover" alt="Preview"></div>
  <div class="form-group"><label class="form-label">Full name <span class="req">*</span></label><input type="text" name="name" class="form-control" value="<?=htmlspecialchars($user['name']??'')?>" required></div>
  <div class="form-group"><label class="form-label">Company / Brand name <span class="req">*</span></label><input type="text" name="company_name" class="form-control" value="<?=htmlspecialchars($biz['company_name']??'')?>" required></div>
  <div class="form-row">
    <div class="form-group"><label class="form-label">Industry</label><select name="industry" class="form-control"><?php foreach(['Food & Beverage','Fashion','Technology','Health & Fitness','Beauty & Skincare','Retail','Entertainment','Other'] as $i): ?><option value="<?=$i?>" <?=($biz['industry']??'')===$i?'selected':''?>><?=$i?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label class="form-label">Website</label><input type="url" name="website" class="form-control" placeholder="https://yoursite.com" value="<?=htmlspecialchars($biz['website']??'')?>"></div>
  </div>
  <div class="form-group"><label class="form-label">About your business</label><div class="field-wrap"><textarea name="description" class="form-control" rows="4" maxlength="500"><?=htmlspecialchars($biz['description']??'')?></textarea><div class="char-counter"></div></div></div>
  <div class="form-group"><label class="form-label">Company logo <span style="font-weight:400;color:var(--text3)">(optional)</span></label><?php if(!empty($biz['logo'])): ?><img src="/linkra/assets/<?=htmlspecialchars($biz['logo'])?>" style="height:48px;border-radius:6px;margin-bottom:8px" alt="Logo"><?php endif; ?><input type="file" name="logo" class="form-control" accept="image/jpeg,image/png,image/webp" data-preview="logo-preview"><img id="logo-preview" class="upload-preview" alt=""></div>
  <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-control" value="<?=htmlspecialchars($user['email']??'')?>" disabled><div class="form-hint">Email cannot be changed.</div></div>
  <div style="display:flex;justify-content:flex-end"><button type="submit" class="btn btn-primary"><i class="ti ti-check"></i>Save profile</button></div>
</form>
</div>
</div>
<script>
document.getElementById('avatar-input')?.addEventListener('change',function(){
  const wrap=document.getElementById('avatar-preview-wrap');
  if(this.files[0]){wrap.style.display='block';}
});
</script>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
