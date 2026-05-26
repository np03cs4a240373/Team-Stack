<?php
// ============================================================
// api/mark-notifications-read.php - Mark all unread notifications
// as read for the current seeker (called via AJAX on bell click)
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

if (!isLoggedIn() || !in_array(getRole(), ['seeker', 'employer'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$pdo = getPDO();
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")
    ->execute([getUserId()]);

echo json_encode(['success' => true]);
