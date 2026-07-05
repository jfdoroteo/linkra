<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-creator.php'; ?>
<div class="page-body" style="max-width:700px">
<?php $conn->query("UPDATE notifications SET is_read=1 WHERE user_id={$user['id']}"); ?>
<div class="page-header"><div><div class="page-heading">Notifications</div></div></div>
<div class="card">
<?php $ns=$conn->query("SELECT * FROM notifications WHERE user_id={$user['id']} ORDER BY created_at DESC LIMIT 60");
if($ns->num_rows===0): ?><div class="empty-state" style="padding:40px"><i class="ti ti-bell-off"></i><h3>All caught up</h3><p>No notifications yet.</p></div>
<?php else: while($n=$ns->fetch_assoc()): ?>
<div class="notif-item"><div class="notif-icon ni-purple"><i class="ti ti-bell"></i></div><div style="flex:1"><div class="notif-text"><?=htmlspecialchars($n['message'])?></div><div class="notif-time"><?=time_ago($n['created_at'])?></div><?php if($n['link']): ?><a href="<?=htmlspecialchars($n['link'])?>" class="btn btn-ghost btn-sm" style="margin-top:4px;padding-left:0">View →</a><?php endif; ?></div></div>
<?php endwhile; endif; ?>
</div></div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>
