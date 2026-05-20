<?php
// map.php — Interactive map of all item locations
define('PAGE_TITLE', 'Item Map');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$db = getDB();

// Get all open lost items with coordinates
$lostItems = $db->query("
    SELECT li.item_id, li.name, li.description, li.status, li.image_path,
           l.building, l.room, l.latitude, l.longitude,
           c.name AS category, 'lost' AS type
    FROM lost_items li
    JOIN locations  l ON li.location_id = l.location_id
    JOIN categories c ON li.category_id = c.category_id
    WHERE l.latitude IS NOT NULL AND l.longitude IS NOT NULL
    AND li.status IN ('open','matched')
")->fetchAll();

$foundItems = $db->query("
    SELECT fi.item_id, fi.name, fi.description, fi.status, fi.image_path,
           l.building, l.room, l.latitude, l.longitude,
           c.name AS category, 'found' AS type
    FROM found_items fi
    JOIN locations  l ON fi.location_id = l.location_id
    JOIN categories c ON fi.category_id = c.category_id
    WHERE l.latitude IS NOT NULL AND l.longitude IS NOT NULL
    AND fi.status IN ('open','matched')
")->fetchAll();

$allItems = array_merge($lostItems, $foundItems);

// Encode for JS
$mapData = json_encode(array_map(fn($item) => [
    'id'       => $item['item_id'],
    'type'     => $item['type'],
    'name'     => $item['name'],
    'category' => $item['category'],
    'building' => $item['building'],
    'room'     => $item['room'] ?? '',
    'status'   => $item['status'],
    'lat'      => (float)$item['latitude'],
    'lng'      => (float)$item['longitude'],
    'url'      => SITE_URL . '/item-detail.php?type=' . $item['type'] . '&id=' . $item['item_id'],
], $allItems));
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header">
  <div class="container">
    <h1><i class="fa fa-map-location-dot"></i> Item Locations Map</h1>
    <p>View all active lost &amp; found reports plotted on the university map.</p>
  </div>
</div>

<div class="page-body">
<div class="container">

  <!-- Legend -->
  <div class="card mb-3">
    <div class="card-body" style="padding:14px 20px;display:flex;gap:20px;flex-wrap:wrap;align-items:center;">
      <span style="font-weight:600;font-size:.88rem;">Legend:</span>
      <span style="display:flex;align-items:center;gap:6px;font-size:.85rem;">
        <span style="width:14px;height:14px;border-radius:50%;background:#dc2626;display:inline-block;"></span> Lost Item
      </span>
      <span style="display:flex;align-items:center;gap:6px;font-size:.85rem;">
        <span style="width:14px;height:14px;border-radius:50%;background:#16a34a;display:inline-block;"></span> Found Item
      </span>
      <span style="margin-left:auto;font-size:.85rem;color:var(--text-muted);">
        <?= count($lostItems) ?> lost · <?= count($foundItems) ?> found active
      </span>
    </div>
  </div>

  <!-- Map -->
  <div class="card">
    <div class="card-body" style="padding:0;">
      <div id="map" style="height:520px;border-radius:var(--radius);"></div>
    </div>
  </div>

  <!-- Item List below map -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">

    <div class="card">
      <div class="card-header">
        <h2 style="color:var(--danger)"><i class="fa fa-circle-question"></i> Active Lost Items (<?= count($lostItems) ?>)</h2>
      </div>
      <div class="card-body" style="padding:0;max-height:320px;overflow-y:auto;">
        <?php if ($lostItems): ?>
        <table>
          <thead><tr><th>Item</th><th>Location</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($lostItems as $item): ?>
            <tr>
              <td>
                <strong><?= sanitize($item['name']) ?></strong>
                <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($item['category']) ?></div>
              </td>
              <td style="font-size:.82rem;"><?= sanitize($item['building']) ?></td>
              <td><a href="<?= SITE_URL ?>/item-detail.php?type=lost&id=<?= $item['item_id'] ?>" class="btn btn-outline btn-sm"><i class="fa fa-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:.88rem;">No active lost items.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 style="color:var(--success)"><i class="fa fa-box-open"></i> Active Found Items (<?= count($foundItems) ?>)</h2>
      </div>
      <div class="card-body" style="padding:0;max-height:320px;overflow-y:auto;">
        <?php if ($foundItems): ?>
        <table>
          <thead><tr><th>Item</th><th>Location</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($foundItems as $item): ?>
            <tr>
              <td>
                <strong><?= sanitize($item['name']) ?></strong>
                <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($item['category']) ?></div>
              </td>
              <td style="font-size:.82rem;"><?= sanitize($item['building']) ?></td>
              <td><a href="<?= SITE_URL ?>/item-detail.php?type=found&id=<?= $item['item_id'] ?>" class="btn btn-outline btn-sm"><i class="fa fa-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:.88rem;">No active found items.</div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const items = <?= $mapData ?>;

  // Default center: University of Knowledge (Peshawar approx.)
  const map = L.map('map').setView([33.9980, 71.4844], 17);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  function makeIcon(type) {
    const color = type === 'lost' ? '#dc2626' : '#16a34a';
    const icon  = type === 'lost' ? '?' : '✓';
    return L.divIcon({
      html: `<div style="background:${color};color:#fff;border-radius:50% 50% 50% 0;
             width:32px;height:32px;display:flex;align-items:center;justify-content:center;
             font-size:14px;font-weight:700;transform:rotate(-45deg);
             box-shadow:0 2px 8px rgba(0,0,0,.3);">
             <span style="transform:rotate(45deg)">${icon}</span></div>`,
      iconSize:   [32, 32],
      iconAnchor: [16, 32],
      popupAnchor:[0, -34],
      className: ''
    });
  }

  if (items.length === 0) {
    // Show university center with info
    L.marker([33.9980, 71.4844]).addTo(map)
     .bindPopup('<strong>University of Knowledge</strong><br>No active items to show.')
     .openPopup();
  } else {
    items.forEach(item => {
      const marker = L.marker([item.lat, item.lng], { icon: makeIcon(item.type) }).addTo(map);
      marker.bindPopup(`
        <div style="min-width:180px;font-family:sans-serif;">
          <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;
               color:${item.type==='lost'?'#dc2626':'#16a34a'};margin-bottom:4px;">
            ${item.type.toUpperCase()} ITEM
          </div>
          <div style="font-weight:700;font-size:.95rem;margin-bottom:4px;">${item.name}</div>
          <div style="font-size:.8rem;color:#666;margin-bottom:2px;">
            📍 ${item.building}${item.room?' — '+item.room:''}
          </div>
          <div style="font-size:.8rem;color:#666;margin-bottom:8px;">🏷️ ${item.category}</div>
          <a href="${item.url}" style="background:${item.type==='lost'?'#dc2626':'#16a34a'};
             color:#fff;padding:5px 12px;border-radius:5px;font-size:.78rem;
             text-decoration:none;display:inline-block;">View Details →</a>
        </div>
      `);
    });

    // Fit map to markers
    const group = L.featureGroup(items.map(i => L.marker([i.lat, i.lng])));
    map.fitBounds(group.getBounds().pad(0.15));
  }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
