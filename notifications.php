<?php
// notifications.php
define('PAGE_TITLE', 'Notifications');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

markNotificationsRead(currentUserId());
$notifications = getUserNotifications(currentUserId());
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header">
  <div class="container">
    <h1><i class="fa fa-bell"></i> Notifications</h1>
    <p>Stay updated on matches, claim status, and system alerts.</p>
  </div>
</div>

<div class="page-body">
<div class="container" style="max-width:750px;">

  <div class="card">
    <div class="card-header">
      <h2>All Notifications</h2>
      <span style="font-size:.82rem;color:var(--text-muted)"><?= count($notifications) ?> total</span>
    </div>
    <?php if ($notifications): ?>
    <div class="notif-list">
      <?php foreach ($notifications as $n): ?>
      <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
        <div class="notif-icon">
          <i class="fa fa-bell"></i>
        </div>
        <div class="notif-text">
          <div><?= htmlspecialchars($n['message']) ?></div>
          <div class="notif-time"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></div>
        </div>
        <?php if (!$n['is_read']): ?>
        <span class="badge badge-open" style="flex-shrink:0;">New</span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="fa fa-bell-slash"></i>
      <h3>No Notifications</h3>
      <p>You're all caught up! Notifications appear here when matches are found or claims are updated.</p>
    </div>
    <?php endif; ?>
  </div>

</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
