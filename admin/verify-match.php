<?php
// admin/verify-match.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$id     = (int)($_GET['id']     ?? 0);
$action = $_GET['action']       ?? '';

if ($id && in_array($action, ['verified', 'rejected'])) {
    verifyMatch($id, $action, currentUserId());
}

header('Location: ' . SITE_URL . '/matches.php');
exit;
