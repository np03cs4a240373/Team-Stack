// pages/applicants.php - Employer: View Applicants
// Shows all applications for employer's jobs
<?php
// Page title and CSS file to load
$pageTitle = 'Applicants';
$pageCss = 'applicants';

// Read job filter value from URL (e.g. ?job_id=3)
$filterJobId = (int)($_GET['job_id'] ?? 0);

// Load the header (navigation, HTML head, etc.)
require_once '../includes/header.php';
?>

<!-- Top header section of the page -->
<div class="page-header">
    <div class="container">
        <h1>Applicants</h1>
        <p>Review candidates who applied for your jobs</p>
    </div>
</div>

<div class="container section">

    <!-- Dropdown filter to show applicants for a specific job -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <form method="GET" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
            <label style="font-weight:600; font-size:.9rem;">Filter by Job:</label>

            <!-- Dropdown auto-submits the form when selection changes -->
            <select name="job_id" class="form-control" style="max-width:300px;" onchange="this.form.submit()">
                <option value="">All Jobs</option>
                <?php foreach ($myJobs as $j): ?>
                    <option value="<?= $j['id'] ?>" <?= $filterJobId == $j['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($j['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Shows total count of applicants being displayed -->
            <span class="text-muted text-sm"><?= count($applicants) ?> applicant(s)</span>
        </form>
    </div>

    <!-- Show empty state if no applicants exist -->
    <?php if (empty($applicants)): ?>
        <div class="empty-state">
            <h3>No applicants yet</h3>
            <p>Applicants will appear here once people apply for your jobs.</p>
        </div>

    <?php else: ?>
        <!-- Applicants table -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Job</th>
                        <th>Cover Letter</th>
                        <th>Applied</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $app): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($app['applicant_name']) ?></strong>
                                <!-- Show location below name if available -->
                                <?php if ($app['applicant_location']): ?>
                                    <div class="text-muted text-sm"><?= htmlspecialchars($app['applicant_location']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($app['applicant_email']) ?></td>
                            <td><?= htmlspecialchars($app['job_title']) ?></td>

                            <!-- Show first 40 characters of cover letter with full text on hover -->
                            <td>
                                <?php if ($app['cover_letter']): ?>
                                    <span title="<?= htmlspecialchars($app['cover_letter']) ?>" style="cursor:help;">
                                        <?= htmlspecialchars(substr($app['cover_letter'], 0, 40)) ?>...
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- Format date to readable format e.g. Jan 01, 2025 -->
                            <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>

                            <!-- Current application status badge -->
                            <td>
                                <span class="status-badge status-<?= $app['status'] ?>">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>

                            <!-- Form to update application status (POST to same page) -->
                            <td>
                                <form method="POST" style="display:flex; gap:0.4rem;">
                                    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                    <select name="status" class="form-control" style="font-size:0.8rem; padding:0.3rem 0.5rem;">
                                        <option value="pending"  <?= $app['status'] === 'pending'  ? 'selected' : '' ?>>Pending</option>
                                        <option value="reviewed" <?= $app['status'] === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                                        <option value="accepted" <?= $app['status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                                        <option value="rejected" <?= $app['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">Save</button>
                                </form>
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