<?php
// ============================================================
// reset-password.php
// Step 2 of password reset:
//   - User arrives here via the link from forgot-password.php
//   - We validate the token (must exist and not be expired)
//   - User enters a new password
//   - Password is updated and token is deleted
// ============================================================
require_once 'includes/auth.php';
require_once 'includes/db.php';

$token   = trim($_GET['token'] ?? '');
$error   = '';
$success = '';

// No token in URL? Send back to login
if (empty($token)) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$pdo = getPDO();

// Look up the token — also check it hasn't expired
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch();

// Handle the new-password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Hash the new password and update the user's record
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE email = ?")
            ->execute([$hashed, $reset['email']]);

        // Delete the token so it can't be reused
        $pdo->prepare("DELETE FROM password_resets WHERE token = ?")
            ->execute([$token]);

        $success = 'Password updated! You can now log in.';
        $reset   = null; // hide the form
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - KaamKhoji</title>
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

        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-subtitle">Enter your new password below</p>

        <?php if ($error): ?>
            <div class="flash flash-error" style="border-radius:8px; margin-bottom:1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash flash-success" style="border-radius:8px; margin-bottom:1.5rem;">
                <?= htmlspecialchars($success) ?>
            </div>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-block">Go to Login</a>

        <?php elseif (!$reset): ?>
            <!-- Token invalid or expired -->
            <div class="flash flash-error" style="border-radius:8px; margin-bottom:1.5rem;">
                This reset link is invalid or has expired.
            </div>
            <a href="<?= BASE_URL ?>/forgot-password.php" class="btn btn-outline btn-block">Request a New Link</a>

        <?php else: ?>
            <!-- Token is valid — show the new password form -->
            <form method="POST" action="<?= BASE_URL ?>/reset-password.php?token=<?= htmlspecialchars($token) ?>">

                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <div class="password-wrap">
                        <input type="password" id="password" name="password"
                               class="form-control"
                               placeholder="Min. 6 characters"
                               required>
                        <button type="button" class="pw-toggle" aria-label="Show/hide password" tabindex="-1" onclick="togglePw('password',this)">
                            <svg class="eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <div class="password-wrap">
                        <input type="password" id="confirm_password" name="confirm_password"
                               class="form-control"
                               placeholder="Repeat new password"
                               required>
                        <button type="button" class="pw-toggle" aria-label="Show/hide password" tabindex="-1" onclick="togglePw('confirm_password',this)">
                            <svg class="eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Update Password
                </button>
            </form>
        <?php endif; ?>

        <div class="auth-switch" style="margin-top:1.25rem;">
            <a href="<?= BASE_URL ?>/login.php">← Back to Login</a>
        </div>

    </div>
</div>

<style>
.password-wrap { position:relative; display:flex; align-items:center; }
.password-wrap .form-control { padding-right:2.8rem; }
.pw-toggle {
    position:absolute; right:0.75rem; background:none; border:none;
    cursor:pointer; color:var(--text-muted); padding:0; display:flex;
    align-items:center; transition:color 0.2s;
}
.pw-toggle:hover { color:var(--primary); }
</style>
<script>
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    if (!inp) return;
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.querySelector('.eye-show').style.display = show ? 'none' : '';
    btn.querySelector('.eye-hide').style.display = show ? '' : 'none';
}
</script>
</body>
</html>
