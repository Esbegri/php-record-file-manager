<?php
/**
 * dashboard.php
 * Main application dashboard showing record listings and management tools.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control
require_login();

// Check if current user is an admin
$isAdmin = ((int)($_SESSION['role'] ?? 0) === 1);

try {
    // Fetch all active (not deleted) records using the centralized PDO instance
    // Note: We filter out 'is_deleted' if you implemented soft delete
    $stmt = $pdo->query("SELECT * FROM records WHERE is_deleted = 0 ORDER BY id DESC");
    $records = $stmt->fetchAll();
} catch (PDOException $e) {
    $records = [];
    $dbError = "Database error occurred.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Record Manager - Dashboard</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.0/css/jquery.dataTables.css">

    <style>
        .cancelled-row { background-color: #f8d7da !important; color: #721c24; }
        .has-file-indicator { border-left: 4px solid #28a745; }
        .action-btn { margin-right: 5px; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><i class="fa-solid fa-database mr-2"></i>Record Management</a>
        <div class="ml-auto">
            <span class="navbar-text mr-3">Welcome, <strong><?= htmlspecialchars($_SESSION['user'] ?? 'User') ?></strong></span>
            <button class="btn btn-outline-light btn-sm mr-2" data-toggle="modal" data-target="#modalChangePassword">
                <i class="fa-solid fa-key"></i> Password
            </button>
            <a href="logout.php" class="btn btn-danger btn-sm"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-success alert-dismissible fade show text-center">
            Operation completed successfully!
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0 text-primary">Records List</h5>
            <div>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNewRecord">
                    <i class="fa-solid fa-plus"></i> Add New Record
                </button>
                <button class="btn btn-info btn-sm ml-2" data-toggle="modal" data-target="#modalSearch">
                    <i class="fa-solid fa-magnifying-glass"></i> Advanced Search
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="mainTable" class="table table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>File No</th>
							<th>Actual ID</th> 
                            <th>National ID</th>
                            <th>Full Name</th>
                            <th>Dept / Category</th>
                            <th>Actions</th>
							
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): 
                            $isCancelled = (bool)($row['cancelled'] ?? false);
                            $hasFile = (bool)($row['has_file'] ?? false);
                        ?>
                        <tr class="<?= $isCancelled ? 'cancelled-row' : '' ?> <?= $hasFile ? 'has-file-indicator' : '' ?>">
                            <td class="font-weight-bold"><?= htmlspecialchars($row['file_no']) ?></td>
							<td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['national_id']) ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td>
                                <small class="text-muted"><?= htmlspecialchars($row['department']) ?></small><br>
                                <span class="badge badge-secondary"><?= htmlspecialchars($row['category']) ?></span>
                            </td>
                            <td>
                                <div class="btn-group">
    <form action="view_record.php" method="POST" style="display:inline;">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <button type="submit" class="btn btn-outline-primary btn-sm action-btn" title="View Documents">
            <i class="fa-solid fa-eye"></i>
        </button>
    </form>

    <?php if (!$isCancelled): ?>
        <button class="btn btn-outline-warning btn-sm action-btn" 
                onclick="cancelRecord(<?= $row['id'] ?>)" title="Cancel Record">
            <i class="fa-solid fa-ban"></i>
        </button>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
        <button class="btn btn-outline-danger btn-sm" 
                onclick="deleteRecord(<?= $row['id'] ?>)" title="Delete Permanently">
            <i class="fa-solid fa-trash"></i>
        </button>
    <?php endif; ?>
</div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'views/modals/change_password_modal.php'; ?>
<?php include 'views/modals/create_record_modal.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.12.0/js/jquery.dataTables.js"></script>
<script>
    $(document).ready(function() {
        $('#mainTable').DataTable({
            "order": [[ 0, "desc" ]],
            "pageLength": 25
        });
    });

    function cancelRecord(id) {
        // Implement logic to trigger cancel modal and set record ID
    }

    function deleteRecord(id) {
        if(confirm('Are you sure you want to delete this record permanently?')) {
            // Implement delete logic via POST
        }
    }
</script>
</body>
</html>