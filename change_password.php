<?php
session_start();

// Prevent unauthorized access
require_once 'unauthorized.php';

// Read inputs safely
$newPassword = isset($_POST['password1']) ? trim($_POST['password1']) : '';
$confirmPassword = isset($_POST['password2']) ? trim($_POST['password2']) : '';

$currentUsername = $_SESSION['user'] ?? '';

echo '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
        crossorigin="anonymous">
  <title>Change Password</title>
</head>
<body class="p-4">
';

if ($currentUsername === '') {
    echo '<div class="alert alert-danger mb-4 text-center" role="alert">Unauthorized.</div>';
    header('refresh:2; url=login.php');
    exit;
}

if ($newPassword === '' || $confirmPassword === '') {
    echo '<div class="alert alert-warning mb-4 text-center" role="alert">Please fill in all fields.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo '<div class="alert alert-danger mb-4 text-center" role="alert">Passwords do not match.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// Optional: basic password policy (you can adjust)
if (strlen($newPassword) < 6) {
    echo '<div class="alert alert-warning mb-4 text-center" role="alert">Password must be at least 6 characters long.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'belge');
$conn->set_charset('utf8');

if ($conn->connect_error) {
    echo '<div class="alert alert-danger mb-4 text-center" role="alert">Database connection failed.</div>';
    header('refresh:2; url=dashboard.php');
    exit;
}

// Hash the password securely
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

// Update with prepared statement
$stmt = $conn->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
$stmt->bind_param('ss', $passwordHash, $currentUsername);

if ($stmt->execute()) {
    echo '<div class="alert alert-success mb-4 text-center" role="alert">Your password has been updated successfully.</div>';
} else {
    echo '<div class="alert alert-danger mb-4 text-center" role="alert">An error occurred while updating the password.</div>';
}

$stmt->close();
$conn->close();

header('refresh:2; url=dashboard.php');

echo '
</body>
</html>';
