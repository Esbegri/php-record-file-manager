<?php
/**
 * cancel_record.php
 * Handles the cancellation of a record with a provided reason.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control
require_login();
csrf_verify(); // Ensure the request is secure

// Input Validation
$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$cancelReason = isset($_POST['cancel_reason']) ? trim($_POST['cancel_reason']) : '';

if ($recordId <= 0 || $cancelReason === '') {
    header('Location: dashboard.php?error=invalid_input');
    exit;
}

// Set metadata
date_default_timezone_set('Europe/Istanbul');
$cancelledAt = date('Y-m-d H:i:s');
$cancelledBy = $_SESSION['user'] ?? 'system';

try {
    // Update record status using PDO
    $sql = "UPDATE records 
            SET cancelled = 1, 
                cancel_reason = ?, 
                cancelled_at = ?, 
                cancelled_by = ? 
            WHERE id = ?";
            
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        $cancelReason, 
        $cancelledAt, 
        $cancelledBy, 
        $recordId
    ]);

    if ($success) {
        header('Location: dashboard.php?status=record_cancelled');
    } else {
        header('Location: dashboard.php?error=update_failed');
    }

} catch (PDOException $e) {
    // In production, log the error message: $e->getMessage()
    header('Location: dashboard.php?error=db_error');
}
exit;