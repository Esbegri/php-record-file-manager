<?php
session_start();

// If already logged in, go to dashboard
if (isset($_SESSION['user'], $_SESSION['role'])) {
    header('Location: dashboard.php');
    exit;
}

$showError = isset($_GET['error']) && $_GET['error'] === '1';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Content-Language" content="en">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Record Manager - Login</title>

  <!-- Font Awesome -->
  <script src="https://kit.fontawesome.com/f18df7a83f.js" crossorigin="anonymous"></script>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

  <style>
    body { background: #f8f9fa; }
    .card-login { max-width: 540px; margin: 6vh auto; }
    .brandlogo { max-height: 120px; object-fit: contain; margin: 1rem auto 0; display:block; }
  </style>
</head>
<body>

<div class="container">
  <div class="card card-login shadow-sm">
    <div class="card-body">

      <!-- Logo (optional) -->
      <img src="./gazilogo.png" alt="Logo" class="brandlogo">

      <form action="login.php" method="post" autocomplete="off" class="mt-3">

        <div class="form-group">
          <label for="username"><strong>Username</strong></label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text">User</span>
            </div>
            <input id="username" name="username" type="text" class="form-control"
                   placeholder="Enter your username" required>
          </div>
        </div>

        <div class="form-group">
          <label for="password"><strong>Password</strong></label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-key"></i></span>
            </div>
            <input id="password" name="password" type="password" class="form-control"
                   placeholder="Enter your password" required>
          </div>
        </div>

        <?php if ($showError): ?>
          <div class="alert alert-danger" role="alert">
            Invalid username or password.
          </div>
        <?php endif; ?>

        <div class="text-right">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-sign-in-alt mr-1"></i> Sign In
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- jQuery & Bootstrap Bundle -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
