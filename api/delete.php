<?php

require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();

$pdo  = getPDO();
$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

if ($type === 'job') {
    // Soft delete — sets is_deleted=1 instead of hard DELETE (BUG-SUP-011/012)
    if (getRole() === 'admin') {
        $stmt = $pdo->prepare("UPDATE jobs SET is_deleted = 1, deleted_at = NOW(), status = 'closed' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif (getRole() === 'employer') {
        $stmt = $pdo->prepare("UPDATE jobs SET is_deleted = 1, deleted_at = NOW(), status = 'closed' WHERE id = ? AND employer_id = ?");
        $stmt->execute([$id, getUserId()]);
    }

    $redirect = getRole() === 'admin' ? BASE_URL . '/admin/jobs.php?msg=deleted' : BASE_URL . '/dashboard/employer.php?msg=deleted';

} elseif ($type === 'user') {
    // Only admins can delete users
    requireRole('admin');

    // Prevent self-deletion
    if ($id === getUserId()) {
        header('Location: ' . BASE_URL . '/admin/users.php');
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    $redirect = BASE_URL . '/admin/users.php?msg=deleted';

} else {
    $redirect = BASE_URL . '/index.php';
}

header('Location: ' . $redirect);
exit;
