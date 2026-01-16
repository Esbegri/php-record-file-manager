<?php
session_start();
require_once 'unauthorized.php';

$action = $_GET['action'] ?? 'view';
$recordId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$fileName = $_GET['name'] ?? '';

if ($recordId <= 0 || $fileName === '') {
    http_response_code(400);
    die('Invalid request.');
}

// Prevent path traversal
$fileName = basename($fileName);

// File location
$filePath = __DIR__ . "/storage/uploads/{$recordId}/{$fileName}";

if (!is_file($filePath)) {
    http_response_code(404);
    die('File not found.');
}

// Detect MIME type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($filePath) ?: 'application/octet-stream';

// Force download or allow inline view
$disposition = ($action === 'download') ? 'attachment' : 'inline';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($fileName) . '"');
header('X-Content-Type-Options: nosniff');

readfile($filePath);
exit;
