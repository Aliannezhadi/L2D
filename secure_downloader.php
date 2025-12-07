<?php
/**
 * L2D – Secure File Downloader with Token & Logging
 *
 * Browser:
 *   secure_downloader.php?url=https://example.com/file.zip&token=YOUR_TOKEN
 *
 * CLI:
 *   L2D_TOKEN=YOUR_TOKEN php secure_downloader.php "https://example.com/file.zip"
 *
 * SECURITY:
 *  - URL validation + scheme check
 *  - Optional host allow-list
 *  - Safe filename
 *  - Max file size limit
 *  - Token protection (web + CLI)
 *  - Logging (success + error)
 */


/* ================== CONFIG ================== */

// Download directory (must be writable)
const DOWNLOAD_DIR         = __DIR__ . '/downloads';

// Max size allowed (50MB)
const MAX_FILE_SIZE_BYTES  = 50 * 1024 * 1024;

// Allowed schemes
const ALLOWED_SCHEMES      = ['http', 'https'];

// Allowed hosts (empty = allow all)
const ALLOWED_HOSTS        = []; // e.g. ['example.com', 'cdn.site.com']

// Security token (CHANGE THIS!)
const SECURITY_TOKEN       = 'CHANGE_ME_TO_A_LONG_RANDOM_TOKEN';

// Enable token checks
const ENABLE_TOKEN_WEB     = true;
const ENABLE_TOKEN_CLI     = true;

// Logging
const ENABLE_LOGGING       = true;
const LOG_FILE             = __DIR__ . '/l2d.log';

/* =============== END CONFIG ================= */


// will store current URL globally for logging
$GLOBALS['L2D_CURRENT_URL'] = '';


/** Logging helper */
function log_event(string $status, string $message, string $url = '', string $extra = ''): void
{
    if (!ENABLE_LOGGING) {
        return;
    }

    $context = php_sapi_name() === 'cli'
        ? 'CLI'
        : ($_SERVER['REMOTE_ADDR'] ?? 'WEB');

    $time = date('Y-m-d H:i:s');

    $line = sprintf(
        '[%s] [%s] [%s] URL="%s" EXTRA="%s" MESSAGE="%s"',
        $time,
        $status,
        $context,
        $url,
        $extra,
        $message
    );

    @file_put_contents(LOG_FILE, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}


/** Exit safely (and log error) */
function exit_with_message(string $message, int $code = 1): void
{
    $url = $GLOBALS['L2D_CURRENT_URL'] ?? '';
    log_event('ERROR', $message, $url);

    if (php_sapi_name() === 'cli') {
        fwrite(STDERR, "[ERROR] " . $message . PHP_EOL);
    } else {
        http_response_code(400);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
    exit($code);
}


/** Check security token for web & CLI */
function check_token(): void
{
    // CLI mode
    if (php_sapi_name() === 'cli') {
        if (!ENABLE_TOKEN_CLI) {
            return;
        }

        $envToken = getenv('L2D_TOKEN') ?: '';

        if (!is_string($envToken) || $envToken === '') {
            exit_with_message("Missing CLI token (use env L2D_TOKEN).");
        }

        if (!hash_equals(SECURITY_TOKEN, $envToken)) {
            exit_with_message("Invalid CLI token.");
        }

        return;
    }

    // Web mode
    if (!ENABLE_TOKEN_WEB) {
        return;
    }

    $token = $_GET['token'] ?? '';

    if (!is_string($token) || $token === '') {
        exit_with_message("Missing token.");
    }

    if (!hash_equals(SECURITY_TOKEN, $token)) {
        exit_with_message("Invalid token.");
    }
}


/** Get URL from CLI or GET */
function get_url_input(): string
{
    if (php_sapi_name() === 'cli') {
        global $argv;
        if (!isset($argv[1])) {
            exit_with_message(
                "No URL provided. Usage:\n" .
                "L2D_TOKEN=YOUR_TOKEN php secure_downloader.php \"https://example.com/file.zip\""
            );
        }
        return trim($argv[1]);
    }

    if (!isset($_GET['url']) || empty($_GET['url'])) {
        exit_with_message("No URL provided. Use ?url=https://example.com/file.zip&token=YOUR_TOKEN");
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
    if ($parts === false) {
        exit_with_message("Cannot parse URL.");
    }

    $scheme = strtolower($parts['scheme'] ?? '');
    if (!in_array($scheme, ALLOWED_SCHEMES, true)) {
        exit_with_message("Only http/https URLs are allowed.");
    }

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
    $path     = parse_url($url, PHP_URL_PATH) ?? '';
    $basename = basename($path);

    if ($basename === '' || $basename === '/' || $basename === '.') {
        $basename = 'file_' . date('Ymd_His');
    }

    // remove dangerous chars
    $basename = preg_replace('/[^A-Za-z0-9._-]/', '_', $basename);

    if ($basename[0] === '.') {
        $basename = 'file' . $basename;
    }

    return $basename;
}


/** Ensure download directory exists & is writable */
function ensure_download_dir(): string
{
    if (!is_dir(DOWNLOAD_DIR)) {
        if (!mkdir(DOWNLOAD_DIR, 0755, true) && !is_dir(DOWNLOAD_DIR)) {
            exit_with_message("Failed to create download directory.");
        }
    }

    $realPath = realpath(DOWNLOAD_DIR);
    if ($realPath === false) {
        exit_with_message("Failed to resolve download directory.");
    }

    if (!is_writable($realPath)) {
        exit_with_message("Download directory is not writable.");
    }

    return $realPath;
}


/** Download using cURL with size limit */
function download_file(string $url, string $destination): void
{
    $fp = fopen($destination, 'wb');
    if ($fp === false) {
        exit_with_message("Cannot open destination file for writing.");
    }

    $ch = curl_init($url);
    if ($ch === false) {
        fclose($fp);
        exit_with_message("Failed to initialize cURL.");
    }

    curl_setopt_array($ch, [
        CURLOPT_FILE            => $fp,
        CURLOPT_FOLLOWLOCATION  => false,
        CURLOPT_TIMEOUT         => 120,
        CURLOPT_CONNECTTIMEOUT  => 15,
        CURLOPT_USERAGENT       => 'L2DDownloader/1.0',
        CURLOPT_SSL_VERIFYPEER  => true,
        CURLOPT_SSL_VERIFYHOST  => 2,
        CURLOPT_NOPROGRESS      => false,
        CURLOPT_PROGRESSFUNCTION => function ($resource, $download_size, $downloaded) {
            if ($downloaded > MAX_FILE_SIZE_BYTES) {
                return 1; // abort download
            }
            if ($download_size > 0 && $download_size > MAX_FILE_SIZE_BYTES) {
                return 1;
            }
            return 0;
        },
    ]);

    $success = curl_exec($ch);
    $error   = curl_error($ch);
    $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);
    fclose($fp);

    if (!$success || $code < 200 || $code >= 300) {
        if (file_exists($destination)) {
            unlink($destination);
        }

        if ($error && strpos($error, 'Callback aborted') !== false) {
            exit_with_message("Download aborted: file size exceeded limit (" . MAX_FILE_SIZE_BYTES . " bytes).");
        }

        exit_with_message("Download failed. HTTP code: {$code}. Error: {$error}");
    }
}


/** Success message (and log) */
function success_message(string $filePath): void
{
    $url = $GLOBALS['L2D_CURRENT_URL'] ?? '';
    log_event('OK', 'Download successful', $url, "SavedTo={$filePath}");

    if (php_sapi_name() === 'cli') {
        echo "[OK] File downloaded to: {$filePath}" . PHP_EOL;
    } else {
        echo "File downloaded successfully.<br>";
        echo "Saved to: " . htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8');
    }
}


/* ================= RUN ================= */

// 1. Check token first
check_token();

// 2. Get URL
$url = get_url_input();
$GLOBALS['L2D_CURRENT_URL'] = $url;

// 3. Validate URL
$validatedUrl = validate_url($url);

// 4. Ensure folder exists
$downloadDir  = ensure_download_dir();

// 5. Build destination path
$filename     = get_safe_filename_from_url($validatedUrl);
$destination  = $downloadDir . DIRECTORY_SEPARATOR . $filename;

// 6. Download
download_file($validatedUrl, $destination);

// 7. Report success
success_message($destination);

?>
