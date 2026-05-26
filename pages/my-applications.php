<?php
// ============================================================
// pages/my-applications.php - Job Seeker: View Applications
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('seeker');

$pdo = getPDO();

$stmt = $pdo->prepare("
    SELECT applications.*, jobs.title AS job_title, jobs.company,
           jobs.location, jobs.type, jobs.status AS job_status
    FROM applications
    JOIN jobs ON applications.job_id = jobs.id
    WHERE applications.seeker_id = ?
    ORDER BY applications.applied_at DESC
");
$stmt->execute([getUserId()]);
$applications = $stmt->fetchAll();

$typeLabels = ['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract','internship'=>'Internship'];

$counts = array_count_values(array_column($applications, 'status'));
$totalApplications = count($applications);

$pageTitle = 'My Applications';
$pageCss = 'my-applications';
require_once '../includes/header.php';
?>

<style>
.stat-card {
    background: var(--bg-card, #fff);
    border: 1px solid var(--border);
    border-radius: var(--radius, 12px);
    padding: 1.5rem;
    display: flex; align-items: center; gap: 1rem;
    transition: border-color 0.2s;
}
[data-theme="light"] .stat-card { background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.stat-card:hover { border-color: #00b4d8; }
.stat-icon.stat-icon-plain { background: none; color: var(--primary, #00b4d8); width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-info { flex: 1; }
.stat-value { font-size: 1.8rem; font-weight: 800; color: var(--primary, #00b4d8); line-height: 1; letter-spacing: -0.5px; }
.stat-label { font-size: 0.8rem; color: var(--text-muted, #64748b); margin-top: 0.2rem; }
</style>

<div class="page-header">
    <div class="container">
        <h1>My Applications</h1>
        <p>Track the status of all your job applications</p>
    </div>
</div>

<div class="container section">

    <!-- Overview Stats -->
    <div class="grid-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalApplications ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $counts['applied'] ?? 0 ?></div>
                <div class="stat-label">Applied</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $counts['pending'] ?? 0 ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $counts['reviewed'] ?? 0 ?></div>
                <div class="stat-label">Reviewed</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= ($counts['shortlisted'] ?? 0) + ($counts['interview'] ?? 0) ?></div>
                <div class="stat-label">Shortlisted</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $counts['accepted'] ?? 0 ?></div>
                <div class="stat-label">Accepted</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $counts['rejected'] ?? 0 ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>

    <?php if (empty($applications)): ?>
        <div class="empty-state">
            <h3>No applications yet</h3>
            <p>Start applying for jobs and track your progress here.</p>
            <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-primary">Browse Jobs</a>
        </div>

    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $app['job_id'] ?>" class="text-primary">
                                    <strong><?= htmlspecialchars($app['job_title']) ?></strong>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($app['company']) ?></td>
                            <td>
                                <span class="job-badge badge-<?= $app['type'] ?>" style="font-size:0.72rem;">
                                    <?= $typeLabels[$app['type']] ?? $app['type'] ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($app['location']) ?></td>
                            <td>
                                <span class="status-badge status-<?= $app['status'] ?>">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $app['job_id'] ?>" class="btn btn-outline btn-sm">View Job</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>


<?php require_once '../includes/footer.php'; ?>
