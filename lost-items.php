<?php
// lost-items.php
define('PAGE_TITLE', 'Lost Items');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$filter = $_GET['status'] ?? '';
$items  = getLostItems(0, $filter);  // all users' open reports
$categories = getCategories();
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header">
  <div class="container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
    <div>
      <h1><i class="fa fa-circle-question"></i> Lost Items</h1>
      <p>Browse all reported lost items across the university.</p>
    </div>
    <a href="<?= SITE_URL ?>/report-lost.php" class="btn btn-accent"><i class="fa fa-plus"></i> Report Lost Item</a>
  </div>
</div>

<div class="page-body">
<div class="container">

  <!-- Filters -->
  <div class="card mb-3">
    <div class="card-body" style="padding:16px 22px;">
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <span style="font-weight:600;font-size:.88rem;">Filter:</span>
        <?php
        $statuses = [''=>'All', 'open'=>'Open', 'matched'=>'Matched', 'claimed'=>'Claimed', 'closed'=>'Closed'];
        foreach ($statuses as $val => $label):
          $active = ($filter === $val) ? 'btn-primary' : 'btn-outline';
        ?>
        <a href="?status=<?= $val ?>" class="btn btn-sm <?= $active ?>"><?= $label ?></a>
        <?php endforeach; ?>
        <span style="margin-left:auto;font-size:.85rem;color:var(--text-muted)"><?= count($items) ?> item(s) found</span>
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
        <div class="item-thumb-placeholder"><i class="fa fa-circle-question"></i></div>
      <?php endif; ?>
      <div class="item-info">
        <div class="item-name"><?= sanitize($item['name']) ?></div>
        <div class="item-meta">
          <span><i class="fa fa-tag"></i> <?= sanitize($item['category']) ?></span>
          <span><i class="fa fa-location-dot"></i> <?= sanitize($item['building']) ?></span>
          <span><i class="fa fa-calendar"></i> <?= date('d M Y', strtotime($item['date_lost'])) ?></span>
        </div>
        <div class="item-desc"><?= sanitize($item['description']) ?></div>
        <div style="font-size:.78rem;color:var(--text-muted);margin-top:4px;">
          <i class="fa fa-user"></i> <?= sanitize($item['reporter']) ?>
        </div>
      </div>
      <div class="item-footer">
        <?= statusBadge($item['status']) ?>
        <a href="<?= SITE_URL ?>/item-detail.php?type=lost&id=<?= $item['item_id'] ?>" class="btn btn-outline btn-sm">
          <i class="fa fa-eye"></i> Details
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty-state">
    <i class="fa fa-circle-question"></i>
    <h3>No Lost Items Found</h3>
    <p>No lost item reports match your filter.</p>
    <a href="<?= SITE_URL ?>/report-lost.php" class="btn btn-primary mt-2">Report a Lost Item</a>
  </div>
  <?php endif; ?>

</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
