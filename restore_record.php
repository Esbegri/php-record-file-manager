<?php
/**
 * restore_record.php
 * Reverses the cancellation of a record.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control
require_login();

// Critical actions must be via POST and verified with CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

csrf_verify();

// Validate Input
$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($recordId <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

try {
    // Restore: Clear cancellation flags using the centralized PDO instance
    $sql = "UPDATE records 
            SET cancelled = 0, 
                cancel_reason = NULL, 
                cancelled_at = NULL, 
                cancelled_by = NULL 
            WHERE id = ?";
            
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([$recordId]);

    if ($success && $stmt->rowCount() > 0) {
        header('Location: dashboard.php?status=record_restored');
    } else {
        header('Location: dashboard.php?error=not_found');
    }

} catch (PDOException $e) {
    // Log error in production: $e->getMessage()
    header('Location: dashboard.php?error=db_error');
}
exit;