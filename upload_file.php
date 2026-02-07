<?php
/**
 * upload_file.php
 * Handles secure file uploads for specific records.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control
require_login();
csrf_verify();

// 1. Validate Record ID
$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($recordId <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

// 2. Validate Upload Existence
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: dashboard.php?error=upload_failed');
    exit;
}

// 3. Define Constraints
$maxBytes = 10 * 1024 * 1024; // 10MB
$allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

// 4. File Checks (Size, Extension, and MIME Type)
$fileSize = $_FILES['file']['size'];
$originalName = $_FILES['file']['name'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// Check Size
if ($fileSize > $maxBytes) {
    header('Location: dashboard.php?error=file_too_large');
    exit;
}

// Check Extension
if (!in_array($extension, $allowedExtensions)) {
    header('Location: dashboard.php?error=invalid_extension');
    exit;
}

// Check actual file content (MIME)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['file']['tmp_name']);

if (!in_array($mime, $allowedMimes)) {
    header('Location: dashboard.php?error=invalid_mime');
    exit;
}

// 5. Secure Storage Logic
$targetDir = __DIR__ . '/storage/uploads/' . $recordId;
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Generate a safe, random filename to prevent overwrites or execution attacks
$storedName = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$targetPath = $targetDir . DIRECTORY_SEPARATOR . $storedName;

if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
    try {
        // Update database using the centralized PDO instance
        $stmt = $pdo->prepare("UPDATE records SET has_file = 1 WHERE id = ?");
        $stmt->execute([$recordId]);

        header('Location: dashboard.php?status=upload_success');
    } catch (PDOException $e) {
        header('Location: dashboard.php?error=db_error');
    }
} else {
    header('Location: dashboard.php?error=save_failed');
}
exit;