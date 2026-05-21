// ============================================================
// jobs.js — AJAX job search and save job
// Used by: pages/jobs.php
// Requires: utils.js (escHtml, formatDate, showAlert)
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    initJobSearch();
});

function initJobSearch() {
    const searchForm = document.getElementById('jobSearchForm');
    const resultsDiv = document.getElementById('jobResults');
    const loadingDiv = document.getElementById('searchLoading');
    if (!searchForm || !resultsDiv) return;

    let debounceTimer;
    searchForm.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(doSearch, 400);
        });
    });

    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        doSearch();
    });

    function doSearch() {
        const keyword    = document.getElementById('searchKeyword')?.value    || '';
        const location   = document.getElementById('searchLocation')?.value   || '';
        let salaryMin    = document.getElementById('searchSalaryMin')?.value  || '';
        let salaryMax    = document.getElementById('searchSalaryMax')?.value  || '';
        const type       = document.getElementById('searchType')?.value       || '';
        const experience = document.getElementById('searchExperience')?.value || '';

        // Normalize reversed salary inputs so the filter always uses min <= max.
        if (salaryMin && salaryMax && Number(salaryMin) > Number(salaryMax)) {
            [salaryMin, salaryMax] = [salaryMax, salaryMin];
        }

        if (loadingDiv) loadingDiv.classList.remove('hidden');
        resultsDiv.style.opacity = '0.5';

        const params = new URLSearchParams({ keyword, location, type, experience });
        if (salaryMin) params.set('salary_min', salaryMin);
        if (salaryMax) params.set('salary_max', salaryMax);

        fetch(BASE_URL + '/api/search-jobs.php?' + params.toString())
            .then(res => res.json())
            .then(data => {
                renderJobs(data);
                if (loadingDiv) loadingDiv.classList.add('hidden');
                resultsDiv.style.opacity = '1';
            })
            .catch(err => {
                console.error('Search error:', err);
                resultsDiv.innerHTML = '<p class="text-muted text-center mt-3">Search failed. Please try again.</p>';
                if (loadingDiv) loadingDiv.classList.add('hidden');
                resultsDiv.style.opacity = '1';
            });
    }

    // Initial load — show all jobs
    doSearch();
}

function renderJobs(jobs) {
    const container = document.getElementById('jobResults');
    if (!container) return;

    if (!jobs || jobs.length === 0) {
        container.innerHTML = `
            <div class="empty-state fade-in">
                <h3>No jobs found</h3>
                <p>Try different keywords or filters.</p>
                <a href="${BASE_URL}/pages/jobs.php" class="btn btn-outline">Clear Search</a>
            </div>`;
        return;
    }

    const typeLabels = {
        'full-time':  'Full Time',
        'part-time':  'Part Time',
        'remote':     'Remote',
        'contract':   'Contract',
        'internship': 'Internship',
    };

    const starSvgEmpty  = `<svg class="star-empty" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>`;
    const starSvgFilled = `<svg class="star-filled" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>`;

    const iconLocation = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>`;
    const iconSalary   = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>`;
    const iconCalendar = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`;
    const iconPeople   = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`;
    const iconCompany  = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>`;

    container.innerHTML = jobs.map(job => `
        <div class="card job-card fade-in">
            <div class="job-card-header">
                <div style="flex:1;min-width:0;">
                    <a href="${BASE_URL}/pages/job-detail.php?id=${escHtml(job.id)}" class="job-title">${escHtml(job.title)}</a>
                    <div class="job-company" style="display:flex;align-items:center;gap:0.3rem;">${iconCompany}${escHtml(job.company)}</div>
                </div>
                <span class="job-badge badge-${escHtml(job.type)}">${escHtml(typeLabels[job.type] || job.type)}</span>
            </div>
            <div class="job-meta">
                <span style="display:inline-flex;align-items:center;gap:0.3rem;">${iconLocation}${escHtml(job.location)}</span>
                ${job.salary ? `<span style="display:inline-flex;align-items:center;gap:0.3rem;">${iconSalary}${escHtml(job.salary)}</span>` : ''}
                <span style="display:inline-flex;align-items:center;gap:0.3rem;">${iconCalendar}${formatDate(job.created_at)}</span>
                <span style="display:inline-flex;align-items:center;gap:0.3rem;">${iconPeople}${job.app_count} applicant${job.app_count == 1 ? '' : 's'}</span>
            </div>
            <p class="text-muted text-sm" style="margin-top:0.5rem;line-height:1.55;">${escHtml(job.description).substring(0, 110)}...</p>
            <div class="job-actions">
                <a href="${BASE_URL}/pages/job-detail.php?id=${escHtml(job.id)}" class="btn btn-primary btn-sm">View Details →</a>
                <button class="star-btn ${job.is_saved ? 'starred' : ''}"
                        data-job-id="${escHtml(job.id)}"
                        title="${job.is_saved ? 'Unsave job' : 'Save job'}">
                    ${starSvgEmpty}${starSvgFilled}
                </button>
            </div>
        </div>
    `).join('');

    // Attach star toggle listeners
    container.querySelectorAll('.star-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleStarJob(btn);
        });
    });
}

function toggleStarJob(btn) {
    const jobId   = btn.dataset.jobId;
    const starred = btn.classList.contains('starred');

    if (starred) {
        fetch(BASE_URL + '/api/unsave-job.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'job_id=' + encodeURIComponent(jobId)
        }).then(r => r.json()).then(d => {
            if (d.success) {
                btn.classList.remove('starred');
                btn.title = 'Save job';
            } else if (d.error) {
                showAlert(d.error, 'error');
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
            } else if (d.error) {
                showAlert(d.error, 'error');
            }
        }).catch(() => {});
    }
}
