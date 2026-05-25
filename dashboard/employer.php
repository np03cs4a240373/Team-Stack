<?php
// ============================================================
// dashboard/employer.php - Employer Dashboard
// Shows: jobs posted, total applicants, recent activity
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer');

$pdo = getPDO();
autoExpireJobs($pdo);
$userId = getUserId();

// Stats
$totalJobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND is_deleted = 0");
$totalJobs->execute([$userId]);
$totalJobs = $totalJobs->fetchColumn();

$activeJobs = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id = ? AND is_deleted = 0 AND status = 'active' AND (deadline IS NULL OR deadline >= CURDATE())");
$activeJobs->execute([$userId]);
$activeJobs = $activeJobs->fetchColumn();

$totalApplicants = $pdo->prepare("
    SELECT COUNT(*) FROM applications
    JOIN jobs ON applications.job_id = jobs.id
    WHERE jobs.employer_id = ? AND jobs.is_deleted = 0
");
$totalApplicants->execute([$userId]);
$totalApplicants = $totalApplicants->fetchColumn();

// Recent jobs with applicant count
$stmt = $pdo->prepare("
    SELECT jobs.*,
           COUNT(applications.id) AS applicant_count
    FROM jobs
    LEFT JOIN applications ON jobs.id = applications.job_id
    WHERE jobs.employer_id = ? AND jobs.is_deleted = 0
    GROUP BY jobs.id
    ORDER BY jobs.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$recentJobs = $stmt->fetchAll();

$pageTitle = 'Employer Dashboard';
$pageCss = 'employer-dashboard';
require_once '../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h1>Employer Dashboard</h1>
        <p>Manage your job postings and review applicants.</p>
    </div>
</div>

<div class="dashboard-body">

    <!-- Stats -->
    <div class="grid-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalJobs ?></div>
                <div class="stat-label">Total Jobs Posted</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $activeJobs ?></div>
                <div class="stat-label">Active Jobs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalApplicants ?></div>
                <div class="stat-label">Total Applicants</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-3">
        <h3 style="margin-bottom:1rem; font-size:1rem; font-weight:600;">Quick Actions</h3>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href="<?= BASE_URL ?>/pages/post-job.php" class="btn btn-outline">Post a Job</a>
            <a href="<?= BASE_URL ?>/pages/applicants.php" class="btn btn-outline">View Applicants</a>
            <a href="<?= BASE_URL ?>/pages/analytics.php" class="btn btn-outline">Analytics</a>
            <a href="<?= BASE_URL ?>/pages/company-profile.php" class="btn btn-outline">Edit Profile</a>
        </div>
    </div>

    <!-- Recent Job Postings -->
    <div class="card">
        <div class="d-flex justify-between align-center mb-2">
            <h3 style="font-size:1rem; font-weight:600;">Your Job Postings</h3>
            <a href="<?= BASE_URL ?>/pages/post-job.php" class="btn btn-primary btn-sm">+ Post New</a>
        </div>

        <?php if (empty($recentJobs)): ?>
            <div class="empty-state" style="padding:2rem;">
                <h3>No jobs posted yet</h3>
                <p>Start attracting candidates by posting your first job.</p>
                <a href="<?= BASE_URL ?>/pages/post-job.php" class="btn btn-outline">Post a Job</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Type</th>
                            <th>Applicants</th>
                            <th>Status</th>
                            <th>Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentJobs as $job): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                                <td><?= ucfirst(str_replace('-', ' ', $job['type'])) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/applicants.php?job_id=<?= $job['id'] ?>" class="text-primary">
                                        <?= $job['applicant_count'] ?> applicants
                                    </a>
                                </td>
                                <td>
                                    <?php
                                        $isExpired = $job['status'] === 'active' && !empty($job['deadline']) && $job['deadline'] < date('Y-m-d');
                                        $displayStatus = $isExpired ? 'closed' : $job['status'];
                                    ?>
                                    <span class="status-badge status-<?= $displayStatus ?>">
                                        <?= ucfirst($displayStatus) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                                <td>
                                    <div style="display:flex;gap:0.35rem;align-items:center;flex-wrap:nowrap;">
                                        <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="btn btn-outline btn-sm">View</a>
                                        <a href="<?= BASE_URL ?>/pages/edit-job.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <?php if ($job['status'] === 'active' && !$isExpired): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/api/toggle-job-status.php" style="display:inline;">
                                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                                <input type="hidden" name="redirect" value="<?= BASE_URL ?>/dashboard/employer.php">
                                                <button type="submit" class="btn btn-sm btn-ghost" style="width:68px; border-color:#64748b;">
                                                    Close
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-ghost" disabled
                                                style="width:68px; color:#94a3b8; border-color:#e2e8f0; cursor:default; opacity:1;">
                                                Closed
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-danger btn-sm btn-employer-delete"
                                            data-url="<?= BASE_URL ?>/api/delete.php?type=job&id=<?= $job['id'] ?>"
                                            data-name="<?= htmlspecialchars($job['title']) ?>">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<style>
.delete-modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;display:flex;align-items:center;justify-content:center;}
.delete-modal{background:#fff;border-radius:16px;padding:2rem;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.delete-modal h3{font-size:1.1rem;font-weight:700;margin-bottom:.5rem;}
.delete-modal p{color:#64748b;font-size:.88rem;margin-bottom:1.5rem;}
.delete-modal-actions{display:flex;gap:.75rem;justify-content:flex-end;}
.btn-cancel-modal{background:#f1f5f9;color:#64748b;border:1.5px solid #e2e8f0;padding:.5rem 1.2rem;border-radius:8px;font-size:.88rem;font-weight:600;cursor:pointer;}
.btn-confirm-delete{background:#e05252;color:#fff;border:none;padding:.5rem 1.2rem;border-radius:8px;font-size:.88rem;font-weight:600;cursor:pointer;}
</style>
<script>
(function () {
    document.querySelectorAll('.btn-employer-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            const url  = this.dataset.url;
            const name = this.dataset.name;
            const bd   = document.createElement('div');
            bd.className = 'delete-modal-backdrop';
            bd.innerHTML = `<div class="delete-modal"><h3>Delete Job</h3><p>Delete <strong>${name.replace(/</g,'&lt;')}</strong>? All applications will also be removed.</p><div class="delete-modal-actions"><button class="btn-cancel-modal">Cancel</button><button class="btn-confirm-delete">Delete</button></div></div>`;
            document.body.appendChild(bd);
            bd.querySelector('.btn-cancel-modal').onclick = () => bd.remove();
            bd.querySelector('.btn-confirm-delete').onclick = function () {
                this.textContent = 'Deleting…'; this.disabled = true;
                fetch(url).then(() => location.reload()).catch(() => location.href = url);
            };
            bd.onclick = e => { if (e.target === bd) bd.remove(); };
        });
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>
