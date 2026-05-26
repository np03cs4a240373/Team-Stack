<?php
// admin/jobs.php - Admin: Manage All Jobs
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('admin');

$pdo = getPDO();

// Filter by status
$filterStatus = $_GET['status'] ?? '';

if (in_array($filterStatus, ['active', 'closed'])) {
    $stmt = $pdo->prepare("
        SELECT jobs.*, users.name AS employer_name,
               COUNT(applications.id) AS app_count
        FROM jobs
        JOIN users ON jobs.employer_id = users.id
        LEFT JOIN applications ON jobs.id = applications.job_id
        WHERE jobs.is_deleted = 0 AND jobs.status = ?
        GROUP BY jobs.id
        ORDER BY jobs.id ASC
    ");
    $stmt->execute([$filterStatus]);
} else {
    $stmt = $pdo->query("
        SELECT jobs.*, users.name AS employer_name,
               COUNT(applications.id) AS app_count
        FROM jobs
        JOIN users ON jobs.employer_id = users.id
        LEFT JOIN applications ON jobs.id = applications.job_id
        WHERE jobs.is_deleted = 0
        GROUP BY jobs.id
        ORDER BY jobs.id ASC
    ");
}
$jobs = $stmt->fetchAll();

$typeLabels = ['full-time' => 'Full Time', 'part-time' => 'Part Time', 'remote' => 'Remote', 'contract' => 'Contract', 'internship' => 'Internship'];

$pageTitle = 'Manage Jobs';
$pageCss = 'admin-pages';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Manage Jobs</h1>
        <p>View and delete all job listings</p>
    </div>
</div>

<div class="container section">

    <!-- Filter -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:600; font-size:.9rem;">Filter:</span>
            <a href="<?= BASE_URL ?>/admin/jobs.php" class="btn <?= !$filterStatus ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
            <a href="<?= BASE_URL ?>/admin/jobs.php?status=active" class="btn <?= $filterStatus === 'active' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Active</a>
            <a href="<?= BASE_URL ?>/admin/jobs.php?status=closed" class="btn <?= $filterStatus === 'closed' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Closed</a>
            <span class="text-muted text-sm" style="margin-left:auto;"><?= count($jobs) ?> job(s)</span>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Company</th>
                    <th>Employer</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Apps</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="10" style="text-align:center; color:var(--text-muted); padding:2rem;">No jobs found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td class="text-muted"><?= $job['id'] ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="text-primary">
                                <strong><?= htmlspecialchars($job['title']) ?></strong>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($job['company']) ?></td>
                        <td><?= htmlspecialchars($job['employer_name']) ?></td>
                        <td>
                            <span class="job-badge badge-<?= $job['type'] ?>" style="font-size:0.72rem;">
                                <?= $typeLabels[$job['type']] ?? $job['type'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($job['location']) ?></td>
                        <td><?= $job['app_count'] ?></td>
                        <td>
                            <span class="status-badge status-<?= $job['status'] ?>">
                                <?= ucfirst($job['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                        <td>
                            <div style="display:flex;gap:0.35rem;align-items:center;flex-wrap:nowrap;">
                                <button class="btn btn-sm btn-admin-toggle-job"
                                    style="<?= $job['status'] === 'active' ? 'background:#f59e0b;color:#fff;border-color:#f59e0b;' : 'background:#10b981;color:#fff;border-color:#10b981;' ?> min-width:72px;"
                                    data-job-id="<?= $job['id'] ?>"
                                    data-status="<?= $job['status'] ?>">
                                    <?= $job['status'] === 'active' ? 'Suspend' : 'Activate' ?>
                                </button>
                                <button class="btn btn-danger btn-sm btn-admin-delete"
                                    data-url="<?= BASE_URL ?>/api/delete.php?type=job&id=<?= $job['id'] ?>"
                                    data-name="<?= htmlspecialchars($job['title']) ?>"
                                    data-type="job">Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<style>
.btn-admin-delete, .btn-admin-toggle-job {
    transition: transform 0.18s ease, box-shadow 0.18s ease !important;
}
.btn-admin-delete:hover, .btn-admin-toggle-job:hover {
    transform: translateY(-2px) scale(1.04);
    box-shadow: 0 4px 12px rgba(0,0,0,0.18);
}
</style>
<script>
(function () {
    // Suspend / Activate job toggle
    document.querySelectorAll('.btn-admin-toggle-job').forEach(btn => {
        btn.addEventListener('click', function () {
            const jobId = this.dataset.jobId;
            const status = this.dataset.status;
            const action = status === 'active' ? 'Suspend' : 'Activate';
            if (!confirm(action + ' this job?')) return;

            const self = this;
            self.disabled = true;
            self.textContent = '…';

            const fd = new FormData();
            fd.append('job_id', jobId);

            fetch('<?= BASE_URL ?>/api/admin-toggle-job.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        self.dataset.status = data.status;
                        self.textContent = data.label;
                        if (data.status === 'active') {
                            self.style.background   = '#f59e0b';
                            self.style.borderColor  = '#f59e0b';
                        } else {
                            self.style.background   = '#10b981';
                            self.style.borderColor  = '#10b981';
                        }
                        const badge = self.closest('tr').querySelector('.status-badge');
                        if (badge) {
                            badge.className = 'status-badge status-' + data.status;
                            badge.textContent = data.badgeText;
                        }
                        self.disabled = false;
                    } else {
                        alert(data.error || 'Failed to update job status.');
                        self.disabled = false;
                        self.textContent = action;
                    }
                })
                .catch(() => { alert('Network error.'); self.disabled = false; self.textContent = action; });
        });
    });

    function showDeleteModal(url, name) {
        const backdrop = document.createElement('div');
        backdrop.className = 'delete-modal-backdrop';
        backdrop.innerHTML = `
            <div class="delete-modal">
                <h3>Delete Job</h3>
                <p>Are you sure you want to delete <strong>${name.replace(/</g,'&lt;')}</strong>? All applications for this job will also be removed.</p>
                <div class="delete-modal-actions">
                    <button class="btn-cancel-modal">Cancel</button>
                    <button class="btn-confirm-delete">Delete</button>
                </div>
            </div>`;
        document.body.appendChild(backdrop);
        backdrop.querySelector('.btn-cancel-modal').onclick = () => backdrop.remove();
        backdrop.querySelector('.btn-confirm-delete').onclick = function () {
            this.textContent = 'Deleting…';
            this.disabled = true;
            fetch(url, { credentials: 'same-origin' })
                .then(() => location.reload())
                .catch(() => { location.href = url; });
        };
        backdrop.addEventListener('click', e => { if (e.target === backdrop) backdrop.remove(); });
    }

    document.querySelectorAll('.btn-admin-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            showDeleteModal(this.dataset.url, this.dataset.name);
        });
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>