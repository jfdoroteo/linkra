<?php
$conn = new mysqli('localhost', 'root', '', '');
if ($conn->connect_error) die('MySQL connection failed: ' . $conn->connect_error);
$conn->query("CREATE DATABASE IF NOT EXISTS linkra_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db('linkra_db');
$conn->set_charset('utf8mb4');

function clean($conn, $val) {
    return $conn->real_escape_string(trim($val));
}

function notify($conn, $user_id, $message, $link = null) {
    $msg  = $conn->real_escape_string($message);
    $lnk  = $link ? "'" . $conn->real_escape_string($link) . "'" : 'NULL';
    $conn->query("INSERT INTO notifications (user_id,message,link) VALUES ($user_id,'$msg',$lnk)");
}

function unread_count($conn, $user_id) {
    $r = $conn->query("SELECT COUNT(*) c FROM notifications WHERE user_id=$user_id AND is_read=0");
    return $r ? $r->fetch_assoc()['c'] : 0;
}

function time_ago($datetime) {
    if (!$datetime) return '';
    $diff = (new DateTime())->diff(new DateTime($datetime));
    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'min ago';
    return 'just now';
}

function peso($amount) {
    return '₱' . number_format((float)$amount, 2);
}

function upload_file($file_key, $prefix, $allowed_types = ['jpg','jpeg','png','webp','gif']) {
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== 0) return null;
    $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_types)) return null;
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/linkra/assets/uploads/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $fname = $prefix . '_' . time() . '_' . rand(100,999) . '.' . $ext;
    if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $dir . $fname)) {
        return 'uploads/' . $fname;
    }
    return null;
}

function avatar_html($user, $size = 'sm', $extra_class = '') {
    $sizes = ['xs'=>'28px','sm'=>'36px','md'=>'44px','lg'=>'64px','xl'=>'88px'];
    $font  = ['xs'=>'11px','sm'=>'13px','md'=>'16px','lg'=>'22px','xl'=>'30px'];
    $s = $sizes[$size] ?? '36px';
    $f = $font[$size]  ?? '13px';
    $name = htmlspecialchars($user['name'] ?? 'U');
    $initials = strtoupper(substr($user['name'] ?? 'U', 0, 2));
    if (!empty($user['avatar'])) {
        return "<img src='/linkra/assets/{$user['avatar']}' class='avatar $extra_class' style='width:$s;height:$s;object-fit:cover;border-radius:50%' alt='$name'>";
    }
    return "<div class='avatar-placeholder avatar-$size avatar-purple $extra_class' style='width:$s;height:$s;font-size:$f'>$initials</div>";
}
