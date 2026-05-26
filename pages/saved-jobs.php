<?php
// ============================================================
// pages/saved-jobs.php - Job Seeker: Saved/Bookmarked Jobs
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('seeker');

$pdo = getPDO();

$stmt = $pdo->prepare("
    SELECT jobs.*, saved_jobs.saved_at
    FROM saved_jobs
    JOIN jobs ON saved_jobs.job_id = jobs.id
    WHERE saved_jobs.seeker_id = ?
    ORDER BY saved_jobs.saved_at DESC
");
$stmt->execute([getUserId()]);
$savedJobs = $stmt->fetchAll();

$typeLabels = ['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract','internship'=>'Internship'];

$pageTitle = 'Saved Jobs';
$pageCss = 'jobs';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Saved Jobs</h1>
        <p>Jobs you bookmarked for later</p>
    </div>
</div>

<div class="container section">

    <?php if (empty($savedJobs)): ?>
        <div class="empty-state">
            <h3>No saved jobs yet</h3>
            <p>Click the "Save" button on any job listing to bookmark it here.</p>
            <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-primary">Browse Jobs</a>
        </div>

    <?php else: ?>
        <div class="grid-3">
            <?php foreach ($savedJobs as $job): ?>
                <div class="card job-card" style="position:relative;">
                    <div class="job-card-header">
                        <div style="flex:1;min-width:0;padding-right:2rem;">
                            <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="job-title">
                                <?= htmlspecialchars($job['title']) ?>
                            </a>
                            <div class="job-company" style="display:flex;align-items:center;gap:0.3rem;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                <?= htmlspecialchars($job['company']) ?>
                            </div>
                        </div>
                        <span class="job-badge badge-<?= $job['type'] ?>">
                            <?= $typeLabels[$job['type']] ?? $job['type'] ?>
                        </span>
                    </div>
                    <div class="job-meta">
                        <span style="display:inline-flex;align-items:center;gap:0.3rem;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars($job['location']) ?>
                        </span>
                        <?php if ($job['salary']): ?>
                            <span style="display:inline-flex;align-items:center;gap:0.3rem;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                <?= htmlspecialchars($job['salary']) ?>
                            </span>
                        <?php endif; ?>
                        <span style="display:inline-flex;align-items:center;gap:0.3rem;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Saved <?= date('M d', strtotime($job['saved_at'])) ?>
                        </span>
                    </div>
                    <div class="job-actions">
                        <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">View &amp; Apply</a>
                        <button class="star-btn starred"
                                data-job-id="<?= $job['id'] ?>"
                                title="Unsave job">
                            <svg class="star-empty" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                            <svg class="star-filled" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#e0a800" stroke="#e0a800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
document.querySelectorAll('.star-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const jobId = btn.dataset.jobId;
        const card  = btn.closest('.card');
        fetch(BASE_URL + '/api/unsave-job.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'job_id=' + encodeURIComponent(jobId)
        }).then(r => r.json()).then(d => {
            if (d.success && card) {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => card.remove(), 300);
            }
        }).catch(() => {});
    });
});
</script>
<?php require_once '../includes/footer.php'; ?>
