<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();

if ($user['role'] === 'patient') {
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $patientId = $stmt->fetchColumn();
    $countRecords = 0; $countAppointments = 0;
    if ($patientId) {
        $s = $pdo->prepare("SELECT COUNT(*) FROM vital_signs WHERE patient_id=?"); $s->execute([$patientId]); $countRecords=(int)$s->fetchColumn();
        $s = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id=?"); $s->execute([$patientId]); $countAppointments=(int)$s->fetchColumn();
    }
    page_header('My health overview');
    ?>
    <div class="welcome-panel"><div><p class="eyebrow">YOUR HEALTH SPACE</p><h2>Welcome, <?= e($user['full_name']) ?></h2><p>Review your health information and stay connected with your care team.</p></div><span class="welcome-icon">✦</span></div>
    <div class="stats-grid"><div class="stat-card"><span>Health records</span><strong><?= $countRecords ?></strong><small>Saved measurements</small></div><div class="stat-card"><span>Appointments</span><strong><?= $countAppointments ?></strong><small>Your appointments</small></div></div>
    <div class="panel"><h3>Your health information</h3><p class="muted">Your healthcare team may add readings and notes here. If you have concerns about a measurement, contact a qualified healthcare professional.</p><a class="btn primary" href="my_records.php">View my records</a></div>
    <?php page_footer(); exit;
}

$hospitalId = (int)$user['hospital_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role='patient' AND hospital_id=? AND account_status='active'");
$stmt->execute([$hospitalId]); $patientCount=(int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM vital_signs v JOIN patients p ON p.id=v.patient_id JOIN users u ON u.id=p.user_id WHERE u.hospital_id=?");
$stmt->execute([$hospitalId]); $recordCount=(int)$stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN users u ON u.id=p.user_id WHERE u.hospital_id=? AND a.status='scheduled'");
$stmt->execute([$hospitalId]); $appointmentCount=(int)$stmt->fetchColumn();

page_header('Dashboard');
?>
<div class="welcome-panel"><div><p class="eyebrow">HEALTHCARE WORKSPACE</p><h2>Good day, <?= e($user['full_name']) ?>.</h2><p>Here is an overview of your hospital workspace.</p></div><span class="welcome-icon">✦</span></div>
<div class="stats-grid">
  <div class="stat-card"><span>Active patients</span><strong><?= $patientCount ?></strong><small>Registered patient accounts</small></div>
  <div class="stat-card"><span>Measurements</span><strong><?= $recordCount ?></strong><small>Recorded health readings</small></div>
  <div class="stat-card"><span>Scheduled appointments</span><strong><?= $appointmentCount ?></strong><small>Upcoming care activities</small></div>
</div>
<div class="content-grid">
 <div class="panel"><h3>Quick actions</h3><div class="action-list"><a href="patients.php">View patient directory <span>→</span></a><a href="appointments.php">Manage appointments <span>→</span></a><a href="insights.php">Review educational insights <span>→</span></a></div></div>
 <div class="panel"><h3>Responsible use</h3><p class="muted">Mediqqo AI is an academic demonstration. Its educational insights do not diagnose conditions, recommend treatment, or replace professional clinical judgement.</p></div>
</div>
<?php page_footer(); ?>
