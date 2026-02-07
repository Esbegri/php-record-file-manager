<?php
/**
 * edit_record.php
 * Fetch and update existing records using PDO.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control
require_login();

// 1. Validate Record ID
$recordId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
if ($recordId <= 0) {
    header('Location: dashboard.php');
    exit;
}

// 2. Handle POST Request (Update logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_record'])) {
    csrf_verify(); // Security check

    try {
        $sql = "UPDATE records SET 
                national_id = ?, first_name = ?, last_name = ?, 
                file_no = ?, gender = ?, date_of_birth = ?, 
                date_of_death = ?, mother_name = ?, father_name = ?, 
                department = ?, category = ?, notes = ? 
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            trim($_POST['national_id'] ?? ''),
            mb_strtoupper(trim($_POST['first_name'] ?? '')),
            mb_strtoupper(trim($_POST['last_name'] ?? '')),
            mb_strtoupper(trim($_POST['file_no'] ?? '')),
            $_POST['gender'] ?? '',
            !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
            !empty($_POST['date_of_death']) ? $_POST['date_of_death'] : null,
            mb_strtoupper(trim($_POST['mother_name'] ?? '')),
            mb_strtoupper(trim($_POST['father_name'] ?? '')),
            mb_strtoupper(trim($_POST['department'] ?? '')),
            $_POST['category'] ?? 'NORMAL',
            trim($_POST['notes'] ?? ''),
            $recordId
        ]);

        header('Location: dashboard.php?status=updated');
        exit;
    } catch (PDOException $e) {
        $error = "Update failed: " . $e->getMessage();
    }
}

// 3. Fetch Record Data (Display logic)
try {
    $stmt = $pdo->prepare("SELECT * FROM records WHERE id = ?");
    $stmt->execute([$recordId]);
    $record = $stmt->fetch();

    if (!$record) {
        header('Location: dashboard.php?error=not_found');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Record #<?= $recordId ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body class="bg-light p-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h5 class="mb-0">Edit Record: <?= htmlspecialchars($record['file_no']) ?></h5>
                    <a href="dashboard.php" class="text-white">&times;</a>
                </div>
                <div class="card-body">
                    <form action="edit_record.php" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $recordId ?>">
                        <input type="hidden" name="update_record" value="1">

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>File Number</label>
                                <input type="text" class="form-control" name="file_no" value="<?= htmlspecialchars($record['file_no']) ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>National ID</label>
                                <input type="text" class="form-control" name="national_id" value="<?= htmlspecialchars($record['national_id']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>First Name</label>
                                <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($record['first_name']) ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Last Name</label>
                                <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($record['last_name']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Gender</label>
                                <select class="form-control" name="gender">
                                    <option value="MALE" <?= $record['gender'] == 'MALE' ? 'selected' : '' ?>>MALE</option>
                                    <option value="FEMALE" <?= $record['gender'] == 'FEMALE' ? 'selected' : '' ?>>FEMALE</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Category</label>
                                <select class="form-control" name="category">
                                    <?php 
                                    $categories = ['NORMAL', 'EX_ENTRY', 'LEGAL_CASE', 'FETUS'];
                                    foreach($categories as $cat): ?>
                                        <option value="<?= $cat ?>" <?= $record['category'] == $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($record['notes']) ?></textarea>
                        </div>

                        <div class="mt-4 text-right">
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>