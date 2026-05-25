<?php
header( "Content-Type: application/json" );
require "scraper.php";

function parseLinkedInUrl( $linkUrl ) {
    $parts = parse_url( $linkUrl );
    if ( empty( $parts['query'] ) ) {
        return [];
    }

    parse_str( $parts['query'], $q );

    $mapped = [];

    if ( !empty( $q['keywords'] ) ) {
        $mapped['keywords'] = $q['keywords'];
    }
    if ( !empty( $q['location'] ) ) {
        $mapped['location'] = $q['location'];
    }
    if ( !empty( $q['f_TPR'] ) ) {
        $mapped['time'] = $q['f_TPR'];
    } elseif ( !empty( $q['time'] ) ) {
        $mapped['time'] = $q['time'];
    }
    if ( !empty( $q['f_E'] ) ) {
        $mapped['experience'] = $q['f_E'];
    }
    if ( !empty( $q['f_JT'] ) ) {
        $mapped['jobType'] = $q['f_JT'];
    }
    if ( !empty( $q['f_WT'] ) ) {
        $mapped['remote'] = $q['f_WT'];
    }
    if ( !empty( $q['sortBy'] ) ) {
        $mapped['sortBy'] = $q['sortBy'];
    }
    if ( !empty( $q['distance'] ) ) {
        $mapped['distance'] = $q['distance'];
    }
    if ( isset( $q['start'] ) ) {
        $mapped['start'] = (int)$q['start'];
    }

    return $mapped;
}

// ✅ Rate limit (basic)
session_start();
if ( !isset( $_SESSION['last_request'] ) ) {
    $_SESSION['last_request'] = time();
} else {
    if ( time() - $_SESSION['last_request'] < 2 ) {
        echo json_encode( [ "error" => "Too many requests" ] );
        exit;
    }
    $_SESSION['last_request'] = time();
}

$urlParam = $_GET['url'] ?? '';
if ( $urlParam ) {
    $linkedInParams = parseLinkedInUrl( $urlParam );
    $_GET = array_merge( $_GET, $linkedInParams );
}

$params = [
    "keywords" => $_GET["keywords"] ?? "mechanical engineer",
    "location" => $_GET["location"] ?? "United States",
    "time" => $_GET["time"] ?? "",
    "experience" => $_GET["experience"] ?? "",
    "jobType" => $_GET["jobType"] ?? "",
    "remote" => $_GET["remote"] ?? "",
    "sortBy" => $_GET["sortBy"] ?? "",
    "distance" => $_GET["distance"] ?? "",
    "start" => (int)($_GET["start"] ?? 0)
];

$data = fetchAllJobs( $params );

echo json_encode( [
    "success" => !isset( $data["error"] ),
    "total" => count( $data ),
    "params" => $params,
    "data" => $data
], JSON_PRETTY_PRINT );

?>