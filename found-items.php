<?php
// found-items.php
define('PAGE_TITLE', 'Found Items');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$filter = $_GET['status'] ?? '';
$items  = getFoundItems(0, $filter);
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header" style="background:linear-gradient(135deg,#065f46 0%,#059669 100%);">
  <div class="container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
    <div>
      <h1><i class="fa fa-box-open"></i> Found Items</h1>
      <p>Browse items found and turned in across the university.</p>
    </div>
    <a href="<?= SITE_URL ?>/report-found.php" class="btn btn-accent"><i class="fa fa-plus"></i> Report Found Item</a>
  </div>
</div>

<div class="page-body">
<div class="container">

  <div class="card mb-3">
    <div class="card-body" style="padding:16px 22px;">
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <span style="font-weight:600;font-size:.88rem;">Filter:</span>
        <?php
        $statuses = [''=>'All', 'open'=>'Open', 'matched'=>'Matched', 'claimed'=>'Claimed'];
        foreach ($statuses as $val => $label):
          $active = ($filter === $val) ? 'btn-primary' : 'btn-outline';
        ?>
        <a href="?status=<?= $val ?>" class="btn btn-sm <?= $active ?>"><?= $label ?></a>
        <?php endforeach; ?>
        <span style="margin-left:auto;font-size:.85rem;color:var(--text-muted)"><?= count($items) ?> item(s)</span>
      </div>
    </div>
  </div>

  <?php if ($items): ?>
  <div class="items-grid">
    <?php foreach ($items as $item): ?>
    <div class="item-card">
      <?php if ($item['image_path'] && file_exists(UPLOAD_DIR . $item['image_path'])): ?>
        <img src="<?= UPLOAD_URL . $item['image_path'] ?>" alt="<?= sanitize($item['name']) ?>" class="item-thumb">
      <?php else: ?>
        <div class="item-thumb-placeholder"><i class="fa fa-box-open"></i></div>
      <?php endif; ?>
      <div class="item-info">
        <div class="item-name"><?= sanitize($item['name']) ?></div>
        <div class="item-meta">
          <span><i class="fa fa-tag"></i> <?= sanitize($item['category']) ?></span>
          <span><i class="fa fa-location-dot"></i> <?= sanitize($item['building']) ?></span>
          <span><i class="fa fa-calendar"></i> <?= date('d M Y', strtotime($item['date_found'])) ?></span>
        </div>
        <div class="item-desc"><?= sanitize($item['description']) ?></div>
        <div style="font-size:.78rem;color:var(--text-muted);margin-top:4px;">
          <i class="fa fa-user"></i> Reported by: <?= sanitize($item['reporter']) ?>
        </div>
      </div>
      <div class="item-footer">
        <?= statusBadge($item['status']) ?>
        <div style="display:flex;gap:6px;">
          <a href="<?= SITE_URL ?>/item-detail.php?type=found&id=<?= $item['item_id'] ?>" class="btn btn-outline btn-sm"><i class="fa fa-eye"></i> Details</a>
          <?php if ($item['status'] === 'open' && $item['user_id'] != currentUserId()): ?>
          <a href="<?= SITE_URL ?>/claim.php?id=<?= $item['item_id'] ?>" class="btn btn-primary btn-sm"><i class="fa fa-hand"></i> Claim</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty-state">
    <i class="fa fa-box-open"></i>
    <h3>No Found Items</h3>
    <p>Nothing matches your current filter.</p>
  </div>
  <?php endif; ?>

</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
