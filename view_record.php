<?php
/**
 * view_record.php
 * Lists all uploaded files/documents associated with a specific record.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control
require_login();

// 1. Validate Record ID (Expects POST from Dashboard)
$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($recordId <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

// 2. Define Directory Path
$dirPath = __DIR__ . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $recordId;
$hasFiles = false;
$fileList = [];

// 3. Scan directory if it exists
if (is_dir($dirPath)) {
    // Filter out hidden files and directory pointers
    $fileList = array_values(array_diff(scandir($dirPath), ['.', '..', '.gitkeep', '.DS_Store']));
    if (count($fileList) > 0) {
        $hasFiles = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Documents - Record #<?= $recordId ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; padding-top: 50px; }
        .file-card { border-radius: 10px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card file-card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa-solid fa-folder-open mr-2"></i> Documents for Record #<?= $recordId ?></h5>
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
                </div>
                <div class="card-body">
                    <?php if ($hasFiles): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mt-3">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Filename</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fileList as $file): 
                                        $safeFile = htmlspecialchars($file);
                                        $viewUrl = "file.php?action=view&id={$recordId}&name=" . urlencode($file);
                                        $downloadUrl = "file.php?action=download&id={$recordId}&name=" . urlencode($file);
                                    ?>
                                        <tr>
                                            <td class="align-middle">
                                                <i class="fa-regular fa-file-pdf text-danger mr-2"></i> 
                                                <strong><?= $safeFile ?></strong>
                                            </td>
                                            <td class="text-right">
                                                <a href="<?= $viewUrl ?>" class="btn btn-primary btn-sm" target="_blank">
                                                    <i class="fa-solid fa-eye"></i> View
                                                </a>
                                                <a href="<?= $downloadUrl ?>" class="btn btn-success btn-sm">
                                                    <i class="fa-solid fa-download"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-file-circle-exclamation fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No documents have been uploaded for this record yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>