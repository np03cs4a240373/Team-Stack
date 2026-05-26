<?php
// ============================================================
// pages/applicant-profile.php — Employer: View a seeker's
// full profile (skills, education, experience, cover letter)
// Access is gated: employer must own the job this app belongs to
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer');

$pdo   = getPDO();
$appId = (int)($_GET['app_id'] ?? 0);

if (!$appId) {
    header('Location: ' . BASE_URL . '/pages/applicants.php');
    exit;
}

// Fetch everything in one query; verify employer owns the job
$stmt = $pdo->prepare("
    SELECT
        applications.id          AS app_id,
        applications.status,
        applications.applied_at,
        applications.cover_letter,
        applications.resume_path,
        applications.job_id,
        users.id                 AS seeker_id,
        users.name,
        users.email,
        users.phone,
        users.location,
        users.bio,
        jobs.title               AS job_title,
        jobs.company,
        jp.skills,
        jp.education,
        jp.experience,
        jp.cv_path
    FROM applications
    JOIN jobs  ON applications.job_id    = jobs.id
    JOIN users ON applications.seeker_id = users.id
    LEFT JOIN jobseeker_profiles jp ON jp.user_id = applications.seeker_id
    WHERE applications.id = ? AND jobs.employer_id = ?
");
$stmt->execute([$appId, getUserId()]);
$p = $stmt->fetch();

if (!$p) {
    header('Location: ' . BASE_URL . '/pages/applicants.php');
    exit;
}

// Resolve avatar path
$avatarUrl = null;
$root = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
foreach (['jpg','jpeg','png','gif','webp'] as $ext) {
    $abs = $root . '/uploads/avatars/' . $p['seeker_id'] . '.' . $ext;
    if (file_exists($abs)) {
        $avatarUrl = BASE_URL . '/uploads/avatars/' . $p['seeker_id'] . '.' . $ext . '?v=' . @filemtime($abs);
        break;
    }
}

$statusColors = [
    'applied'     => '#64748b',
    'pending'     => '#f59e0b',
    'reviewed'    => '#3b82f6',
    'shortlisted' => '#8b5cf6',
    'interview'   => '#06b6d4',
    'accepted'    => '#10b981',
    'rejected'    => '#ef4444',
    'withdrawn'   => '#94a3b8',
];
$statusColor = $statusColors[$p['status']] ?? '#64748b';

$pageTitle = htmlspecialchars($p['name']) . ' — Applicant Profile';
$pageCss   = 'applicants';
require_once '../includes/header.php';
?>

<style>
.profile-hero {
    background: linear-gradient(135deg, #e0f7fa 0%, #f0fdff 60%, #f8fafc 100%);
    border-bottom: 1px solid rgba(0,180,216,0.12);
    padding: 2.5rem 0 2rem;
}
.profile-hero-inner {
    max-width: 780px;
    margin: 0 auto;
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.profile-avatar-lg {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #00b4d8;
    color: #fff;
    font-size: 2rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 3px solid #fff;
    box-shadow: 0 4px 16px rgba(0,180,216,0.25);
    overflow: hidden;
}
.profile-avatar-lg img { width:100%; height:100%; object-fit:cover; }
.profile-hero-info { flex: 1; min-width: 0; }
.profile-hero-name {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 0.25rem;
    line-height: 1.2;
}
.profile-hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    margin-top: 0.4rem;
}
.profile-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.82rem;
    color: #64748b;
}
.profile-section {
    max-width: 780px;
    margin: 0 auto;
    padding: 0 1.5rem;
}
.profile-card {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 14px;
    padding: 1.5rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}
.profile-card-title {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #00b4d8;
    margin-bottom: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.45rem;
}
.profile-card-body {
    font-size: 0.88rem;
    color: #334155;
    line-height: 1.75;
    white-space: pre-wrap;
    word-break: break-word;
}
.skill-chip {
    display: inline-flex;
    align-items: center;
    background: rgba(0,180,216,0.1);
    color: #0096b3;
    border-radius: 999px;
    padding: 0.25rem 0.75rem;
    font-size: 0.78rem;
    font-weight: 600;
    margin: 0.2rem 0.2rem 0.2rem 0;
}
.app-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.85rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #fff;
}
.empty-field {
    color: #94a3b8;
    font-style: italic;
    font-size: 0.85rem;
}
</style>

<!-- Profile Hero -->
<div class="profile-hero">
    <div class="profile-hero-inner">
        <div class="profile-avatar-lg">
            <?php if ($avatarUrl): ?>
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            <?php else: ?>
                <?= strtoupper(mb_substr($p['name'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <div class="profile-hero-info">
            <div class="profile-hero-name"><?= htmlspecialchars($p['name']) ?></div>
            <div class="profile-hero-meta">
                <?php if ($p['email']): ?>
                <span class="profile-meta-item">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?= htmlspecialchars($p['email']) ?>
                </span>
                <?php endif; ?>
                <?php if ($p['phone']): ?>
                <span class="profile-meta-item">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.63 3.36 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 15.92z"/></svg>
                    <?= htmlspecialchars($p['phone']) ?>
                </span>
                <?php endif; ?>
                <?php if ($p['location']): ?>
                <span class="profile-meta-item">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <?= htmlspecialchars($p['location']) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.6rem; flex-shrink:0;">
            <span class="app-status-pill" style="background:<?= $statusColor ?>;">
                <?= ucfirst($p['status']) ?>
            </span>
            <span style="font-size:0.75rem; color:#94a3b8;">
                Applied for <strong style="color:#475569;"><?= htmlspecialchars($p['job_title']) ?></strong>
            </span>
            <span style="font-size:0.72rem; color:#94a3b8;">
                <?= date('M d, Y', strtotime($p['applied_at'])) ?>
            </span>
        </div>
    </div>
</div>

<div class="container section">
<div class="profile-section">

    <!-- Back + Download row -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.75rem; margin-bottom:1.5rem;">
        <a href="<?= BASE_URL ?>/pages/applicants.php" class="btn btn-outline btn-sm">
            ← Back to Applicants
        </a>
        <?php if (!empty($p['resume_path'])): ?>
        <a href="<?= BASE_URL ?>/api/download-resume.php?app_id=<?= $appId ?>"
           class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:0.4rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download CV / Resume
        </a>
        <?php endif; ?>
    </div>

    <!-- About / Bio -->
    <?php if (!empty($p['bio'])): ?>
    <div class="profile-card">
        <div class="profile-card-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            About
        </div>
        <div class="profile-card-body"><?= nl2br(htmlspecialchars($p['bio'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- Skills -->
    <div class="profile-card">
        <div class="profile-card-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Skills
        </div>
        <?php if (!empty($p['skills'])): ?>
            <div>
                <?php foreach (array_filter(array_map('trim', explode(',', $p['skills']))) as $skill): ?>
                    <span class="skill-chip"><?= htmlspecialchars($skill) ?></span>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <span class="empty-field">No skills listed yet.</span>
        <?php endif; ?>
    </div>

    <!-- Education -->
    <div class="profile-card">
        <div class="profile-card-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            Education
        </div>
        <?php if (!empty($p['education'])): ?>
            <div class="profile-card-body"><?= nl2br(htmlspecialchars($p['education'])) ?></div>
        <?php else: ?>
            <span class="empty-field">No education listed yet.</span>
        <?php endif; ?>
    </div>

    <!-- Work Experience -->
    <div class="profile-card">
        <div class="profile-card-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Work Experience
        </div>
        <?php if (!empty($p['experience'])): ?>
            <div class="profile-card-body"><?= nl2br(htmlspecialchars($p['experience'])) ?></div>
        <?php else: ?>
            <span class="empty-field">No work experience listed yet.</span>
        <?php endif; ?>
    </div>

    <!-- Cover Letter -->
    <?php if (!empty($p['cover_letter'])): ?>
    <div class="profile-card">
        <div class="profile-card-title">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Cover Letter
            <span style="font-size:0.7rem; color:#94a3b8; font-weight:400; text-transform:none; letter-spacing:0;">
                — for <?= htmlspecialchars($p['job_title']) ?> at <?= htmlspecialchars($p['company']) ?>
            </span>
        </div>
        <div class="profile-card-body"><?= nl2br(htmlspecialchars($p['cover_letter'])) ?></div>
    </div>
    <?php endif; ?>

</div>
</div>

<?php require_once '../includes/footer.php'; ?>
