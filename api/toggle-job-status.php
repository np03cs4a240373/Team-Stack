<?php
// api/toggle-job-status.php — Employer: close or reopen a job
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer');

$pdo    = getPDO();
$userId = getUserId();
$jobId  = (int)($_POST['job_id'] ?? 0);

if (!$jobId) {
    header('Location: ' . BASE_URL . '/dashboard/employer.php');
    exit;
}

// Fetch job status (verify ownership)
$stmt = $pdo->prepare("SELECT status FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$jobId, $userId]);
$job = $stmt->fetch();

if (!$job) {
    header('Location: ' . BASE_URL . '/dashboard/employer.php');
    exit;
}

$newStatus = $job['status'] === 'active' ? 'closed' : 'active';

$upd = $pdo->prepare("UPDATE jobs SET status = ? WHERE id = ? AND employer_id = ?");
$upd->execute([$newStatus, $jobId, $userId]);

$redirect = $_POST['redirect'] ?? BASE_URL . '/dashboard/employer.php';
header('Location: ' . $redirect . '?msg=job_updated');
exit;
