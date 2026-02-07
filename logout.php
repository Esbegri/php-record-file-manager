<?php
/**
 * logout.php
 * Securely terminates the user session and cleans up session cookies.
 */

require __DIR__ . '/app/bootstrap.php';

// 1. Clear all session variables
$_SESSION = [];

// 2. If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Finally, destroy the session on the server side
session_destroy();

// 4. Redirect to the login page with a success message
header("Location: login_form.php?status=logged_out");
exit;