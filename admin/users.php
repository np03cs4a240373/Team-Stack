<?php
// admin/users.php - Admin: Manage All Users
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('admin');

$pdo = getPDO();

// Filter by role
$filterRole = $_GET['role'] ?? '';
$validRoles = ['seeker', 'employer', 'admin'];

if (in_array($filterRole, $validRoles)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY id ASC");
    $stmt->execute([$filterRole]);
} else {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
}

// Handle status filter
$filterStatus = $_GET['status'] ?? '';
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users';
$pageCss = 'admin-pages';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Manage Users</h1>
        <p>View and delete user accounts</p>
    </div>
</div>

<div class="container section">

    <!-- Filter -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:600; font-size:.9rem;">Filter:</span>
            <a href="<?= BASE_URL ?>/admin/users.php" class="btn <?= !$filterRole ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
            <a href="<?= BASE_URL ?>/admin/users.php?role=seeker" class="btn <?= $filterRole === 'seeker'   ? 'btn-primary' : 'btn-outline' ?> btn-sm">Job Seekers</a>
            <a href="<?= BASE_URL ?>/admin/users.php?role=employer" class="btn <?= $filterRole === 'employer' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Employers</a>
            <a href="<?= BASE_URL ?>/admin/users.php?role=admin" class="btn <?= $filterRole === 'admin'    ? 'btn-primary' : 'btn-outline' ?> btn-sm">Admins</a>
            <span class="text-muted text-sm" style="margin-left:auto;"><?= count($users) ?> user(s)</span>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem;">No users found.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="text-muted"><?= $u['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($u['name']) ?></strong>
                            <div class="text-muted text-xs"><?= htmlspecialchars($u['email']) ?></div>
                        </td>
                        <td>
                            <span class="status-badge <?= $u['role'] === 'admin' ? 'status-reviewed' : ($u['role'] === 'employer' ? 'status-accepted' : 'status-pending') ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td>
                            <?php $isActive = isset($u['is_active']) ? (int)$u['is_active'] : 1; ?>
                            <span class="status-badge user-status-badge-<?= $u['id'] ?>" style="<?= $isActive ? 'background:#dcfce7;color:#166534;' : 'background:#fee2e2;color:#991b1b;' ?>">
                                <?= $isActive ? 'Active' : 'Suspended' ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                            <?php if ($u['id'] !== getUserId()): ?>
                                <button class="btn btn-sm btn-toggle-status"
                                    style="<?= $isActive ? 'background:#f59e0b;color:#fff;border-color:#f59e0b;' : 'background:#10b981;color:#fff;border-color:#10b981;' ?>"
                                    data-user-id="<?= $u['id'] ?>"
                                    data-is-active="<?= $isActive ?>">
                                    <?= $isActive ? 'Suspend' : 'Activate' ?>
                                </button>
                                <button class="btn btn-danger btn-sm btn-admin-delete"
                                    data-url="<?= BASE_URL ?>/api/delete.php?type=user&id=<?= $u['id'] ?>"
                                    data-name="<?= htmlspecialchars($u['name']) ?>"
                                    data-type="user">Delete</button>
                            <?php else: ?>
                                <span class="text-muted text-sm">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<style>
.btn-toggle-status {
    min-width: 82px;
    text-align: center;
}
.btn-toggle-status, .btn-admin-delete {
    transition: transform 0.18s ease, box-shadow 0.18s ease !important;
}
.btn-toggle-status:hover, .btn-admin-delete:hover {
    transform: translateY(-2px) scale(1.04);
    box-shadow: 0 4px 12px rgba(0,0,0,0.18);
}
</style>
<script>
(function () {
    function showDeleteModal(url, name) {
        const backdrop = document.createElement('div');
        backdrop.className = 'delete-modal-backdrop';
        backdrop.innerHTML = `
            <div class="delete-modal">
                <h3>Delete User</h3>
                <p>Are you sure you want to delete <strong>${name.replace(/</g,'&lt;')}</strong>? This will also remove all their jobs and applications.</p>
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
            fetch(url).then(r => {
                if (r.redirected || r.ok) {
                    const row = document.querySelector(`.btn-admin-delete[data-url="${CSS.escape ? CSS.escape(url) : url}"]`)?.closest('tr');
                    if (row) row.remove();
                    backdrop.remove();
                }
            }).catch(() => { backdrop.remove(); location.href = url; });
        };
        backdrop.addEventListener('click', e => { if (e.target === backdrop) backdrop.remove(); });
    }

    document.querySelectorAll('.btn-admin-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            showDeleteModal(this.dataset.url, this.dataset.name);
        });
    });

    // Suspend / Activate toggle
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
        btn.addEventListener('click', function () {
            const userId   = this.dataset.userId;
            const isActive = parseInt(this.dataset.isActive);
            const action   = isActive ? 'Suspend' : 'Activate';
            if (!confirm(action + ' this user?')) return;

            const self = this;
            const fd   = new FormData();
            fd.append('user_id', userId);

            fetch('<?= BASE_URL ?>/api/toggle-user-status.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const newActive = data.is_active;
                        self.dataset.isActive = newActive;
                        self.textContent = data.label;
                        self.style.background    = newActive ? '#f59e0b' : '#10b981';
                        self.style.borderColor   = newActive ? '#f59e0b' : '#10b981';

                        const badge = document.querySelector('.user-status-badge-' + userId);
                        if (badge) {
                            badge.textContent = newActive ? 'Active' : 'Suspended';
                            badge.style.background = newActive ? '#dcfce7' : '#fee2e2';
                            badge.style.color      = newActive ? '#166534' : '#991b1b';
                        }
                    } else {
                        alert(data.error || 'Failed to update status.');
                    }
                })
                .catch(() => alert('Network error.'));
        });
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>