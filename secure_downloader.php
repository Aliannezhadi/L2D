<?php
/**
 * Secure File Downloader (Full Version)
 *
 * Usage in Browser:
 *   secure_downloader.php?url=https://example.com/file.zip
 *
 * Usage in CLI:
 *   php secure_downloader.php "https://example.com/file.zip"
 *
 * --------------------------------------------------------------
 *  SECURITY FEATURES:
 *  ✔ URL validation
 *  ✔ Only HTTP/HTTPS allowed
 *  ✔ Optional allowed host restriction
 *  ✔ Safe filename handling
 *  ✔ Size limitation
 *  ✔ Prevent directory traversal
 *  ✔ No follow redirects (prevents malicious redirects)
 *  ✔ cURL with SSL verification
 *  ✔ CLI + Browser support
 * --------------------------------------------------------------
 */


/* ================== CONFIG ================== */

// Download directory ( MUST be writable )
const DOWNLOAD_DIR = __DIR__ . '/downloads';

// Max size allowed (50MB)
const MAX_FILE_SIZE_BYTES = 50 * 1024 * 1024;

// Allowed schemes
const ALLOWED_SCHEMES = ['http', 'https'];

// Allowed hosts (empty = allow all)
const ALLOWED_HOSTS = []; 
// Example: ['example.com', 'cdn.site.com'];

/* ================= END CONFIG =============== */


/** Exit safely */
function exit_with_message(string $message, int $code = 1): void
{
    if (php_sapi_name() === 'cli') {
        fwrite(STDERR, "[ERROR] $message" . PHP_EOL);
    } else {
        http_response_code(400);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
    exit($code);
}


/** Get URL from CLI or GET */
function get_url_input(): string
{
    if (php_sapi_name() === 'cli') {
        global $argv;
        if (!isset($argv[1])) {
            exit_with_message("No URL provided. Usage: php secure_downloader.php \"https://example.com/file.zip\"");
        }
        return trim($argv[1]);
    }

    if (!isset($_GET['url']) || empty($_GET['url'])) {
        exit_with_message("No URL provided. Use ?url=https://example.com/file.zip");
    }

    return trim($_GET['url']);
}


/** Validate the URL */
function validate_url(string $url): string
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        exit_with_message("Invalid URL.");
    }

    $parts = parse_url($url);

    if (!$parts) {
        exit_with_message("Cannot parse URL.");
    }

    // Scheme check
    $scheme = strtolower($parts['scheme'] ?? '');
    if (!in_array($scheme, ALLOWED_SCHEMES, true)) {
        exit_with_message("Only http/https URLs allowed.");
    }

    // Host restriction
    if (!empty(ALLOWED_HOSTS)) {
        $host = strtolower($parts['host'] ?? '');
        if (!in_array($host, ALLOWED_HOSTS, true)) {
            exit_with_message("Host is not allowed.");
        }
    }

    return $url;
}


/** Generate safe filename */
function get_safe_filename_from_url(string $url): string
{
    $path = parse_url($url, PHP_URL_PATH);
    $basename = basename($path);

    if (!$basename || $basename === '/' || $basename === '.') {
        $basename = 'file_' . date('Ymd_His');
    }

    // Remove dangerous characters
    $basename = preg_replace('/[^A-Za-z0-9._-]/', '_', $basename);

    // Avoid ".htaccess"
    if ($basename[0] === '.') {
        $basename = 'file' . $basename;
    }

    return $basename;
}


/** Ensure download directory exists */
function ensure_download_dir(): string
{
    if (!is_dir(DOWNLOAD_DIR)) {
        mkdir(DOWNLOAD_DIR, 0755, true);
    }

    $realPath = realpath(DOWNLOAD_DIR);

    if (!$realPath) {
        exit_with_message("Download directory error.");
    }

    if (!is_writable($realPath)) {
        exit_with_message("Download directory is not writable.");
    }

    return $realPath;
}


/** Download file using cURL with size limit */
function download_file(string $url, string $destination): void
{
    $fp = fopen($destination, 'wb');
    if (!$fp) {
        exit_with_message("Cannot open output file.");
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_USERAGENT => 'L2DDownloader/1.0',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_NOPROGRESS => false,

        // size checker
        CU
