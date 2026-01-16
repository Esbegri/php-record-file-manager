<?php
session_start();

// Prevent unauthorized access
require_once 'unauthorized.php';

// Current user
$currentUser = $_SESSION['user'] ?? 'system';

// Read inputs (trim + uppercase where relevant)
$nationalId   = isset($_POST['national_id']) ? mb_strtoupper(trim($_POST['national_id'])) : '';
$firstName    = isset($_POST['first_name']) ? mb_strtoupper(trim($_POST['first_name'])) : '';
$lastName     = isset($_POST['last_name']) ? mb_strtoupper(trim($_POST['last_name'])) : '';
$fileNo       = isset($_POST['file_no']) ? mb_strtoupper(trim($_POST['file_no'])) : '';
$dateOfBirth  = $_POST['date_of_birth'] ?? null; // YYYY-MM-DD
$dateOfDeath  = $_POST['date_of_death'] ?? null; // YYYY-MM-DD
$motherName   = isset($_POST['mother_name']) ? mb_strtoupper(trim($_POST['mother_name'])) : '';
$fatherName   = isset($_POST['father_name']) ? mb_strtoupper(trim($_POST['father_name'])) : '';
$department   = isset($_POST['department']) ? mb_strtoupper(trim($_POST['department'])) : '';
$notes        = isset($_POST['notes']) ? trim($_POST['notes']) : '';
$category     = isset($_POST['category']) ? mb_strtoupper(trim($_POST['category'])) : '';
$gender       = isset($_POST['gender']) ? mb_strtoupper(trim($_POST['gender'])) : '';

date_default_timezone_set('Europe/Istanbul');
$createdAt = date('Y-m-d H:i:s');

// Basic validation
if ($fileNo === '') {
    echo '<div class="alert alert-primary text-center">File No cannot be empty.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'belge');
$conn->set_charset('utf8');

if ($conn->connect_error) {
    echo '<div class="alert alert-danger text-center">Database connection failed.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// 1) Check duplicate file_no (prepared statement)
$checkStmt = $conn->prepare('SELECT COUNT(*) AS cnt FROM records WHERE file_no = ?');
$checkStmt->bind_param('s', $fileNo);
$checkStmt->execute();
$result = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if (!empty($result) && (int)$result['cnt'] > 0) {
    echo '<div class="alert alert-primary text-center">A record with the same File No already exists.</div>';
    $conn->close();
    header('refresh:2; url=dashboard.php');
    exit;
}

// 2) Insert record (prepared statement)
$insertStmt = $conn->prepare("
    INSERT INTO records (
        national_id,
        first_name,
        last_name,
        file_no,
        gender,
        date_of_birth,
        date_of_death,
        mother_name,
        father_name,
        department,
        category,
        notes,
        created_at,
        created_by,
        has_file
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
");

$insertStmt->bind_param(
    'ssssssssssssss',
    $nationalId,
    $firstName,
    $lastName,
    $fileNo,
    $gender,
    $dateOfBirth,
    $dateOfDeath,
    $motherName,
    $fatherName,
    $department,
    $category,
    $notes,
    $createdAt,
    $currentUser
);

if (!$insertStmt->execute()) {
    $insertStmt->close();
    $conn->close();
    echo '<div class="alert alert-danger text-center">Failed to create record.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

$recordId = $conn->insert_id;
$insertStmt->close();

// 3) File upload (optional)
if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {

    // Allowed extensions (adjust if needed)
    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    $maxBytes = 10 * 1024 * 1024; // 10MB

    if ($_FILES['file']['size'] > $maxBytes) {
        echo '<div class="alert alert-warning text-center">File size limit exceeded (max 10MB).</div>';
    } else {

        $originalName = $_FILES['file']['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            echo '<div class="alert alert-warning text-center">Invalid file type. Allowed: PDF, JPG, PNG.</div>';
        } else {
            // Store uploads under /storage/uploads/{recordId}/
            $targetDir = __DIR__ . '/storage/uploads/' . $recordId;
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // Prevent unsafe filenames: generate a safe stored name
            $storedName = 'file_' . time() . '.' . $extension;
            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $storedName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {

                // Update record -> has_file = 1
                $updateStmt = $conn->prepare('UPDATE records SET has_file = 1 WHERE id = ?');
                $updateStmt->bind_param('i', $recordId);
                $updateStmt->execute();
                $updateStmt->close();

                echo '<div class="alert alert-success text-center">Record created and file uploaded successfully.</div>';
            } else {
                echo '<div class="alert alert-danger text-center">Record created, but file upload failed.</div>';
            }
        }
    }
} else {
    echo '<div class="alert alert-success text-center">Record created successfully.</div>';
}

$conn->close();
header('refresh:2; url=dashboard.php');
exit;
?>
