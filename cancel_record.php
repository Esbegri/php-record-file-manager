<?php
session_start();
require_once 'unauthorized.php';

// Input validation
$recordId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$cancelReasonRaw = isset($_POST['cancel_reason']) ? trim($_POST['cancel_reason']) : '';

if ($recordId <= 0 || $cancelReasonRaw === '') {
    echo "<div class='alert alert-danger text-center'>Invalid or missing data provided.</div>";
    header('refresh:2; url=dashboard.php');
    exit;
}

date_default_timezone_set('Europe/Istanbul');
$cancelledAt = date('Y-m-d H:i:s');
$cancelledBy = $_SESSION['user'] ?? 'system';

// Database connection
$conn = new mysqli('localhost', 'root', '', 'belge');
$conn->set_charset('utf8');

if ($conn->connect_error) {
    echo "<div class='alert alert-danger text-center'>Database connection failed.</div>";
    header('refresh:2; url=dashboard.php');
    exit;
}

// Secure update with prepared statement
$stmt = $conn->prepare("
    UPDATE records
    SET
        cancelled = 1,
        cancel_reason = ?,
        cancelled_at = ?,
        cancelled_by = ?
    WHERE id = ?
");

$stmt->bind_param('sssi', $cancelReasonRaw, $cancelledAt, $cancelledBy, $recordId);
$ok = $stmt->execute();

$stmt->close();
$conn->close();

// Safe output
$cancelReasonSafe = htmlspecialchars($cancelReasonRaw, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="refresh" content="2;url=dashboard.php">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <title>Cancel Record</title>
  <style>
    body { background:#f8f9fa; display:flex; justify-content:center; align-items:center; height:100vh; }
  </style>
</head>
<body>
  <div class="text-center">
    <?php if ($ok): ?>
      <h3 class="text-danger">Record has been cancelled</h3>
      <p><strong>Cancellation Reason:</strong> <?php echo $cancelReasonSafe; ?></p>
      <p class="small text-muted">Redirecting to dashboard...</p>
    <?php else: ?>
      <h3 class="text-danger">Cancellation failed</h3>
      <p class="small text-muted">Redirecting to dashboard...</p>
    <?php endif; ?>
  </div>
</body>
</html>
