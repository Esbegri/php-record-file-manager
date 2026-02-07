<?php
/**
 * unauthorized.php
 * Displays a 403 Forbidden message when a user lacks sufficient permissions.
 */

require __DIR__ . '/app/bootstrap.php';

// If the user isn't even logged in, send them to the login page instead
if (empty($_SESSION['user'])) {
    header('Location: login_form.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { text-align: center; padding: 40px; border-radius: 15px; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .error-icon { font-size: 80px; color: #dc3545; margin-bottom: 20px; }
        .error-title { font-weight: 700; color: #343a40; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="error-card">
                <div class="error-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h1 class="error-title">Access Denied</h1>
                <p class="text-muted">Sorry, you do not have the required permissions to view this page.</p>
                <hr>
                <p class="small text-secondary">If you believe this is an error, please contact your system administrator.</p>
                <div class="mt-4">
                    <a href="dashboard.php" class="btn btn-primary px-4">
                        <i class="fa-solid fa-house mr-2"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>