<?php
// ============================================================
// pages/company-profile.php - Employer: Company Profile
// Logo, description, industry, website
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer');

$pdo    = getPDO();
$userId = getUserId();
$error  = '';
$success= '';

$uploadBase = dirname(__DIR__);

// Load existing company profile
$stmt = $pdo->prepare("SELECT * FROM company_profiles WHERE employer_id = ?");
$stmt->execute([$userId]);
$company = $stmt->fetch() ?: ['logo' => '', 'description' => '', 'industry' => '', 'website' => '', 'location' => ''];

// Load current user data (employer account)
$stmtU = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtU->execute([$userId]);
$user = $stmtU->fetch();

// No separate employer avatar: company logo is used as the account image.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $error = 'Invalid form submission. Please try again.';
    }

    // --- Account (employer user) fields ---
    $name        = trim($_POST['name'] ?? $user['name']);
    $phone       = trim($_POST['phone'] ?? $user['phone']);
    $userLoc     = trim($_POST['user_location'] ?? $user['location']);
    $bio         = trim($_POST['bio'] ?? $user['bio']);
    $password    = trim($_POST['password'] ?? '');
    $confirm     = trim($_POST['confirm_password'] ?? '');

    // --- Company fields ---
    $description = trim($_POST['description'] ?? $company['description']);
    $industry    = trim($_POST['industry'] ?? $company['industry']);
    $website     = trim($_POST['website'] ?? $company['website']);
    $companyLoc  = trim($_POST['location'] ?? $company['location']);
    $logoPath    = $company['logo'] ?? '';

    // Basic validation
    if (empty($name)) {
        $error = 'Name cannot be empty.';
    } elseif (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $error = 'Phone number must be exactly 10 digits.';
    } elseif (empty($description)) {
        $error = 'Company description is required.';
    }

    // Employer avatar uploads are disabled — company logo is used instead.

    // Handle company logo upload
    if (empty($error) && !empty($_FILES['logo']['name'])) {
        $file  = $_FILES['logo'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg','image/png','image/gif','image/webp','image/jpg','image/x-png'];
        if (!in_array($mime, $allowed)) {
            $error = 'Logo must be a JPG, PNG, GIF or WebP image.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'Logo must be under 2 MB.';
        } else {
            $logoDir = $uploadBase . '/uploads/logos/';
            if (!is_dir($logoDir)) mkdir($logoDir, 0755, true);
            foreach (['jpg','jpeg','png','gif','webp'] as $ext) {
                $old = $logoDir . $userId . '.' . $ext;
                if (file_exists($old)) unlink($old);
            }
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $ext     = $ext === 'jpeg' ? 'jpg' : $ext;
            $newPath = $logoDir . $userId . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $newPath)) {
                $logoPath = 'uploads/logos/' . $userId . '.' . $ext;
            } else {
                $error = 'Failed to upload logo.';
            }
        }
    }

    // Password validation (optional)
    if (empty($error) && !empty($password)) {
        if (($pwErr = validatePassword($password)) !== '') {
            $error = $pwErr;
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        }
    }

    // Save updates if no errors
    if (empty($error)) {
        // Update users table (account)
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmtU2 = $pdo->prepare("UPDATE users SET name = ?, phone = ?, location = ?, bio = ?, password = ? WHERE id = ?");
            $stmtU2->execute([$name, $phone, $userLoc, $bio, $hashed, $userId]);
        } else {
            $stmtU2 = $pdo->prepare("UPDATE users SET name = ?, phone = ?, location = ?, bio = ? WHERE id = ?");
            $stmtU2->execute([$name, $phone, $userLoc, $bio, $userId]);
        }
        $_SESSION['name'] = $name;
        $user['name'] = $name;
        $user['phone'] = $phone;
        $user['location'] = $userLoc;

        // Update company profile
        $stmt = $pdo->prepare("
            INSERT INTO company_profiles (employer_id, logo, description, industry, website, location)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE logo = VALUES(logo), description = VALUES(description),
                                    industry = VALUES(industry), website = VALUES(website),
                                    location = VALUES(location)
        ");
        $stmt->execute([$userId, $logoPath, $description, $industry, $website, $companyLoc]);

        $success = 'Profile updated successfully!';
        $company = ['logo' => $logoPath, 'description' => $description, 'industry' => $industry, 'website' => $website, 'location' => $companyLoc];
    }
}

// Get employer name for display
$empName = getUserName();

// Stats for this employer
$stTotalJobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ?");
$stTotalJobs->execute([$userId]);
$statTotalJobs = (int)$stTotalJobs->fetchColumn();

$stTotalApps = $pdo->prepare("SELECT COUNT(*) FROM applications JOIN jobs ON applications.job_id = jobs.id WHERE jobs.employer_id = ?");
$stTotalApps->execute([$userId]);
$statTotalApps = (int)$stTotalApps->fetchColumn();

$stAccepted = $pdo->prepare("SELECT COUNT(*) FROM applications JOIN jobs ON applications.job_id = jobs.id WHERE jobs.employer_id = ? AND applications.status = 'accepted'");
$stAccepted->execute([$userId]);
$statAccepted = (int)$stAccepted->fetchColumn();

$stPending = $pdo->prepare("SELECT COUNT(*) FROM applications JOIN jobs ON applications.job_id = jobs.id WHERE jobs.employer_id = ? AND applications.status IN ('applied','pending')");
$stPending->execute([$userId]);
$statPending = (int)$stPending->fetchColumn();

$pageTitle = 'Company Profile';
$pageCss   = 'profile';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Company Profile</h1>
        <p>Help job seekers learn about your company</p>
    </div>
</div>

<div class="container section">
    <div style="max-width:680px; margin:0 auto;">

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

        <!-- Stats Row -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:1rem; margin-bottom:1.5rem;">
            <div class="card" style="padding:1.1rem 1.2rem; text-align:center;">
                <div style="font-size:1.6rem; font-weight:800; color:var(--primary);"><?= $statTotalJobs ?></div>
                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.15rem;">Total Jobs</div>
            </div>
            <div class="card" style="padding:1.1rem 1.2rem; text-align:center;">
                <div style="font-size:1.6rem; font-weight:800; color:var(--primary);"><?= $statTotalApps ?></div>
                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.15rem;">Total Applicants</div>
            </div>
            <div class="card" style="padding:1.1rem 1.2rem; text-align:center;">
                <div style="font-size:1.6rem; font-weight:800; color:#10b981;"><?= $statAccepted ?></div>
                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.15rem;">Accepted</div>
            </div>
            <div class="card" style="padding:1.1rem 1.2rem; text-align:center;">
                <div style="font-size:1.6rem; font-weight:800; color:#f59e0b;"><?= $statPending ?></div>
                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.15rem;">Pending Review</div>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="card mb-3" style="display:flex; align-items:center; gap:1.25rem;">
            <?php if (!empty($company['logo'])): ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($company['logo']) ?>"
                     alt="Company Logo"
                     style="width:64px;height:64px;border-radius:10px;object-fit:contain;border:1px solid var(--border);background:#f8fafc;">
            <?php else: ?>
                <div style="width:64px;height:64px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
            <?php endif; ?>
            <div>
                <div style="font-weight:700; font-size:1rem;"><?= htmlspecialchars($empName) ?></div>
                <?php if (!empty($company['industry'])): ?>
                    <div class="text-muted text-sm"><?= htmlspecialchars($company['industry']) ?><?= !empty($company['location']) ? ' · ' . htmlspecialchars($company['location']) : '' ?></div>
                <?php endif; ?>
                <?php if (!empty($company['website'])): ?>
                    <a href="<?= htmlspecialchars($company['website']) ?>" target="_blank" rel="noopener" class="text-sm" style="color:var(--primary);"><?= htmlspecialchars($company['website']) ?></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <form method="POST" action="<?= BASE_URL ?>/pages/company-profile.php" enctype="multipart/form-data">
                <?= csrfField() ?>

                <!-- Account fields (no separate avatar) -->
                <div class="form-group">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="name">Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                   placeholder="98XXXXXXXX"
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   inputmode="numeric">
                            <span class="form-error">Phone number must be exactly 10 digits.</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="user_location">Location</label>
                        <input type="text" id="user_location" name="user_location" class="form-control" value="<?= htmlspecialchars($user['location'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="bio">Bio</label>
                        <input type="text" id="bio" name="bio" class="form-control" value="<?= htmlspecialchars($user['bio'] ?? '') ?>">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="password">New Password <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="logo">Company Logo <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <input type="file" id="logo" name="logo"
                           accept="image/jpeg,image/png,image/gif,image/webp"
                           class="form-control" style="padding:0.45rem 1rem;">
                    <small class="text-muted">JPG, PNG, GIF or WebP · Max 2 MB</small>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="industry">Industry</label>
                        <input type="text" id="industry" name="industry" class="form-control"
                               placeholder="e.g. Information Technology"
                               value="<?= htmlspecialchars($company['industry']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-control"
                               placeholder="e.g. Kathmandu, Nepal"
                               value="<?= htmlspecialchars($company['location'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="website">Website</label>
                    <input type="url" id="website" name="website" class="form-control"
                           placeholder="https://yourcompany.com"
                           value="<?= htmlspecialchars($company['website']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Company Description *</label>
                    <textarea id="description" name="description" class="form-control" rows="5" style="resize:none;"
                              placeholder="Tell job seekers about your company, culture, mission, and what it's like to work there..."
                              required><?= htmlspecialchars($company['description']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Save Profile</button>

            </form>
        </div>

    </div>
</div>

<script>
(function () {
    const phoneInput = document.getElementById('phone');
    if (!phoneInput) return;
    phoneInput.addEventListener('keypress', function (e) {
        if (!/[0-9]/.test(e.key)) e.preventDefault();
    });
    phoneInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });
    phoneInput.addEventListener('paste', function (e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        this.value = pasted.replace(/[^0-9]/g, '').slice(0, 10);
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>
