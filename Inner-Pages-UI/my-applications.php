
// pages/my-applications.php - Job Seeker: View Applications

<?php
// Page title and CSS file to load
$pageTitle = 'My Applications';
$pageCss = 'my-applications';

// Job type labels to display readable names in the table
$typeLabels = ['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract','internship'=>'Internship'];

// Load the header (navigation, HTML head, etc.)
require_once '../includes/header.php';
?>

<!-- Top header section of the page -->
<div class="page-header">
    <div class="container">
        <h1>My Applications</h1>
        <p>Track the status of all your job applications</p>
    </div>
</div>

<div class="container section">

    <!-- Show empty state if no applications exist -->
    <?php if (empty($applications)): ?>
        <div class="empty-state">
            <h3>No applications yet</h3>
            <p>Start applying for jobs and track your progress here.</p>
            <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-primary">Browse Jobs</a>
        </div>

    <?php else: ?>

        <!-- Summary stat cards: count of each application status -->
        <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
            <?php
            // Count how many applications exist per status
            $counts = array_count_values(array_column($applications, 'status'));
            $labels = ['pending'=>'Pending','reviewed'=>'Reviewed','accepted'=>'Accepted','rejected'=>'Rejected'];

            // Only show a stat card if that status has at least 1 application
            foreach ($labels as $key => $label):
                if (($counts[$key] ?? 0) > 0):
            ?>
                <div class="stat-card" style="flex:1; min-width:140px;">
                    <div class="stat-info">
                        <div class="stat-value"><?= $counts[$key] ?? 0 ?></div>
                        <div class="stat-label"><?= $label ?></div>
                    </div>
                </div>
            <?php endif; endforeach; ?>
        </div>

        <!-- Applications table -->
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
                            <!-- Job title links to the job detail page -->
                            <td>
                                <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $app['job_id'] ?>" class="text-primary">
                                    <strong><?= htmlspecialchars($app['job_title']) ?></strong>
                                </a>
                            </td>

                            <td><?= htmlspecialchars($app['company']) ?></td>

                            <!-- Job type badge e.g. Full Time, Remote -->
                            <td>
                                <span class="job-badge badge-<?= $app['type'] ?>" style="font-size:0.72rem;">
                                    <?= $typeLabels[$app['type']] ?? $app['type'] ?>
                                </span>
                            </td>

                            <td><?= htmlspecialchars($app['location']) ?></td>

                            <!-- Application status badge: pending, reviewed, accepted, rejected -->
                            <td>
                                <span class="status-badge status-<?= $app['status'] ?>">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>

                            <!-- Format date to readable format e.g. Jan 01, 2025 -->
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

<!-- Load footer (closing HTML tags, scripts) -->
<?php require_once '../includes/footer.php'; ?>