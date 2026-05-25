<?php

$files = glob("cache/*.json");
$jobs = [];
$fileList = [];

foreach ($files as $file) {
    $data = json_decode(file_get_contents($file), true);
    if (isset($data['data']) && is_array($data['data'])) {
        $jobs = array_merge($jobs, $data['data']);
    } elseif (is_array($data)) {
        // For all_jobs files
        $jobs = array_merge($jobs, $data);
    }
    $basename = basename($file);
    // Shorten name
    if (preg_match('/all_jobs_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})\.json/', $basename, $matches)) {
        $shortName = 'All Jobs ' . $matches[1] . ' ' . str_replace('-', ':', $matches[2]);
    } else {
        $shortName = substr($basename, 0, 20) . (strlen($basename) > 20 ? '...' : '');
    }
    $fileList[] = ['short' => $shortName, 'full' => $basename];
}

// Filter unique jobs based on linkedin_url
$uniqueJobs = [];
$seenUrls = [];
foreach ($jobs as $job) {
    $url = $job['linkedin_url'] ?? '';
    if (!empty($url) && !in_array($url, $seenUrls)) {
        $seenUrls[] = $url;
        $uniqueJobs[] = $job;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkedIn Job Scraper Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }
        .header {
            background-color: #0077b5;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header h1 {
            font-size: 1.5rem;
        }
        .header-actions {
            display: flex;
            gap: 1rem;
        }
        .export-btn {
            background-color: #005885;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .export-btn.danger {
            background-color: #dc3545;
        }
        .export-btn.danger:hover {
            background-color: #c82333;
        }
        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        .sidebar {
            width: 250px;
            background-color: #fff;
            border-right: 1px solid #ddd;
            padding: 1rem;
            overflow-y: auto;
            transition: transform 0.3s;
        }
        .sidebar.collapsed {
            transform: translateX(-250px);
        }
        .sidebar h3 {
            margin-bottom: 1rem;
            color: #0077b5;
        }
        .file-list {
            list-style: none;
        }
        .file-list li {
            padding: 0.5rem;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .file-list li:hover {
            background-color: #f9f9f9;
        }
        .toggle-btn {
            position: fixed;
            top: 50%;
            left: 10px;
            background-color: #0077b5;
            color: white;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            z-index: 1000;
            transition: left 0.3s;
        }
        .sidebar.collapsed ~ .toggle-btn {
            left: 10px;
        }
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        .stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1;
            text-align: center;
        }
        .stat-card h3 {
            color: #0077b5;
            margin-bottom: 0.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #0077b5;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .no-data {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                position: fixed;
                top: 80px;
                left: 0;
                height: calc(100vh - 80px);
                z-index: 999;
            }
            .toggle-btn {
                display: block;
            }
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }
        .modal-content {
            background-color: #000;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-height: 80%;
            overflow-y: auto;
            color: #fff;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #fff;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>LinkedIn Job Scraper Dashboard</h1>
    <div class="header-actions">
        <a href="fetch_jobs.php" class="export-btn">🔍 Advanced Search</a>
        <a href="filter.php" class="export-btn" style="display:none">Filter & Export</a>
        <a href="export.php" class="export-btn">Export All</a>
        <button class="export-btn danger" onclick="flushCache()">🗑️ Flush Cache</button>
    </div>
</div>

<div class="container">
    <aside class="sidebar" id="sidebar">
        <h3>Cached Files</h3>
        <ul class="file-list">
            <?php foreach ($fileList as $file): ?>
                <li onclick="showJson('<?php echo htmlspecialchars($file['full']); ?>')"><?php echo htmlspecialchars($file['short']); ?></li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <button class="toggle-btn" id="toggleBtn">☰</button>

    <main class="main-content">
        <div class="stats">
            <div class="stat-card">
                <h3><?php echo count($files); ?></h3>
                <p>Files</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($uniqueJobs); ?></h3>
                <p>Unique Jobs</p>
            </div>
        </div>

        <?php if (empty($uniqueJobs)): ?>
            <div class="no-data">
                <h2>No Jobs Found</h2>
                <p>Please run the scraper to collect job data.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>SL No.</th>
                        <th>Company</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>URL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $slNo = 1; foreach ($uniqueJobs as $job): ?>
                        <tr>
                            <td><?php echo $slNo++; ?></td>
                            <td><?php echo htmlspecialchars($job['company'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($job['title'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($job['location'] ?? 'N/A'); ?></td>
                            <td><a href="<?php echo htmlspecialchars($job['linkedin_url'] ?? '#'); ?>" target="_blank">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</div>

<!-- JSON Preview Modal -->
<div id="jsonModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <pre id="jsonContent"></pre>
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
    });

    // JSON preview modal
    function showJson(filename) {
        fetch('cache/' + filename)
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    document.getElementById('jsonContent').textContent = JSON.stringify(data, null, 2);
                } catch (e) {
                    document.getElementById('jsonContent').textContent = 'Invalid JSON: ' + text;
                }
                document.getElementById('jsonModal').style.display = 'block';
            })
            .catch(error => {
                document.getElementById('jsonContent').textContent = 'Error loading file: ' + error;
                document.getElementById('jsonModal').style.display = 'block';
            });
    }

    // Close modal
    document.querySelector('.close').addEventListener('click', () => {
        document.getElementById('jsonModal').style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        if (event.target == document.getElementById('jsonModal')) {
            document.getElementById('jsonModal').style.display = 'none';
        }
    });

    // Flush cache function
    function flushCache() {
        if (confirm('Are you sure you want to delete all cached JSON files? This action cannot be undone.')) {
            fetch('flush_cache.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cache flushed successfully! Page will reload.');
                    location.reload();
                } else {
                    alert('Error flushing cache: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    }
</script>

</body>
</html>