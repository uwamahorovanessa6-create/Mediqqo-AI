<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never {
    header("Location: {$path}");
    exit;
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        redirect('index.php');
    }
}

function require_role(array $roles): void {
    require_login();
    if (!in_array(current_user()['role'], $roles, true)) {
        http_response_code(403);
        exit('Access denied. Your account does not have permission to view this page.');
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Your form session expired. Go back, refresh the page, and try again.');
    }
}

function notify_user(PDO $pdo, int $userId, string $title, string $message): void {
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $title, $message]);
}

function get_patient_for_user(PDO $pdo, int $patientId): ?array {
    $user = current_user();
    if (!$user) return null;

    $sql = "SELECT p.id AS patient_id, p.user_id, p.date_of_birth, p.gender, p.phone,
                   p.emergency_contact, u.full_name, u.email, u.username, u.hospital_id
            FROM patients p JOIN users u ON u.id = p.user_id
            WHERE p.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch();
    if (!$patient) return null;

    if ($user['role'] === 'patient' && (int)$patient['user_id'] !== (int)$user['id']) {
        return null;
    }
    if (in_array($user['role'], ['doctor', 'nurse'], true) &&
        (int)$patient['hospital_id'] !== (int)$user['hospital_id']) {
        return null;
    }
    return $patient;
}

function page_header(string $title): void {
    $user = current_user();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= e($title) ?> | Mediqqo AI</title>
      <link rel="stylesheet" href="assets/css/style.css">
      <script defer src="assets/js/app.js"></script>
    </head>
    <body>
    <div class="app-shell">
      <aside class="sidebar">
        <a class="brand" href="dashboard.php"><span class="brand-mark">M</span><span>Mediqqo <b>AI</b></span></a>
        <div class="sidebar-user">
          <div class="avatar"><?= e(strtoupper(substr($user['full_name'] ?? 'M', 0, 1))) ?></div>
          <div><strong><?= e($user['full_name'] ?? '') ?></strong><small><?= e(ucfirst($user['role'] ?? '')) ?></small></div>
        </div>
        <nav class="nav">
          <a href="dashboard.php">Overview</a>
          <?php if (in_array($user['role'], ['doctor','nurse'], true)): ?>
            <a href="patients.php">Patients</a>
            <a href="appointments.php">Appointments</a>
            <a href="insights.php">AI Insights</a>
          <?php else: ?>
            <a href="my_records.php">My health records</a>
            <a href="my_appointments.php">My appointments</a>
            <a href="my_insights.php">My insights</a>
          <?php endif; ?>
          <a href="notifications.php">Notifications</a>
          <a href="change_password.php">Account settings</a>
        </nav>
        <a class="logout-link" href="logout.php">Sign out</a>
      </aside>
      <main class="main-content">
        <header class="topbar"><div><small>MEDIQQO AI / WORKSPACE</small><h1><?= e($title) ?></h1></div><div class="topbar-user"><?= e($user['full_name'] ?? '') ?></div></header>
    <?php
}

function page_footer(): void {
    echo '</main></div></body></html>';
}
