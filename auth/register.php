<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';
redirect_if_logged_in();
$error = '';
$prefill = $_GET['role'] ?? 'creator';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role    = in_array($_POST['role']??'', ['business','creator']) ? $_POST['role'] : 'creator';
    $name    = clean($conn, $_POST['name']    ?? '');
    $email   = clean($conn, $_POST['email']   ?? '');
    $pass    = $_POST['password']  ?? '';
    $confirm = $_POST['confirm']   ?? '';

    if (!$name || !$email || !$pass || !$confirm) { $error = 'Please fill in all required fields.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Enter a valid email address.'; }
    elseif (strlen($pass) < 6) { $error = 'Password must be at least 6 characters.'; }
    elseif ($pass !== $confirm) { $error = 'Passwords do not match.'; }
    else {
        $check = $conn->query("SELECT id FROM users WHERE email='$email' LIMIT 1");
        if ($check && $check->num_rows > 0) { $error = 'Email is already registered.'; }
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO users (name,email,password,role) VALUES ('$name','$email','$hash','$role')");
            $uid = $conn->insert_id;
            if ($role === 'business') {
                $company  = clean($conn, $_POST['company_name'] ?? $name);
                $industry = clean($conn, $_POST['industry']     ?? '');
                $conn->query("INSERT INTO businesses (user_id,company_name,industry) VALUES ($uid,'$company','$industry')");
            } else {
                $niche = clean($conn, $_POST['niche'] ?? '');
                $bio   = clean($conn, $_POST['bio']   ?? '');
                $conn->query("INSERT INTO creators (user_id,niche,bio) VALUES ($uid,'$niche','$bio')");
            }
            header('Location:/linkra/auth/login.php?registered=1'); exit();
        }
    }
}
?>
<!DOCTYPE html><html lang="en" data-theme="light"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create account — LINKRA</title>
<link rel="stylesheet" href="/linkra/assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>(function(){const t=localStorage.getItem('linkra_theme')||'light';document.documentElement.setAttribute('data-theme',t);})()</script>
</head><body>
<div class="auth-page">
  <div class="auth-card" style="max-width:500px">
    <div style="position:absolute;top:16px;right:16px"><?php include $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/theme-toggle.php'; ?></div>
    <div class="auth-logo">
      <a href="/linkra/index.php" style="text-decoration:none"><div class="logo-text">LINK<span>RA</span></div></a>
    </div>
    <h2 class="auth-title">Create your account</h2>
    <p class="auth-sub">Choose your role to get started</p>
    <?php if ($error): ?><div class="alert alert-danger" data-auto-dismiss><i class="ti ti-alert-circle"></i><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="POST">
      <div class="role-selector">
        <label class="role-opt <?=$prefill==='business'?'selected':''?>" data-role="business">
          <input type="radio" name="role" value="business" <?=$prefill==='business'?'checked':''?>>
          <i class="ti ti-building-store"></i>
          <div class="role-name">Business</div>
          <div class="role-desc">Post campaigns &amp; get content</div>
        </label>
        <label class="role-opt <?=$prefill==='creator'?'selected':''?>" data-role="creator">
          <input type="radio" name="role" value="creator" <?=$prefill==='creator'?'checked':''?>>
          <i class="ti ti-video"></i>
          <div class="role-name">Creator</div>
          <div class="role-desc">Submit content &amp; earn</div>
        </label>
      </div>
      <div class="form-group">
        <label class="form-label">Full name <span class="req">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="Your full name" value="<?=htmlspecialchars($_POST['name']??'')?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email <span class="req">*</span></label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?=htmlspecialchars($_POST['email']??'')?>" required>
      </div>
      <div class="form-row">
        <div class="form-group mb-0">
          <label class="form-label">Password <span class="req">*</span></label>
          <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
        </div>
        <div class="form-group mb-0">
          <label class="form-label">Confirm password <span class="req">*</span></label>
          <input type="password" name="confirm" class="form-control" placeholder="Repeat password" required>
        </div>
      </div>
      <!-- Business fields -->
      <div class="role-fields" id="fields-business" style="display:<?=$prefill==='business'?'block':'none'?>;margin-top:16px">
        <div class="divider"></div>
        <div class="form-group">
          <label class="form-label">Company / Brand name <span class="req">*</span></label>
          <input type="text" name="company_name" class="form-control" placeholder="e.g. Juan's Bistro" value="<?=htmlspecialchars($_POST['company_name']??'')?>">
        </div>
        <div class="form-group">
          <label class="form-label">Industry</label>
          <select name="industry" class="form-control">
            <option value="">Select industry</option>
            <?php foreach (['Food & Beverage','Fashion','Technology','Health & Fitness','Beauty & Skincare','Retail','Entertainment','Other'] as $i): ?>
            <option value="<?=$i?>" <?=($_POST['industry']??'')===$i?'selected':''?>><?=$i?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <!-- Creator fields -->
      <div class="role-fields" id="fields-creator" style="display:<?=$prefill==='creator'?'block':'none'?>;margin-top:16px">
        <div class="divider"></div>
        <div class="form-group">
          <label class="form-label">Content niche</label>
          <select name="niche" class="form-control">
            <option value="">Select your niche</option>
            <?php foreach (['Food','Fashion','Technology','Lifestyle','Fitness','Beauty','Travel','Entertainment','Other'] as $n): ?>
            <option value="<?=$n?>" <?=($_POST['niche']??'')===$n?'selected':''?>><?=$n?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Short bio</label>
          <div class="field-wrap">
            <textarea name="bio" class="form-control" placeholder="Tell businesses what you do..." maxlength="200" rows="3"><?=htmlspecialchars($_POST['bio']??'')?></textarea>
            <div class="char-counter">0/200</div>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top:20px"><i class="ti ti-user-plus"></i>Create account</button>
    </form>
    <div class="auth-footer">Already have an account? <a href="/linkra/auth/login.php">Log in</a></div>
  </div>
</div>
<script src="/linkra/assets/js/main.js"></script>
</body></html>
