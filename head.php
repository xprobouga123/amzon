<?php

function isBot($userAgent, $ipAddress) {
    // Bot user agents to block
    $bots = [
        '/bot|crawl|slurp|spider/i',
        '/.*\.microsoft\.com$/i',
        '/.*\.rzone\.de$/i',
        '/.*\.datacamp\.sk$/i',
        '/.*\.barracuda\.com$/i',
        '/.*\.akamaitechnologies\.com$/i',
        '/.*\.as54203\.net$/i',
        '/.*\.amazonaws\.com$/i',
        '/.*\.clients.your-server\.de$/i',
        '/.*\.m247\.com$/i',
        '/.*\.cdn77\.com$/i',
        '/.*\.leakix\.org$/i',
        '/.*\.colocrossing\.com$/i',
        '/.*\.linode\.com$/i',
        '/.*\.ovh\.net$/i',
        '/.*\.googleusercontent\.com$/i'
    ];

    // Check user agent
    foreach ($bots as $pattern) {
        if (preg_match($pattern, $userAgent)) {
            return true;
        }
    }

    // Check IP address for cache.google.com
    if (gethostbyaddr($ipAddress) === 'cache.google.com') {
        return true;
    }

    return false;
}

// Retrieve visitor's IP
$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// Check if the visitor is a bot
if (isBot($userAgent, $ip)) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

// Use ipinfo.io to get location info
$token = "a669e756eeab82"; // Your IPinfo.io token
$url = "https://ipinfo.io/{$ip}?token={$token}";

$response = @file_get_contents($url);
if ($response === false) {
    // Handle error appropriately
    $location = (object) ['country' => 'Unknown', 'org' => 'Unknown'];
} else {
    $location = json_decode($response);
}

// Block visitors not from specific countries or specific organizations
$allowedCountries = ["MA", "DE"];
$blockedOrganizations = ["Microsoft Corporation", "Amazon.com, Inc.", "DigitalOcean, LLC"];
$status = in_array($location->country ?? 'Unknown', $allowedCountries) && !in_array($location->org ?? 'Unknown', $blockedOrganizations) ? "authorized" : "blocked";

// Log details
$log = date("Y-m-d H:i:s") . "\t" . $ip . "\t" . ($location->country ?? 'Unknown') . "\t" . ($location->org ?? 'Unknown') . "\t" . $status . "\n";
file_put_contents("log.txt", $log, FILE_APPEND);

// Block or allow access
if ($status == "authorized") {
    // Logic to display the page
} else {
    header("HTTP/1.1 403 Forbidden");
    exit();
}
?>