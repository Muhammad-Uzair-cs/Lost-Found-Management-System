<?php
// delete-item.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

if ($type === 'lost') {
    $item = getLostItemById($id);
    if ($item && ($item['user_id'] == currentUserId() || isAdmin())) {
        if ($item['image_path'] && file_exists(UPLOAD_DIR . $item['image_path'])) {
            @unlink(UPLOAD_DIR . $item['image_path']);
        }
        deleteLostItem($id);
    }
    header('Location: ' . SITE_URL . '/lost-items.php');
} elseif ($type === 'found') {
    $item = getFoundItemById($id);
    if ($item && ($item['user_id'] == currentUserId() || isAdmin())) {
        if ($item['image_path'] && file_exists(UPLOAD_DIR . $item['image_path'])) {
            @unlink(UPLOAD_DIR . $item['image_path']);
        }
        deleteFoundItem($id);
    }
    header('Location: ' . SITE_URL . '/found-items.php');
} else {
    header('Location: ' . SITE_URL . '/dashboard.php');
}
exit;
