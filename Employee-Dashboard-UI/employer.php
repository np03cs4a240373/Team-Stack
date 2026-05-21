<!-- Dashboard Header -->
<!-- Show dashboard title and short message -->
<div class="dashboard-header">
    <div class="container">
        <h1>Employer Dashboard</h1>
        <p>Manage your job postings and review applicants.</p>
    </div>
</div>

<div class="dashboard-body">

    <!-- 3 stat cards: total jobs, active jobs, total applicants -->
    <div class="grid-3 mb-3">

        <!-- Total jobs card -->
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>

            <!-- Show total posted jobs -->
            <div class="stat-info">
                <div class="stat-value"><?= $totalJobs ?></div>
                <div class="stat-label">Total Jobs Posted</div>
            </div>
        </div>

        <!-- Active jobs card -->
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>

            <!-- Show active jobs -->
            <div class="stat-info">
                <div class="stat-value"><?= $activeJobs ?></div>
                <div class="stat-label">Active Jobs</div>
            </div>
        </div>

        <!-- Total applicants card -->
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>

            <!-- Show total applicants -->
            <div class="stat-info">
                <div class="stat-value"><?= $totalApplicants ?></div>
                <div class="stat-label">Total Applicants</div>
            </div>
        </div>
    </div>

    <!-- Quick action buttons -->
    <div class="card mb-3">

        <!-- Section title -->
        <h3 style="margin-bottom:1rem; font-size:1rem; font-weight:600;">
            Quick Actions
        </h3>

        <!-- Action buttons -->
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">

            <!-- Post job button -->
            <a href="<?= BASE_URL ?>/pages/post-job.php" class="btn btn-outline">
                Post a Job
            </a>

            <!-- View applicants button -->
            <a href="<?= BASE_URL ?>/pages/applicants.php" class="btn btn-outline">
                View Applicants
            </a>

            <!-- Analytics button -->
            <a href="<?= BASE_URL ?>/pages/analytics.php" class="btn btn-outline">
                Analytics
            </a>

            <!-- Edit profile button -->
            <a href="<?= BASE_URL ?>/pages/profile.php" class="btn btn-outline">
                Edit Profile
            </a>
        </div>
    </div>

    <!-- Recent job postings section -->
    <div class="card">

        <!-- Section heading -->
        <div class="d-flex justify-between align-center mb-2">

            <h3 style="font-size:1rem; font-weight:600;">
                Your Job Postings
            </h3>

            <!-- Post new job button -->
            <a href="<?= BASE_URL ?>/pages/post-job.php" class="btn btn-primary btn-sm">
                + Post New
            </a>
        </div>

        <!-- Show message if no jobs are posted -->
        <?php if (empty($recentJobs)): ?>

            <div class="empty-state" style="padding:2rem;">

                <!-- Empty message -->
                <h3>No jobs posted yet</h3>

                <p>Start attracting candidates by posting your first job.</p>

                <!-- Post job button -->
                <a href="<?= BASE_URL ?>/pages/post-job.php" class="btn btn-outline">
                    Post a Job
                </a>
            </div>

        <?php else: ?>

            <!-- Jobs table -->
            <div class="table-wrap">

                <table>

                    <!-- Table heading -->
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

                    <!-- Table body -->
                    <tbody>

                        <!-- Loop through all jobs -->
                        <?php foreach ($recentJobs as $job): ?>

                            <tr>

                                <!-- Job title -->
                                <td>
                                    <strong><?= htmlspecialchars($job['title']) ?></strong>
                                </td>

                                <!-- Job type -->
                                <td>
                                    <?= ucfirst(str_replace('-', ' ', $job['type'])) ?>
                                </td>

                                <!-- Applicants count -->
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/applicants.php?job_id=<?= $job['id'] ?>" class="text-primary">
                                        <?= $job['applicant_count'] ?> applicants
                                    </a>
                                </td>

                                <!-- Job status -->
                                <td>

                                    <!-- Status badge -->
                                    <span class="status-badge status-<?= $displayStatus ?>">
                                        <?= ucfirst($displayStatus) ?>
                                    </span>
                                </td>

                                <!-- Posted date -->
                                <td>
                                    <?= date('M d, Y', strtotime($job['created_at'])) ?>
                                </td>

                                <!-- Action buttons -->
                                <td>

                                    <div style="display:flex;gap:0.35rem;align-items:center;flex-wrap:nowrap;">

                                        <!-- View button -->
                                        <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="btn btn-outline btn-sm">
                                            View
                                        </a>

                                        <!-- Edit button -->
                                        <a href="<?= BASE_URL ?>/pages/edit-job.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">
                                            Edit
                                        </a>

                                        <!-- Close button -->
                                        <button class="btn btn-sm btn-ghost">
                                            Close
                                        </button>

                                        <!-- Delete button -->
                                        <button class="btn btn-danger btn-sm btn-employer-delete">
                                            Delete
                                        </button>

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