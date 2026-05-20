<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/dashboard.php');
} else {
    header('Location: ' . SITE_URL . '/login.php');
}
exit;
