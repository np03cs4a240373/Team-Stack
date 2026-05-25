<?php
// ============================================================
// pages/post-job.php - Employer: Post a New Job
// ============================================================
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireRole('employer'); // Only employers can post jobs

$pdo   = getPDO();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        $error = 'Invalid form submission. Please try again.';
    }

    // Collect and sanitize inputs
    $title          = trim($_POST['title']           ?? '');
    $company        = trim($_POST['company']         ?? '');
    $location       = trim($_POST['location']        ?? '');
    $type           = $_POST['type']                 ?? 'full-time';
    $experience     = $_POST['experience_level']     ?? 'any';
    $salaryMin      = (int)($_POST['salary_min']     ?? 0) ?: null;
    $salaryMax      = (int)($_POST['salary_max']     ?? 0) ?: null;
    $deadline       = trim($_POST['deadline']        ?? '');
    $description    = trim($_POST['description']     ?? '');
    $requirements   = trim($_POST['requirements']    ?? '');

    $validTypes      = ['full-time','part-time','remote','contract','internship'];
    $validExperience = ['any','entry','mid','senior'];
    if (!in_array($type, $validTypes))            $type       = 'full-time';
    if (!in_array($experience, $validExperience)) $experience = 'any';
    $deadline = (!empty($deadline) && strtotime($deadline)) ? $deadline : null;

    if ($salaryMin && $salaryMax && $salaryMin > $salaryMax) {
        $error = 'Minimum salary cannot be greater than maximum salary.';
    }

    if (!$error && (empty($title) || empty($company) || empty($location) || empty($description))) {
        $error = 'Please fill in all required fields.';
    } elseif (!$error) {
        $salaryDisplay = $salaryMin ? 'Rs. ' . number_format($salaryMin) . ($salaryMax ? ' – ' . number_format($salaryMax) : '+') : '';
        $stmt = $pdo->prepare("
            INSERT INTO jobs (employer_id, title, company, location, type, experience_level, salary, salary_min, salary_max, deadline, description, requirements)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            getUserId(), $title, $company, $location,
            $type, $experience, $salaryDisplay, $salaryMin, $salaryMax, $deadline, $description, $requirements
        ]);

        header('Location: ' . BASE_URL . '/dashboard/employer.php?msg=job_posted');
        exit;
    }
}

$pageTitle = 'Post a Job';
$pageCss = 'post-job';
require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Post a New Job</h1>
        <p>Fill in the details below to attract the right candidates</p>
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

            <form method="POST" action="<?= BASE_URL ?>/pages/post-job.php" data-validate>
                <?= csrfField() ?>

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
                        <select id="type" name="type" class="form-control" required>
                            <option value="full-time"  <?= (($_POST['type']??'')  === 'full-time')  ? 'selected' : '' ?>>Full Time</option>
                            <option value="part-time"  <?= (($_POST['type']??'')  === 'part-time')  ? 'selected' : '' ?>>Part Time</option>
                            <option value="remote"     <?= (($_POST['type']??'')  === 'remote')     ? 'selected' : '' ?>>Remote</option>
                            <option value="contract"   <?= (($_POST['type']??'')  === 'contract')   ? 'selected' : '' ?>>Contract</option>
                            <option value="internship" <?= (($_POST['type']??'')  === 'internship') ? 'selected' : '' ?>>Internship</option>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="experience_level">Experience Level</label>
                        <select id="experience_level" name="experience_level" class="form-control">
                            <option value="any"    <?= (($_POST['experience_level']??'any') === 'any')    ? 'selected' : '' ?>>Any Level</option>
                            <option value="entry"  <?= (($_POST['experience_level']??'')   === 'entry')   ? 'selected' : '' ?>>Entry Level (0–2 yrs)</option>
                            <option value="mid"    <?= (($_POST['experience_level']??'')   === 'mid')     ? 'selected' : '' ?>>Mid Level (2–5 yrs)</option>
                            <option value="senior" <?= (($_POST['experience_level']??'')   === 'senior')  ? 'selected' : '' ?>>Senior Level (5+ yrs)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="deadline">Application Deadline <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                        <input type="date" id="deadline" name="deadline"
                               class="form-control"
                               min="<?= date('Y-m-d') ?>"
                               value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="salary_min">Min. Salary (NPR) <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                        <input type="number" id="salary_min" name="salary_min"
                               class="form-control" min="0" step="1000"
                               placeholder="e.g. 30000"
                               value="<?= htmlspecialchars($_POST['salary_min'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="salary_max">Max. Salary (NPR) <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                        <input type="number" id="salary_max" name="salary_max"
                               class="form-control" min="0" step="1000"
                               placeholder="e.g. 60000"
                               value="<?= htmlspecialchars($_POST['salary_max'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.4rem;">
                        <label class="form-label" for="description" style="margin-bottom:0;">Job Description *</label>
                        <button type="button" id="aiGenerateBtn" style="
                            display:inline-flex; align-items:center; gap:0.35rem;
                            background:linear-gradient(135deg,#00b4d8,#0096b3);
                            color:#fff; border:none; border-radius:6px;
                            padding:0.32rem 0.75rem; font-size:0.78rem; font-weight:600;
                            cursor:pointer; font-family:inherit;
                            transition:opacity 0.2s, transform 0.2s;
                            white-space:nowrap;
                        ">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            <span id="aiGenerateBtnText">Generate with AI</span>
                        </button>
                    </div>
                    <textarea id="description" name="description"
                              class="form-control"
                              rows="6"
                              placeholder="Describe the role, responsibilities, and what makes it exciting..."
                              required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <span class="form-error">Description is required.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="requirements">Requirements <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <textarea id="requirements" name="requirements"
                              class="form-control"
                              rows="4"
                              placeholder="Skills, experience, education requirements..."><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
                </div>

                <!-- AI generate notice (hidden by default) -->
                <div id="aiNotice" style="display:none; font-size:0.78rem; color:#0096b3; background:rgba(0,180,216,0.08); border:1px solid rgba(0,180,216,0.2); border-radius:6px; padding:0.55rem 0.85rem; margin-bottom:1rem;">
                    AI-generated content filled in. Review and edit before posting.
                </div>

                <div style="display:flex; gap:1rem;">
                    <button type="submit" class="btn btn-primary btn-lg">Post Job</button>
                    <a href="<?= BASE_URL ?>/dashboard/employer.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const btn      = document.getElementById('aiGenerateBtn');
    const btnText  = document.getElementById('aiGenerateBtnText');
    const descBox  = document.getElementById('description');
    const reqBox   = document.getElementById('requirements');
    const notice   = document.getElementById('aiNotice');

    btn.addEventListener('click', async function () {
        const title    = document.getElementById('title').value.trim();
        const company  = document.getElementById('company').value.trim();
        const location = document.getElementById('location').value.trim();
        const type     = document.getElementById('type').value;

        if (!title) {
            alert('Please enter a Job Title first so AI can generate a relevant description.');
            document.getElementById('title').focus();
            return;
        }

        btn.disabled         = true;
        btn.style.opacity    = '0.7';
        btnText.textContent  = 'Generating…';
        descBox.style.opacity = '0.5';

        try {
            const res  = await fetch(BASE_URL + '/api/generate-job-description.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ title, company, location, type }),
            });
            const data = await res.json();

            if (data.error) {
                alert('AI Error: ' + data.error);
            } else {
                descBox.value = data.description || '';
                if (data.requirements) reqBox.value = data.requirements;
                notice.style.display = 'block';
            }
        } catch (e) {
            alert('Could not reach the AI service. Please try again or write the description manually.');
        }

        btn.disabled         = false;
        btn.style.opacity    = '1';
        btnText.textContent  = 'Generate with AI';
        descBox.style.opacity = '1';
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>
