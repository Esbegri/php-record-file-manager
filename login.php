<?php
session_start();

// Read inputs
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

// Basic validation
if ($username === '' || $password === '') {
    header('Location: login_form.php?error=1');
    exit;
}

// DB
$conn = new mysqli('localhost', 'root', '', 'belge');
$conn->set_charset('utf8');

if ($conn->connect_error) {
    // Don't expose connection details
    header('Location: login_form.php?error=1');
    exit;
}

// Fetch user
$stmt = $conn->prepare('SELECT password_hash, role FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($passwordHash, $role);
    $stmt->fetch();

    // Verify password
    if (password_verify($password, $passwordHash)) {
        // Session standard
        $_SESSION['user'] = $username;
        $_SESSION['role'] = (string)$role;

        // Optional: regenerate session ID to prevent fixation
        session_regenerate_id(true);

        $stmt->close();
        $conn->close();

        header('Location: dashboard.php');
        exit;
    }
}

$stmt->close();
$conn->close();

// Generic error (do not reveal whether username exists)
header('Location: login_form.php?error=1');
exit;
