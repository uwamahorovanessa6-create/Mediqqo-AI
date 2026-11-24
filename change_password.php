<?php
require_once __DIR__ . '/includes/functions.php';
require_login();$error='';$success='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$old=$_POST['old_password']??'';$new=$_POST['new_password']??'';
 $s=$pdo->prepare("SELECT password FROM users WHERE id=?");$s->execute([current_user()['id']]);$hash=$s->fetchColumn();
 if(!$hash||!password_verify($old,$hash)){$error='Current password is incorrect.';}
 elseif(strlen($new)<10){$error='New password must contain at least 10 characters.';}
 else{$s=$pdo->prepare("UPDATE users SET password=? WHERE id=?");$s->execute([password_hash($new,PASSWORD_DEFAULT),current_user()['id']]);$success='Password updated successfully.';}
}
page_header('Account settings');
?>
<div class="panel narrow"><h3>Change password</h3><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><?php if($success):?><div class="alert success"><?=e($success)?></div><?php endif;?>
<form method="POST"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><label>Current password</label><input type="password" name="old_password" required><label>New password</label><input type="password" name="new_password" minlength="10" required><button class="btn primary">Update password</button></form></div>
<?php page_footer(); ?>
