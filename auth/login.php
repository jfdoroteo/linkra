<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';
redirect_if_logged_in();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($conn, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!$email || !$pass) {
        $error = 'Please fill in all fields.';
    } else {
        $r = $conn->query("SELECT * FROM users WHERE email='$email' AND is_active=1 LIMIT 1");
        if (!$r) { $error = 'Database error. Please import the SQL file first.'; }
        else {
            $u = $r->fetch_assoc();
            if ($u && password_verify($pass, $u['password'])) {
                $_SESSION['user'] = ['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role'],'avatar'=>$u['avatar']];
                switch ($u['role']) {
                    case 'business': header('Location:/linkra/business/dashboard.php'); exit();
                    case 'creator':  header('Location:/linkra/creator/browse.php');     exit();
                    default:         $error = 'Access denied from this page.';
                }
            } else { $error = 'Invalid email or password.'; }
        }
    }
}
?>
<!DOCTYPE html><html lang="en" data-theme="light"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Log in — LINKRA</title>
<link rel="stylesheet" href="/linkra/assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>(function(){const t=localStorage.getItem('linkra_theme')||'light';document.documentElement.setAttribute('data-theme',t);})()</script>
</head><body>
<div class="auth-page">
  <div class="auth-card">
    <div style="position:absolute;top:16px;right:16px"><?php include $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/theme-toggle.php'; ?></div>
    <div class="auth-logo">
      <a href="/linkra/index.php" style="text-decoration:none"><div class="logo-text">LINK<span>RA</span></div></a>
      <p>Creator &amp; Business Collaboration</p>
    </div>
    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-sub">Log in to your LINKRA account</p>
    <?php if ($error): ?><div class="alert alert-danger" data-auto-dismiss><i class="ti ti-alert-circle"></i><?=htmlspecialchars($error)?></div><?php endif; ?>
    <?php if (isset($_GET['registered'])): ?><div class="alert alert-success" data-auto-dismiss><i class="ti ti-check"></i>Account created! You can now log in.</div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Email <span class="req">*</span></label>
        <div class="input-icon-wrap"><i class="ti ti-mail icon"></i>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?=htmlspecialchars($_POST['email']??'')?>" required></div>
      </div>
      <div class="form-group">
        <label class="form-label">Password <span class="req">*</span></label>
        <div class="input-icon-wrap"><i class="ti ti-lock icon"></i>
        <input type="password" name="password" class="form-control" placeholder="Your password" required></div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px"><i class="ti ti-login"></i>Log in</button>
    </form>
    <div class="auth-footer">Don't have an account? <a href="/linkra/auth/register.php">Sign up</a></div>
  </div>
</div>
<script src="/linkra/assets/js/main.js"></script>
</body></html>
