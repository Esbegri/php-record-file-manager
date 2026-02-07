<?php
/**
 * login.php
 * Handles user authentication, password verification, and session management.
 */

require __DIR__ . '/app/bootstrap.php';

// If user is already logged in, redirect to dashboard
if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// Read and sanitize inputs
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

// Basic validation
if ($username === '' || $password === '') {
    header('Location: login_form.php?error=empty_fields');
    exit;
}

try {
    // Fetch user details using the centralized PDO instance
    $stmt = $pdo->prepare('SELECT password_hash, role FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Verify the provided password against the stored hash
        if (password_verify($password, $user['password_hash'])) {
            
            // Set session variables
            $_SESSION['user'] = $username;
            $_SESSION['role'] = (int)$user['role'];

            // Security: Regenerate session ID to prevent Session Fixation attacks
            session_regenerate_id(true);

            header('Location: dashboard.php');
            exit;
        }
    }

    // Generic error message for security (don't specify if user or password was wrong)
    header('Location: login_form.php?error=invalid_credentials');
    exit;

} catch (PDOException $e) {
    // In production, log $e->getMessage() and show a generic error
    header('Location: login_form.php?error=server_error');
    exit;
}