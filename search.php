<?php
// search.php
define('PAGE_TITLE', 'Search Items');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$keyword    = trim($_GET['q']          ?? '');
$type       = $_GET['type']            ?? 'all';
$categoryId = (int)($_GET['category']  ?? 0);
$locationId = (int)($_GET['location']  ?? 0);

$results    = [];
$searched   = false;
if ($keyword !== '' || $categoryId || $locationId) {
    $results  = searchItems($keyword, $type, $categoryId, $locationId);
    $searched = true;
}

$categories = getCategories();
$locations  = getLocations();
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header">
  <div class="container">
    <h1><i class="fa fa-search"></i> Search Items</h1>
    <p>Search across all lost and found reports by keyword, category, or location.</p>
  </div>
</div>

<div class="page-body">
<div class="container">

  <!-- Search Box -->
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET">
        <div class="search-bar">
          <input type="text" name="q" placeholder="Search by name, description, or location..."
                 value="<?= htmlspecialchars($keyword) ?>">
          <button type="submit"><i class="fa fa-search"></i></button>
        </div>
        <div class="filter-row">
          <select name="type">
            <option value="all"   <?= $type==='all'   ?'selected':''?>>All Types</option>
            <option value="lost"  <?= $type==='lost'  ?'selected':''?>>Lost Items Only</option>
            <option value="found" <?= $type==='found' ?'selected':''?>>Found Items Only</option>
          </select>
          <select name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_id'] ?>" <?= $categoryId==$cat['category_id']?'selected':''?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <select name="location">
            <option value="">All Locations</option>
            <?php foreach ($locations as $loc): ?>
            <option value="<?= $loc['location_id'] ?>" <?= $locationId==$loc['location_id']?'selected':''?>>
              <?= htmlspecialchars($loc['building']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Apply Filters</button>
          <a href="<?= SITE_URL ?>/search.php" class="btn btn-outline btn-sm"><i class="fa fa-rotate-left"></i> Clear</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Results -->
  <?php if ($searched): ?>
    <?php if ($results): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
      <h2 style="font-family:var(--font-display);font-size:1.1rem;">
        <?= count($results) ?> result(s) for
        <?php if ($keyword): ?>"<strong><?= htmlspecialchars($keyword) ?></strong>"<?php else: ?>your filters<?php endif; ?>
      </h2>
    </div>
    <div class="items-grid">
      <?php foreach ($results as $item): ?>
      <div class="item-card">
        <?php if ($item['image_path'] && file_exists(UPLOAD_DIR . $item['image_path'])): ?>
          <img src="<?= UPLOAD_URL . $item['image_path'] ?>" alt="<?= sanitize($item['name']) ?>" class="item-thumb">
        <?php else: ?>
          <div class="item-thumb-placeholder">
            <i class="fa <?= $item['type']==='lost' ? 'fa-circle-question' : 'fa-box-open' ?>"></i>
          </div>
        <?php endif; ?>
        <div class="item-info">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
            <span class="badge <?= $item['type']==='lost' ? 'badge-open' : 'badge-claimed' ?>" style="font-size:.7rem;">
              <?= strtoupper($item['type']) ?>
            </span>
          </div>
          <div class="item-name"><?= sanitize($item['name']) ?></div>
          <div class="item-meta">
            <span><i class="fa fa-tag"></i> <?= sanitize($item['category']) ?></span>
            <span><i class="fa fa-location-dot"></i> <?= sanitize($item['building']) ?></span>
            <span><i class="fa fa-calendar"></i> <?= date('d M Y', strtotime($item['date_event'])) ?></span>
          </div>
          <div class="item-desc"><?= sanitize($item['description']) ?></div>
        </div>
        <div class="item-footer">
          <?= statusBadge($item['status']) ?>
          <div style="display:flex;gap:6px;">
            <a href="<?= SITE_URL ?>/item-detail.php?type=<?= $item['type'] ?>&id=<?= $item['item_id'] ?>"
               class="btn btn-outline btn-sm"><i class="fa fa-eye"></i> View</a>
            <?php if ($item['type'] === 'found' && $item['status'] === 'open'): ?>
            <a href="<?= SITE_URL ?>/claim.php?id=<?= $item['item_id'] ?>" class="btn btn-primary btn-sm">Claim</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <i class="fa fa-magnifying-glass"></i>
      <h3>No Results Found</h3>
      <p>Try different keywords, or browse all lost/found items.</p>
      <div style="display:flex;gap:12px;justify-content:center;margin-top:16px;">
        <a href="<?= SITE_URL ?>/lost-items.php"  class="btn btn-outline">Browse Lost Items</a>
        <a href="<?= SITE_URL ?>/found-items.php" class="btn btn-outline">Browse Found Items</a>
      </div>
    </div>
    <?php endif; ?>
  <?php else: ?>
  <div class="empty-state">
    <i class="fa fa-search"></i>
    <h3>Enter a Search Term</h3>
    <p>Type a keyword in the search box above, or use the filters to narrow down results.</p>
  </div>
  <?php endif; ?>

</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
