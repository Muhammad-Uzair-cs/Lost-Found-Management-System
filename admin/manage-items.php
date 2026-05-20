<?php
// admin/manage-items.php
define('PAGE_TITLE', 'Manage Items');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$type  = $_GET['type']   ?? 'lost';
$items = ($type === 'lost') ? getLostItems() : getFoundItems();
?>
<?php require __DIR__ . '/../includes/header.php'; ?>

<div class="page-header" style="background:linear-gradient(135deg,#1e1b4b 0%,#3730a3 100%);">
  <div class="container">
    <a href="<?= SITE_URL ?>/admin/index.php" style="color:rgba(255,255,255,.7);font-size:.88rem;"><i class="fa fa-arrow-left"></i> Admin Dashboard</a>
    <h1 style="margin-top:8px;"><i class="fa fa-list"></i> Manage <?= ucfirst($type) ?> Items</h1>
  </div>
</div>

<div class="page-body">
<div class="container">

  <!-- Type toggle -->
  <div class="card mb-3">
    <div class="card-body" style="padding:14px 20px;display:flex;gap:8px;align-items:center;">
      <a href="?type=lost"  class="btn btn-sm <?= $type==='lost' ?'btn-primary':'btn-outline'?>"><i class="fa fa-circle-question"></i> Lost Items</a>
      <a href="?type=found" class="btn btn-sm <?= $type==='found'?'btn-primary':'btn-outline'?>"><i class="fa fa-box-open"></i> Found Items</a>
      <span style="margin-left:auto;font-size:.85rem;color:var(--text-muted)"><?= count($items) ?> total</span>
    </div>
  </div>

  <div class="card">
    <div class="card-body" style="padding:0;">
      <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>Photo</th><th>Item Name</th><th>Category</th><th>Location</th><th>Date</th><th>Reporter</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td>
              <?php if ($item['image_path'] && file_exists(UPLOAD_DIR . $item['image_path'])): ?>
              <img src="<?= UPLOAD_URL . $item['image_path'] ?>" class="td-img">
              <?php else: ?>
              <div style="width:48px;height:48px;background:var(--surface);border-radius:6px;display:flex;align-items:center;justify-content:center;color:#aab4cc;font-size:.9rem;"><i class="fa fa-image"></i></div>
              <?php endif; ?>
            </td>
            <td><strong><?= sanitize($item['name']) ?></strong></td>
            <td><?= sanitize($item['category']) ?></td>
            <td><?= sanitize($item['building']) ?><?= $item['room'] ? ' / ' . sanitize($item['room']) : '' ?></td>
            <td style="white-space:nowrap;"><?= date('d M Y', strtotime($type==='lost'?$item['date_lost']:$item['date_found'])) ?></td>
            <td style="font-size:.82rem;"><?= sanitize($item['reporter']) ?></td>
            <td><?= statusBadge($item['status']) ?></td>
            <td>
              <div style="display:flex;gap:6px;">
                <a href="<?= SITE_URL ?>/item-detail.php?type=<?= $type ?>&id=<?= $item['item_id'] ?>" class="btn btn-outline btn-sm"><i class="fa fa-eye"></i></a>
                <a href="<?= SITE_URL ?>/delete-item.php?type=<?= $type ?>&id=<?= $item['item_id'] ?>" class="btn btn-danger btn-sm"
                   data-confirm="Delete this item report? This cannot be undone."><i class="fa fa-trash"></i></a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$items): ?>
          <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted);">No items found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>

</div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
