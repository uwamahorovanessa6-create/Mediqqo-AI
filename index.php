<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password)) {
        $error = 'Enter a valid email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, hospital_id, full_name, username, email, password, role, account_status
                               FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['account_status'] === 'active' && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            unset($user['password']);
            $_SESSION['user'] = $user;
            redirect('dashboard.php');
        } else {
            $error = 'The email or password is incorrect, or the account is not active.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in | Mediqqo AI</title><link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
<div class="login-wrap">
  <section class="login-brand"><div class="brand-mark large">M</div><p class="eyebrow">SMARTER HEALTH MANAGEMENT</p><h1>Care connected.<br><span>Insights empowered.</span></h1><p>One workspace for healthcare teams and the people they care for.</p></section>
  <section class="login-card">
    <div class="brand"><span class="brand-mark">M</span><span>Mediqqo <b>AI</b></span></div>
    <h2>Welcome back</h2><p class="muted">Sign in to continue to your workspace.</p>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <label>Email address</label><input type="email" name="email" required autocomplete="username" placeholder="you@example.com">
      <label>Password</label><input type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
      <button class="btn primary full" type="submit">Sign in</button>
    </form>
    <p class="small-note">Patients: use the secure invitation link provided by your healthcare team to create your account.</p>
  </section>
</div>
</body></html>
