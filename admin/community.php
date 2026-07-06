<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/nav-admin.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/config/db.php';
?>
<div class="page-body">
<?php
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (isset($_POST['del_post']))    $conn->query("DELETE FROM posts WHERE id=".intval($_POST['del_post']));
    if (isset($_POST['del_comment'])) $conn->query("DELETE FROM post_comments WHERE id=".intval($_POST['del_comment']));
    header('Location:/linkra/admin/community.php'); exit();
}

$posts = $conn->query("SELECT p.*,u.name uname,u.avatar uavatar,u.role urole,(SELECT COUNT(*) FROM post_likes WHERE post_id=p.id) likes,(SELECT COUNT(*) FROM post_comments WHERE post_id=p.id) ccount FROM posts p JOIN users u ON u.id=p.user_id ORDER BY p.created_at DESC");
?>
<div class="page-header">
  <div>
    <div class="page-heading">Moderate Community</div>
    <div class="page-sub">Review and remove inappropriate posts or comments.</div>
  </div>
</div>

<?php if($posts->num_rows===0): ?>
  <div class="empty-state">
    <i class="ti ti-messages"></i>
    <h3>No community posts</h3>
    <p>Nothing to moderate yet.</p>
  </div>
<?php else: while($p=$posts->fetch_assoc()):  
  $pu=['name'=>$p['uname'],'avatar'=>$p['uavatar']];
  $cmts=$conn->query("SELECT c.*,u.name un,u.avatar ua FROM post_comments c JOIN users u ON u.id=c.user_id WHERE c.post_id={$p['id']} ORDER BY c.created_at ASC");
?>
<div class="card mb-16">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:10px">
    <div style="display:flex;align-items:center;gap:10px;flex:1">
      <?=avatar_html($pu,'sm')?>
      <div>
        <div style="font-weight:600;font-size:13px">
          <?=htmlspecialchars($p['uname'])?> 
          <span class="badge badge-<?=$p['urole']?>" style="font-size:10px;vertical-align:middle"><?=ucfirst($p['urole'])?></span>
        </div>
        <div style="font-size:11px;color:var(--text3)"><?=time_ago($p['created_at'])?> · <?=$p['likes']?> likes · <?=$p['ccount']?> comments</div>
      </div>
    </div>
    <form method="POST" onsubmit="return confirm('Delete this post?')">
      <input type="hidden" name="del_post" value="<?=$p['id']?>">
      <button type="submit" class="btn btn-danger btn-sm"><i class="ti ti-trash"></i>Delete post</button>
    </form>
  </div>
  
  <p style="font-size:13px;color:var(--text2);line-height:1.6;margin-bottom:10px"><?=nl2br(htmlspecialchars($p['body']))?></p>
  
  <?php if($p['image_url']): ?>
    <img src="/linkra/assets/<?=htmlspecialchars($p['image_url'])?>" style="max-height:140px;border-radius:var(--radius-sm);margin-bottom:10px" alt="">
  <?php endif; ?>
  
  <?php if($cmts->num_rows>0): ?>
  <div style="padding-top:10px;border-top:1px solid var(--border)">
    <div style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Comments (<?=$cmts->num_rows?>)</div>
    <?php while($c=$cmts->fetch_assoc()): $cu2=['name'=>$c['un'],'avatar'=>$c['ua']]; ?>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:6px 0;border-bottom:1px solid var(--border)">
      <div style="display:flex;align-items:center;gap:6px;flex:1">
        <?=avatar_html($cu2,'xs')?>
        <span style="font-size:12px"><strong><?=htmlspecialchars($c['un'])?></strong>: <?=htmlspecialchars($c['body'])?></span>
        <span style="font-size:11px;color:var(--text3);margin-left:auto;white-space:nowrap"><?=time_ago($c['created_at'])?></span>
      </div>
      <form method="POST" onsubmit="return confirm('Delete comment?')">
        <input type="hidden" name="del_comment" value="<?=$c['id']?>">
        <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger);padding:3px 6px;flex-shrink:0"><i class="ti ti-x"></i></button>
      </form>
    </div>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>
</div>
<?php endwhile; endif; ?>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/linkra/partials/footer.php'; ?>