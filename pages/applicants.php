<!-- pages/applicants.php - Employer: View Applicants-->
<!-- Shows all applications for employer's jobs-->

<!-- Top header section of the page -->
<div class="page-header">
    <div class="container">
        <h1>Applicants</h1>
        <p>Review candidates who applied for your jobs</p>
    </div>
</div>

<div class="container section">

    <!-- Dropdown filter to show applicants for a specific job -->
    <div class="card mb-3" style="padding:1rem 1.5rem;">
        <form method="GET" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
            <label style="font-weight:600; font-size:.9rem;">Filter by Job:</label>

            <!-- Dropdown auto-submits the form when selection changes -->
            <select name="job_id" class="form-control" style="max-width:300px;" onchange="this.form.submit()">
                <option value="">All Jobs</option>

            <!--add php for each code and remove code till /select -->
                <option value="1">Frontend Developer</option>
                <option value="2">UI/UX Designer</option>
                <option value="3">React Developer</option>

            </select>

            <!-- Shows total count of applicants being displayed -->
            <!--add php code-->
            <span class="text-muted text-sm"> 3 applicant(s)</span>
        </form>
    </div>

   

        <!-- Applicants table -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Job</th>
                        <th>Cover Letter</th>
                        <th>Applied</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                        <!--add php code-->
                        <tr>
                            <td>
                                <strong>SampleN<!--add php code--> </strong>
                        
                                <div class="text-muted text-sm"> SampleL <!--add php code--> </div>
                            </td>
                            <td> SampleEmail <!--add php code--> </td>
                            <td> SampleFE  <!--add php code--> </td>

                            <!-- Show first 40 characters of cover letter with full text on hover -->
                            <td>
                                <span title="I am very interested in this role and have strong frontend skills." style="cursor:help;"> I am very interested in this role...
                                </span>  <!--add php code--> 
                            </td>

                            <!-- Format date to readable format e.g. Jan 01, 2025 -->
                            <td>SampleDate  <!--add php code--> </td>

                            <!-- Current application status badge -->
                            <td>
                                <span class="status-badge status-pending">
                                    SamplePending <!--add php code--> 
                                </span>
                            </td>

                            <!-- Form to update application status (POST to same page) -->
                            <td>
                                 <!--add php code-->
                                <form style="display:flex; gap:0.4rem;">
                                     <select name="status" class="form-control" style="font-size:0.8rem; padding:0.3rem 0.5rem;">
                                         <option value="pending" selected>Pending</option>
                                         <option value="reviewed">Reviewed</option>
                                         <option value="accepted">Accepted</option>
                                         <option value="rejected">Rejected</option>
                                    </select>
                                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                </form>
                            </td>
                        </tr>
                </tbody>
            </table>
        </div>


</div>

