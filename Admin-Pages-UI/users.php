<?php
// admin/users.php - Admin: Manage All Users
// Page title and CSS file 
$pageTitle = 'Manage Users';
$pageCss = 'admin-pages';

// Get selected filter value from URL 
$filterRole = $_GET['role'] ?? '';

// Load the header file
require_once '../includes/header.php';
?>

<!--Page heading -->
<div class="page-header">
    <div class="container">
        <h1>Manage Users</h1>
        <p>View and delete user accounts</p>
    </div>
</div>

<div class="container section">

    <!-- Filter buttons to show user roles-->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:600; font-size:.9rem;">Filter:</span>

            <!-- Active button gets filled style if currently selected -->
            <a href="<?= BASE_URL ?>/admin/users.php" class="btn <?= !$filterRole ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
            <a href="<?= BASE_URL ?>/admin/users.php?role=seeker" class="btn <?= $filterRole === 'seeker'   ? 'btn-primary' : 'btn-outline' ?> btn-sm">Job Seekers</a>
            <a href="<?= BASE_URL ?>/admin/users.php?role=employer" class="btn <?= $filterRole === 'employer' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Employers</a>
            <a href="<?= BASE_URL ?>/admin/users.php?role=admin" class="btn <?= $filterRole === 'admin'    ? 'btn-primary' : 'btn-outline' ?> btn-sm">Admins</a>

            <!-- Shows total count of users -->
            <span class="text-muted text-sm" style="margin-left:auto;"><?= count($users) ?> user(s)</span>
        </div>
    </div>

    <!-- Users table -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Location</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                <!-- Show message if no users exist -->
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:var(--text-muted); padding:2rem;">No users found.</td>
                    </tr>
                <?php endif; ?>

                <!-- Loop through each user -->
                <?php foreach ($users as $u): ?>
                    <tr>
                        <!-- User ID -->
                        <td class="text-muted"><?= $u['id'] ?></td>
                        <!-- User name -->
                        <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                        <!-- User email -->
                        <td><?= htmlspecialchars($u['email']) ?></td>

                        <!--User Role badge: blue=admin, green=employer, yellow=seeker -->
                        <td>
                            <span class="status-badge <?= $u['role'] === 'admin' ? 'status-reviewed' : ($u['role'] === 'employer' ? 'status-accepted' : 'status-pending') ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>

                        <!--User location (show dash if empty) -->
                        <td><?= htmlspecialchars($u['location'] ?? '—') ?></td>

                        <!-- Joined date in format e.g. Jan 05, 2026 -->
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>

                        <!-- Delete button for all except the currently logged-in admin -->
                        <td>
                            <?php if ($u['id'] !== getUserId()): ?>
                                <a href="<?= BASE_URL ?>/api/delete.php?type=user&id=<?= $u['id'] ?>"
                                    onclick="return confirm('Delete user <?= htmlspecialchars(addslashes($u['name'])) ?>? This will also delete their jobs and applications.')"
                                    class="btn btn-danger btn-sm">Delete</a>
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

<!-- Include footer file -->
<?php require_once '../includes/footer.php'; ?>