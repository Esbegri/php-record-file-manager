<?php
session_start();
require_once 'unauthorized.php';

// Validate ID
$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($recordId <= 0) {
    die("Invalid request: record ID not found.");
}

// Read inputs (match edit_record.php)
$nationalId   = isset($_POST['national_id']) ? mb_strtoupper(trim($_POST['national_id'])) : '';
$firstName    = isset($_POST['first_name']) ? mb_strtoupper(trim($_POST['first_name'])) : '';
$lastName     = isset($_POST['last_name']) ? mb_strtoupper(trim($_POST['last_name'])) : '';
$gender       = isset($_POST['gender']) ? mb_strtoupper(trim($_POST['gender'])) : '';
$dateOfBirth  = $_POST['date_of_birth'] ?? null; // YYYY-MM-DD
$dateOfDeath  = $_POST['date_of_death'] ?? null; // YYYY-MM-DD
$motherName   = isset($_POST['mother_name']) ? mb_strtoupper(trim($_POST['mother_name'])) : '';
$fatherName   = isset($_POST['father_name']) ? mb_strtoupper(trim($_POST['father_name'])) : '';
$department   = isset($_POST['department']) ? mb_strtoupper(trim($_POST['department'])) : '';
$category     = isset($_POST['category']) ? mb_strtoupper(trim($_POST['category'])) : '';
$notes        = isset($_POST['notes']) ? trim($_POST['notes']) : '';

date_default_timezone_set('Europe/Istanbul');
$changedAt = date('Y-m-d H:i:s');
$changedBy = $_SESSION['user'] ?? 'UNKNOWN';

// Database connection
$conn = new mysqli('localhost', 'root', '', 'belge');
$conn->set_charset('utf8');

if ($conn->connect_error) {
    die("Database connection error.");
}

// Update record (prepared statement)
$stmt = $conn->prepare("
    UPDATE records
    SET
        national_id = ?,
        first_name = ?,
        last_name = ?,
        gender = ?,
        date_of_birth = ?,
        date_of_death = ?,
        mother_name = ?,
        father_name = ?,
        department = ?,
        category = ?,
        notes = ?
    WHERE id = ?
");

$stmt->bind_param(
    "sssssssssssi",
    $nationalId,
    $firstName,
    $lastName,
    $gender,
    $dateOfBirth,
    $dateOfDeath,
    $motherName,
    $fatherName,
    $department,
    $category,
    $notes,
    $recordId
);

$stmt->execute();

$changeLogInserted = false;

// If something changed, log it
if ($stmt->affected_rows > 0) {
    $logStmt = $conn->prepare("
        INSERT INTO record_changes (record_id, changed_at, changed_by)
        VALUES (?, ?, ?)
    ");
    $logStmt->bind_param("iss", $recordId, $changedAt, $changedBy);
    $logStmt->execute();
    $logStmt->close();

    $changeLogInserted = true;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="3;url=dashboard.php">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<title>Update Status</title>
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="height:100vh;">

<div class="card shadow-lg border-0 p-4" style="width: 420px;">
    <?php if ($changeLogInserted): ?>
        <div class="alert alert-success text-center mb-0">
            <h5 class="mb-2">✅ Update Successful</h5>
            <p>The record has been updated successfully.</p>
            <p class="small text-muted">You will be redirected to the dashboard in 3 seconds...</p>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center mb-0">
            <h5 class="mb-2">ℹ️ No Changes Detected</h5>
            <p>The data was unchanged, or the update did not affect any rows.</p>
            <p class="small text-muted">You will be redirected to the dashboard in 3 seconds...</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
