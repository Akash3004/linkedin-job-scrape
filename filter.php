<?php
// filter.php - Job Filter and Export Tool

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require 'scraper.php'; // Include scraper functions

// Load all jobs from cache
$files = glob("cache/*.json");
$allJobs = [];

foreach ($files as $file) {
    $data = json_decode(file_get_contents($file), true);
    if (isset($data['data']) && is_array($data['data'])) {
        $allJobs = array_merge($allJobs, $data['data']);
    } elseif (is_array($data)) {
        $allJobs = array_merge($allJobs, $data);
    }
}

// Filter unique jobs (optional - comment out if causing issues)
$uniqueJobs = [];
$seenUrls = [];
foreach ($allJobs as $job) {
    $url = $job['linkedin_url'] ?? $job['linkedin url'] ?? '';
    if (!empty($url) && !in_array($url, $seenUrls)) {
        $seenUrls[] = $url;
        $uniqueJobs[] = $job;
    } elseif (empty($url)) {
        // Include jobs without URLs
        $uniqueJobs[] = $job;
    }
}

// If no unique jobs, use all jobs
if (empty($uniqueJobs)) {
    $uniqueJobs = $allJobs;
}

// Handle form submission for Excel export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {

    // Get filter criteria from form
    $keywords = trim(strtolower($_POST['keywords'] ?? ''));
    $location = trim(strtolower($_POST['location'] ?? ''));
    $company = trim(strtolower($_POST['company'] ?? ''));
    $title = trim(strtolower($_POST['title'] ?? ''));

    // Filter jobs based on criteria (from cache only)
    $filteredJobs = array_filter($uniqueJobs, function($job) use ($keywords, $location, $company, $title) {
        // Check keywords in title, company, or positions
        if (!empty($keywords)) {
            $searchText = strtolower(($job['title'] ?? '') . ' ' . ($job['company'] ?? '') . ' ' . ($job['positions'] ?? ''));
            if (strpos($searchText, $keywords) === false) {
                return false;
            }
        }

        // Check location
        if (!empty($location)) {
            $jobLocation = strtolower($job['location'] ?? $job['locations'] ?? '');
            if (strpos($jobLocation, $location) === false) {
                return false;
            }
        }

        // Check company
        if (!empty($company)) {
            $jobCompany = strtolower($job['company'] ?? '');
            if (strpos($jobCompany, $company) === false) {
                return false;
            }
        }

        // Check title
        if (!empty($title)) {
            $jobTitle = strtolower($job['title'] ?? '');
            if (strpos($jobTitle, $title) === false) {
                return false;
            }
        }

        return true;
    });

    // Clean output buffer
    if (ob_get_length()) ob_end_clean();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="Filtered_Jobs_' . date('Y-m-d_H-i-s') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header
    $sheet->setCellValue('A1', 'SL NO.');
    $sheet->setCellValue('B1', 'COMPANY');
    $sheet->setCellValue('C1', 'COMPANY URL');
    $sheet->setCellValue('D1', 'TITLE');
    $sheet->setCellValue('E1', 'LOCATION');
    $sheet->setCellValue('F1', 'POSITIONS');
    $sheet->setCellValue('G1', 'URL');

    // Style header
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
    ];
    $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

    // Write filtered data
    $row = 2;
    $slNo = 1;
    foreach ($filteredJobs as $job) {
        $sheet->setCellValue('A' . $row, $slNo++);
        $sheet->setCellValue('B' . $row, $job['company'] ?? '');
        $sheet->setCellValue('C' . $row, $job['company_url'] ?? $job['company url'] ?? '');
        $sheet->setCellValue('D' . $row, $job['title'] ?? '');
        $sheet->setCellValue('E' . $row, $job['location'] ?? ($job['locations'] ?? ''));
        $sheet->setCellValue('F' . $row, $job['positions'] ?? '');
        $sheet->setCellValue('G' . $row, $job['linkedin_url'] ?? ($job['linkedin url'] ?? ''));
        $row++;
    }

    // Auto-size columns
    foreach (range('A', 'G') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Filter & Export Tool</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 600px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .stats {
            text-align: center;
            margin-top: 1rem;
            color: #666;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Job Filter & Export Tool</h1>
        <form method="post">
            <div class="form-group">
                <label for="keywords">Keywords (in title, company, or positions):</label>
                <input type="text" id="keywords" name="keywords" placeholder="e.g., Software Engineer, Google, Remote">
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" placeholder="e.g., United States, Remote">
            </div>
            <div class="form-group">
                <label for="company">Company:</label>
                <input type="text" id="company" name="company" placeholder="e.g., Google, Microsoft">
            </div>
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" placeholder="e.g., Software Engineer">
            </div>
            <button type="submit" name="export" class="btn">📊 Export Filtered Jobs to Excel</button>
        </form>
        <div class="stats">
            Total Jobs in Cache: <?php echo count($uniqueJobs); ?>
        </div>
        <a href="index.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>