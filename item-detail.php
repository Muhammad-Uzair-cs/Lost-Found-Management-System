<?php
// item-detail.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$type = $_GET['type'] ?? 'lost';
$id   = (int)($_GET['id'] ?? 0);

if ($type === 'lost') {
    $item = getLostItemById($id);
    $dateLabel = 'Date Lost';
    $dateVal   = $item['date_lost'] ?? '';
} else {
    $item = getFoundItemById($id);
    $dateLabel = 'Date Found';
    $dateVal   = $item['date_found'] ?? '';
}

if (!$item) { header('Location: ' . SITE_URL . '/dashboard.php'); exit; }

define('PAGE_TITLE', htmlspecialchars($item['name']));
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header" style="<?= $type==='found'?'background:linear-gradient(135deg,#065f46 0%,#059669 100%)':'' ?>">
  <div class="container">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
      <a href="<?= SITE_URL ?>/<?= $type ?>-items.php" style="color:rgba(255,255,255,.7);font-size:.88rem;"><i class="fa fa-arrow-left"></i> Back</a>
      <span style="color:rgba(255,255,255,.4)">|</span>
      <?= statusBadge($item['status']) ?>
    </div>
    <h1 style="margin-top:10px;"><?= sanitize($item['name']) ?></h1>
    <p><?= $type === 'lost' ? 'Lost Item Report' : 'Found Item Report' ?> · <?= sanitize($item['category']) ?></p>
  </div>
</div>

<div class="page-body">
<div class="container">
<div class="two-col">

  <!-- Main Content -->
  <div style="display:flex;flex-direction:column;gap:20px;">

    <!-- Item Image -->
    <?php if ($item['image_path'] && file_exists(UPLOAD_DIR . $item['image_path'])): ?>
    <div class="card overflow-hidden">
      <img src="<?= UPLOAD_URL . $item['image_path'] ?>" alt="<?= sanitize($item['name']) ?>"
           style="width:100%;max-height:400px;object-fit:contain;background:#f0f2f8;padding:12px;">
    </div>
    <?php endif; ?>

    <!-- Details -->
    <div class="card">
      <div class="card-header"><h2>Item Details</h2></div>
      <div class="card-body">
        <table style="width:100%;font-size:.9rem;">
          <tr><th style="width:38%;padding:10px 0;color:var(--text-muted);font-weight:500;vertical-align:top;">Name</th><td style="padding:10px 0;font-weight:600;"><?= sanitize($item['name']) ?></td></tr>
          <tr><th style="padding:10px 0;color:var(--text-muted);font-weight:500;vertical-align:top;">Category</th><td style="padding:10px 0;"><?= sanitize($item['category']) ?></td></tr>
          <tr><th style="padding:10px 0;color:var(--text-muted);font-weight:500;vertical-align:top;"><?= $dateLabel ?></th><td style="padding:10px 0;"><?= date('d F Y', strtotime($dateVal)) ?></td></tr>
          <tr><th style="padding:10px 0;color:var(--text-muted);font-weight:500;vertical-align:top;">Location</th><td style="padding:10px 0;"><?= sanitize($item['building']) ?><?= $item['room'] ? ' — ' . sanitize($item['room']) : '' ?></td></tr>
          <tr><th style="padding:10px 0;color:var(--text-muted);font-weight:500;vertical-align:top;">Status</th><td style="padding:10px 0;"><?= statusBadge($item['status']) ?></td></tr>
          <tr><th style="padding:10px 0;color:var(--text-muted);font-weight:500;vertical-align:top;">Description</th><td style="padding:10px 0;"><?= nl2br(sanitize($item['description'])) ?></td></tr>
          <tr><th style="padding:10px 0;color:var(--text-muted);font-weight:500;vertical-align:top;">Reported by</th><td style="padding:10px 0;"><?= sanitize($item['reporter']) ?></td></tr>
        </table>
      </div>
    </div>

    <!-- Map -->
    <?php if (!empty($item['latitude']) && !empty($item['longitude'])): ?>
    <div class="card">
      <div class="card-header"><h2><i class="fa fa-map-location-dot"></i> Location on Map</h2></div>
      <div class="card-body" style="padding:0 0 16px;">
        <div id="map"></div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        initMap(<?= $item['latitude'] ?>, <?= $item['longitude'] ?>,
          '<?= addslashes($item['building'] . ' — ' . ($item['room'] ?? '')) ?>');
      });
    </script>
    <?php endif; ?>

  </div>

  <!-- Sidebar -->
  <div>
    <!-- Actions -->
    <div class="card sidebar-card" style="border-top:3px solid var(--accent);">
      <div class="card-header"><h2>Actions</h2></div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">

        <?php if ($type === 'found' && $item['status'] === 'open' && $item['user_id'] != currentUserId()): ?>
        <a href="<?= SITE_URL ?>/claim.php?id=<?= $item['item_id'] ?>" class="btn btn-primary">
          <i class="fa fa-hand"></i> Submit Claim Request
        </a>
        <?php endif; ?>

        <?php if ($item['user_id'] == currentUserId() || isAdmin()): ?>
        <a href="<?= SITE_URL ?>/<?= $type === 'lost' ? 'report-lost' : 'report-found' ?>.php?edit=<?= $item['item_id'] ?>" class="btn btn-outline">
          <i class="fa fa-pen"></i> Edit Report
        </a>
        <a href="<?= SITE_URL ?>/delete-item.php?type=<?= $type ?>&id=<?= $item['item_id'] ?>"
           class="btn btn-danger btn-sm" data-confirm="Are you sure you want to delete this report?">
          <i class="fa fa-trash"></i> Delete Report
        </a>
        <?php endif; ?>

        <a href="<?= SITE_URL ?>/search.php?q=<?= urlencode($item['name']) ?>" class="btn btn-outline">
          <i class="fa fa-search"></i> Search Similar Items
        </a>
      </div>
    </div>

    <!-- Reporter Contact -->
    <div class="card mt-3">
      <div class="card-header"><h2>Reporter Info</h2></div>
      <div class="card-body" style="font-size:.88rem;">
        <p><i class="fa fa-user" style="color:var(--text-muted)"></i> &nbsp;<?= sanitize($item['reporter']) ?></p>
        <?php if (isAdmin()): ?>
        <p style="margin-top:8px;"><i class="fa fa-envelope" style="color:var(--text-muted)"></i> &nbsp;<a href="mailto:<?= sanitize($item['reporter_email']) ?>"><?= sanitize($item['reporter_email']) ?></a></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
