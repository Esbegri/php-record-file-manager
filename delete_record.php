<?php
/**
 * delete_record.php
 * Handles soft deletion of records for administrative users.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control - Only admins should be allowed to delete records
require_admin(); 
csrf_verify();

// Input Validation
$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$deleteReason = isset($_POST['delete_reason']) ? trim($_POST['delete_reason']) : '';

if ($recordId <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

if ($deleteReason === '') {
    header('Location: dashboard.php?error=reason_required');
    exit;
}

// Metadata for tracking
date_default_timezone_set('Europe/Istanbul');
$deletedAt = date('Y-m-d H:i:s');
$deletedBy = $_SESSION['user'] ?? 'UNKNOWN';

try {
    // 1) Mark as deleted (Soft Delete)
    // We update 'is_deleted' flag instead of removing the row to preserve data integrity
    $sql = "UPDATE records 
            SET is_deleted = 1, 
                deleted_reason = ?, 
                deleted_at = ?, 
                deleted_by = ? 
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        $deleteReason, 
        $deletedAt, 
        $deletedBy, 
        $recordId
    ]);

    if ($success && $stmt->rowCount() > 0) {
        // 2) Optional: Log the deletion event to a separate audit table
        $logSql = "INSERT INTO record_deletions (record_id, deleted_at, deleted_by, deleted_reason) 
                   VALUES (?, ?, ?, ?)";
        $logStmt = $pdo->prepare($logSql);
        $logStmt->execute([$recordId, $deletedAt, $deletedBy, $deleteReason]);

        header('Location: dashboard.php?status=record_deleted');
    } else {
        header('Location: dashboard.php?error=not_found');
    }

} catch (PDOException $e) {
    // In a real application, log $e->getMessage() for debugging
    header('Location: dashboard.php?error=db_error');
}
exit;