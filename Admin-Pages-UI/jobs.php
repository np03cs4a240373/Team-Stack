<?php
// admin/jobs.php - Admin: Manage All Jobs
// Page title and CSS file 
$pageTitle = 'Manage Jobs';
$pageCss = 'admin-pages';

// Store readable labels for job types
$typeLabels = ['full-time'=>'Full Time','part-time'=>'Part Time','remote'=>'Remote','contract'=>'Contract','internship'=>'Internship'];

// Read filter value from URL
$filterStatus = $_GET['status'] ?? '';

// Load the header file 
require_once '../includes/header.php';
?>

<!-- Page heading -->
<div class="page-header">
    <div class="container">
        <h1>Manage Jobs</h1>
        <p>View and delete all job listings</p>
    </div>
</div>

<div class="container section">

    <!-- Filter buttons for job status -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:600; font-size:.9rem;">Filter:</span>

            <!--Show all jobs-->
            <a href="<?= BASE_URL ?>/admin/jobs.php" class="btn <?= !$filterStatus ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>

            <!-- Show only active jobs -->
            <a href="<?= BASE_URL ?>/admin/jobs.php?status=active" class="btn <?= $filterStatus==='active' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Active</a>

            <!-- Show only closed jobs -->
            <a href="<?= BASE_URL ?>/admin/jobs.php?status=closed" class="btn <?= $filterStatus==='closed' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Closed</a>

            <!-- Shows total count of jobs-->
            <span class="text-muted text-sm" style="margin-left:auto;"><?= count($jobs) ?> job(s)</span>
        </div>
    </div>

    <!-- Jobs table -->
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

                <!-- Show message if no jobs exist -->
                <?php if (empty($jobs)): ?>
                    <tr><td colspan="10" style="text-align:center; color:var(--text-muted); padding:2rem;">No jobs found.</td></tr>
                <?php endif; ?>

                <!-- Loop through all jobs -->
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <!-- Job ID -->
                        <td class="text-muted"><?= $job['id'] ?></td>

                        <!-- Job title with link to  job detail page -->
                        <td>
                            <a href="<?= BASE_URL ?>/pages/job-detail.php?id=<?= $job['id'] ?>" class="text-primary">
                                <strong><?= htmlspecialchars($job['title']) ?></strong>
                            </a>
                        </td>
                        <!-- Company name -->
                        <td><?= htmlspecialchars($job['company']) ?></td>
                        <!-- Employer name -->
                        <td><?= htmlspecialchars($job['employer_name']) ?></td>

                        <!-- Job type badge-->
                        <td>
                            <span class="job-badge badge-<?= $job['type'] ?>" style="font-size:0.72rem;">
                                <?= $typeLabels[$job['type']] ?? $job['type'] ?>
                            </span>
                        </td>

                        <td><?= htmlspecialchars($job['location']) ?></td>

                        <!-- Total number of applications-->
                        <td><?= $job['app_count'] ?></td>

                        <!-- Job status :active or closed -->
                        <td>
                            <span class="status-badge status-<?= $job['status'] ?>">
                                <?= ucfirst($job['status']) ?>
                            </span>
                        </td>

                        <!-- Posteed date in format e.g. Jan 05, 2026 -->
                        <td><?= date('M d, Y', strtotime($job['created_at'])) ?></td>

                        <!-- Delete job button-->
                        <td>
                            <a href="<?= BASE_URL ?>/api/delete.php?type=job&id=<?= $job['id'] ?>"
                               onclick="return confirm('Delete this job and all its applications?')"
                               class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>

</div>


<!-- Include footer file -->
<?php require_once '../includes/footer.php'; ?>