<?php
// dashboard.php
define('PAGE_TITLE', 'Dashboard');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user      = currentUser();
$myLost    = getLostItems(currentUserId());
$myFound   = getFoundItems(currentUserId());
$myMatches = getMatches(currentUserId());
$myClaims  = getClaims(currentUserId());
$notifs    = getUserNotifications(currentUserId());

// Recent 4 for preview
$recentLost  = array_slice($myLost,  0, 4);
$recentFound = array_slice($myFound, 0, 4);
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<!-- Hero -->
<div class="dash-hero">
  <div class="container">
    <h1>Welcome, <?= sanitize($user['full_name']) ?> 👋</h1>
    <p>Manage your lost &amp; found reports, track matches, and submit claims.</p>
    <div class="dash-actions">
      <a href="<?= SITE_URL ?>/report-lost.php"  class="btn btn-accent btn-lg"><i class="fa fa-plus"></i> Report Lost Item</a>
      <a href="<?= SITE_URL ?>/report-found.php" class="btn btn-outline btn-lg" style="color:#fff;border-color:rgba(255,255,255,.4);"><i class="fa fa-box-open"></i> Report Found Item</a>
      <a href="<?= SITE_URL ?>/search.php"       class="btn btn-outline btn-lg" style="color:#fff;border-color:rgba(255,255,255,.4);"><i class="fa fa-search"></i> Search Items</a>
    </div>
  </div>
</div>

<div class="page-body">
<div class="container">

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card blue">
      <i class="fa fa-circle-question stat-icon"></i>
      <div class="stat-number"><?= count($myLost) ?></div>
      <div class="stat-label">My Lost Reports</div>
    </div>
    <div class="stat-card green">
      <i class="fa fa-box-open stat-icon"></i>
      <div class="stat-number"><?= count($myFound) ?></div>
      <div class="stat-label">My Found Reports</div>
    </div>
    <div class="stat-card orange">
      <i class="fa fa-link stat-icon"></i>
      <div class="stat-number"><?= count($myMatches) ?></div>
      <div class="stat-label">Matches Found</div>
    </div>
    <div class="stat-card">
      <i class="fa fa-file-circle-check stat-icon"></i>
      <div class="stat-number"><?= count($myClaims) ?></div>
      <div class="stat-label">Claim Requests</div>
    </div>
    <div class="stat-card red">
      <i class="fa fa-bell stat-icon"></i>
      <div class="stat-number"><?= count(array_filter($notifs, fn($n) => !$n['is_read'])) ?></div>
      <div class="stat-label">Unread Notifications</div>
    </div>
  </div>

  <!-- Notifications banner -->
  <?php $unreadNotifs = array_filter($notifs, fn($n) => !$n['is_read']); ?>
  <?php if ($unreadNotifs): ?>
  <div class="alert alert-info mt-2" data-auto-dismiss="1">
    <i class="fa fa-bell"></i>
    You have <?= count($unreadNotifs) ?> unread notification(s).
    <a href="<?= SITE_URL ?>/notifications.php" style="font-weight:600;margin-left:8px;">View all →</a>
  </div>
  <?php endif; ?>

  <!-- My Recent Lost Items -->
  <div class="card mt-3">
    <div class="card-header">
      <h2><i class="fa fa-circle-question" style="color:var(--primary-lt)"></i> My Lost Reports</h2>
      <a href="<?= SITE_URL ?>/lost-items.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="card-body">
      <?php if ($recentLost): ?>
      <div class="items-grid">
        <?php foreach ($recentLost as $item): ?>
        <div class="item-card">
          <?php if ($item['image_path'] && file_exists(UPLOAD_DIR . $item['image_path'])): ?>
            <img src="<?= UPLOAD_URL . $item['image_path'] ?>" alt="<?= sanitize($item['name']) ?>" class="item-thumb">
          <?php else: ?>
            <div class="item-thumb-placeholder"><i class="fa fa-image"></i></div>
          <?php endif; ?>
          <div class="item-info">
            <div class="item-name"><?= sanitize($item['name']) ?></div>
            <div class="item-meta">
              <span><i class="fa fa-tag"></i> <?= sanitize($item['category']) ?></span>
              <span><i class="fa fa-location-dot"></i> <?= sanitize($item['building']) ?></span>
            </div>
            <div class="item-desc"><?= sanitize($item['description']) ?></div>
          </div>
          <div class="item-footer">
            <?= statusBadge($item['status']) ?>
            <a href="<?= SITE_URL ?>/item-detail.php?type=lost&id=<?= $item['item_id'] ?>" class="btn btn-outline btn-sm">Details</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="empty-state">
        <i class="fa fa-circle-question"></i>
        <h3>No Lost Reports Yet</h3>
        <p>Lost something? Report it now and we'll help you find it.</p>
        <a href="<?= SITE_URL ?>/report-lost.php" class="btn btn-primary mt-2">Report Lost Item</a>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recent Matches -->
  <?php if ($myMatches): ?>
  <div class="card mt-3">
    <div class="card-header">
      <h2><i class="fa fa-link" style="color:var(--accent)"></i> Recent Matches</h2>
      <a href="<?= SITE_URL ?>/matches.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="card-body">
      <div class="table-wrapper">
      <table>
        <thead><tr><th>Lost Item</th><th>Found Item</th><th>Score</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach (array_slice($myMatches, 0, 5) as $m): ?>
        <tr>
          <td><?= sanitize($m['lost_name']) ?></td>
          <td><?= sanitize($m['found_name']) ?></td>
          <td>
            <div class="match-score">
              <div class="score-ring" data-score="<?= round($m['score']) ?>" style="--pct:<?= round($m['score']) ?>%">
                <span style="position:absolute;font-weight:700;font-size:.7rem;z-index:1"><?= round($m['score']) ?>%</span>
              </div>
            </div>
          </td>
          <td><?= statusBadge($m['status']) ?></td>
          <td><a href="<?= SITE_URL ?>/matches.php" class="btn btn-outline btn-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
