<?php
/**
 * change_password.php
 * Handles secure password updates for the current user.
 */

require __DIR__ . '/app/bootstrap.php';

// Access Control
require_login();
csrf_verify(); // Secure against CSRF attacks

// Read and sanitize inputs
$newPassword     = isset($_POST['password1']) ? trim($_POST['password1']) : '';
$confirmPassword = isset($_POST['password2']) ? trim($_POST['password2']) : '';
$currentUsername = $_SESSION['user'] ?? '';

// Basic Validation
if ($currentUsername === '') {
    header('Location: login.php?error=unauthorized');
    exit;
}

if ($newPassword === '' || $confirmPassword === '') {
    header('Location: dashboard.php?error=empty_fields');
    exit;
}

if ($newPassword !== $confirmPassword) {
    header('Location: dashboard.php?error=password_mismatch');
    exit;
}

// Password Policy (Minimum 6 characters)
if (strlen($newPassword) < 6) {
    header('Location: dashboard.php?error=password_too_short');
    exit;
}

try {
    // Hash the password securely using modern bcrypt
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password using the centralized PDO instance
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
    $success = $stmt->execute([$passwordHash, $currentUsername]);

    if ($success) {
        header('Location: dashboard.php?status=password_updated');
    } else {
        header('Location: dashboard.php?error=update_failed');
    }

} catch (PDOException $e) {
    // Error handling
    header('Location: dashboard.php?error=db_error');
}
exit;