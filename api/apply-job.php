<?php
// api/apply-job.php - AJAX: Submit Job Application
// Returns JSON: { success: true } or { error: "..." }

require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/mail.php';

header('Content-Type: application/json');

// Must be a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Must be logged in as a seeker
if (!isLoggedIn() || getRole() !== 'seeker') {
    echo json_encode(['error' => 'Please login as a Job Seeker to apply.']);
    exit;
}

$pdo = getPDO();

$jobId       = (int)($_POST['job_id'] ?? 0);
$coverLetter = trim($_POST['cover_letter'] ?? '');
$seekerId    = getUserId();
$resumePath  = null;

if (!$jobId) {
    echo json_encode(['error' => 'Invalid job.']);
    exit;
}

// Reject special characters in cover letter (allow letters, numbers, whitespace, and common punctuation)
if (preg_match('/[^a-zA-Z0-9\s.,!?\'"()\-:;\x{0900}-\x{097F}]/u', $coverLetter)) {
    echo json_encode(['error' => 'Cover letter contains special characters. Only letters, numbers, and basic punctuation are allowed.']);
    exit;
}

// Check the job exists and is active
$stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND status = 'active'");
$stmt->execute([$jobId]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'This job is no longer available.']);
    exit;
}

// Check if already applied
$stmt = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?");
$stmt->execute([$jobId, $seekerId]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'You have already applied for this job.']);
    exit;
}

// Handle PDF resume upload (BUG-SUP-017)
if (!empty($_FILES['resume']['name'])) {
    $file    = $_FILES['resume'];
    $maxSize = 5 * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'Upload error (code ' . $file['error'] . '). Check PHP settings.']);
        exit;
    }
    if ($file['size'] > $maxSize) {
        echo json_encode(['error' => 'Resume file is too large. Maximum 5MB allowed.']);
        exit;
    }
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $fileExt  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($mimeType !== 'application/pdf' || $fileExt !== 'pdf') {
        echo json_encode(['error' => 'Only PDF files are accepted for resume upload.']);
        exit;
    }

    $uploadDir = dirname(__DIR__) . '/uploads/resumes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $safeName   = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    $fileName   = $seekerId . '_' . $jobId . '_' . $safeName;
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['error' => 'Failed to upload resume. Please try again.']);
        exit;
    }

    $resumePath = 'uploads/resumes/' . $fileName;
}

// Fetch job + employer info for email and notification
$jobStmt = $pdo->prepare("
    SELECT jobs.title, jobs.company, jobs.employer_id,
           users.email AS employer_email, users.name AS employer_name
    FROM jobs JOIN users ON jobs.employer_id = users.id WHERE jobs.id = ?
");
$jobStmt->execute([$jobId]);
$jobInfo = $jobStmt->fetch();

// Fetch seeker info for email
$seekerStmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$seekerStmt->execute([$seekerId]);
$seeker = $seekerStmt->fetch();

// Insert application with status = 'applied'
try {
    $stmt = $pdo->prepare("
        INSERT INTO applications (job_id, seeker_id, cover_letter, resume_path, status)
        VALUES (?, ?, ?, ?, 'applied')
    ");
    $stmt->execute([$jobId, $seekerId, $coverLetter, $resumePath]);

    // Send email notifications and create in-app notification for employer
    if ($jobInfo && $seeker) {
        mailApplicationConfirmation($seeker['email'], $seeker['name'], $jobInfo['title'], $jobInfo['company']);
        mailNewApplicantAlert($jobInfo['employer_email'], $jobInfo['employer_name'], $jobInfo['title'], $seeker['name']);

        $notifMsg = $seeker['name'] . ' has applied for your job "' . $jobInfo['title'] . '" at ' . $jobInfo['company'] . '.';
        $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, link)
            VALUES (?, 'new_applicant', 'New Application', ?, '/pages/applicants.php')
        ")->execute([$jobInfo['employer_id'], $notifMsg]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Fallback: try without resume_path column if it doesn't exist yet
    try {
        $stmt = $pdo->prepare("
            INSERT INTO applications (job_id, seeker_id, cover_letter, status)
            VALUES (?, ?, ?, 'applied')
        ");
        $stmt->execute([$jobId, $seekerId, $coverLetter]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e2) {
        echo json_encode(['error' => 'Application failed. Please try again.']);
    }
}
