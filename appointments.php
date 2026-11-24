<?php
require_once __DIR__ . '/includes/functions.php';
require_role(['doctor','nurse']);
$u=current_user();$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$pid=(int)($_POST['patient_id']??0);$date=$_POST['appointment_date']??'';$reason=trim($_POST['reason']??'');
 $patient=get_patient_for_user($pdo,$pid);
 if(!$patient||!$date){$error='Choose a valid patient and appointment date.';}
 else{
  $s=$pdo->prepare("INSERT INTO appointments (patient_id,staff_id,appointment_date,reason) VALUES (?,?,?,?)");
  $s->execute([$pid,$u['id'],str_replace('T',' ',$date).':00',$reason?:null]);
  notify_user($pdo,(int)$patient['user_id'],'Appointment scheduled','A new appointment has been scheduled. Please contact your healthcare team if you have questions.');
  redirect('appointments.php');
 }
}
$s=$pdo->prepare("SELECT p.id,p.full_name FROM patients p JOIN users u ON u.id=p.user_id WHERE u.hospital_id=? ORDER BY p.full_name");$s->execute([$u['hospital_id']]);$patients=$s->fetchAll();
$s=$pdo->prepare("SELECT a.*,u.full_name FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN users u ON u.id=p.user_id WHERE u.hospital_id=? ORDER BY a.appointment_date DESC");$s->execute([$u['hospital_id']]);$rows=$s->fetchAll();
page_header('Appointments');
?>
<div class="content-grid"><div class="panel"><h3>Schedule appointment</h3><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<form method="POST"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label>Patient</label><select name="patient_id" required><option value="">Select patient</option><?php foreach($patients as $p):?><option value="<?=$p['id']?>"><?=e($p['full_name'])?></option><?php endforeach;?></select><label>Date and time</label><input type="datetime-local" name="appointment_date" required><label>Reason</label><input name="reason" maxlength="255"><button class="btn primary">Schedule</button></form></div>
<div class="panel"><h3>Appointment list</h3><div class="table-scroll"><table><thead><tr><th>Patient</th><th>Date</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=e($r['full_name'])?></td><td><?=e($r['appointment_date'])?></td><td><?=e($r['status'])?></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="3" class="empty">No appointments yet.</td></tr><?php endif;?></tbody></table></div></div></div>
<?php page_footer(); ?>
