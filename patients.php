<?php
require_once __DIR__ . '/includes/functions.php';
require_role(['doctor','nurse']);
$u=current_user();
$stmt=$pdo->prepare("SELECT u.id AS user_id,u.full_name,u.email,u.account_status,p.id AS patient_id,p.phone
 FROM users u LEFT JOIN patients p ON p.user_id=u.id
 WHERE u.role='patient' AND u.hospital_id=? ORDER BY u.created_at DESC");
$stmt->execute([$u['hospital_id']]); $patients=$stmt->fetchAll();
page_header('Patient management');
?>
<div class="page-actions"><p class="muted">Patient accounts connected to your hospital.</p><a class="btn primary" href="invite_patient.php">+ Invite patient</a></div>
<div class="panel table-panel"><div class="table-scroll"><table><thead><tr><th>Patient</th><th>Email</th><th>Phone</th><th>Account</th><th></th></tr></thead><tbody>
<?php foreach($patients as $p): ?><tr><td><strong><?= e($p['full_name']) ?></strong></td><td><?= e($p['email']) ?></td><td><?= e($p['phone'] ?? '—') ?></td><td><span class="status"><?= e($p['account_status']) ?></span></td><td><?php if($p['patient_id']): ?><a href="patient_view.php?id=<?= (int)$p['patient_id'] ?>">Open</a><?php else: ?><span class="muted">Profile pending</span><?php endif; ?></td></tr><?php endforeach; ?>
<?php if(!$patients): ?><tr><td colspan="5" class="empty">No patient accounts yet. Invite a demonstration patient to begin.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php page_footer(); ?>
