<?php
// ============================================================
// pages/seeker-profile.php - Job Seeker Extended Profile
// Skills, Education, Work Experience
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('seeker');

$pdo    = getPDO();
$userId = getUserId();
$error  = '';
$success= '';

// Load existing profile
$stmt = $pdo->prepare("SELECT * FROM jobseeker_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch() ?: ['skills' => '', 'education' => '', 'experience' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skills     = trim($_POST['skills']     ?? '');
    $education  = trim($_POST['education']  ?? '');
    $experience = trim($_POST['experience'] ?? '');

    if (empty($skills) || empty($education)) {
        $error = 'Skills and Education are required fields.';
    } else {
        // Upsert
        $stmt = $pdo->prepare("
            INSERT INTO jobseeker_profiles (user_id, skills, education, experience)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE skills = VALUES(skills), education = VALUES(education), experience = VALUES(experience)
        ");
        $stmt->execute([$userId, $skills, $education, $experience]);
        $success = 'Profile updated successfully!';
        $profile = ['skills' => $skills, 'education' => $education, 'experience' => $experience];
    }
}

$pageTitle = 'My Skills & Experience';
$pageCss   = 'profile';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Skills & Experience</h1>
        <p>Help employers understand your background</p>
    </div>
</div>

<div class="container section">
    <div style="max-width:680px; margin:0 auto;">

        <div style="display:flex; gap:0.75rem; margin-bottom:1.5rem; flex-wrap:wrap;">
            <a href="<?= BASE_URL ?>/pages/profile.php" class="btn btn-outline btn-sm">← Basic Info</a>
            <span class="btn btn-primary btn-sm" style="cursor:default;">Skills &amp; Experience</span>
        </div>

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

        <div class="card">
            <form method="POST" action="<?= BASE_URL ?>/pages/seeker-profile.php">

                <div class="form-group">
                    <label class="form-label" for="skills">Skills *</label>
                    <textarea id="skills" name="skills" class="form-control" rows="3"
                              placeholder="e.g. PHP, MySQL, JavaScript, React, Communication, Problem Solving..."
                              required><?= htmlspecialchars($profile['skills']) ?></textarea>
                    <small class="text-muted">List your key skills, separated by commas.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="education">Education *</label>
                    <textarea id="education" name="education" class="form-control" rows="4"
                              placeholder="e.g.&#10;Bachelor of Computer Science — Tribhuvan University (2018–2022)&#10;+2 Science — XYZ College (2016–2018)"
                              required><?= htmlspecialchars($profile['education']) ?></textarea>
                    <small class="text-muted">List your degrees, institutions, and years. One per line.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="experience">Work Experience <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <textarea id="experience" name="experience" class="form-control" rows="5"
                              placeholder="e.g.&#10;Junior Developer — Tech Corp, Kathmandu (Jan 2022 – Present)&#10;- Built REST APIs with PHP and MySQL&#10;- Maintained React frontend&#10;&#10;Intern — Startup Hub (Jun 2021 – Dec 2021)"><?= htmlspecialchars($profile['experience']) ?></textarea>
                    <small class="text-muted">Describe your past jobs, roles, and responsibilities.</small>
                </div>

                <button type="submit" class="btn btn-primary">Save Profile</button>

            </form>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
