<?php
require_once __DIR__ . '/includes/functions.php';
require_role(['doctor','nurse']);
$patientId=(int)($_GET['patient_id']??$_POST['patient_id']??0);$patient=get_patient_for_user($pdo,$patientId);
if(!$patient){http_response_code(404);exit('Patient not found or access denied.');}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $sys=trim($_POST['systolic_bp']??'');$dia=trim($_POST['diastolic_bp']??'');$glu=trim($_POST['blood_glucose']??'');$notes=trim($_POST['notes']??'');
 $validNum=fn($v)=>$v===''|| (is_numeric($v)&& (float)$v>0 && (float)$v<1000);
 if(!$validNum($sys)||!$validNum($dia)||!$validNum($glu)||($sys===''&&$dia===''&&$glu==='')){$error='Enter at least one valid positive measurement.';}
 elseif(($sys==='')!==($dia==='')){$error='Enter both systolic and diastolic blood pressure, or leave both blank.';}
 else{
  $s=$pdo->prepare("INSERT INTO vital_signs (patient_id,recorded_by,systolic_bp,diastolic_bp,blood_glucose,notes) VALUES (?,?,?,?,?,?)");
  $s->execute([$patientId,current_user()['id'],$sys===''?null:$sys,$dia===''?null:$dia,$glu===''?null:$glu,$notes?:null]);
  notify_user($pdo,(int)$patient['user_id'],'New health record','A new measurement has been added to your health record.');
  redirect("patient_view.php?id={$patientId}");
 }
}
page_header('Add health measurement');
?>
<div class="panel narrow"><h3><?=e($patient['full_name'])?></h3><p class="muted">Enter values exactly as provided by the healthcare team. Units are displayed for clarity.</p>
<?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<form method="POST"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="patient_id" value="<?=$patientId?>">
<label>Systolic blood pressure (mmHg)</label><input type="number" step="0.1" min="1" max="999" name="systolic_bp">
<label>Diastolic blood pressure (mmHg)</label><input type="number" step="0.1" min="1" max="999" name="diastolic_bp">
<label>Blood glucose (mmol/L)</label><input type="number" step="0.1" min="0.1" max="999" name="blood_glucose">
<label>Notes (optional)</label><textarea name="notes" rows="3" maxlength="2000"></textarea>
<button class="btn primary">Save measurement</button></form></div>
<?php page_footer(); ?>
