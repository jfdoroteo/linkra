<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_login() {
    if (!isset($_SESSION['user'])) {
        header('Location:/linkra/auth/login.php'); exit();
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['user']['role'] !== $role) {
        $map = ['business'=>'/linkra/business/dashboard.php','creator'=>'/linkra/creator/browse.php','admin'=>'/linkra/admin/dashboard.php'];
        header('Location:' . ($map[$_SESSION['user']['role']] ?? '/linkra/auth/login.php')); exit();
    }
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['user']);
}

function redirect_if_logged_in() {
    if (!isset($_SESSION['user'])) return;
    $map = ['business'=>'/linkra/business/dashboard.php','creator'=>'/linkra/creator/browse.php','admin'=>'/linkra/admin/dashboard.php'];
    header('Location:' . ($map[$_SESSION['user']['role']] ?? '/linkra/index.php')); exit();
}

function refresh_session($conn) {
    if (!isset($_SESSION['user'])) return;
    $id = intval($_SESSION['user']['id']);
    $r  = $conn->query("SELECT id,name,email,role,avatar FROM users WHERE id=$id AND is_active=1 LIMIT 1");
    if ($r && $u = $r->fetch_assoc()) {
        $_SESSION['user'] = $u;
    }
}
