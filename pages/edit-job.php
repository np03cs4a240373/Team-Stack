<?php
// pages/edit-job.php - Employer: Edit an Existing Job
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer');

$pdo   = getPDO();
$error = '';
$jobId = (int)($_GET['id'] ?? 0);

if (!$jobId) {
    header('Location: ' . BASE_URL . '/dashboard/employer.php');
    exit;
}

// Fetch job and verify ownership
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
$stmt->execute([$jobId, getUserId()]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header('Location: ' . BASE_URL . '/dashboard/employer.php?msg=not_found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $error = 'Invalid form submission. Please try again.';
    }

    $title        = trim($_POST['title']          ?? '');
    $company      = trim($_POST['company']        ?? '');
    $location     = trim($_POST['location']       ?? '');
    $type         = $_POST['type']                ?? 'full-time';
    $experience   = $_POST['experience_level']    ?? 'any';
    $salaryMin    = (int)($_POST['salary_min']    ?? 0) ?: null;
    $salaryMax    = (int)($_POST['salary_max']    ?? 0) ?: null;
    $deadline     = trim($_POST['deadline']       ?? '');
    $description  = trim($_POST['description']    ?? '');
    $requirements = trim($_POST['requirements']   ?? '');
    $status       = $_POST['status']              ?? 'active';

    $validTypes      = ['full-time','part-time','remote','contract','internship'];
    $validExperience = ['any','entry','mid','senior'];
    $validStatuses   = ['active','closed'];
    if (!in_array($type, $validTypes))            $type       = 'full-time';
    if (!in_array($experience, $validExperience)) $experience = 'any';
    if (!in_array($status, $validStatuses))       $status     = 'active';
    $deadline = (!empty($deadline) && strtotime($deadline)) ? $deadline : null;

    if ($salaryMin && $salaryMax && $salaryMin > $salaryMax) {
        $error = 'Minimum salary cannot be greater than maximum salary.';
    }

    if (!$error && (empty($title) || empty($company) || empty($location) || empty($description))) {
        $error = 'Please fill in all required fields.';
    } elseif (!$error) {
        $salaryDisplay = $salaryMin
            ? 'Rs. ' . number_format($salaryMin) . ($salaryMax ? ' – ' . number_format($salaryMax) : '+')
            : '';
        $upd = $pdo->prepare("
            UPDATE jobs SET
                title = ?, company = ?, location = ?, type = ?,
                experience_level = ?, salary = ?, salary_min = ?, salary_max = ?,
                deadline = ?, description = ?, requirements = ?, status = ?
            WHERE id = ? AND employer_id = ?
        ");
        $upd->execute([
            $title, $company, $location, $type,
            $experience, $salaryDisplay, $salaryMin, $salaryMax,
            $deadline, $description, $requirements, $status,
            $jobId, getUserId()
        ]);

        header('Location: ' . BASE_URL . '/dashboard/employer.php?msg=job_updated');
        exit;
    }

    $job = array_merge($job, [
        'title'           => $title,
        'company'         => $company,
        'location'        => $location,
        'type'            => $type,
        'experience_level'=> $experience,
        'salary_min'      => $salaryMin,
        'salary_max'      => $salaryMax,
        'deadline'        => $deadline ?? '',
        'description'     => $description,
        'requirements'    => $requirements,
        'status'          => $status,
    ]);
}

$pageTitle = 'Edit Job';
$pageCss   = 'post-job';
require_once '../includes/header.php';
?>

    <div class="page-header">
    <div class="container">
        <h1>Edit Job</h1>
        <p>Update the details for: <strong><?= htmlspecialchars($job['title']) ?></strong></p>
    </div>
    </div>

    <div class="container section">
        <div style="max-width:720px; margin:0 auto;">
            <div class="card">

                <?php if ($error): ?>
                    <div class="flash flash-error" style="border-radius:8px; margin-bottom:1.5rem;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/pages/edit-job.php?id=<?= $jobId ?>" data-validate>
                    <?= csrfField() ?>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="title">Job Title *</label>
                            <input type="text" id="title" name="title"
                                class="form-control"
                                value="<?= htmlspecialchars($job['title']) ?>"
                                required>
                            <span class="form-error">Job title is required.</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="company">Company Name *</label>
                            <input type="text" id="company" name="company"
                                class="form-control"
                                value="<?= htmlspecialchars($job['company']) ?>"
                                required>
                            <span class="form-error">Company name is required.</span>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="location">Location *</label>
                            <input type="text" id="location" name="location"
                                class="form-control"
                                value="<?= htmlspecialchars($job['location']) ?>"
                                required>
                            <span class="form-error">Location is required.</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="type">Job Type *</label>
                            <select id="type" name="type" class="form-control" required>
                                <?php
                                $types = ['full-time' => 'Full Time', 'part-time' => 'Part Time', 'remote' => 'Remote', 'contract' => 'Contract', 'internship' => 'Internship'];
                                foreach ($types as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $job['type'] === $val ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="experience_level">Experience Level</label>
                            <select id="experience_level" name="experience_level" class="form-control">
                                <option value="any" <?= ($job['experience_level'] ?? 'any') === 'any'    ? 'selected' : '' ?>>Any Level</option>
                                <option value="entry" <?= ($job['experience_level'] ?? '')    === 'entry'   ? 'selected' : '' ?>>Entry Level (0–2 yrs)</option>
                                <option value="mid" <?= ($job['experience_level'] ?? '')    === 'mid'     ? 'selected' : '' ?>>Mid Level (2–5 yrs)</option>
                                <option value="senior" <?= ($job['experience_level'] ?? '')    === 'senior'  ? 'selected' : '' ?>>Senior Level (5+ yrs)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="deadline">Application Deadline <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                            <input type="date" id="deadline" name="deadline"
                                class="form-control"
                                value="<?= htmlspecialchars($job['deadline'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="salary_min">Min. Salary (NPR) <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                            <input type="number" id="salary_min" name="salary_min"
                                class="form-control"
                                min="0"
                                step="1000"
                                value="<?= htmlspecialchars($job['salary_min'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="salary_max">Max. Salary (NPR) <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                            <input type="number" id="salary_max" name="salary_max"
                                class="form-control"
                                min="0"
                                step="1000"
                                value="<?= htmlspecialchars($job['salary_max'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label" for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="active" <?= $job['status'] === 'active'  ? 'selected' : '' ?>>Active</option>
                                <option value="closed" <?= $job['status'] === 'closed'  ? 'selected' : '' ?>>Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Job Description *</label>
                        <textarea id="description" name="description"
                            class="form-control" rows="6"
                            required><?= htmlspecialchars($job['description']) ?></textarea>
                        <span class="form-error">Description is required.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="requirements">Requirements <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                        <textarea id="requirements" name="requirements"
                            class="form-control" rows="4"><?= htmlspecialchars($job['requirements'] ?? '') ?></textarea>
                    </div>

                    <div style="display:flex; gap:1rem;">
                        <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                        <a href="<?= BASE_URL ?>/dashboard/employer.php" class="btn btn-outline btn-lg">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
