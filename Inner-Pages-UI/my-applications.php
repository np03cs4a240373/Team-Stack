<style>
/* Statistics Card */
.stat-card {
    background: var(--bg-card, #fff);
    border: 1px solid var(--border);
    border-radius: var(--radius, 12px);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: border-color 0.2s;
}

[data-theme="light"] .stat-card {
    background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.stat-card:hover {
    border-color: #00b4d8;
}

/* Card Icon */
.stat-icon.stat-icon-plain {
    background: none;
    color: var(--primary, #00b4d8);
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--primary, #00b4d8);
    line-height: 1;
    letter-spacing: -0.5px;
}

.stat-label {
    font-size: 0.8rem;
    color: var(--text-muted, #64748b);
    margin-top: 0.2rem;
}
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">

        <h1>My Applications</h1>

        <p>Track the status of all your job applications</p>

    </div>
</div>

<div class="container section">

    <!-- Overview Statistics -->
    <div class="grid-3 mb-3">

        <!-- Total Applications -->
        <div class="stat-card">

            <div class="stat-icon stat-icon-plain">

                <svg width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">

                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>

                </svg>

            </div>

            <div class="stat-info">

                <div class="stat-value">
                    10
                </div>

                <div class="stat-label">
                    Total Applications
                </div>

            </div>

        </div>

        <!-- Applied Applications -->
        <div class="stat-card">

            <div class="stat-icon stat-icon-plain">

                <svg width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">

                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>

                </svg>

            </div>

            <div class="stat-info">

                <div class="stat-value">
                    4
                </div>

                <div class="stat-label">
                    Applied
                </div>

            </div>

        </div>

        <!-- Pending Applications -->
        <div class="stat-card">

            <div class="stat-icon stat-icon-plain">

                <svg width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">

                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>

                </svg>

            </div>

            <div class="stat-info">

                <div class="stat-value">
                    2
                </div>

                <div class="stat-label">
                    Pending
                </div>

            </div>

        </div>

    </div>

    <!-- Extra Statistics -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; margin-bottom:1.5rem;">

        <!-- Reviewed -->
        <div class="stat-card">

            <div class="stat-icon stat-icon-plain">

                <svg width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">

                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>

                </svg>

            </div>

            <div class="stat-info">

                <div class="stat-value">
                    1
                </div>

                <div class="stat-label">
                    Reviewed
                </div>

            </div>

        </div>

        <!-- Shortlisted -->
        <div class="stat-card">

            <div class="stat-icon stat-icon-plain">

                <svg width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">

                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>

                </svg>

            </div>

            <div class="stat-info">

                <div class="stat-value">
                    2
                </div>

                <div class="stat-label">
                    Shortlisted
                </div>

            </div>

        </div>

        <!-- Accepted -->
        <div class="stat-card">

            <div class="stat-icon stat-icon-plain">

                <svg width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">

                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>

                </svg>

            </div>

            <div class="stat-info">

                <div class="stat-value">
                    1
                </div>

                <div class="stat-label">
                    Accepted
                </div>

            </div>

        </div>

        <!-- Rejected -->
        <div class="stat-card">

            <div class="stat-icon stat-icon-plain">

                <svg width="26" height="26" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">

                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>

                </svg>

            </div>

            <div class="stat-info">

                <div class="stat-value">
                    3
                </div>

                <div class="stat-label">
                    Rejected
                </div>

            </div>

        </div>

    </div>

    <!-- Empty State -->
    <div class="empty-state">

        <h3>No applications yet</h3>

        <p>Start applying for jobs and track your progress here.</p>

        <a href="#" class="btn btn-primary">
            Browse Jobs
        </a>

    </div>

    <!-- Applications Table -->
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

                <!-- Application Row -->
                <tr>

                    <td>

                        <!-- Job Detail Link -->
                        <a href="#" class="text-primary">

                            <strong>
                                Frontend Developer
                            </strong>

                        </a>

                    </td>

                    <td>ABC Company</td>

                    <td>

                        <!-- Job Type Badge -->
                        <span class="job-badge badge-full-time"
                              style="font-size:0.72rem;">

                            Full Time

                        </span>

                    </td>

                    <td>Kathmandu</td>

                    <td>

                        <!-- Application Status -->
                        <span class="status-badge status-pending">

                            Pending

                        </span>

                    </td>

                    <td>May 21, 2026</td>

                    <td>

                        <!-- View Job Button -->
                        <a href="#"
                           class="btn btn-outline btn-sm">

                            View Job

                        </a>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>