<?php
require_once __DIR__ . '/includes/functions.php';
require_role(['patient']);
$s=$pdo->prepare("SELECT id FROM patients WHERE user_id=?");$s->execute([current_user()['id']]);$pid=$s->fetchColumn();
$rows=[];if($pid){$s=$pdo->prepare("SELECT * FROM ai_insights WHERE patient_id=? ORDER BY created_at DESC");$s->execute([$pid]);$rows=$s->fetchAll();}
page_header('My educational insights');
?>
<div class="panel"><h3>Educational summaries</h3><p class="muted">These summaries are informational only. They are not diagnoses or treatment instructions.</p><?php foreach($rows as $r):?><article class="insight"><small><?=e($r['created_at'])?></small><p><?=e($r['insight'])?></p></article><?php endforeach;?><?php if(!$rows):?><p class="empty">No insights are available yet.</p><?php endif;?></div>
<?php page_footer(); ?>
