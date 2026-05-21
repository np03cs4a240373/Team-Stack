// apply-job.js — AJAX job application form + cover letter word count
// Used by: pages/job-detail.php
// Requires: utils.js (showAlert)

function showJobAppliedToast() {
    // Remove any existing toast
    const existing = document.getElementById('applyToast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'applyToast';
    toast.innerHTML =
        '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">' +
        '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>' +
        '<polyline points="22 4 12 14.01 9 11.01"/>' +
        '</svg>' +
        '<div>' +
        '<div style="font-weight:700;font-size:0.95rem;">Job Applied!</div>' +
        '<div style="font-size:0.8rem;opacity:0.9;">Your application has been submitted.</div>' +
        '</div>';
    toast.style.cssText =
        'position:fixed;bottom:32px;left:50%;transform:translateX(-50%) translateY(20px);' +
        'background:#10b981;color:#fff;' +
        'display:flex;align-items:center;gap:0.75rem;' +
        'padding:0.85rem 1.5rem;border-radius:12px;' +
        'box-shadow:0 8px 32px rgba(16,185,129,0.4);' +
        'z-index:99999;font-family:inherit;' +
        'opacity:0;transition:opacity 0.3s ease,transform 0.3s ease;' +
        'white-space:nowrap;';

    document.body.appendChild(toast);

    // Slide in
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
    });

    // Slide out and remove after 3.5s
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-50%) translateY(20px)';
        setTimeout(() => toast.remove(), 350);
    }, 3500);
}

const WORD_MIN = 200;
const WORD_MAX = 250;

// Only letters, numbers, whitespace, and common punctuation allowed
const SPECIAL_CHAR_RE = /[^a-zA-Z0-9\s.,!?'"()\-:;ऀ-ॿ]/;

function countWords(str) {
    return str.trim() === '' ? 0 : str.trim().split(/\s+/).length;
}

function getCoverLetterError() {
    const el = document.getElementById('cover_letter');
    if (!el) return null;
    let existing = document.getElementById('coverLetterSpecialErr');
    if (!existing) {
        existing = document.createElement('p');
        existing.id = 'coverLetterSpecialErr';
        existing.style.cssText = 'color:#ef4444;font-size:0.8rem;margin-top:0.35rem;display:none;';
        existing.textContent = 'Special characters (e.g. @, #, $, %, &, *) are not allowed.';
        el.parentNode.insertBefore(existing, el.nextSibling);
    }
    return existing;
}

document.addEventListener('DOMContentLoaded', function () {
    // ---- Cover letter word count + special char validation ----
    const coverLetter  = document.getElementById('cover_letter');
    const wordCountNum = document.getElementById('wordCountNum');
    const wordCountMsg = document.getElementById('wordCountMsg');

    if (coverLetter && wordCountNum) {
        coverLetter.addEventListener('input', function () {
            let text  = coverLetter.value;

            // Strip special characters in real time
            const errEl = getCoverLetterError();
            if (SPECIAL_CHAR_RE.test(text)) {
                coverLetter.value = text.replace(new RegExp(SPECIAL_CHAR_RE.source, 'g'), '');
                text = coverLetter.value;
                if (errEl) errEl.style.display = 'block';
                setTimeout(() => { if (errEl) errEl.style.display = 'none'; }, 3000);
            }

            let words = countWords(text);

            // Block beyond 250 words
            if (words > WORD_MAX) {
                const trimmed = text.trim().split(/\s+/).slice(0, WORD_MAX).join(' ');
                coverLetter.value = trimmed + ' ';
                words = WORD_MAX;
            }

            wordCountNum.textContent = words + ' / ' + WORD_MAX + ' words';

            if (wordCountMsg) {
                if (words > WORD_MAX - 15 && words <= WORD_MAX) {
                    wordCountMsg.textContent = 'Approaching limit';
                    wordCountMsg.style.color = '#f59e0b';
                    wordCountNum.style.color = '#f59e0b';
                } else if (words >= WORD_MIN) {
                    wordCountMsg.textContent = 'Good length';
                    wordCountMsg.style.color = '#10b981';
                    wordCountNum.style.color = '#10b981';
                } else {
                    wordCountMsg.textContent = '';
                    wordCountNum.style.color = 'var(--text-muted)';
                }
            }
        });
    }

    // ---- Apply form submit ----
    const applyForm = document.getElementById('applyForm');
    if (!applyForm) return;

    applyForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = applyForm.querySelector('button[type="submit"]');
        const formData  = new FormData(applyForm);

        // Guard: reject if special characters somehow still present
        const clValue = (applyForm.querySelector('#cover_letter') || {}).value || '';
        if (SPECIAL_CHAR_RE.test(clValue)) {
            showAlert('Cover letter contains special characters. Please remove them before submitting.', 'error');
            return;
        }

        submitBtn.disabled    = true;
        submitBtn.textContent = 'Submitting...';

        fetch(BASE_URL + '/api/apply-job.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showJobAppliedToast();
                applyForm.reset();
                if (wordCountNum) { wordCountNum.textContent = '0 / 250 words'; wordCountNum.style.color = 'var(--text-muted)'; }
                if (wordCountMsg) { wordCountMsg.textContent = ''; }
                submitBtn.textContent = 'Applied ✓';
                submitBtn.style.background = '#10b981';
            } else {
                showAlert(data.error || 'Something went wrong.', 'error');
                submitBtn.disabled    = false;
                submitBtn.textContent = 'Submit Application';
            }
        })
        .catch(() => {
            showAlert('Network error. Try again.', 'error');
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Submit Application';
        });
    });
});
