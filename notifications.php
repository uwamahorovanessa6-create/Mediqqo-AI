<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$s=$pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 100");$s->execute([current_user()['id']]);$rows=$s->fetchAll();
$s=$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");$s->execute([current_user()['id']]);
page_header('Notifications');
?>
<div class="panel"><h3>Your notifications</h3><?php foreach($rows as $r):?><article class="notification"><strong><?=e($r['title'])?></strong><small><?=e($r['created_at'])?></small><p><?=e($r['message'])?></p></article><?php endforeach;?><?php if(!$rows):?><p class="empty">You have no notifications.</p><?php endif;?></div>
<?php page_footer(); ?>
