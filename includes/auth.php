<?php

session_start();

if (!defined('BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    define('BASE_URL', str_replace($docRoot, '', $projectRoot));
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}


function getRole(): ?string {
    return $_SESSION['role'] ?? null;
}


function getUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}


function getUserName(): ?string {
    return $_SESSION['name'] ?? null;
}


function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php?msg=login_required');
        exit;
    }
}


function requireRole(string $role): void {
    requireLogin();
    if (getRole() !== $role) {
        header('Location: ' . BASE_URL . '/index.php?msg=access_denied');
        exit;
    }
}


function loginUser(array $user): void {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];
}


function getDashboardUrl(): string {
    return match(getRole()) {
        'employer' => BASE_URL . '/dashboard/employer.php',
        'admin'    => BASE_URL . '/dashboard/admin.php',
        default    => BASE_URL . '/dashboard/job-seeker.php',
    };
}
