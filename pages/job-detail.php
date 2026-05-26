<?php
// pages/job-detail.php - Single Job Detail Page
// Shows full job info + apply form for seekers
require_once '../includes/auth.php';
require_once '../includes/db.php';

$pdo = getPDO();
autoExpireJobs($pdo);

// Get job ID from URL
$jobId = (int)($_GET['id'] ?? 0);
if (!$jobId) {
    header('Location: ' . BASE_URL . '/pages/jobs.php');
    exit;
}

// Fetch job details (including company profile for the sidebar)
if (isLoggedIn() && getRole() === 'employer') {
    $stmt = $pdo->prepare("
        SELECT jobs.*, users.name AS employer_name, users.location AS employer_location,
               cp.description AS company_description, cp.website AS company_website,
               cp.industry AS company_industry, cp.location AS company_location,
               (SELECT COUNT(*) FROM applications WHERE job_id = jobs.id) AS app_count
        FROM jobs
        JOIN users ON jobs.employer_id = users.id
        LEFT JOIN company_profiles cp ON cp.employer_id = jobs.employer_id
        WHERE jobs.id = ? AND jobs.is_deleted = 0 AND jobs.employer_id = ?
    ");
    $stmt->execute([$jobId, getUserId()]);
} else {
    $stmt = $pdo->prepare("
        SELECT jobs.*, users.name AS employer_name, users.location AS employer_location,
               cp.description AS company_description, cp.website AS company_website,
               cp.industry AS company_industry, cp.location AS company_location,
               (SELECT COUNT(*) FROM applications WHERE job_id = jobs.id) AS app_count
        FROM jobs
        JOIN users ON jobs.employer_id = users.id
        LEFT JOIN company_profiles cp ON cp.employer_id = jobs.employer_id
        WHERE jobs.id = ? AND jobs.status = 'active' AND jobs.is_deleted = 0
    ");
    $stmt->execute([$jobId]);
}
$job = $stmt->fetch();

if (!$job) {
    header('Location: ' . BASE_URL . '/pages/jobs.php');
    exit;
}

$today = date('Y-m-d');
$job['is_expired'] = $job['status'] === 'active' && !empty($job['deadline']) && $job['deadline'] < $today;
$job['deadline_display'] = $job['deadline'] ? date('M d, Y', strtotime($job['deadline'])) : 'No deadline';

// Check if user already applied
$alreadyApplied = false;
$alreadySaved   = false;
if (isLoggedIn() && getRole() === 'seeker') {
    $checkApply = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?");
    $checkApply->execute([$jobId, getUserId()]);
    $alreadyApplied = (bool)$checkApply->fetch();

    $checkSave = $pdo->prepare("SELECT id FROM saved_jobs WHERE job_id = ? AND seeker_id = ?");
    $checkSave->execute([$jobId, getUserId()]);
    $alreadySaved = (bool)$checkSave->fetch();
}

$typeLabels = [
    'full-time'  => 'Full Time',
    'part-time'  => 'Part Time',
    'remote'     => 'Remote',
    'contract'   => 'Contract',
    'internship' => 'Internship',
];

// Resolve company logo from filesystem
$companyLogoUrl = null;
$_logoRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
foreach (['jpg','jpeg','png','gif','webp'] as $_ext) {
    $_abs = $_logoRoot . '/uploads/logos/' . $job['employer_id'] . '.' . $_ext;
    if (file_exists($_abs)) {
        $companyLogoUrl = BASE_URL . '/uploads/logos/' . $job['employer_id'] . '.' . $_ext . '?v=' . @filemtime($_abs);
        break;
    }
}

$pageTitle = $job['title'];
$pageCss = 'job-detail';
require_once '../includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb container">
    <a href="<?= BASE_URL ?>/index.php">Home</a>
    <span class="breadcrumb-sep">/</span>
    <a href="<?= BASE_URL ?>/pages/jobs.php">Jobs</a>
    <span class="breadcrumb-sep">/</span>
    <span><?= htmlspecialchars($job['title']) ?></span>
</div>

<!-- Job Detail Layout: main content + sidebar -->
<div class="job-detail-layout">

    <!-- ---- LEFT: Job Details ---- -->
    <div>
        <div class="job-detail-card">
            <!-- Title & Badge -->
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1rem;">
                <div>
                    <h1 class="job-detail-title"><?= htmlspecialchars($job['title']) ?></h1>
                    <p style="color:var(--text-muted); margin:0.5rem 0 0 0;">
                        <?= htmlspecialchars($job['company']) ?> &nbsp;·&nbsp;
                        <?= htmlspecialchars($job['location']) ?>
                    </p>
                    <?php if ($job['is_expired'] || $job['status'] === 'closed'): ?>
                        <span class="status-badge status-closed" style="margin-top:0.75rem; display:inline-block;">Closed</span>
                    <?php endif; ?>
                </div>
                <span class="job-badge badge-<?= $job['type'] ?>">
                    <?= $typeLabels[$job['type']] ?? $job['type'] ?>
                </span>
            </div>


            <!-- Description -->
            <div class="job-detail-section">
                <h3>Job Description</h3>
                <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>
            </div>

            <!-- Requirements -->
            <?php if ($job['requirements']): ?>
            <div class="job-detail-section">
                <h3>Requirements</h3>
                <p><?= nl2br(htmlspecialchars($job['requirements'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- ---- APPLY FORM (only for logged-in seekers) ---- -->
        <?php if (isLoggedIn() && getRole() === 'seeker'): ?>
            <div class="apply-section">
                <h3>Apply for this Job</h3>

                <?php if ($alreadyApplied): ?>
                    <div class="flash flash-success" style="border-radius:8px;">
                        You have already applied for this job.
                    </div>
                <?php else: ?>
                    <!-- Apply form — submitted via AJAX (see js/apply-job.js) -->
                    <form id="applyForm" enctype="multipart/form-data" style="min-height:0;">
                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

                        <div class="form-group" style="min-height:0;">
                            <label class="form-label" for="cover_letter">Cover Letter <span style="color:var(--text-muted); font-weight:400;">(optional, max 250 words)</span></label>
                            <textarea id="cover_letter" name="cover_letter"
                                      class="form-control"
                                      rows="5"
                                      style="resize:none;"
                                      placeholder="Tell the employer why you're a great fit..."></textarea>
                            <div id="wordCountWrap" style="display:flex;justify-content:space-between;align-items:center;margin-top:0.3rem;">
                                <span id="wordCountMsg" style="font-size:0.78rem;color:var(--text-muted);"></span>
                                <span id="wordCountNum" style="font-size:0.78rem;color:var(--text-muted);">0 / 250 words</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="resume">Resume / CV <span style="color:var(--text-muted); font-weight:400;">(PDF only, max 5MB)</span></label>
                            <input type="file"
                                   id="resume"
                                   name="resume"
                                   accept=".pdf"
                                   class="form-control"
                                   style="padding: 0.5rem 1rem; cursor:pointer;">
                            <span class="form-error" id="resumeError" style="display:none;">Please upload a valid PDF file under 5MB.</span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            Submit Application
                        </button>
                    </form>
                <?php endif; ?>
            </div>

        <?php elseif (!isLoggedIn()): ?>
            <div class="apply-section text-center">
                <p class="text-muted mb-2">You need to be logged in to apply for this job.</p>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-lg">Login to Apply</a>
                &nbsp;
                <a href="<?= BASE_URL ?>/signup.php?role=seeker" class="btn btn-outline btn-lg">Create Account</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- ---- RIGHT: Sidebar ---- -->
    <div class="job-sidebar">

        <!-- Job Info Card -->
        <div class="sidebar-card">
            <h4 style="font-size:0.95rem; font-weight:600; margin-bottom:1rem; color:var(--primary);">Job Overview</h4>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Job Type</span>
                <span class="sidebar-info-value"><?= $typeLabels[$job['type']] ?? $job['type'] ?></span>
            </div>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Location</span>
                <span class="sidebar-info-value"><?= htmlspecialchars($job['location']) ?></span>
            </div>
            <?php if ($job['salary']): ?>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Salary</span>
                <span class="sidebar-info-value"><?= htmlspecialchars($job['salary']) ?></span>
            </div>
            <?php endif; ?>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Posted</span>
                <span class="sidebar-info-value"><?= date('M d, Y', strtotime($job['created_at'])) ?></span>
            </div>
            <?php if ($job['deadline']): ?>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Deadline</span>
                <span class="sidebar-info-value"><?= $job['deadline_display'] ?></span>
            </div>
            <?php endif; ?>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Applicants</span>
                <span class="sidebar-info-value">
                    <span class="applicant-count"><?= (int)$job['app_count'] ?> applicant<?= $job['app_count'] == 1 ? '' : 's' ?></span>
                </span>
            </div>
        </div>

        <!-- Company Card -->
        <div class="sidebar-card">
            <h4 style="font-size:0.95rem; font-weight:600; margin-bottom:1rem; color:var(--primary);">About the Company</h4>

            <!-- Logo + Name -->
            <div style="display:flex; align-items:center; gap:0.85rem; margin-bottom:1rem;">
                <?php if ($companyLogoUrl): ?>
                    <img src="<?= htmlspecialchars($companyLogoUrl) ?>" alt="<?= htmlspecialchars($job['company']) ?> logo"
                         style="width:52px; height:52px; border-radius:10px; object-fit:cover; border:1px solid rgba(0,0,0,0.08); flex-shrink:0;">
                <?php else: ?>
                    <div style="width:52px; height:52px; border-radius:10px; background:rgba(0,180,216,0.12); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#00b4d8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                <?php endif; ?>
                <div style="min-width:0;">
                    <div style="font-weight:700; font-size:0.95rem; color:#1e293b; line-height:1.3;">
                        <?= htmlspecialchars($job['company']) ?>
                    </div>
                    <?php if (!empty($job['company_industry'])): ?>
                        <div style="font-size:0.75rem; color:#64748b; margin-top:0.15rem;">
                            <?= htmlspecialchars($job['company_industry']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <?php if (!empty($job['company_description'])): ?>
                <p style="font-size:0.82rem; color:#475569; line-height:1.65; margin-bottom:0.85rem;">
                    <?= nl2br(htmlspecialchars($job['company_description'])) ?>
                </p>
            <?php endif; ?>

            <!-- Location -->
            <?php $displayLocation = $job['company_location'] ?: $job['employer_location']; ?>
            <?php if ($displayLocation): ?>
                <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; color:#64748b; margin-bottom:0.5rem;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <?= htmlspecialchars($displayLocation) ?>
                </div>
            <?php endif; ?>

            <!-- Website -->
            <?php if (!empty($job['company_website'])): ?>
                <?php
                    $website = $job['company_website'];
                    $displayUrl = preg_replace('#^https?://#', '', rtrim($website, '/'));
                    // Ensure it has a scheme for the href
                    $hrefUrl = preg_match('#^https?://#', $website) ? $website : 'https://' . $website;
                ?>
                <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.8rem; margin-bottom:0.5rem;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#00b4d8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <a href="<?= htmlspecialchars($hrefUrl) ?>" target="_blank" rel="noopener noreferrer"
                       style="color:#00b4d8; text-decoration:none; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                       onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                        <?= htmlspecialchars($displayUrl) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Save Job Star Toggle -->
        <?php if (isLoggedIn() && getRole() === 'seeker'): ?>
        <div class="sidebar-card" style="display:flex;align-items:center;gap:0.75rem;">
            <button class="star-btn <?= $alreadySaved ? 'starred' : '' ?>"
                    id="detailStarBtn"
                    data-job-id="<?= $job['id'] ?>"
                    title="<?= $alreadySaved ? 'Unsave job' : 'Save job' ?>">
                <svg class="star-empty" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                <svg class="star-filled" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            </button>
            <span style="font-size:0.85rem;color:var(--text-muted);" id="detailStarLabel">
                <?= $alreadySaved ? 'Job Saved' : 'Save this Job' ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Back Button -->
        <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-outline btn-block" style="text-align:center;">
            ← Back to Jobs
        </a>
    </div>

</div>

<script>
// ---- Star toggle on job detail page ----
(function(){
    const btn = document.getElementById('detailStarBtn');
    if (!btn) return;
    btn.addEventListener('click', function() {
        const jobId   = btn.dataset.jobId;
        const starred = btn.classList.contains('starred');
        const label   = document.getElementById('detailStarLabel');

        if (starred) {
            fetch(BASE_URL + '/api/unsave-job.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'job_id=' + encodeURIComponent(jobId)
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    btn.classList.remove('starred');
                    btn.title = 'Save job';
                    if (label) label.textContent = 'Save this Job';
                }
            }).catch(() => {});
        } else {
            fetch(BASE_URL + '/api/save-job.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'job_id=' + encodeURIComponent(jobId)
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    btn.classList.add('starred');
                    btn.title = 'Unsave job';
                    if (label) label.textContent = 'Job Saved';
                }
            }).catch(() => {});
        }
    });
})();
</script>
<?php require_once '../includes/footer.php'; ?>
