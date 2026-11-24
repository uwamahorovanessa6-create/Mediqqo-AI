<?php
require_once __DIR__ . '/includes/functions.php';
require_role(['doctor','nurse']);
$u=current_user();$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$pid=(int)($_POST['patient_id']??0);$patient=get_patient_for_user($pdo,$pid);
 if($patient){
  $s=$pdo->prepare("SELECT * FROM vital_signs WHERE patient_id=? ORDER BY recorded_at DESC LIMIT 2");$s->execute([$pid]);$readings=$s->fetchAll();
  if(!$readings){$insight='No measurements are available yet. Add a measurement before generating an educational summary.';}
  else{
   $latest=$readings[0];$parts=[];
   if($latest['systolic_bp']!==null && $latest['diastolic_bp']!==null){
    $parts[]='Latest recorded blood pressure: '.(float)$latest['systolic_bp'].'/'.(float)$latest['diastolic_bp'].' mmHg. A single reading cannot establish a diagnosis; interpretation depends on repeat measurements and clinical context.';
   }
   if($latest['blood_glucose']!==null){
    $parts[]='Latest recorded glucose: '.(float)$latest['blood_glucose'].' mmol/L. Meaning depends on timing of the test, symptoms, and the person’s clinical context; a qualified professional should interpret it.';
   }
   if(count($readings)>1){$parts[]='A previous record is available for comparison. Review the readings together rather than drawing conclusions from one measurement.';}
   $insight=implode(' ',$parts).' This is an educational summary only, not a diagnosis or treatment recommendation.';
  }
  $s=$pdo->prepare("INSERT INTO ai_insights (patient_id,generated_by,insight,insight_type) VALUES (?,?,?,'educational_summary')");$s->execute([$pid,$u['id'],$insight]);
  notify_user($pdo,(int)$patient['user_id'],'New educational insight','Your care team added an educational summary to your account.');
  $message='Educational summary saved.';
 }
}
$s=$pdo->prepare("SELECT p.id,p.full_name FROM patients p JOIN users u ON u.id=p.user_id WHERE u.hospital_id=? ORDER BY p.full_name");$s->execute([$u['hospital_id']]);$patients=$s->fetchAll();
$s=$pdo->prepare("SELECT i.*,u.full_name FROM ai_insights i JOIN patients p ON p.id=i.patient_id JOIN users u ON u.id=p.user_id WHERE u.hospital_id=? ORDER BY i.created_at DESC LIMIT 30");$s->execute([$u['hospital_id']]);$rows=$s->fetchAll();
page_header('AI educational insights');
?>
<div class="content-grid"><div class="panel"><h3>Generate an educational summary</h3><p class="muted">This feature summarizes stored readings using simple rules. It is not a clinical AI model and does not diagnose or recommend treatment.</p><?php if($message):?><div class="alert success"><?=e($message)?></div><?php endif;?>
<form method="POST"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label>Select patient</label><select name="patient_id" required><option value="">Choose patient</option><?php foreach($patients as $p):?><option value="<?=$p['id']?>"><?=e($p['full_name'])?></option><?php endforeach;?></select><button class="btn primary">Generate summary</button></form></div>
<div class="panel"><h3>Recent summaries</h3><?php foreach($rows as $r):?><article class="insight"><strong><?=e($r['full_name'])?></strong><small><?=e($r['created_at'])?></small><p><?=e($r['insight'])?></p></article><?php endforeach;?><?php if(!$rows):?><p class="empty">No summaries generated yet.</p><?php endif;?></div></div>
<?php page_footer(); ?>
