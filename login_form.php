<?php
/**
 * login_form.php
 * The user interface for authenticating into the Record Management System.
 */

require __DIR__ . '/app/bootstrap.php';

// Redirect to dashboard if session is already active
if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// Error handling based on URL parameters
$errorMessage = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_credentials':
            $errorMessage = 'Invalid username or password.';
            break;
        case 'empty_fields':
            $errorMessage = 'Please fill in all required fields.';
            break;
        case 'unauthorized':
            $errorMessage = 'Please login to access this area.';
            break;
        default:
            $errorMessage = 'An error occurred. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Record Management System</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background-color: #f4f7f6; height: 100vh; display: flex; align-items: center; }
        .login-card { border: none; border-radius: 1rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1); }
        .card-header { background: #fff; border-bottom: none; border-radius: 1rem 1rem 0 0 !important; }
        .btn-login { border-radius: 2rem; padding: 0.6rem 2rem; font-weight: 600; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card login-card p-4">
                <div class="card-header text-center pt-4">
                    <h3 class="text-primary font-weight-bold">Record Manager</h3>
                    <p class="text-muted small">Authentication Required</p>
                </div>
                
                <div class="card-body">
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger text-center small" role="alert">
                            <i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($errorMessage) ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="form-group mb-4">
                            <label for="username" class="font-weight-bold">Username</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-user text-muted"></i></span>
                                </div>
                                <input type="text" name="username" id="username" class="form-control border-left-0" placeholder="Username" required autofocus>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="password" class="font-weight-bold">Password</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-lock text-muted"></i></span>
                                </div>
                                <input type="password" name="password" id="password" class="form-control border-left-0" placeholder="Password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-login shadow-sm mt-3">
                            Sign In <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </form>
                </div>
                
                <div class="card-footer bg-white border-0 text-center pb-4">
                    <small class="text-muted">&copy; <?= date('Y') ?> Record Management System</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>