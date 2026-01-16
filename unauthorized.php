<?php
// This file must be included AFTER session_start()

if (!isset($_SESSION['user']) || !isset($_SESSION['role'])) {
    header('Location: login_form.php');
    exit;
}
