<?php
// ============================================================
// api/download-resume.php - Secure Resume Download
// Only employer who owns the job can download the resume
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();

$pdo   = getPDO();
$appId = (int)($_GET['app_id'] ?? 0);

if (!$appId) {
    http_response_code(400);
    exit('Invalid request.');
}

// Fetch the application — verify access rights
if (getRole() === 'employer') {
    // Employer can only download resumes for their own jobs
    $stmt = $pdo->prepare("
        SELECT applications.resume_path, users.name AS seeker_name
        FROM applications
        JOIN jobs ON applications.job_id = jobs.id
        JOIN users ON applications.seeker_id = users.id
        WHERE applications.id = ? AND jobs.employer_id = ?
    ");
    $stmt->execute([$appId, getUserId()]);
} elseif (getRole() === 'admin') {
    $stmt = $pdo->prepare("
        SELECT applications.resume_path, users.name AS seeker_name
        FROM applications
        JOIN users ON applications.seeker_id = users.id
        WHERE applications.id = ?
    ");
    $stmt->execute([$appId]);
} else {
    http_response_code(403);
    exit('Access denied.');
}

$app = $stmt->fetch();

if (!$app || empty($app['resume_path'])) {
    http_response_code(404);
    exit('Resume not found.');
}

$filePath = dirname(__DIR__) . '/' . $app['resume_path'];

if (!file_exists($filePath)) {
    http_response_code(404);
    exit('File not found on server.');
}

// Detect file type and set appropriate content type + extension
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mimeMap = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
$contentType  = $mimeMap[$ext] ?? 'application/octet-stream';
$safeName     = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $app['seeker_name']);
$safeFilename = $safeName . '_Resume.' . $ext;

header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, no-cache, no-store');
header('Pragma: no-cache');

readfile($filePath);
exit;
