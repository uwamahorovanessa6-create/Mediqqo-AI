<?php
require_once __DIR__ . '/includes/functions.php';
require_role(['doctor','nurse']);
$u=current_user(); $error=''; $link='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $email=trim($_POST['email']??'');
 if(!filter_var($email,FILTER_VALIDATE_EMAIL)){ $error='Enter a valid email address.'; }
 else {
  $s=$pdo->prepare("SELECT id FROM users WHERE email=?"); $s->execute([$email]);
  if($s->fetch()){ $error='An account with that email already exists.'; }
  else {
   $token=bin2hex(random_bytes(32)); $hash=password_hash($token,PASSWORD_DEFAULT);
   $s=$pdo->prepare("INSERT INTO invitations (patient_email,token_hash,invited_by,expires_at) VALUES (?,?,?,DATE_ADD(NOW(), INTERVAL 48 HOUR))");
   $s->execute([$email,$hash,$u['id']]);
   $link='patient_register.php?token='.urlencode($token);
  }
 }
}
page_header('Invite a patient');
?>
<div class="panel narrow"><h3>Create a registration invitation</h3><p class="muted">For this local demo, the invitation link is displayed on screen instead of being emailed. Share it only with your fictional test patient.</p>
<?php if($error): ?><div class="alert error"><?=e($error)?></div><?php endif;?>
<form method="POST"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label>Patient email</label><input type="email" name="email" required placeholder="patient@example.com"><button class="btn primary" type="submit">Generate invitation</button></form>
<?php if($link): ?><div class="alert success"><strong>Invitation created.</strong><p>Copy this link and open it in your browser:</p><input readonly value="<?=e($link)?>" onclick="this.select()"><p class="small-note">This link expires in 48 hours and can be used once.</p></div><?php endif;?>
<a href="patients.php">← Back to patients</a></div>
<?php page_footer(); ?>
