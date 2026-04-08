
<!-- Top header section of the page -->
<div class="page-header">
    <div class="container">
        <h1>Post a New Job</h1>
        <p>Fill in the details below to attract the right candidates</p>
    </div>
</div>

<div class="container section">
    <div style="max-width:720px; margin:0 auto;">
        <div class="card">

            <!-- Show error message if validation failed -->
            <!--add php code here-->

            <!-- Job posting form: POSTs to same page -->
            <!--add php for base url-->
            <form method="POST" action="#" data-validate>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="title">Job Title *</label>
                        <input type="text" id="title" name="title"
                               class="form-control"
                               placeholder="e.g. PHP Developer"
                               value=""
                               required>
                    <!--add php code in values = part -->
                        <span class="form-error">Job title is required.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="company">Company Name *</label>
                        <input type="text" id="company" name="company"
                               class="form-control"
                               placeholder="e.g. Tech Corp"
                               value=" "
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
                               value=" "
                               required>
                        <span class="form-error">Location is required.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="type">Job Type *</label>
                        <!-- Dropdown remembers selected value after failed form submission -->
                        <select id="type" name="type" class="form-control" required>

                        <!--add code in option part-->

                        <option value="full-time">Full Time</option>
                        <option value="part-time">Part Time</option>
                        <option value="remote">Remote</option>
                        <option value="contract">Contract</option>
                        <option value="internship">Internship</option>
                        </select>
                    </div>
                </div>

                <!-- Salary is optional -->
                <div class="form-group">
                    <label class="form-label" for="salary">Salary Range <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <input type="text" id="salary" name="salary"
                           class="form-control"
                           placeholder="e.g. Rs. 40,000 - 60,000 / month"
                           value=" ">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Job Description *</label>
                    <textarea id="description" name="description"
                              class="form-control"
                              rows="6"
                              placeholder="Describe the role, responsibilities, and what makes it exciting..."
                              required> <!--add code here--> </textarea>
                    <span class="form-error">Description is required.</span>
                </div>

                <!-- Requirements is optional -->
                <div class="form-group">
                    <label class="form-label" for="requirements">Requirements <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
                    <textarea id="requirements" name="requirements"
                              class="form-control"
                              rows="4"
                              placeholder="Skills, experience, education requirements..."> <!--add code here--> </textarea>
                </div>

                <!-- Submit or go back to employer dashboard -->
                <div style="display:flex; gap:1rem;">
                    <button type="submit" class="btn btn-primary btn-lg">Post Job</button>

                    <!--add php code for links-->

                    <a href="#" class="btn btn-outline btn-lg">Cancel</a>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Load footer (closing HTML tags, scripts) -->
