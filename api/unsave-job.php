<?php
// api/unsave-job.php - Remove a saved job
// Supports both AJAX (POST -> JSON) and page redirect (GET)
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('seeker');

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // AJAX request — return JSON
    header('Content-Type: application/json');
    $jobId = (int)($_POST['job_id'] ?? 0);
    if (!$jobId) {
        echo json_encode(['error' => 'Invalid job.']);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM saved_jobs WHERE job_id = ? AND seeker_id = ?");
    $stmt->execute([$jobId, getUserId()]);
    echo json_encode(['success' => true]);
    exit;
}

// GET request — redirect back to saved jobs page
$jobId = (int)($_GET['job_id'] ?? 0);
if ($jobId) {
    $stmt = $pdo->prepare("DELETE FROM saved_jobs WHERE job_id = ? AND seeker_id = ?");
    $stmt->execute([$jobId, getUserId()]);
}

header('Location: ' . BASE_URL . '/pages/saved-jobs.php');
exit;
