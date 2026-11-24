<?php
require_once __DIR__ . '/includes/functions.php';
require_role(['doctor','nurse','patient']);
$id=(int)($_GET['id']??0);$patient=get_patient_for_user($pdo,$id);
if(!$patient){http_response_code(404);exit('Patient not found or access denied.');}
$stmt=$pdo->prepare("SELECT * FROM vital_signs WHERE patient_id=? ORDER BY recorded_at DESC LIMIT 30");$stmt->execute([$id]);$records=$stmt->fetchAll();
page_header('Patient details');
?>
<div class="panel"><div class="page-actions"><div><h2><?=e($patient['full_name'])?></h2><p class="muted"><?=e($patient['email'])?></p></div><?php if(current_user()['role']!=='patient'):?><a class="btn primary" href="record_add.php?patient_id=<?=$id?>">+ Add measurement</a><?php endif;?></div>
<div class="detail-grid"><div><small>Phone</small><strong><?=e($patient['phone']??'Not provided')?></strong></div><div><small>Date of birth</small><strong><?=e($patient['date_of_birth']??'Not provided')?></strong></div><div><small>Gender</small><strong><?=e($patient['gender']??'Not provided')?></strong></div></div></div>
<div class="panel"><h3>Recent health measurements</h3><div class="table-scroll"><table><thead><tr><th>Date</th><th>Blood pressure (mmHg)</th><th>Glucose (mmol/L)</th><th>Notes</th></tr></thead><tbody>
<?php foreach($records as $r):?><tr><td><?=e($r['recorded_at'])?></td><td><?=e($r['systolic_bp']??'—')?> / <?=e($r['diastolic_bp']??'—')?></td><td><?=e($r['blood_glucose']??'—')?></td><td><?=e($r['notes']??'')?></td></tr><?php endforeach;?>
<?php if(!$records):?><tr><td colspan="4" class="empty">No measurements recorded.</td></tr><?php endif;?>
</tbody></table></div></div>
<?php page_footer(); ?>
