

<!-- Breadcrumb -->
<div class="breadcrumb container">
    <a href="<?= BASE_URL ?>/index.php">Home</a>
    <span class="breadcrumb-sep">/</span>
    <a href="<?= BASE_URL ?>/pages/jobs.php">Jobs</a>
    <span class="breadcrumb-sep">/</span>
    <span><?= htmlspecialchars($job['title']) ?></span>
</div>

<!-- Job Detail Layout: main content + sidebar -->
<div class="job-detail-layout">

    <!-- ---- LEFT: Job Details ---- -->
    <div>
        <div class="job-detail-card">
            <!-- Title & Badge -->
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1rem;">
                <div>
                    <h1 class="job-detail-title"><?= htmlspecialchars($job['title']) ?></h1>
                    <p style="color:var(--text-muted);">
                        <?= htmlspecialchars($job['company']) ?> &nbsp;·&nbsp;
                        <?= htmlspecialchars($job['location']) ?>
                    </p>
                </div>
                <span class="job-badge badge-<?= $job['type'] ?>">
                    <?= $typeLabels[$job['type']] ?? $job['type'] ?>
                </span>
            </div>

            <!-- Meta Info -->
            <div class="job-meta" style="margin-bottom:1rem;">
                <?php if ($job['salary']): ?>
                    <span><?= htmlspecialchars($job['salary']) ?></span>
                <?php endif; ?>
                <span>Posted <?= date('M d, Y', strtotime($job['created_at'])) ?></span>
            </div>

            <!-- Description -->
            <div class="job-detail-section">
                <h3>Job Description</h3>
                <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>
            </div>

            <!-- Requirements -->
            <?php if ($job['requirements']): ?>
            <div class="job-detail-section">
                <h3>Requirements</h3>
                <p><?= nl2br(htmlspecialchars($job['requirements'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- ---- APPLY FORM (only for logged-in seekers) ---- -->
        <?php if (isLoggedIn() && getRole() === 'seeker'): ?>
            <div class="apply-section">
                <h3>Apply for this Job</h3>

                <?php if ($alreadyApplied): ?>
                    <div class="flash flash-success" style="border-radius:8px;">
                        You have already applied for this job.
                    </div>
                <?php else: ?>
                    <!-- Apply form — submitted via AJAX (see js/apply-job.js) -->
                    <form id="applyForm" enctype="multipart/form-data" style="min-height:0;">
                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">

                        <div class="form-group" style="min-height:0;">
                            <label class="form-label" for="cover_letter">Cover Letter <span style="color:var(--text-muted); font-weight:400;">(optional, max 250 words)</span></label>
                            <textarea id="cover_letter" name="cover_letter"
                                      class="form-control"
                                      rows="5"
                                      placeholder="Tell the employer why you're a great fit..."></textarea>
                            <div id="wordCountWrap" style="display:flex;justify-content:space-between;align-items:center;margin-top:0.3rem;">
                                <span id="wordCountMsg" style="font-size:0.78rem;color:var(--text-muted);"></span>
                                <span id="wordCountNum" style="font-size:0.78rem;color:var(--text-muted);">0 / 250 words</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="resume">Resume / CV <span style="color:var(--text-muted); font-weight:400;">(PDF only, max 5MB)</span></label>
                            <input type="file"
                                   id="resume"
                                   name="resume"
                                   accept=".pdf"
                                   class="form-control"
                                   style="padding: 0.5rem 1rem; cursor:pointer;">
                            <span class="form-error" id="resumeError" style="display:none;">Please upload a valid PDF file under 5MB.</span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            Submit Application
                        </button>
                    </form>
                <?php endif; ?>
            </div>

        
    </div>

    <!-- ---- RIGHT: Sidebar ---- -->
    <div class="job-sidebar">

        <!-- Job Info Card -->
        <div class="sidebar-card">
            <h4 style="font-size:0.95rem; font-weight:600; margin-bottom:1rem; color:var(--primary);">Job Overview</h4>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Job Type</span>
                <span class="sidebar-info-value"><?= $typeLabels[$job['type']] ?? $job['type'] ?></span>
            </div>
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Location</span>
                <span class="sidebar-info-value"><?= htmlspecialchars($job['location']) ?></span>
            </div>
            
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Salary</span>
                <span class="sidebar-info-value"><?= htmlspecialchars($job['salary']) ?></span>
            </div>
            
            <div class="sidebar-info-item">
                <span class="sidebar-info-label">Posted</span>
                <span class="sidebar-info-value"><?= date('M d, Y', strtotime($job['created_at'])) ?></span>
            </div>
        </div>

        <!-- Company Card -->
        <div class="sidebar-card">
            <h4 style="font-size:0.95rem; font-weight:600; margin-bottom:1rem; color:var(--primary);">About the Company</h4>
            <p style="font-weight:600;"><?= htmlspecialchars($job['company']) ?></p>
            <p class="text-muted text-sm mt-1">Posted by: <?= htmlspecialchars($job['employer_name']) ?></p>
        </div>

        <!-- Save Job Star Toggle -->
        

        <!-- Back Button -->
        <a href="<?= BASE_URL ?>/pages/jobs.php" class="btn btn-outline btn-block" style="text-align:center;">
            ← Back to Jobs
        </a>
    </div>

</div>

<script>
// ---- Star toggle on job detail page ----
(function(){
    const btn = document.getElementById('detailStarBtn');
    if (!btn) return;
    btn.addEventListener('click', function() {
        const jobId   = btn.dataset.jobId;
        const starred = btn.classList.contains('starred');
        const label   = document.getElementById('detailStarLabel');

        if (starred) {
            fetch(BASE_URL + '/api/unsave-job.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'job_id=' + encodeURIComponent(jobId)
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    btn.classList.remove('starred');
                    btn.title = 'Save job';
                    if (label) label.textContent = 'Save this Job';
                }
            }).catch(() => {});
        } else {
            fetch(BASE_URL + '/api/save-job.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'job_id=' + encodeURIComponent(jobId)
            }).then(r => r.json()).then(d => {
                if (d.success) {
                    btn.classList.add('starred');
                    btn.title = 'Unsave job';
                    if (label) label.textContent = 'Job Saved';
                }
            }).catch(() => {});
        }
    });
})();
</script>
