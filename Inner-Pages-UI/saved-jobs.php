
// pages/saved-jobs.php - Job Seeker: Saved/Bookmarked Jobs

<?php
// Page title and CSS file to load
$pageTitle = 'Saved Jobs';
$pageCss = 'saved-jobs';

// Job type labels to display readable names on cards
$typeLabels = ['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract','internship'=>'Internship'];

// Load the header (navigation, HTML head, etc.)
require_once '../includes/header.php';
?>

<!-- Top header section of the page -->
<div class="page-header">
    <div class="container">
        <h1>Saved Jobs</h1>
        <p>Jobs you bookmarked for later</p>
    </div>
</div>

<div class="container section">

    <!-- Show empty state if no saved jobs exist -->
    <?php if (empty($savedJobs)): ?>
        <div class="empty-state">
            <h3>No saved jobs yet</h3>
            <p>Click the "Save" button on any job listing to bookmark it here.</p>
            <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-primary">Browse Jobs</a>
        </div>

    <?php else: ?>
        <!-- Grid of saved job cards -->
        <div class="grid-3">
            <?php foreach ($savedJobs as $job): ?>
                <div class="card job-card">
                    <div class="job-card-header">
                        <div>
                            <!-- Job title links to the job detail page -->
                            <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="job-title">
                                <?= htmlspecialchars($job['title']) ?>
                            </a>
                            <div class="job-company"><?= htmlspecialchars($job['company']) ?></div>
                        </div>

                        <!-- Job type badge e.g. Full Time, Remote -->
                        <span class="job-badge badge-<?= $job['type'] ?>">
                            <?= $typeLabels[$job['type']] ?? $job['type'] ?>
                        </span>
                    </div>

                    <!-- Job meta: location, salary, date saved -->
                    <div class="job-meta">
                        <span>📍 <?= htmlspecialchars($job['location']) ?></span>
                        <?php if ($job['salary']): ?>
                            <span>💰 <?= htmlspecialchars($job['salary']) ?></span>
                        <?php endif; ?>
                        <span>Saved <?= date('M d', strtotime($job['saved_at'])) ?></span>
                    </div>

                    <!-- Action buttons: view job or remove from saved -->
                    <div class="job-actions">
                        <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">View & Apply</a>
                        <a href="<?= BASE_URL ?>/api/unsave-job.php?job_id=<?= $job['id'] ?>"
                           onclick="return confirm('Remove from saved jobs?')"
                           class="btn btn-danger btn-sm">Remove</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Load footer (closing HTML tags, scripts) -->
<?php require_once '../includes/footer.php'; ?>