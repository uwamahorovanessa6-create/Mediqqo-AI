<?php
require_once __DIR__ . '/includes/functions.php';
require_role(['patient']);
$s=$pdo->prepare("SELECT id FROM patients WHERE user_id=?");$s->execute([current_user()['id']]);$pid=$s->fetchColumn();
page_header('My health records');
if($pid){$s=$pdo->prepare("SELECT * FROM vital_signs WHERE patient_id=? ORDER BY recorded_at DESC");$s->execute([$pid]);$rows=$s->fetchAll();}else{$rows=[];}
?>
<div class="panel"><h3>My measurements</h3><p class="muted">These records are for review and discussion with your healthcare professional.</p><div class="table-scroll"><table><thead><tr><th>Date</th><th>Blood pressure (mmHg)</th><th>Glucose (mmol/L)</th><th>Notes</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=e($r['recorded_at'])?></td><td><?=e($r['systolic_bp']??'—')?> / <?=e($r['diastolic_bp']??'—')?></td><td><?=e($r['blood_glucose']??'—')?></td><td><?=e($r['notes']??'')?></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="4" class="empty">No records available yet.</td></tr><?php endif;?></tbody></table></div></div>
<?php page_footer(); ?>
