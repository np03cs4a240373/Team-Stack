<!-- Dashboard Header -->
<!-- Show welcome message with user's name -->
<div class="dashboard-header">
    <div class="container">
        <h1>Welcome,User! <!--add php code--></h1>
        <p>Here's an overview of your job search activity.</p>
    </div>
</div>

<div class="dashboard-body">

    <!-- 3 stat cards: total apps, accepted, saved jobs -->
    <div class="grid-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">0</div>
                <div class="stat-label">Total Applications</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">0</div>
                <div class="stat-label">Accepted</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-plain">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="stat-info">
                <div class="stat-value">0</div>
                <div class="stat-label">Saved Jobs</div>
            </div>
        </div>
    </div>

    <!-- Quick action buttons -->
    <div class="card mb-3">
        <h3 style="margin-bottom:1rem; font-size:1rem; font-weight:600;">Quick Actions</h3>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href=#  <!--add php code--> class="btn btn-primary">Find Jobs</a>     
            <a href=# <!--add php code--> class="btn btn-outline">My Applications</a>
            <a href=# <!--add php code--> class="btn btn-outline">Saved Jobs</a>
            <a href=# <!--add php code--> class="btn btn-outline">Edit Profile</a>
        </div>
    </div>

    <!-- Recent applications -->
    <div class="card">
        <div class="d-flex justify-between align-center mb-2">
            <h3 style="font-size:1rem; font-weight:600;">Recent Applications</h3>
            <a href= # <!--add php code--> class="btn btn-outline btn-sm">View All</a>
        </div>

        
    </div>

</div>
