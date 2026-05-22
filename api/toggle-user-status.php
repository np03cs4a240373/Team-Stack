<?php
// ============================================================
// api/toggle-user-status.php - Admin: Suspend or Activate a user
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('admin');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$pdo    = getPDO();
$userId = (int)($_POST['user_id'] ?? 0);

if (!$userId || $userId === getUserId()) {
    echo json_encode(['error' => 'Invalid user.']);
    exit;
}

// Get current status
$stmt = $pdo->prepare("SELECT is_active, name FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['error' => 'User not found.']);
    exit;
}

$newStatus = $user['is_active'] ? 0 : 1;
$pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$newStatus, $userId]);

echo json_encode([
    'success'    => true,
    'is_active'  => $newStatus,
    'label'      => $newStatus ? 'Suspend' : 'Activate',
    'badge'      => $newStatus ? 'active' : 'suspended',
]);
