<?php
// api/withdraw-application.php - Seeker withdraws their own application
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('seeker');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$pdo   = getPDO();
$appId = (int)($_POST['application_id'] ?? 0);

if (!$appId) {
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

// Verify this application belongs to the current seeker and is not already withdrawn/accepted/rejected
$stmt = $pdo->prepare("
    SELECT id, status FROM applications
    WHERE id = ? AND seeker_id = ?
");
$stmt->execute([$appId, getUserId()]);
$app = $stmt->fetch();

if (!$app) {
    echo json_encode(['error' => 'Application not found.']);
    exit;
}

if (in_array($app['status'], ['withdrawn', 'accepted', 'rejected'])) {
    echo json_encode(['error' => 'This application cannot be withdrawn.']);
    exit;
}

$pdo->prepare("UPDATE applications SET status = 'withdrawn' WHERE id = ?")
    ->execute([$appId]);

echo json_encode(['success' => true]);
