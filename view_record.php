<?php
session_start();
require_once 'unauthorized.php';

$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($recordId <= 0) {
    die("Invalid record ID.");
}

$dirPath = __DIR__ . "/storage/uploads/{$recordId}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Files</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h4 class="mb-3">📂 Record ID: <?php echo htmlspecialchars((string)$recordId); ?></h4>

<?php
if (is_dir($dirPath)) {
    $files = array_values(array_diff(scandir($dirPath), ['.', '..']));

    if (count($files) === 0) {
        echo '<div class="alert alert-warning">No files found for this record.</div>';
    } else {
        echo '<table class="table table-bordered table-striped">';
        echo '<thead class="thead-dark"><tr><th>File Name</th><th>Action</th></tr></thead><tbody>';

        foreach ($files as $file) {
            // Basic filename safety
            $safeFile = basename($file);
            $fileName = htmlspecialchars($safeFile, ENT_QUOTES);

            $viewUrl = "file.php?action=view&id={$recordId}&name=" . urlencode($safeFile);
            $downloadUrl = "file.php?action=download&id={$recordId}&name=" . urlencode($safeFile);

            echo "
            <tr>
                <td><i class='fa fa-file me-2'></i> {$fileName}</td>
                <td>
                    <a class='btn btn-sm btn-primary' href='{$viewUrl}' target='_blank'>
                        <i class='fa fa-eye'></i> View
                    </a>
                    <a class='btn btn-sm btn-success' href='{$downloadUrl}'>
                        <i class='fa fa-download'></i> Download
                    </a>
                </td>
            </tr>";
        }

        echo '</tbody></table>';
    }
} else {
    echo '<div class="alert alert-danger">This record does not have any files.</div>';
}
?>

    <a href="dashboard.php" class="btn btn-secondary mt-3">
        <i class="fa fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

</body>
</html>
