<?php
require_once __DIR__ . '/includes/functions.php';
$error=''; $validInvite=null; $token=$_GET['token']??$_POST['token']??'';
if(is_string($token) && strlen($token)===64){
 $stmt=$pdo->query("SELECT id,patient_email,token_hash,invited_by,expires_at,used_at FROM invitations WHERE used_at IS NULL AND expires_at>NOW() ORDER BY id DESC");
 foreach($stmt->fetchAll() as $candidate){ if(password_verify($token,$candidate['token_hash'])){$validInvite=$candidate;break;} }
}
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 if(!$validInvite){$error='This invitation is invalid, expired, or already used.';}
 else{
  $name=trim($_POST['full_name']??'');$username=trim($_POST['username']??'');$password=$_POST['password']??'';
  if($name===''||!preg_match('/^[A-Za-z0-9_.-]{3,80}$/',$username)||strlen($password)<10){$error='Enter your name, a username (3–80 characters), and a password of at least 10 characters.';}
  else{
   $staff=$pdo->prepare("SELECT hospital_id FROM users WHERE id=? AND role IN ('doctor','nurse')");$staff->execute([$validInvite['invited_by']]);$hospitalId=$staff->fetchColumn();
   try{
    $pdo->beginTransaction();
    $s=$pdo->prepare("INSERT INTO users (hospital_id,full_name,username,email,password,role,account_status) VALUES (?,?,?,?,?,'patient','active')");
    $s->execute([$hospitalId,$name,$username,$validInvite['patient_email'],password_hash($password,PASSWORD_DEFAULT)]);
    $userId=(int)$pdo->lastInsertId();
    $s=$pdo->prepare("INSERT INTO patients (user_id,date_of_birth,gender,phone) VALUES (?,?,?,?)");
    $s->execute([$userId,$_POST['date_of_birth']?:null,$_POST['gender']?:null,trim($_POST['phone']??'')?:null]);
    $s=$pdo->prepare("UPDATE invitations SET used_at=NOW() WHERE id=? AND used_at IS NULL");$s->execute([$validInvite['id']]);
    $pdo->commit();$error='Your account has been created. You can now sign in.';
   }catch(Throwable $e){$pdo->rollBack();$error='Registration could not be completed. Check that your username is not already used.';}
  }
 }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Patient registration | Mediqqo AI</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="login-page"><div class="login-card register-card"><div class="brand"><span class="brand-mark">M</span><span>Mediqqo <b>AI</b></span></div><h2>Patient registration</h2>
<?php if($error):?><div class="alert <?=str_starts_with($error,'Your account')?'success':'error'?>"><?=e($error)?</div><?php endif;?>
<?php if($validInvite && !str_starts_with($error,'Your account')):?>
<form method="POST"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="token" value="<?=e($token)?>">
<label>Full name</label><input name="full_name" required>
<label>Username</label><input name="username" required minlength="3">
<label>Email</label><input value="<?=e($validInvite['patient_email'])?>" disabled>
<label>Create password (at least 10 characters)</label><input type="password" name="password" required minlength="10">
<label>Date of birth (optional)</label><input type="date" name="date_of_birth">
<label>Gender (optional)</label><select name="gender"><option value="">Prefer not to say</option><option>Female</option><option>Male</option><option>Other</option></select>
<label>Phone (optional)</label><input name="phone">
<button class="btn primary full">Create account</button></form>
<?php elseif(!$validInvite):?><div class="alert error">This invitation is invalid or expired. Ask your healthcare team for a new one.</div><?php endif;?>
<p><a href="index.php">Return to sign in</a></p></div></body></html>
