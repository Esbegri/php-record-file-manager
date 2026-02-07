<?php
// app/security.php

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function csrf_field(): string {
  $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
  return "<input type='hidden' name='csrf_token' value='{$t}'>";
}

function csrf_verify(): void {
  $posted = $_POST['csrf_token'] ?? '';
  if (!$posted || !hash_equals(csrf_token(), $posted)) {
    http_response_code(419);
    die("CSRF validation failed.");
  }
}
