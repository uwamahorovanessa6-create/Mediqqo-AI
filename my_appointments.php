<?php
require_once __DIR__ . '/includes/functions.php';
require_role(['patient']);
$s=$pdo->prepare("SELECT id FROM patients WHERE user_id=?");$s->execute([current_user()['id']]);$pid=$s->fetchColumn();
$rows=[];if($pid){$s=$pdo->prepare("SELECT * FROM appointments WHERE patient_id=? ORDER BY appointment_date DESC");$s->execute([$pid]);$rows=$s->fetchAll();}
page_header('My appointments');
?>
<div class="panel"><h3>Appointment history</h3><div class="table-scroll"><table><thead><tr><th>Date and time</th><th>Reason</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=e($r['appointment_date'])?></td><td><?=e($r['reason']??'—')?></td><td><?=e($r['status'])?></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="3" class="empty">No appointments available.</td></tr><?php endif;?></tbody></table></div></div>
<?php page_footer(); ?>
