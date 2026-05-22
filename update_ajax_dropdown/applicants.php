<?php
// ============================================================
// pages/applicants.php - Employer: View Applicants (paginated)
// Secure download + AJAX status update with email notification
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer');

$pdo    = getPDO();
$userId = getUserId();

$filterJobId = (int)($_GET['job_id'] ?? 0);
$page        = max(1, (int)($_GET['page'] ?? 1));
$perPage     = 10;
$offset      = ($page - 1) * $perPage;

// Get all employer's jobs (for filter dropdown)
$jobsStmt = $pdo->prepare("SELECT id, title FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
$jobsStmt->execute([$userId]);
$myJobs = $jobsStmt->fetchAll();

// Verify job ownership when filtering
if ($filterJobId) {
    $verifyStmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND employer_id = ?");
    $verifyStmt->execute([$filterJobId, $userId]);
    if (!$verifyStmt->fetch()) $filterJobId = 0;
}

// Base WHERE clause
$where  = $filterJobId ? "WHERE jobs.employer_id = ? AND applications.job_id = ?" : "WHERE jobs.employer_id = ?";
$params = $filterJobId ? [$userId, $filterJobId] : [$userId];

// Total count for pagination
$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM applications
    JOIN jobs ON applications.job_id = jobs.id
    $where
");
$countStmt->execute($params);
$totalCount = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalCount / $perPage));

// Paginated applicants (with seeker profile)
$stmt = $pdo->prepare("
    SELECT applications.*, users.name AS applicant_name, users.email AS applicant_email,
           users.location AS applicant_location, jobs.title AS job_title,
           jp.skills AS applicant_skills, jp.education AS applicant_education,
           jp.experience AS applicant_experience
    FROM applications
    JOIN users ON applications.seeker_id = users.id
    JOIN jobs ON applications.job_id = jobs.id
    LEFT JOIN jobseeker_profiles jp ON jp.user_id = applications.seeker_id
    $where
    ORDER BY applications.applied_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$applicants = $stmt->fetchAll();

$pageTitle = 'Applicants';
$pageCss   = 'applicants';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Applicants</h1>
        <p>Review candidates who applied for your jobs</p>
    </div>
</div>

<div class="container section">

    <!-- Filter by job -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <form method="GET" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
            <label style="font-weight:600; font-size:.9rem; white-space:nowrap;">Filter by Job:</label>
            <div style="position:relative; flex:1; min-width:180px; max-width:320px;">
                <select name="job_id" class="form-control" style="appearance:none; -webkit-appearance:none; padding-right:2rem; cursor:pointer;" onchange="this.form.submit()">
                    <option value="">All Jobs</option>
                    <?php foreach ($myJobs as $j): ?>
                        <option value="<?= $j['id'] ?>" <?= $filterJobId == $j['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($j['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <svg style="position:absolute;right:0.7rem;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--text-muted);" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <span class="text-muted text-sm" style="white-space:nowrap;"><?= $totalCount ?> applicant(s)</span>
        </form>
    </div>

    <?php if (empty($applicants)): ?>
        <div class="empty-state">
            <h3>No applicants yet</h3>
            <p>Applicants will appear here once people apply for your jobs.</p>
        </div>

    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Job</th>
                        <th>Cover Letter</th>
                        <th>Resume</th>
                        <th>Applied</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $app): ?>
                        <tr id="app-row-<?= $app['id'] ?>">
                            <td>
                                <strong><?= htmlspecialchars($app['applicant_name']) ?></strong>
                                <div class="text-muted text-xs"><?= htmlspecialchars($app['applicant_email']) ?></div>
                                <?php if ($app['applicant_location']): ?>
                                    <div class="text-muted text-xs"><?= htmlspecialchars($app['applicant_location']) ?></div>
                                <?php endif; ?>
                                <div style="margin-top:0.4rem;">
                                    <a href="<?= BASE_URL ?>/pages/applicant-profile.php?app_id=<?= $app['id'] ?>"
                                       class="btn btn-outline btn-sm"
                                       style="font-size:0.72rem; padding:0.2rem 0.6rem;">
                                        View Profile
                                    </a>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($app['job_title']) ?></td>
                            <td>
                                <?php if ($app['cover_letter']): ?>
                                    <span title="<?= htmlspecialchars($app['cover_letter']) ?>" style="cursor:help; font-size:0.82rem;">
                                        <?= htmlspecialchars(mb_substr($app['cover_letter'], 0, 50)) ?>…
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($app['resume_path'])): ?>
                                    <a href="<?= BASE_URL ?>/api/download-resume.php?app_id=<?= $app['id'] ?>"
                                       class="btn btn-outline btn-sm"
                                       style="display:inline-flex;align-items:center;gap:0.3rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        Download CV
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted text-sm">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $app['status'] ?>" id="status-badge-<?= $app['id'] ?>">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:0.4rem; align-items:center;">
                                    <select class="form-control status-select" data-app-id="<?= $app['id'] ?>"
                                            style="font-size:0.8rem; padding:0.3rem 0.5rem; width:auto;">
                                        <?php foreach (['applied','pending','reviewed','shortlisted','interview','accepted','rejected'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $app['status'] === $s ? 'selected' : '' ?>>
                                                <?= ucfirst($s) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-primary btn-sm save-status-btn" data-app-id="<?= $app['id'] ?>">Save</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="display:flex; justify-content:center; gap:0.5rem; margin-top:1.5rem; flex-wrap:wrap;">
            <?php if ($page > 1): ?>
                <a href="?job_id=<?= $filterJobId ?>&page=<?= $page - 1 ?>" class="btn btn-outline btn-sm">← Prev</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="?job_id=<?= $filterJobId ?>&page=<?= $p ?>"
                   class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?job_id=<?= $filterJobId ?>&page=<?= $page + 1 ?>" class="btn btn-outline btn-sm">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<script>
(function () {
    document.querySelectorAll('.save-status-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const appId  = this.dataset.appId;
            const select = document.querySelector('.status-select[data-app-id="' + appId + '"]');
            const status = select.value;
            const self   = this;

            self.disabled    = true;
            self.textContent = '…';

            const fd = new FormData();
            fd.append('application_id', appId);
            fd.append('status', status);

            fetch(BASE_URL + '/api/update-application-status.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.getElementById('status-badge-' + appId);
                        if (badge) {
                            badge.className = 'status-badge status-' + status;
                            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                        }
                        self.textContent = 'Saved ✓';
                        self.style.background = '#10b981';
                        setTimeout(() => { self.textContent = 'Save'; self.style.background = ''; self.disabled = false; }, 2000);
                    } else {
                        alert(data.error || 'Failed to update status.');
                        self.disabled = false; self.textContent = 'Save';
                    }
                })
                .catch(() => { alert('Network error.'); self.disabled = false; self.textContent = 'Save'; });
        });
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>
