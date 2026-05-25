<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Job Search & Fetch</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #2d3748;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .header {
            /* background: linear-gradient(135deg, #0077b5 0%, #3182ce 100%); */
            background: #0077b5;
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2.8rem;
            margin-bottom: 15px;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container {
            padding: 50px 70px;
        }

        .form-section {
            margin-bottom: 40px;
            padding: 30px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .form-section h2 {
            font-size: 1.5rem;
            color: #0077b5;
            margin-bottom: 25px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section h2::before {
            content: '📋';
            font-size: 1.8rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #4a5568;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label::before {
            content: '•';
            color: #3182ce;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: #2d3748;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 4px rgba(49, 130, 206, 0.1);
            transform: translateY(-1px);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
            padding: 15px;
            background: white;
            border: 1px solid #e2e8f0;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #3182ce;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
            color: #4a5568;
        }

        .company-size-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .company-size-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
            flex: 1 1 auto;
            min-width: 180px;
        }

        .company-size-option:hover {
            background: #f8fafc;
            border-color: #cbd5e0;
        }

        .company-size-option input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #3182ce;
        }

        .company-size-option span {
            font-size: 0.9rem;
            color: #4a5568;
        }

        .actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 16px 35px;
            border: none;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-align: center;
            min-width: 160px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(49, 130, 206, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(49, 130, 206, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: white;
            color: #4a5568;
            border: 2px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .results {
            margin-top: 40px;
            padding: 30px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .results h3 {
            color: #0077b5;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .status {
            padding: 15px;
            margin-bottom: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status.loading {
            background: #fef5e7;
            color: #92400e;
            border: 1px solid #f59e0b;
        }

        .status.success {
            background: #f0fff4;
            color: #22543d;
            border: 1px solid #48bb78;
        }

        .status.error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #f56565;
        }

        .job-count {
            text-align: center;
            font-size: 1.4rem;
            color: #2d3748;
            margin: 25px 0;
            font-weight: 600;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f4f6;
            border-top-color: #667eea;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Advanced Job Search</h1>
            <p>Fetch targeted job listings with precise filters</p>
        </div>

        <div class="form-container">
            <form id="jobSearchForm">
                <div class="form-section">
                    <h2>Search Criteria</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="jobTitle">Job Title</label>
                            <input type="text" id="jobTitle" name="jobTitle" placeholder="e.g., Software Engineer, Data Analyst" required>
                        </div>

                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" placeholder="e.g., United States, California, Remote" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Filters</h2>
                    <div class="form-grid">

                        <!-- Select box Posted Within -->
                        <div class="form-group">
                            <label for="postedWithin">Posted Within</label>
                            <select id="postedWithin" name="postedWithin">
                                <option value="">Any Time</option>
                                <option value="r86400">Last 24 hours</option>
                                <option value="r604800">Last 7 days</option>
                                <option value="r2592000">Last 30 days</option>
                                <option value="r7776000">Last 90 days</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="experience">Experience Level</label>
                            <select id="experience" name="experience">
                                <option value="">Any Experience</option>
                                <option value="1">Internship</option>
                                <option value="2">Entry level</option>
                                <option value="3">Associate</option>
                                <option value="4">Mid-Senior level</option>
                                <option value="5">Director</option>
                                <option value="6">Executive</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="under10Applicants">Under 10 Applicants</label>
                            <div class="checkbox-group">
                                <input type="checkbox" id="under10Applicants" name="under10Applicants" value="1">
                                <span>Yes</span>
                            </div>
                        </div>
                       
                         <div class="form-group">
                            <label for="easyApply">Easy Apply only</label>
                            <div class="checkbox-group">
                                <input type="checkbox" id="easyApply" name="easyApply" value="1">
                                <span>Yes</span>
                            </div>
                        </div>

                        <!-- Select box Job Type -->
                        <div class="form-group">
                            <label for="jobType">Job Type</label>
                            <select id="jobType" name="jobType">
                                <option value="">Any Job Type</option>
                                <option value="F">Full-time</option>
                                <option value="P">Part-time</option>
                                <option value="C">Contract</option>
                                <option value="T">Temporary</option>
                                <option value="V">Volunteer</option>
                                <option value="I">Internship</option>
                            </select>
                        </div>

                        <!-- Select box Work Mode -->
                        <div class="form-group">
                            <label for="remote">Work Mode</label>
                            <select id="remote" name="remote">
                                <option value="">Any Work Mode</option>
                                <option value="1">Remote</option>
                                <option value="2">On-site</option>
                                <option value="3">Hybrid</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Company Size</label>
                            <div class="company-size-grid">
                                <div class="company-size-option">
                                    <input type="checkbox" name="companySize[]" value="1-10">
                                    <span>1-10 employees</span>
                                </div>
                                <div class="company-size-option">
                                    <input type="checkbox" name="companySize[]" value="11-50">
                                    <span>11-50 employees</span>
                                </div>
                                <div class="company-size-option">
                                    <input type="checkbox" name="companySize[]" value="51-200">
                                    <span>51-200 employees</span>
                                </div>
                                <div class="company-size-option">
                                    <input type="checkbox" name="companySize[]" value="201-500">
                                    <span>201-500 employees</span>
                                </div>
                                <div class="company-size-option">
                                    <input type="checkbox" name="companySize[]" value="501-1,000">
                                    <span>501-1,000 employees</span>
                                </div>
                                <div class="company-size-option">
                                    <input type="checkbox" name="companySize[]" value="1,001-5,000">
                                    <span>1,001-5,000 employees</span>
                                </div>
                                <div class="company-size-option">
                                    <input type="checkbox" name="companySize[]" value="5,001-10,000">
                                    <span>5,001-10,000 employees</span>
                                </div>
                                <div class="company-size-option">
                                    <input type="checkbox" name="companySize[]" value="10,001+">
                                    <span>10,001+ employees</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Options</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="maxJobs">Maximum Jobs to Fetch</label>
                            <input type="number" id="maxJobs" name="maxJobs" value="10" min="10" max="300" placeholder="e.g., 50">
                        </div>
                    </div>

                    <div class="checkbox-group" style="display:none">
                        <input type="checkbox" id="uniqueCompany" name="uniqueCompany" checked>
                        <label for="uniqueCompany">Return only unique companies</label>
                    </div>

                    <div class="checkbox-group" style="display:none"> 
                        <input type="checkbox" id="includePostedDate" name="includePostedDate" checked>
                        <label for="includePostedDate">Include job posted date in results</label>
                    </div>

                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-primary" id="searchBtn">
                        🚀 Fetch Jobs
                    </button>
                    <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
                </div>
            </form>

            <div id="results" class="results hidden">
                <h3>Search Results</h3>
                <div id="status"></div>
                <div id="jobCount" class="job-count"></div>
                <div class="actions">
                    <button class="btn btn-primary" id="exportBtn" style="display: none;">📊 Export to Excel</button>
                    <button class="btn btn-secondary" id="filterBtn" style="display: none !important;">🔍 Advanced Filter</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('jobSearchForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const searchBtn = document.getElementById('searchBtn');
            const resultsDiv = document.getElementById('results');
            const statusDiv = document.getElementById('status');
            const jobCountDiv = document.getElementById('jobCount');
            const exportBtn = document.getElementById('exportBtn');
            const filterBtn = document.getElementById('filterBtn');

            // Get form data
            const formData = new FormData(this);
            const params = new URLSearchParams();

            for (let [key, value] of formData.entries()) {
                if (value !== '' && value !== null) {
                    params.append(key, value);
                }
            }

            // Add silent mode for AJAX
            params.append('ajax', '1');

            // Company size: collect all checked values as a comma-separated string
            const companySizeChecked = Array.from(document.querySelectorAll('input[name="companySize[]"]:checked')).map(cb => cb.value);
            if (companySizeChecked.length > 0) {
                params.append('size', companySizeChecked.join(','));
            }

            // Show loading state
            searchBtn.disabled = true;
            searchBtn.innerHTML = '<span class="loading-spinner"></span>Fetching Jobs...';
            resultsDiv.classList.remove('hidden');
            statusDiv.innerHTML = '<div class="status loading">🔄 Starting job search...</div>';
            jobCountDiv.textContent = '';
            exportBtn.style.display = 'none';
            filterBtn.style.display = 'none';

            try {
                // Make the request to scrape_all.php
                const response = await fetch('scrape_all.php?' + params.toString());
                const data = await response.json();

                // Update UI based on response
                if (data.success) {
                    statusDiv.innerHTML = '<div class="status success">✅ Job search completed successfully!</div>';
                    jobCountDiv.innerHTML = `
                        <strong>${data.filtered_jobs || data.total_jobs}</strong> jobs found<br>
                        <small>Search completed in ${data.execution_time}s</small>
                    `;
                    exportBtn.style.display = 'inline-block';
                    // filterBtn.style.display = 'inline-block';

                    // Store results for export
                    exportBtn.dataset.file = data.output_file;
                } else {
                    statusDiv.innerHTML = '<div class="status error">❌ Error: ' + (data.message || 'Unknown error occurred') + '</div>';
                    jobCountDiv.textContent = '';
                }

            } catch (error) {
                statusDiv.innerHTML = '<div class="status error">❌ Network error: ' + error.message + '</div>';
                jobCountDiv.textContent = '';
            } finally {
                searchBtn.disabled = false;
                searchBtn.innerHTML = '🚀 Fetch Jobs';
            }
        });

        // Export button handler
        document.getElementById('exportBtn').addEventListener('click', function() {
            const fileName = this.dataset.file;
            if (fileName) {
                window.location.href = 'export.php';
            }
        });

        // Filter button handler
        document.getElementById('filterBtn').addEventListener('click', function() {
            window.location.href = 'filter.php';
        });
    </script>
</body>
</html>