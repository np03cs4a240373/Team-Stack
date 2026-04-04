

// pages/post-job.php - Employer: Post a New Job

<?php
// Page title and CSS file to load
$pageTitle = 'Post a Job';
$pageCss = 'post-job';

// Error message variable (filled by backend if validation fails)
$error = '';

// Load the header (navigation, HTML head, etc.)
require_once '../includes/header.php';
?>

<!-- Top header section of the page -->
<div class="page-header">
    <div class="container">
        <h1>Post a New Job</h1>
        <p>Fill in the details below to attract the right candidates</p>
    </div>
</div>

<div class="container section">
    <div style="max-width:720px; margin:0 auto;">
        <div class="card">

            <!-- Show error message if validation failed -->
            <?php if ($error): ?>
                <div class="flash flash-error" style="border-radius:8px; margin-bottom:1.5rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Job posting form: POSTs to same page -->
            <form method="POST" action="<?= BASE_URL ?>/pages/post-job.php" data-validate>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="title">Job Title *</label>
                        <input type="text" id="title" name="title"
                               class="form-control"
                               placeholder="e.g. PHP Developer"
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                               required>
                        <span class="form-error">Job title is required.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="company">Company Name *</label>
                        <input type="text" id="company" name="company"
                               class="form-control"
                               placeholder="e.g. Tech Corp"
                               value="<?= htmlspecialchars($_POST['company'] ?? '') ?>"
                               required>
                        <span class="form-error">Company name is required.</span>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="location">Location *</label>
                        <input type="text" id="location" name="location"
                               class="form-control"
                               placeholder="e.g. Kathmandu / Remote"
                               value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                               required>
                        <span class="form-error">Location is required.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="type">Job Type *</label>
                        <!-- Dropdown remembers selected value after failed form submission -->
                        <select id="type" name="type" class="form-control" required>
                            <option value="full-time"  <?= (($_POST['type']??'')  === 'full-time')  ? 'selected' : '' ?>>Full Time</option>
                            <option value="part-time"  <?= (($_POST['type']??'')  === 'part-time')  ? 'selected' : '' ?>>Part Time</option>
                            <option value="remote"     <?= (($_POST['type']??'')  === 'remote')     ? 'selected' : '' ?>>Remote</option>
                            <option value="contract"   <?= (($_POST['type']??'')  === 'contract')   ? 'selected' : '' ?>>Contract</option>
                            <option value="internship" <?= (($_POST['type']??'')  === 'internship') ? 'selected' : '' ?>>Internship</option>
                        </select>
                    </div>
                </div>

                <!-- Salary is optional -->
                <div class="form-group">
                    <label class="form-label" for="salary">Salary Range <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <input type="text" id="salary" name="salary"
                           class="form-control"
                           placeholder="e.g. Rs. 40,000 - 60,000 / month"
                           value="<?= htmlspecialchars($_POST['salary'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Job Description *</label>
                    <textarea id="description" name="description"
                              class="form-control"
                              rows="6"
                              placeholder="Describe the role, responsibilities, and what makes it exciting..."
                              required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <span class="form-error">Description is required.</span>
                </div>

                <!-- Requirements is optional -->
                <div class="form-group">
                    <label class="form-label" for="requirements">Requirements <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <textarea id="requirements" name="requirements"
                              class="form-control"
                              rows="4"
                              placeholder="Skills, experience, education requirements..."><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
                </div>

                <!-- Submit or go back to employer dashboard -->
                <div style="display:flex; gap:1rem;">
                    <button type="submit" class="btn btn-primary btn-lg">Post Job</button>
                    <a href="<?= BASE_URL ?>/dashboard/employer.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Load footer (closing HTML tags, scripts) -->
<?php require_once '../includes/footer.php'; ?>