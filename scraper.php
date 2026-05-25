<?php
function fetchJobs( $params ) {
    $config = require "config.php";

    $queryParams = [
        "keywords" => $params["keywords"] ?? "",
        "location" => $params["location"] ?? "United States",
        "start" => $params["start"] ?? 0
    ];

    // Optional filters
    // if ( !empty( $params["time"] ) ) {
    //     $timeValue = $params["time"];
    //     // If it's a number (days), convert to LinkedIn format r{seconds}
    //     if (is_numeric($timeValue)) {
    //         $timeValue = 'r' . ($timeValue * 86400);
    //     }
    //     $queryParams["f_TPR"] = $timeValue;
    // }

    if ( !empty( $params['postedWithin'] ) ) {
        $timeValue = $params['postedWithin'];
        if (is_numeric($timeValue)) {
            $timeValue = 'r' . ($timeValue * 86400);
        }
        $queryParams["f_TPR"] = $timeValue;
    }

    if ( !empty( $params["experience"] ) ) {
        $queryParams["f_E"] = $params["experience"];
    }

    if ( !empty( $params["under10Applicants"] ) ) {
        $queryParams["f_JIYN"] = $params["under10Applicants"];
    }

    if ( !empty( $params["easyApply"] ) ) {
        $queryParams["f_AL"] = $params["easyApply"];
    }

    if ( !empty( $params["jobType"] ) ) {
         $queryParams["f_JT"] = $params["jobType"];
    }

    if ( !empty( $params["remote"] ) ) {
        $queryParams["f_WT"] = $params["remote"];
    }

    // if ( !empty( $params["size"] ) ) {
    //     $queryParams["size"] = $params["size"];
    // }

    if ( !empty( $params["sortBy"] ) ) {
        $queryParams["sortBy"] = $params["sortBy"];
    }

    $query = http_build_query( $queryParams );

    $cacheKey = md5( $query );
    $cacheFile = __DIR__ . "/cache/$cacheKey.json";

    // ✅ Cache check
    if ( file_exists( $cacheFile ) && ( time() - filemtime( $cacheFile ) < $config["cache_time"] ) ) {
        return json_decode( file_get_contents( $cacheFile ), true );
    }

    $url = $config["base_url"] . "?" . $query;
    $html = makeRequest( $url, $config );

    if ( !$html ) {
        return ["error" => "Failed to fetch data"];
    }

    $data = parseJobs( $html );

    // ✅ Save cache
    file_put_contents( $cacheFile, json_encode( $data ) );

    return $data;
}

function fetchAllJobs( $params, $maxJobs = 300 ) {
    $config = require "config.php";
    $allJobs = [];
    $start = 0;
    $pageSize = 10; // LinkedIn seeMore API returns 10 jobs per page

    // Remove start from params as we'll handle pagination
    $baseParams = $params;
    unset($baseParams["start"]);

    while (true) {
        // Check if we've reached the job limit
        if (count($allJobs) >= $maxJobs) {
            break;
        }

        $params["start"] = $start;
    
        $queryParams = [
            "keywords" => $baseParams["keywords"] ?? "",
            "location" => $baseParams["location"] ?? "United States",
            "start" => $start
        ];

        // Optional filters
        // if ( !empty( $baseParams["time"] ) ) {
        //     $timeValue = $baseParams["time"];
        //     // If it's a number (days), convert to LinkedIn format r{seconds}
        //     if (is_numeric($timeValue)) {
        //         $timeValue = 'r' . ($timeValue * 86400);
        //     }
        //     $queryParams["f_TPR"] = $timeValue;
        // }
        
        if ( !empty( $baseParams['postedWithin'] ) ) {
            $timeValue = $baseParams['postedWithin'];
            if (is_numeric($timeValue)) {
                $timeValue = 'r' . ($timeValue * 86400);
            }
            $queryParams["f_TPR"] = $timeValue;
        }

        if ( !empty( $baseParams["experience"] ) ) {
            $queryParams["f_E"] = $baseParams["experience"];
        }

        if ( !empty( $baseParams["under10Applicants"] ) ) {
            $queryParams["f_JIYN"] = $baseParams["under10Applicants"];
        }

        if ( !empty( $baseParams["easyApply"] ) ) {
            $queryParams["f_AL"] = $baseParams["easyApply"];
        }

        if ( !empty( $baseParams["jobType"] ) ) {
             $queryParams["f_JT"] = $baseParams["jobType"];
        }

        if ( !empty( $baseParams["remote"] ) ) {
            $queryParams["f_WT"] = $baseParams["remote"];
        }

        if ( !empty( $baseParams["companySize"] ) ) {
            // &size=1-10%2C11-50
            $queryParams["size"] = $baseParams["companySize"];
        }

        if ( !empty( $baseParams["sortBy"] ) ) {
            $queryParams["sortBy"] = $baseParams["sortBy"];
        }

        $query = http_build_query( $queryParams );
        $cacheKey = md5( $query );
        $cacheFile = __DIR__ . "/cache/$cacheKey.json";

        // Check cache first
        if ( file_exists( $cacheFile ) && ( time() - filemtime( $cacheFile ) < $config["cache_time"] ) ) {
            $data = json_decode( file_get_contents( $cacheFile ), true );
        } else {
            
            $url = $config["base_url"] . "?" . $query;
            $html = makeRequest( $url, $config );

            if ( !$html ) {
                break; // Stop if request fails
            }

            $data = parseJobs( $html );

            // Filter based on the companies size by param size
            // if ( !empty( $baseParams["size"] ) && is_array( $data ) ) {
            //     $sizeFilter = $baseParams["size"];
            //     $data = array_filter( $data, function( $job ) use ( $sizeFilter ) {
            //         if ( empty( $job["company_size"] ) ) {
            //             return false; // Exclude if company size is unknown
            //         }
            //         return strpos( $sizeFilter, $job["company_size"] ) !== false;
            //     });
            // }

            // Save cache
            file_put_contents( $cacheFile, json_encode( $data ) );
        }

        // If no jobs returned, we've reached the end
        if ( empty( $data ) || isset( $data["error"] ) ) {
            break;
        }

        // Add jobs but respect the limit
        $remainingSlots = $maxJobs - count($allJobs);
        if (count($data) > $remainingSlots) {
            $data = array_slice($data, 0, $remainingSlots);
        }

        $allJobs = array_merge( $allJobs, $data );

        // If we got fewer jobs than page size, we've reached the end
        if ( count( $data ) < $pageSize ) {
            break;
        }

        $start += $pageSize;

        // Add a small delay to be respectful to the server
        sleep(rand(2,5));
    }
    
    // failure
    if (empty($allJobs)) {
        return ["error" => "Failed to fetch any jobs"];
    }

    return $allJobs;
}

function makeRequest( $url, $config ) {
    $maxRetries = 3;
    $retryDelay = 5; // seconds

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $ch = curl_init();

        curl_setopt_array( $ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $config["user_agent"],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $config["timeout"],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => $config["cacert"],
            CURLOPT_HTTPHEADER => [
                "Accept: text/html",
                "Connection: keep-alive"
            ]
        ] );

        $response = curl_exec( $ch );
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close( $ch );

        if ($curlError) {
            echo "CURL ERROR (attempt $attempt/$maxRetries): " . $curlError . "\n";
            if ($attempt < $maxRetries) {
                sleep($retryDelay * $attempt);
                continue;
            }
            return false;
        }

        if ($httpCode == 524 || $httpCode >= 500) {
            echo "Server error $httpCode (attempt $attempt/$maxRetries), retrying...\n";
            if ($attempt < $maxRetries) {
                sleep($retryDelay * $attempt);
                continue;
            }
            return false;
        }

        return $response;
    }

    return false;
}

function parseJobs( $html ) {
    $dom = new DOMDocument();
    libxml_use_internal_errors( true );
    $dom->loadHTML( $html );

    $xpath = new DOMXPath( $dom );

    $jobs = [];

    // Find all job cards - they are div elements with job-search-card class
    $jobCards = $xpath->query( "//div[contains(@class, 'job-search-card')]" );

    foreach ( $jobCards as $card ) {
        /**
         * comapny
         * company url
         * title
         * positions
         * location
         * linkedin url
         * source
         */

        // Get title from h3
        $titleNode = $xpath->query( ".//h3[@class='base-search-card__title']", $card )->item( 0 );
        $title = trim( $titleNode?->textContent ?? "" );

        if ( !$title ) continue;

        // Get company from h4
        $companyNode = $xpath->query( ".//h4[@class='base-search-card__subtitle']", $card )->item( 0 );
        $company = trim( $companyNode?->textContent ?? "" );

        // Get company URL
        $companyLinkNode = $xpath->query(".//h4[contains(@class,'base-search-card__subtitle')]//a", $card)->item(0);
        $companyUrl = $companyLinkNode?->getAttribute("href");                
        
        // Get postions from span with class job-search-card__insight
        $positionsNode = $xpath->query( ".//span[contains(@class, 'job-search-card__insight')]", $card )->item( 0 );
        $positions = trim( $positionsNode?->textContent ?? "" );

        // Get location
        $locationNode = $xpath->query( ".//span[contains(@class, 'job-search-card__location')]", $card )->item( 0 );
        $location = trim( $locationNode?->textContent ?? "" );

        // Get link
        $linkNode = $xpath->query( ".//a[@class='base-card__full-link']", $card )->item( 0 );
        $link = $linkNode?->getAttribute( "href" );
        $link = cleanUrl( $link );

        // Get linkedin url from data-entity-urn attribute of the card
        $entityUrn = $card->getAttribute( "data-entity-urn" );
        if ( $entityUrn ) {
            $parts = explode( ":", $entityUrn );
            $jobId = end( $parts );
            $link = "https://www.linkedin.com/jobs/view/$jobId/";
        }

        // scrape company size from the company page if company url is available
        $companySize = "";
        if ( $companyUrl ) {
            $companyHtml = makeRequest( $companyUrl, require "config.php" );
            if ( $companyHtml ) {
                $companyDom = new DOMDocument();
                libxml_use_internal_errors( true );
                $companyDom->loadHTML( $companyHtml );
                $companyXpath = new DOMXPath( $companyDom );

                // Get company size from span with class org-top-card-summary-info-list__info-item
                // More robust XPath to find the employee count
                $sizeNode = $companyXpath->query("//div[contains(@class, 'company-details')]//dd[contains(., 'employees')] | //span[contains(., 'employees')]")->item(0);
                if ( $sizeNode ) {
                    $companySize = trim( $sizeNode->textContent );
                }
            }
        }

        $jobs[] = [
            "company" => $company,
            "company_size" => $companySize,
            "company_url" => $companyUrl,
            "title" => $title,
            "positions" => $positions,    
            "location" => $location,
            "linkedin_url" => $link,
            "source" => "linkedin"
        ];
    }

    return $jobs;
}

function cleanUrl( $url ) {
    if (!is_string($url) || $url === null) {
        return '';
    }
    return explode( "?", $url )[0];
}