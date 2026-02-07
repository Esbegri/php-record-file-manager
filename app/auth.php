<?php
/**
 * auth.php
 * Authentication and Authorization helpers.
 */

/**
 * Ensures the user is logged in. 
 * Redirects to login page if not.
 */
function require_login() {
    if (!isset($_SESSION['user'])) {
        header('Location: login_form.php');
        exit;
    }
}

/**
 * Ensures the user has admin privileges.
 * Redirects to unauthorized page if not.
 */
function require_admin() {
    require_login(); // First, check if logged in
    
    // Role 1 = Admin, Role 0 = User (or as defined in your DB)
    if (!isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
        header('Location: unauthorized.php');
        exit;
    }
}

/**
 * Checks if current user is admin (returns boolean)
 */
function is_admin() {
    return isset($_SESSION['role']) && (int)$_SESSION['role'] === 1;
}