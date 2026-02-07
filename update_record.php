<?php
/**
 * update_record.php
 * Handles the server-side logic for updating an existing record.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control
require_login();
csrf_verify(); // Ensure security against CSRF attacks

// 1. Validate Record ID
$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($recordId <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

// 2. Sanitize and Map Inputs
$nationalId   = mb_strtoupper(trim($_POST['national_id'] ?? ''));
$firstName    = mb_strtoupper(trim($_POST['first_name'] ?? ''));
$lastName     = mb_strtoupper(trim($_POST['last_name'] ?? ''));
$fileNo       = mb_strtoupper(trim($_POST['file_no'] ?? ''));
$gender       = mb_strtoupper(trim($_POST['gender'] ?? ''));
$dateOfBirth  = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
$dateOfDeath  = !empty($_POST['date_of_death']) ? $_POST['date_of_death'] : null;
$motherName   = mb_strtoupper(trim($_POST['mother_name'] ?? ''));
$fatherName   = mb_strtoupper(trim($_POST['father_name'] ?? ''));
$department   = mb_strtoupper(trim($_POST['department'] ?? ''));
$category     = mb_strtoupper(trim($_POST['category'] ?? ''));
$notes        = trim($_POST['notes'] ?? '');

$changedBy    = $_SESSION['user'] ?? 'system';
date_default_timezone_set('Europe/Istanbul');
$changedAt    = date('Y-m-d H:i:s');

try {
    // 3. Update Record using PDO
    $sql = "UPDATE records SET 
                national_id = ?, first_name = ?, last_name = ?, 
                file_no = ?, gender = ?, date_of_birth = ?, 
                date_of_death = ?, mother_name = ?, father_name = ?, 
                department = ?, category = ?, notes = ? 
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nationalId, $firstName, $lastName, $fileNo, $gender,
        $dateOfBirth, $dateOfDeath, $motherName, $fatherName,
        $department, $category, $notes, $recordId
    ]);

    // 4. Log the Change (Optional Audit Trail)
    if ($stmt->rowCount() >= 0) { // rowCount might be 0 if no data actually changed
        $logSql = "INSERT INTO record_changes (record_id, changed_at, changed_by) VALUES (?, ?, ?)";
        $pdo->prepare($logSql)->execute([$recordId, $changedAt, $changedBy]);

        header('Location: dashboard.php?status=updated');
    } else {
        header('Location: dashboard.php?error=not_found');
    }

} catch (PDOException $e) {
    // In production, log $e->getMessage() for debugging
    header('Location: dashboard.php?error=db_error');
}
exit;