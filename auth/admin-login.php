<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
    header('Location:/linkra/admin/dashboard.php'); exit();
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($conn, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $r = $conn->query("SELECT * FROM users WHERE email='$email' AND role='admin' AND is_active=1 LIMIT 1");
    $u = $r ? $r->fetch_assoc() : null;
    if ($u && password_verify($pass, $u['password'])) {
        $_SESSION['user'] = ['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>'admin','avatar'=>$u['avatar']];
        header('Location:/linkra/admin/dashboard.php'); exit();
    } else { $error = 'Invalid credentials or not an admin account.'; }
}
?>
<!DOCTYPE html><html lang="en" data-theme="light"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — LINKRA</title>
<link rel="stylesheet" href="/linkra/assets/css/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>(function(){const t=localStorage.getItem('linkra_theme')||'light';document.documentElement.setAttribute('data-theme',t);})()</script>
</head><body>
<div class="auth-page" style="background:#1a0a2e">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-text">LINK<span>RA</span></div>
      <p style="color:var(--danger);font-weight:600">Administrator Access</p>
    </div>
    <h2 class="auth-title">Admin Login</h2>
    <?php if ($error): ?><div class="alert alert-danger"><i class="ti ti-alert-circle"></i><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label class="form-label">Admin Email</label>
        <div class="input-icon-wrap"><i class="ti ti-mail icon"></i>
        <input type="email" name="email" class="form-control" placeholder="admin@linkra.com" required></div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-icon-wrap"><i class="ti ti-lock icon"></i>
        <input type="password" name="password" class="form-control" required></div>
      </div>
      <button type="submit" class="btn btn-danger btn-full"><i class="ti ti-shield"></i>Access Admin Panel</button>
    </form>
    <div class="auth-footer"><a href="/linkra/index.php" style="color:var(--text2)">← Back to site</a></div>
  </div>
</div>
<script src="/linkra/assets/js/main.js"></script>
</body></html>
