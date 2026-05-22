<?php
// ============================================================
// api/update-application-status.php - AJAX: Employer updates
// applicant status and triggers email notification to seeker
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/mail.php';

requireRole('employer');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$pdo      = getPDO();
$appId    = (int)($_POST['application_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';
$validStatuses = ['applied', 'pending', 'reviewed', 'shortlisted', 'interview', 'accepted', 'rejected', 'withdrawn'];

if (!$appId || !in_array($newStatus, $validStatuses)) {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

// Fetch application details — verify employer owns the job
$stmt = $pdo->prepare("
    SELECT applications.id, applications.seeker_id, applications.status AS old_status,
           users.email AS seeker_email, users.name AS seeker_name,
           jobs.title AS job_title, jobs.company,
           employer.email AS employer_email
    FROM applications
    JOIN jobs ON applications.job_id = jobs.id
    JOIN users ON applications.seeker_id = users.id
    JOIN users AS employer ON jobs.employer_id = employer.id
    WHERE applications.id = ? AND jobs.employer_id = ?
");
$stmt->execute([$appId, getUserId()]);
$app = $stmt->fetch();

if (!$app) {
    echo json_encode(['error' => 'Application not found or access denied.']);
    exit;
}

// Update status
$pdo->prepare("UPDATE applications SET status = ? WHERE id = ?")->execute([$newStatus, $appId]);

// Send email notification and create in-app notification if status changed
if ($newStatus !== $app['old_status']) {
    mailStatusChange(
        $app['seeker_email'],
        $app['seeker_name'],
        $app['job_title'],
        $app['company'],
        $newStatus
    );

    // Insert in-app notification for accepted applications
    if ($newStatus === 'accepted') {
        $notifMsg = 'Your application for "' . $app['job_title'] . '" at ' . $app['company'] . ' has been approved!';
        $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, link)
            VALUES (?, 'application_accepted', 'Application Approved', ?, '/pages/my-applications.php')
        ")->execute([$app['seeker_id'], $notifMsg]);
    }
}

echo json_encode(['success' => true, 'status' => $newStatus]);
