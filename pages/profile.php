<?php
// pages/profile.php - Edit Profile (all roles)
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireLogin();

$pdo    = getPDO();
$userId = getUserId();
$error  = '';
$success = '';

// Load current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Load seeker extended profile (skills/education/experience/cv)
$seekerProfile = null;
$skillsError   = '';
$skillsSuccess = '';
$cvError       = '';
$cvSuccess     = '';
if ($user['role'] === 'seeker') {
    $sp = $pdo->prepare("SELECT * FROM jobseeker_profiles WHERE user_id = ?");
    $sp->execute([$userId]);
    $seekerProfile = $sp->fetch() ?: ['skills' => '', 'education' => '', 'experience' => '', 'cv_path' => ''];
}

// Helper: find existing profile picture for user
function getUserProfilePic(int $uid, string $base): ?string
{
    foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
        $rel = 'uploads/avatars/' . $uid . '.' . $ext;
        if (file_exists($base . '/' . $rel)) return $rel;
    }
    return null;
}
$uploadBase = dirname(__DIR__);
$existingPic = getUserProfilePic($userId, $uploadBase);

// Ensure uploads directories exist
foreach (['uploads/avatars', 'uploads/resumes', 'uploads/logos'] as $dir) {
    $dirPath = $uploadBase . '/' . $dir;
    if (!is_dir($dirPath)) mkdir($dirPath, 0755, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken()) {
        $error = 'Invalid form submission. Please try again.';
    } else {

        // --- CV upload form (seekers only) ---
        if (isset($_POST['section']) && $_POST['section'] === 'cv' && $user['role'] === 'seeker') {
            if (!empty($_FILES['cv']['name']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
                $cvFile = $_FILES['cv'];
                if ($cvFile['error'] !== UPLOAD_ERR_OK) {
                    $cvError = 'Upload error (code ' . $cvFile['error'] . '). Check PHP upload settings.';
                } else {
                    $cvExt     = strtolower(pathinfo($cvFile['name'], PATHINFO_EXTENSION));
                    $finfo     = new finfo(FILEINFO_MIME_TYPE);
                    $cvMime    = $finfo->file($cvFile['tmp_name']);
                    if ($cvMime !== 'application/pdf' || $cvExt !== 'pdf') {
                        $cvError = 'CV must be a PDF file.';
                    } elseif ($cvFile['size'] > 5 * 1024 * 1024) {
                        $cvError = 'CV must be under 5 MB.';
                    } else {
                        $cvDir = $uploadBase . '/uploads/resumes/';
                        if (!is_dir($cvDir)) mkdir($cvDir, 0755, true);
                        $safeCvName = 'cv_' . $userId . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $cvFile['name']);
                        if (move_uploaded_file($cvFile['tmp_name'], $cvDir . $safeCvName)) {
                            $cvPath = 'uploads/resumes/' . $safeCvName;
                            $pdo->prepare("
                        INSERT INTO jobseeker_profiles (user_id, cv_path)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE cv_path = VALUES(cv_path)
                    ")->execute([$userId, $cvPath]);
                            $seekerProfile['cv_path'] = $cvPath;
                            $cvSuccess = 'CV uploaded successfully!';
                        } else {
                            $cvError = 'Failed to save CV. Check folder permissions.';
                        }
                    }
                }
            } else {
                $cvError = 'Please select a file to upload.';
            }

            // --- Skills form (seekers only) ---
        } elseif (isset($_POST['section']) && $_POST['section'] === 'skills' && $user['role'] === 'seeker') {
            $skills     = trim($_POST['skills']     ?? '');
            $education  = trim($_POST['education']  ?? '');
            $experience = trim($_POST['experience'] ?? '');
            if (empty($skills) || empty($education)) {
                $skillsError = 'Skills and Education are required.';
            } else {
                $sp2 = $pdo->prepare("
            INSERT INTO jobseeker_profiles (user_id, skills, education, experience)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE skills = VALUES(skills), education = VALUES(education), experience = VALUES(experience)
        ");
                $sp2->execute([$userId, $skills, $education, $experience]);
                $skillsSuccess  = 'Skills updated successfully!';
                $seekerProfile['skills']     = $skills;
                $seekerProfile['education']  = $education;
                $seekerProfile['experience'] = $experience;
            }

            // --- Main profile form ---
        } else {
            $name     = trim($_POST['name']     ?? '');
            $phone    = trim($_POST['phone']    ?? '');
            $location = trim($_POST['location'] ?? '');
            $bio      = trim($_POST['bio']      ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirm  = trim($_POST['confirm_password'] ?? '');

            if (empty($name)) {
                $error = 'Name cannot be empty.';
            } elseif (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
                $error = 'Phone number must be exactly 10 digits.';
            } elseif (!empty($password) && ($pwErr = validatePassword($password)) !== '') {
                $error = $pwErr;
            } elseif (!empty($password) && $password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                // Handle profile picture upload
                if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $file = $_FILES['profile_pic'];
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Upload error (code ' . $file['error'] . '). Check PHP upload settings.';
                    } else {
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $fileExt     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $fileExt     = $fileExt === 'jpeg' ? 'jpg' : $fileExt;
                        // Use finfo for MIME (more reliable on Windows than mime_content_type)
                        $finfo   = new finfo(FILEINFO_MIME_TYPE);
                        $mime    = $finfo->file($file['tmp_name']);
                        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/x-png'];
                        if (!in_array($mime, $allowed) && !in_array($fileExt, $allowedExts)) {
                            $error = 'Profile picture must be a JPG, PNG, GIF or WebP image.';
                        } elseif ($file['size'] > 3 * 1024 * 1024) {
                            $error = 'Profile picture must be under 3 MB.';
                        } else {
                            $avatarDir = $uploadBase . '/uploads/avatars/';
                            if (!is_dir($avatarDir)) mkdir($avatarDir, 0755, true);
                            foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $e) {
                                $old = $avatarDir . $userId . '.' . $e;
                                if (file_exists($old)) unlink($old);
                            }
                            $newPath = $avatarDir . $userId . '.' . $fileExt;
                            if (move_uploaded_file($file['tmp_name'], $newPath)) {
                                $existingPic = 'uploads/avatars/' . $userId . '.' . $fileExt;
                            } else {
                                $error = 'Failed to save profile picture. Check folder permissions.';
                            }
                        }
                    }
                }

                if (empty($error)) {
                    if (!empty($password)) {
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET name=?, phone=?, location=?, bio=?, password=? WHERE id=?");
                        $stmt->execute([$name, $phone, $location, $bio, $hashed, $userId]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET name=?, phone=?, location=?, bio=? WHERE id=?");
                        $stmt->execute([$name, $phone, $location, $bio, $userId]);
                    }

                    $_SESSION['name'] = $name;
                    $success = 'Profile updated successfully!';

                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();
                }
            }
        } // end main profile else
    } // end CSRF else
} // end POST

$pageTitle = 'My Profile';
$pageCss = 'profile';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>My Profile</h1>
        <p>Update your personal information</p>
    </div>
</div>

<div class="container section">
    <div style="max-width:640px; margin:0 auto;">

        <?php if ($success): ?>
            <div class="flash flash-success" style="border-radius:8px; margin-bottom:1.5rem;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="flash flash-error" style="border-radius:8px; margin-bottom:1.5rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Profile Header Card -->
        <div class="card mb-3" style="text-align:center; padding:2rem;">
            <?php if ($existingPic): ?>
                <?php $picMtime = @filemtime($uploadBase . '/' . $existingPic) ?: time(); ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($existingPic) ?>?v=<?= $picMtime ?>" alt="Profile"
                    style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto 1rem;border:3px solid #e0f7fa;display:block;">
            <?php else: ?>
                <div style="width:80px;height:80px;border-radius:50%;background:#00b4d8;display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:#fff;margin:0 auto 1rem;">
                    <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
                </div>
            <?php endif; ?>
            <h2 style="font-size:1.2rem; font-weight:700;"><?= htmlspecialchars($user['name']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
            <span class="status-badge <?= $user['role'] === 'employer' ? 'status-accepted' : ($user['role'] === 'admin' ? 'status-reviewed' : 'status-pending') ?>" style="margin-top:0.5rem; display:inline-block;">
                <?= ucfirst($user['role']) ?>
            </span>
        </div>

        <!-- Edit Form -->
        <div class="card">
            <h3 style="font-size:1rem; font-weight:600; margin-bottom:1.5rem;">Edit Information</h3>

            <form method="POST" action="<?= BASE_URL ?>/pages/profile.php" data-validate enctype="multipart/form-data">
                <?= csrfField() ?>

                <!-- Profile Picture Upload -->
                <div class="form-group">
                    <label class="form-label" for="profile_pic">Profile Picture</label>
                    <input type="file" id="profile_pic" name="profile_pic"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        class="form-control"
                        style="padding:0.45rem 1rem;">
                    <small class="text-muted">JPG, PNG, GIF or WebP · Max 3 MB</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" id="name" name="name"
                        class="form-control"
                        value="<?= htmlspecialchars($user['name']) ?>"
                        required>
                    <span class="form-error">Name is required.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled
                        style="opacity:0.6; cursor:not-allowed;">
                    <small class="text-muted">Email cannot be changed.</small>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                            class="form-control"
                            placeholder="98XXXXXXXX"
                            maxlength="10"
                            pattern="[0-9]{10}"
                            inputmode="numeric"
                            value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        <span class="form-error">Phone number must be exactly 10 digits.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" id="location" name="location"
                            class="form-control"
                            placeholder="e.g. Kathmandu"
                            value="<?= htmlspecialchars($user['location'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="bio">Bio / About</label>
                    <textarea id="bio" name="bio"
                        class="form-control"
                        rows="3"
                        style="resize:none;"
                        placeholder="Tell employers a bit about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>

                <hr style="border:none; border-top:1px solid var(--border); margin:1.5rem 0;">
                <h4 style="font-size:0.9rem; font-weight:600; margin-bottom:1rem; color:var(--text-muted);">
                    Change Password (leave blank to keep current)
                </h4>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <div class="password-wrap">
                            <input type="password" id="password" name="password"
                                class="form-control"
                                placeholder="Min. 8 chars, upper, lower, number, special">
                            <button type="button" class="pw-toggle" aria-label="Show/hide password" tabindex="-1" onclick="togglePw('password',this)">
                                <svg class="eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg class="eye-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                    <line x1="1" y1="1" x2="23" y2="23" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <div class="password-wrap">
                            <input type="password" id="confirm_password" name="confirm_password"
                                class="form-control"
                                placeholder="Repeat new password">
                            <button type="button" class="pw-toggle" aria-label="Show/hide password" tabindex="-1" onclick="togglePw('confirm_password',this)">
                                <svg class="eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg class="eye-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                    <line x1="1" y1="1" x2="23" y2="23" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>

            </form>
        </div>

        <?php if ($user['role'] === 'seeker'): ?>
            <?php
            // --- Profile Completeness (BUG-SUP-006) ---
            $completenessItems = [
                'Photo'       => !empty($existingPic),
                'Phone'       => !empty($user['phone']),
                'Location'    => !empty($user['location']),
                'Skills'      => !empty($seekerProfile['skills']),
                'Education'   => !empty($seekerProfile['education']),
                'Experience'  => !empty($seekerProfile['experience']),
                'CV / Resume' => !empty($seekerProfile['cv_path']),
            ];
            $completedCount  = count(array_filter($completenessItems));
            $totalItems      = count($completenessItems);
            $completePct     = (int)round(($completedCount / $totalItems) * 100);
            $completeColor   = $completePct < 40 ? '#ef4444' : ($completePct < 75 ? '#f59e0b' : '#10b981');
            ?>

            <!-- Profile Completeness Card -->
            <div class="card" style="margin-top:1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <h3 style="font-size:1rem; font-weight:600; margin:0;">Profile Completeness</h3>
                    <span style="font-size:1.2rem; font-weight:800; color:<?= $completeColor ?>;"><?= $completePct ?>%</span>
                </div>
                <div style="background:var(--border,#e2e8f0); border-radius:50px; height:8px; margin-bottom:1rem;">
                    <div style="width:<?= $completePct ?>%; background:<?= $completeColor ?>; border-radius:50px; height:8px; transition:width 0.5s ease;"></div>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                    <?php foreach ($completenessItems as $label => $done): ?>
                        <span style="font-size:0.75rem; padding:0.2rem 0.65rem; border-radius:50px;
                                 background:<?= $done ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.1)' ?>;
                                 color:<?= $done ? '#10b981' : '#ef4444' ?>; font-weight:600;">
                            <?= $done ? '✓' : '○' ?> <?= $label ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- CV / Resume Upload Card (BUG-SUP-005) -->
            <div class="card" style="margin-top:1.5rem;">
                <h3 style="font-size:1rem; font-weight:600; margin-bottom:1.5rem;">CV / Resume</h3>

                <?php if ($cvSuccess): ?>
                    <div class="flash flash-success" style="border-radius:8px; margin-bottom:1rem;">
                        <?= htmlspecialchars($cvSuccess) ?>
                    </div>
                <?php endif; ?>
                <?php if ($cvError): ?>
                    <div class="flash flash-error" style="border-radius:8px; margin-bottom:1rem;">
                        <?= htmlspecialchars($cvError) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($seekerProfile['cv_path'])): ?>
                    <div style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; background:rgba(0,180,216,0.06); border:1px solid rgba(0,180,216,0.2); border-radius:8px; margin-bottom:1rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00b4d8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        <span style="font-size:0.85rem; flex:1; word-break:break-all;"><?= htmlspecialchars(basename($seekerProfile['cv_path'])) ?></span>
                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($seekerProfile['cv_path']) ?>" target="_blank" class="btn btn-outline btn-sm">View</a>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/pages/profile.php" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="cv">
                    <div class="form-group">
                        <label class="form-label" for="cv">Upload CV</label>
                        <input type="file" id="cv" name="cv" class="form-control"
                            accept=".pdf,application/pdf"
                            style="padding:0.45rem 1rem;">
                        <small class="text-muted">PDF only &middot; Max 5 MB</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload CV</button>
                </form>
            </div>

            <!-- Skills & Experience section -->
            <div class="card" style="margin-top:1.5rem;">
                <h3 style="font-size:1rem; font-weight:600; margin-bottom:1.5rem;">Skills &amp; Experience</h3>

                <?php if ($skillsSuccess): ?>
                    <div class="flash flash-success" style="border-radius:8px; margin-bottom:1.5rem;">
                        <?= htmlspecialchars($skillsSuccess) ?>
                    </div>
                <?php endif; ?>
                <?php if ($skillsError): ?>
                    <div class="flash flash-error" style="border-radius:8px; margin-bottom:1.5rem;">
                        <?= htmlspecialchars($skillsError) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/pages/profile.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="section" value="skills">

                    <div class="form-group">
                        <label class="form-label" for="skills">Skills *</label>
                        <textarea id="skills" name="skills" class="form-control" rows="3"
                            placeholder="e.g. PHP, MySQL, JavaScript, React, Communication..."
                            style="resize:none;"
                            required><?= htmlspecialchars($seekerProfile['skills']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="education">Education *</label>
                        <textarea id="education" name="education" class="form-control" rows="4"
                            placeholder="e.g.&#10;Bachelor of Computer Science, Tribhuvan University (2018 to 2022)&#10;+2 Science, XYZ College (2016 to 2018)"
                            style="resize:none;"
                            required><?= htmlspecialchars($seekerProfile['education']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="experience">Work Experience <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                        <textarea id="experience" name="experience" class="form-control" rows="5"
                            placeholder="e.g.&#10;Junior Developer, Tech Corp, Kathmandu (Jan 2022 to Present)&#10;Built REST APIs with PHP and MySQL&#10;&#10;Intern, Startup Hub (Jun 2021 to Dec 2021)"
                            style="resize:none;"><?= htmlspecialchars($seekerProfile['experience']) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Skills</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>

<style>
    .password-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-wrap .form-control {
        padding-right: 2.8rem;
    }

    .pw-toggle {
        position: absolute;
        right: 0.75rem;
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-muted);
        padding: 0;
        display: flex;
        align-items: center;
        transition: color 0.2s;
    }

    .pw-toggle:hover {
        color: var(--primary);
    }
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
    (function() {
        const phoneInput = document.getElementById('phone');
        if (!phoneInput) return;
        phoneInput.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) e.preventDefault();
        });
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            this.value = pasted.replace(/[^0-9]/g, '').slice(0, 10);
        });
    })();
</script>

<?php require_once '../includes/footer.php'; ?>
