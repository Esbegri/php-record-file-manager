<?php
/**
 * advanced_search.php
 * Provides dynamic filtering capabilities for records using PDO.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control
require_login();

// 1. Initialize Filtering logic
$conditions = [];
$params = [];

/**
 * Helper function to build dynamic SQL conditions
 */
function addSearchCondition(&$conditions, &$params, $field, $value, $isLike = false) {
    if ($value !== '' && $value !== null) {
        if ($isLike) {
            $conditions[] = "$field LIKE ?";
            $params[] = "%$value%";
        } else {
            $conditions[] = "$field = ?";
            $params[] = $value;
        }
    }
}

// 2. Collect and sanitize inputs
$firstName  = trim($_GET['first_name'] ?? '');
$lastName   = trim($_GET['last_name'] ?? '');
$gender      = trim($_GET['gender'] ?? '');
$fileNo     = trim($_GET['file_no'] ?? '');
$nationalId = trim($_GET['national_id'] ?? '');
$department  = trim($_GET['department'] ?? '');
$category    = trim($_GET['category'] ?? '');

// 3. Map inputs to database columns
addSearchCondition($conditions, $params, 'first_name',  $firstName,  true);
addSearchCondition($conditions, $params, 'last_name',   $lastName,   true);
addSearchCondition($conditions, $params, 'gender',      $gender);
addSearchCondition($conditions, $params, 'file_no',     $fileNo);
addSearchCondition($conditions, $params, 'national_id', $nationalId);
addSearchCondition($conditions, $params, 'department',  $department);
addSearchCondition($conditions, $params, 'category',    $category);

// 4. Build the final SQL query
$sql = "SELECT * FROM records";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Search error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - Record Manager</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .cancelled { background-color: #f8d7da !important; text-decoration: line-through; }
        .has-file { border-left: 5px solid #28a745; }
    </style>
</head>
<body class="p-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Search Results (<?= count($results) ?>)</h2>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>File No</th>
                        <th>National ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Gender</th>
                        <th>DOB</th>
                        <th>DOD</th>
                        <th>Mother Name</th>
                        <th>Father Name</th>
                        <th>Department</th>
                        <th>Category</th>
                        <th>File</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($results) > 0): ?>
                        <?php foreach ($results as $row): 
                            $rowClass = ($row['cancelled'] ?? 0) ? 'cancelled' : (($row['has_file'] ?? 0) ? 'has-file' : '');
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td><?= (int)$row['id'] ?></td>
                            <td><?= htmlspecialchars($row['file_no'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['national_id'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['first_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['last_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['gender'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['date_of_birth'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['date_of_death'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['mother_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['father_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['department'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                            <td><?= ($row['has_file'] ?? 0) ? '<span class="badge badge-success">Yes</span>' : 'No' ?></td>
                            <td>
                                <?php if ($row['cancelled'] ?? 0): ?>
                                    <span class="badge badge-danger">Cancelled</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="14" class="text-center">No records found matching your criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>