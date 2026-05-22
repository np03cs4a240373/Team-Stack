<?php
// api/admin-toggle-job.php — Admin: toggle job active/closed
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('admin');
header('Content-Type: application/json');

$pdo   = getPDO();
$jobId = (int)($_POST['job_id'] ?? 0);

if (!$jobId) {
    echo json_encode(['success' => false, 'error' => 'Invalid job ID.']);
    exit;
}

$stmt = $pdo->prepare("SELECT status FROM jobs WHERE id = ?");
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    echo json_encode(['success' => false, 'error' => 'Job not found.']);
    exit;
}

$newStatus = $job['status'] === 'active' ? 'closed' : 'active';
$pdo->prepare("UPDATE jobs SET status = ? WHERE id = ?")->execute([$newStatus, $jobId]);

echo json_encode([
    'success'   => true,
    'status'    => $newStatus,
    'label'     => $newStatus === 'active' ? 'Suspend' : 'Activate',
    'badgeText' => ucfirst($newStatus),
]);
