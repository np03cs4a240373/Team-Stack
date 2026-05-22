<?php
// ============================================================
// forgot-password.php
// Step 1 of password reset:
//   - User enters their email
//   - We generate a secure token and store it in the DB
//   - We display the reset link (no email server needed on XAMPP)
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/mail.php';

// Already logged in? Redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . getDashboardUrl());
    exit;
}

// Auto-create the table if it doesn't exist yet
$pdo = getPDO();
$pdo->exec("
    CREATE TABLE IF NOT EXISTS password_resets (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        email      VARCHAR(150) NOT NULL,
        token      VARCHAR(64)  NOT NULL UNIQUE,
        expires_at DATETIME     NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$error   = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));

            $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
            $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, NOW() + INTERVAL 1 HOUR)")
                ->execute([$email, $token]);

            $resetLink = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/reset-password.php?token=' . $token;
            mailPasswordReset($email, $resetLink);
        }

        // Always show success — don't reveal whether the email exists
        $message = 'If that email is registered, a password reset link has been sent. Please check your inbox.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - KaamKhoji</title>
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700,900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <a href="<?= BASE_URL ?>/index.php">
                <img src="<?= BASE_URL ?>/assets/KaamKhoji.png" alt="KaamKhoji" style="height:80px;width:auto;display:block;margin:-20px auto;">
            </a>
        </div>

        <h1 class="auth-title">Forgot Password</h1>
        <p class="auth-subtitle">Enter your email to get a password reset link</p>

        <?php if ($error): ?>
            <div class="flash flash-error" style="border-radius:8px; margin-bottom:1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="flash flash-success" style="border-radius:8px; margin-bottom:1rem;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!$message): ?>
            <form method="POST" action="<?= BASE_URL ?>/forgot-password.php" data-validate>
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           class="form-control"
                           placeholder="Enter your registered email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required>
                    <span class="form-error">Please enter a valid email address.</span>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Get Reset Link
                </button>
            </form>
        <?php endif; ?>

        <div class="auth-switch" style="margin-top:1.25rem;">
            <a href="<?= BASE_URL ?>/login.php">← Back to Login</a>
        </div>

    </div>
</div>

<script src="<?= BASE_URL ?>/js/utils.js"></script>
<script src="<?= BASE_URL ?>/js/form-validation.js"></script>
</body>
</html>
