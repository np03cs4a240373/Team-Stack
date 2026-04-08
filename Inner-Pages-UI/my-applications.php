<!-- Top header section of the page -->
<div class="page-header">
    <div class="container">
        <h1>My Applications</h1>
        <p>Track the status of all your job applications</p>
    </div>
</div>

<div class="container section">

    <!-- Show empty state if no applications exist -->

        <!--add php code here-->

        <div class="empty-state">
            <h3>No applications yet</h3>
            <p>Start applying for jobs and track your progress here.</p>
            <!--add php code here in link-->
            <a href="#" class="btn btn-primary">Browse Jobs</a>
        </div>


        <!-- Summary stat cards: count of each application status -->
        <!--add php code here-->
                <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
            <div class="stat-card" style="flex:1; min-width:140px;">
                <div class="stat-info">
                    <div class="stat-value">2</div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>

            <div class="stat-card" style="flex:1; min-width:140px;">
                <div class="stat-info">
                    <div class="stat-value">1</div>
                    <div class="stat-label">Reviewed</div>
                </div>
            </div>

            <div class="stat-card" style="flex:1; min-width:140px;">
                <div class="stat-info">
                    <div class="stat-value">1</div>
                    <div class="stat-label">Accepted</div>
                </div>
            </div>

            <div class="stat-card" style="flex:1; min-width:140px;">
                <div class="stat-info">
                    <div class="stat-value">1</div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
        </div>

        <!-- Applications table -->
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
                        <!--add php code here-->
                        <tr>
                            <!-- Job title links to the job detail page -->

                            <!--add php code and update-->
                                <td>
                                <a href="job-detail.php?id=1" class="text-primary">
                                    <strong>Frontend Developer</strong>
                                </a>
                            </td>
                             <!--add php code and remove sampleT-->
                            <td>SampleT </td>

                            <!-- Job type badge e.g. Full Time, Remote -->
                            <!--add php code here-->
                            <td>
                                <span class="job-badge badge-full-time" style="font-size:0.72rem;">
                                    Full Time
                                </span>
                            </td>
                            <!--till here-->
                            <!--update samplel and add code-->
                            <td>SampleL</td>

                            <!-- Application status badge: pending, reviewed, accepted, rejected -->
                            
                            <!--add php code here-->
                            <td>
                                <span class="status-badge status-pending">
                                    Pending
                                </span>
                            </td>
                             <!--till here-->

                            <!-- Format date to readable format e.g. Jan 01, 2025 -->
                             <!--add php code here and replace sampleDate-->
                            <td>SampleDate</td>

                            <td>
                                <!--change code -->
                                <a href="#" class="btn btn-outline btn-sm">View Job</a>
                            </td>
                        </tr>
               
                </tbody>
            </table>
        </div>

</div>

<!-- Load footer (closing HTML tags, scripts) -->