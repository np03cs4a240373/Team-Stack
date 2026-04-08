
<!-- pages/saved-jobs.php - Job Seeker: Saved/Bookmarked Jobs-->

<!-- Top header section of the page -->
<div class="page-header">
    <div class="container">
        <h1>Saved Jobs</h1>
        <p>Jobs you bookmarked for later</p>
    </div>
</div>

<div class="container section">

    <!-- Show empty state if no saved jobs exist -->

    <!--php code-->

        <div class="empty-state">
            <h3>No saved jobs yet</h3>
            <p>Click the "Save" button on any job listing to bookmark it here.</p>
            <a href="#" class="btn btn-primary">Browse Jobs</a>
        </div>

        <!-- Grid of saved job cards -->
        <div class="grid-3">
            
                <div class="card job-card">
                    <div class="job-card-header">
                        <div>
                            <!-- Job title links to the job detail page -->
                            <a href="#" class="job-title">
                              SampleText
                            </a>
                            <div class="job-company">SampleText</div>
                        </div>

                        <!-- Job type badge e.g. Full Time, Remote -->
                        <span class="job-badge badge-full-time">
                         SampleTime
                        </span>
                    </div>

                    <!-- Job meta: location, salary, date saved -->
                    <div class="job-meta">
                        <span> SampleLocation</span>
        
                        <span>SampleSalary</span>
                        
                        <span>Saved SampleDate</span>
                    </div>

                    <!-- Action buttons: view job or remove from saved -->
                    <div class="job-actions">
                        <a href="#" class="btn btn-primary btn-sm">View & Apply</a>
                        <a href="#"
                           onclick="return confirm('Remove from saved jobs?')"
                           class="btn btn-danger btn-sm">Remove</a>
                    </div>
                </div>
         
        </div>
   

</div>

<!-- Load footer (closing HTML tags, scripts) -->
