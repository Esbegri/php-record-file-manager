<?php
session_start();
require_once 'unauthorized.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Invalid request method.');
}

$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($recordId <= 0) {
    http_response_code(400);
    die('Invalid record ID.');
}

$conn = new mysqli('localhost', 'root', '', 'belge');
$conn->set_charset('utf8');

if ($conn->connect_error) {
    die('Database connection failed.');
}

// Restore: remove cancellation flags
$stmt = $conn->prepare("
    UPDATE records
    SET
        cancelled = 0,
        cancel_reason = NULL,
        cancelled_at = NULL,
        cancelled_by = NULL
    WHERE id = ?
");
$stmt->bind_param('i', $recordId);

$ok = $stmt->execute();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="refresh" content="2;url=dashboard.php">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <title>Restore Record</title>
  <style>
    body { background-color:#f8f9fa; display:flex; justify-content:center; align-items:center; height:100vh; }
    .message { text-align:center; font-size:1.2rem; font-weight:600; }
  </style>
</head>
<body>
  <div class="message">
    <?php if ($ok): ?>
      <div class="text-success">
        <i class="fa fa-undo fa-2x"></i><br>
        Record has been restored successfully.
      </div>
      <div class="small text-muted mt-2">Redirecting to dashboard...</div>
    <?php else: ?>
      <div class="text-danger">
        <i class="fa fa-triangle-exclamation fa-2x"></i><br>
        An error occurred while restoring the record.
      </div>
      <div class="small text-muted mt-2">Redirecting to dashboard...</div>
    <?php endif; ?>
  </div>
</body>
</html>
