<div class="page-header">
    <div class="container">
        <h1>Manage Jobs</h1>
        <p>View and delete all job listings</p>
    </div>
</div>

<div class="container section">

    <!-- filter by status -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
            <span style="font-weight:600; font-size:.9rem;">Filter:</span>

            <!--add php code in all links-->

            <a href="#" class="btn btn-primary btn-sm">All</a>
            <a href="#" class="btn btn-outline btn-sm">Active</a>
            <a href="#" class="btn btn-outline btn-sm">Closed</a>
            <span class="text-muted text-sm" style="margin-left:auto;">2 job(s)  <!-- add php count here --> </span>
        </div>
    </div>

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
                <!-- shown when no jobs found -->
                <!-- <tr><td colspan="10" style="text-align:center; color:var(--text-muted); padding:2rem;">No jobs found.</td></tr> -->

                <!-- example row: active full-time job -->
                <tr>
                    <td class="text-muted">1</td>
                    <td>
                         <!--add php code in this link-->
                        <a href="#" class="text-primary">
                            <strong>Frontend Developer</strong>
                        </a>
                    </td>
                    <td>Tech Nepal</td>
                    <td>Ram Sharma</td>
                    <td>
                        <span class="job-badge badge-full-time" style="font-size:0.72rem;">
                            Full Time
                        </span>
                    </td>
                    <td>Kathmandu</td>
                    <td>5</td>
                    <td>
                        <span class="status-badge status-active">
                            Active
                        </span>
                    </td>
                    <td>Mar 10, 2025</td>
                    <td>
                         <!--add php code in this link-->
                        <a href="#"
                           onclick="return confirm('Delete this job and all its applications?')"
                           class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>

                <!-- example row: closed internship job -->
                <tr>
                    <td class="text-muted">2</td>
                    <td>
                         <!--add php code in this link-->
                        <a href="#" class="text-primary">
                            <strong>Marketing Intern</strong>
                        </a>
                    </td>
                    <td>Brand Co</td>
                    <td>Hari Bahadur</td>
                    <td>
                        <span class="job-badge badge-internship" style="font-size:0.72rem;">
                            Internship
                        </span>
                    </td>
                    <td>Pokhara</td>
                    <td>2</td>
                    <td>
                        <span class="status-badge status-closed">
                            Closed
                        </span>
                    </td>
                    <td>Feb 20, 2025</td>
                    <td>
                         <!--add php code in this link-->
                        <a href="#"
                           onclick="return confirm('Delete this job and all its applications?')"
                           class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>