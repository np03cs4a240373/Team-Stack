<?php
$pageTitle = 'My Dashboard';
$pageCss = 'job-seeker';
require_once '../includes/header.php';

// Sample data for UI preview
$userName = 'John Doe';
$totalApplications = 12;
$acceptedCount = 3;
$savedJobsCount = 7;
$recentApplications = [
    ['job_title' => 'Frontend Developer', 'company' => 'Tech Corp', 'status' => 'pending', 'applied_at' => '2024-01-15'],
    ['job_title' => 'UI Designer', 'company' => 'Creative Studio', 'status' => 'accepted', 'applied_at' => '2024-01-10'],
];
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="container">
        <h1>Welcome, <?= htmlspecialchars($userName) ?>!</h1>
        <p>Here's an overview of your job search activity.</p>
    </div>
</div>

<div class="dashboard-body">

    <!-- Stats Cards -->
    <div class="grid-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $totalApplications ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $acceptedCount ?></div>
                <div class="stat-label">Accepted</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= $savedJobsCount ?></div>
                <div class="stat-label">Saved Jobs</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-3">
        <h3 style="margin-bottom:1rem; font-size:1rem; font-weight:600;">Quick Actions</h3>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href="/pages/jobs.php" class="btn btn-primary">Find Jobs</a>
            <a href="/pages/my-applications.php" class="btn btn-outline">My Applications</a>
            <a href="/pages/saved-jobs.php" class="btn btn-outline">Saved Jobs</a>
            <a href="/pages/profile.php" class="btn btn-outline">Edit Profile</a>
        </div>
    </div>

    <!-- Recent Applications Table -->
    <div class="card">
        <div class="d-flex justify-between align-center mb-2">
            <h3 style="font-size:1rem; font-weight:600;">Recent Applications</h3>
            <a href="/pages/my-applications.php" class="btn btn-outline btn-sm">View All</a>
        </div>

        <?php if (empty($recentApplications)): ?>
            <!-- Empty state shown when there are no applications -->
            <div class="empty-state" style="padding:2rem;">
                <h3>No applications yet</h3>
                <p>Start applying for jobs to see them here.</p>
                <a href="/pages/jobs.php" class="btn btn-primary">Browse Jobs</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Applied On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentApplications as $app): ?>
                            <tr>
                                <td><?= htmlspecialchars($app['job_title']) ?></td>
                                <td><?= htmlspecialchars($app['company']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $app['status'] ?>">
                                        <?= ucfirst($app['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>