<?php
ini_set('max_execution_time', 600); // 600 seconds = 10 minutes
ini_set('memory_limit', '512M');    // 512 MB

// Logging function
function logMessage($message, $level = 'INFO') {
    $logFile = __DIR__ . '/logs/scraper_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Check for AJAX request
$isAjax = isset($_GET['ajax']) || isset($_POST['ajax']);

// Check for silent mode via GET parameter or user agent
$silent = isset($_GET['silent']) || strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Wget') !== false || $isAjax;

if (!$silent && !$isAjax) {
    header("Content-Type: application/json");
}
// Optional (recommended for long scripts)
set_time_limit(300);

$params = [
    "keywords" => isset($_GET["keywords"]) ? $_GET["keywords"] : $_GET["jobTitle"] ?? "", // keywords or jobTitle
    "location" => isset($_GET["location"]) ? $_GET["location"] : "United States", // location or geoId
    "postedWithin" => isset($_GET["postedWithin"]) ? $_GET["postedWithin"] : "", // postedWithin or time
    "experience" => isset($_GET["experience"]) ? $_GET["experience"] : "", // experience or f_E
    "under10Applicants" => isset($_GET["under10Applicants"]) ? $_GET["under10Applicants"] : "", // under10Applicants or f_AL
    "easyApply" => isset($_GET["easyApply"]) ? $_GET["easyApply"] : "", // easyApply or f_EA
    "jobType" => isset($_GET["jobType"]) ? $_GET["jobType"] : "", // jobType or f_JT
    "remote" => isset($_GET["remote"]) ? $_GET["remote"] : "", // remote or f_WT
    "companySize" => isset($_GET["size"]) ? $_GET["size"] : "", // companySize or f_C
];

// debug
// echo "Received parameters: ";
// echo json_encode($params) . "\n";
// die;

$maxJobs = isset($_GET['maxJobs']) ? (int)$_GET['maxJobs'] : 300;
$uniqueCompany = isset($_GET['uniqueCompany']);
$includePostedDate = isset($_GET['includePostedDate']);

require "scraper.php";

// ✅ Rate limit (basic) - skip for silent mode
if (!$silent) {
    session_start();
    if (!isset($_SESSION['last_request'])) {
        $_SESSION['last_request'] = time();
    } else {
        if (time() - $_SESSION['last_request'] < 10) { // Longer delay for full scrape
            $errorMsg = "Too many requests. Please wait 10 seconds between full scrapes.";
            logMessage($errorMsg, 'WARNING');
            if (!$silent) {
                echo json_encode(["error" => $errorMsg]);
            }
            exit;
        }
        $_SESSION['last_request'] = time();
    }
}

$startTime = microtime(true);
$keywords = $params['keywords'];

if (empty($keywords)) {
    $errorMsg = "No keywords provided for scraping";
    logMessage($errorMsg, 'ERROR');
    if (!$silent) {
        echo json_encode(["error" => $errorMsg]);
    }
    exit;
}

if (!$silent) {
    echo "Starting scrape for keywords: $keywords (Max: $maxJobs jobs)\n";
    flush();
}

logMessage("Starting scrape for keywords: $keywords (Max: $maxJobs jobs)");

try {
    $jobs = fetchAllJobs($params, $maxJobs);
    $jobCount = is_array($jobs) ? count($jobs) : 0;

    if (!$silent) {
        echo "Found $jobCount jobs\n";
        flush();
    }

    logMessage("Found $jobCount jobs");
    $data = is_array($jobs) ? $jobs : [];

} catch (Throwable $e) {
    $errorMsg = "Error scraping: " . $e->getMessage();
    logMessage($errorMsg, 'ERROR');
    if (!$silent) {
        echo "$errorMsg\n";
        flush();
    }
    $data = ["error" => $errorMsg];
}

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

// Save all jobs to a single file
// $outputFile = __DIR__ . "/cache/all_jobs_" . date("Y-m-d_H-i-s") . ".json";
// file_put_contents($outputFile, json_encode($data, JSON_PRETTY_PRINT));

// $result = [
//     "success" => !isset($data["error"]),
//     "total_jobs" => isset($data["error"]) ? 0 : count($data),
//     "keywords" => $keywords,
//     "max_jobs_limit" => $maxJobs,
//     "params" => $params,
//     "output_file" => basename($outputFile),
//     "execution_time" => $duration,
//     "timestamp" => date("Y-m-d H:i:s"),
//     "message" => isset($data["error"]) ? $data["error"] : "Jobs scraped and saved to " . basename($outputFile)
// ];

// if (isset($data["error"])) {
//     logMessage("Scrape failed: " . $data["error"], 'ERROR');
// } else {
//     logMessage("Scrape completed. Keywords: $keywords, Jobs: " . count($data) . ", File: " . basename($outputFile) . ", Time: {$duration}s");
// }

// if (!$silent) {
//     echo json_encode($result, JSON_PRETTY_PRINT);
// }

// Final response
$response = [
    "success" => !isset($data["error"]),
    "total_jobs" => isset($data["error"]) ? 0 : count($data),
    "keywords" => $keywords,
    "max_jobs_limit" => $maxJobs,
    "params" => $params,
    "execution_time" => $duration,
    "timestamp" => date("Y-m-d H:i:s"),
    "message" => isset($data["error"]) ? $data["error"] : "Jobs scraped successfully"
];
    
echo json_encode($response, JSON_PRETTY_PRINT);
?>