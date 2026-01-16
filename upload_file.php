<?php
session_start();
require_once 'unauthorized.php';

// Accept record ID from POST
$recordId = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($recordId <= 0) {
    echo '<div class="alert alert-danger text-center">Invalid record ID.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

if (!isset($_FILES['file']) || empty($_FILES['file']['name'])) {
    echo '<div class="alert alert-warning text-center">No file selected.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// Basic upload checks
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo '<div class="alert alert-danger text-center">Upload failed. Please try again.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// Limits & allowed types
$maxBytes = 10 * 1024 * 1024; // 10MB
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

if ($_FILES['file']['size'] > $maxBytes) {
    echo '<div class="alert alert-warning text-center">File size limit exceeded (max 10MB).</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

$originalName = $_FILES['file']['name'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions, true)) {
    echo '<div class="alert alert-warning text-center">Invalid file type. Allowed: PDF, JPG, PNG.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// (Optional but better) MIME check
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['file']['tmp_name']);
$allowedMimes = [
    'application/pdf',
    'image/jpeg',
    'image/png'
];
if (!in_array($mime, $allowedMimes, true)) {
    echo '<div class="alert alert-warning text-center">Invalid file content.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// Create target directory: /storage/uploads/{recordId}/
$targetDir = __DIR__ . '/storage/uploads/' . $recordId;
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Safe stored filename (avoid using original filename)
$storedName = 'file_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
$targetPath = $targetDir . DIRECTORY_SEPARATOR . $storedName;

if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
    echo '<div class="alert alert-danger text-center">Failed to save the uploaded file.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// DB update: mark record has_file = 1
$conn = new mysqli('localhost', 'root', '', 'belge');
$conn->set_charset('utf8');

if ($conn->connect_error) {
    echo '<div class="alert alert-warning text-center">File uploaded, but database connection failed.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

$stmt = $conn->prepare('UPDATE records SET has_file = 1 WHERE id = ?');
$stmt->bind_param('i', $recordId);
$stmt->execute();
$stmt->close();
$conn->close();

echo '<div class="alert alert-success text-center">File uploaded successfully.</div>';
header('refresh:2; url=dashboard.php');
exit;
