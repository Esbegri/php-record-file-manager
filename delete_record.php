<?php
session_start();
require_once 'unauthorized.php';

$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$deleteReason = isset($_POST['delete_reason']) ? trim($_POST['delete_reason']) : '';

$deletedBy = $_SESSION['user'] ?? 'UNKNOWN';

date_default_timezone_set('Europe/Istanbul');
$deletedAt = date('Y-m-d H:i:s');

if ($recordId <= 0) {
    echo '<div class="alert alert-danger text-center">Invalid record ID.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

if ($deleteReason === '') {
    echo '<div class="alert alert-warning text-center">Deletion reason is required.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// DB
$conn = new mysqli('localhost', 'root', '', 'belge');
$conn->set_charset('utf8');

if ($conn->connect_error) {
    echo '<div class="alert alert-danger text-center">Database connection failed.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// 1) Mark as deleted (soft delete)
$stmt = $conn->prepare("
    UPDATE records
    SET
        is_deleted = 1,
        deleted_reason = ?,
        deleted_at = ?,
        deleted_by = ?
    WHERE id = ?
");
$stmt->bind_param('sssi', $deleteReason, $deletedAt, $deletedBy, $recordId);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

// 2) Log deletion event (optional but nice)
if ($affected > 0) {
    $logStmt = $conn->prepare("
        INSERT INTO record_deletions (record_id, deleted_at, deleted_by, deleted_reason)
        VALUES (?, ?, ?, ?)
    ");
    $logStmt->bind_param('isss', $recordId, $deletedAt, $deletedBy, $deleteReason);
    $logStmt->execute();
    $logStmt->close();

    echo '<div class="alert alert-success text-center">Record deleted successfully.</div>';
} else {
    echo '<div class="alert alert-warning text-center">No changes were made (record may not exist).</div>';
}

$conn->close();

header('refresh:2; url=dashboard.php');
exit;
