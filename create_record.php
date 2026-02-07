<?php
/**
 * create_record.php
 * Handles the creation of new records and optional file uploads.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control
require_login(); 
csrf_verify();

// Data Sanitization
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
$currentUser  = $_SESSION['user'] ?? 'system';

// Basic Validation
if ($fileNo === '') {
    header('Location: dashboard.php?error=missing_file_no');
    exit;
}

try {
    // Check for duplicate File Number using centralized PDO instance
    $check = $pdo->prepare("SELECT id FROM records WHERE file_no = ?");
    $check->execute([$fileNo]);
    if ($check->fetch()) {
        header('Location: dashboard.php?error=duplicate_file');
        exit;
    }

    // Insert new record
    $sql = "INSERT INTO records (
        national_id, first_name, last_name, file_no, gender, 
        date_of_birth, date_of_death, mother_name, father_name, 
        department, category, notes, created_at, created_by, has_file
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 0)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nationalId, $firstName, $lastName, $fileNo, $gender,
        $dateOfBirth, $dateOfDeath, $motherName, $fatherName,
        $department, $category, $notes, $currentUser
    ]);

    $recordId = (int)$pdo->lastInsertId();

    // Handle File Upload if provided
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $allowed = $config['upload']['allowed_ext'] ?? ['pdf', 'jpg', 'png'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $targetDir = __DIR__ . '/storage/uploads/' . $recordId;
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $storedName = 'doc_' . time() . '.' . $ext;
            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $storedName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                // Mark record as having an attached file
                $pdo->prepare("UPDATE records SET has_file = 1 WHERE id = ?")->execute([$recordId]);
            }
        }
    }

    header('Location: dashboard.php?status=success');

} catch (PDOException $e) {
    // Log the error in a real-world scenario
    header('Location: dashboard.php?error=db_error');
}
exit;