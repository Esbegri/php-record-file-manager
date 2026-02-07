<?php
/**
 * file.php
 * Securely serves files from the storage directory.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control (bootstrap already started session)
require_login();

$action   = $_GET['action'] ?? 'view';
$recordId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$fileName = $_GET['name'] ?? '';

if ($recordId <= 0 || empty($fileName)) {
    http_response_code(400);
    die('Invalid request.');
}

// Prevent path traversal (Security)
$fileName = basename($fileName);

// Modern path construction
$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $recordId . DIRECTORY_SEPARATOR . $fileName;

if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    die('File not found on server.');
}

// Detect MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($filePath) ?: 'application/octet-stream';

// Force download or allow inline view
$disposition = ($action === 'download') ? 'attachment' : 'inline';

// Headers for security and delivery
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($fileName) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');

// Clean buffer to prevent corrupted files
ob_clean();
flush();

readfile($filePath);
exit;