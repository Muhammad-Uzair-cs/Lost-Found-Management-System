<?php
// admin/reports.php
define('PAGE_TITLE', 'Reports & Analytics');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();

// Monthly lost items (last 6 months)
$monthlyLost = $db->query("
    SELECT DATE_FORMAT(date_lost, '%b %Y') AS month,
           DATE_FORMAT(date_lost, '%Y-%m') AS month_key,
           COUNT(*) AS total
    FROM lost_items
    WHERE date_lost >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month_key ORDER BY month_key ASC
")->fetchAll();

// Monthly found items
$monthlyFound = $db->query("
    SELECT DATE_FORMAT(date_found, '%b %Y') AS month,
           DATE_FORMAT(date_found, '%Y-%m') AS month_key,
           COUNT(*) AS total
    FROM found_items
    WHERE date_found >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month_key ORDER BY month_key ASC
")->fetchAll();

// Items by category
$byCategory = $db->query("
    SELECT c.name AS category,
           COUNT(DISTINCT li.item_id) AS lost_count,
           COUNT(DISTINCT fi.item_id) AS found_count
    FROM categories c
    LEFT JOIN lost_items  li ON li.category_id = c.category_id
    LEFT JOIN found_items fi ON fi.category_id = c.category_id
    GROUP BY c.category_id ORDER BY (lost_count + found_count) DESC
")->fetchAll();

// Items by location
$byLocation = $db->query("
    SELECT l.building,
           COUNT(DISTINCT li.item_id) AS lost_count,
           COUNT(DISTINCT fi.item_id) AS found_count
    FROM locations l
    LEFT JOIN lost_items  li ON li.location_id = l.location_id
    LEFT JOIN found_items fi ON fi.location_id = l.location_id
    GROUP BY l.location_id ORDER BY (lost_count + found_count) DESC
")->fetchAll();

// Resolution rate
$totalClaims   = (int)$db->query("SELECT COUNT(*) FROM claims")->fetchColumn();
$resolvedClaims= (int)$db->query("SELECT COUNT(*) FROM claims WHERE status='approved'")->fetchColumn();
$resolutionRate = $totalClaims > 0 ? round(($resolvedClaims / $totalClaims) * 100) : 0;

$stats = getAdminStats();

// Top reporters
$topUsers = $db->query("
    SELECT u.full_name, u.email,
           COUNT(DISTINCT li.item_id) AS lost_count,
           COUNT(DISTINCT fi.item_id) AS found_count
    FROM users u
    LEFT JOIN lost_items  li ON u.user_id = li.user_id
    LEFT JOIN found_items fi ON u.user_id = fi.user_id
    WHERE u.role = 'user'
    GROUP BY u.user_id
    HAVING (lost_count + found_count) > 0
    ORDER BY (lost_count + found_count) DESC
    LIMIT 5
")->fetchAll();
?>
<?php require __DIR__ . '/../includes/header.php'; ?>

<div class="page-header" style="background:linear-gradient(135deg,#1e1b4b 0%,#3730a3 100%);">
  <div class="container">
    <a href="<?= SITE_URL ?>/admin/index.php" style="color:rgba(255,255,255,.7);font-size:.88rem;"><i class="fa fa-arrow-left"></i> Admin Dashboard</a>
    <h1 style="margin-top:8px;"><i class="fa fa-chart-bar"></i> Reports &amp; Analytics</h1>
    <p>System-wide statistics and trends.</p>
  </div>
</div>

<div class="page-body">
<div class="container">

  <!-- KPI Cards -->
  <div class="stats-grid">
    <div class="stat-card blue">
      <i class="fa fa-users stat-icon"></i>
      <div class="stat-number"><?= $stats['total_users'] ?></div>
      <div class="stat-label">Registered Users</div>
    </div>
    <div class="stat-card">
      <i class="fa fa-circle-question stat-icon"></i>
      <div class="stat-number"><?= $stats['total_lost'] ?></div>
      <div class="stat-label">Lost Reports</div>
    </div>
    <div class="stat-card green">
      <i class="fa fa-box-open stat-icon"></i>
      <div class="stat-number"><?= $stats['total_found'] ?></div>
      <div class="stat-label">Found Reports</div>
    </div>
    <div class="stat-card orange">
      <i class="fa fa-link stat-icon"></i>
      <div class="stat-number"><?= $stats['total_matches'] ?></div>
      <div class="stat-label">Total Matches</div>
    </div>
    <div class="stat-card purple">
      <i class="fa fa-percent stat-icon"></i>
      <div class="stat-number"><?= $resolutionRate ?>%</div>
      <div class="stat-label">Resolution Rate</div>
    </div>
    <div class="stat-card green">
      <i class="fa fa-circle-check stat-icon"></i>
      <div class="stat-number"><?= $stats['resolved'] ?></div>
      <div class="stat-label">Cases Resolved</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:4px;">

    <!-- Items by Category -->
    <div class="card">
      <div class="card-header"><h2><i class="fa fa-tags" style="color:var(--primary-lt)"></i> Items by Category</h2></div>
      <div class="card-body" style="padding:0;">
        <table>
          <thead><tr><th>Category</th><th>Lost</th><th>Found</th><th>Total</th><th>Bar</th></tr></thead>
          <tbody>
            <?php
            $maxCat = max(array_map(fn($r) => $r['lost_count'] + $r['found_count'], $byCategory) ?: [1]);
            foreach ($byCategory as $row):
              $total = $row['lost_count'] + $row['found_count'];
              $pct   = $maxCat > 0 ? round(($total / $maxCat) * 100) : 0;
            ?>
            <tr>
              <td><strong><?= sanitize($row['category']) ?></strong></td>
              <td style="color:var(--danger)"><?= $row['lost_count'] ?></td>
              <td style="color:var(--success)"><?= $row['found_count'] ?></td>
              <td><?= $total ?></td>
              <td style="width:120px;">
                <div style="background:var(--border);border-radius:4px;height:8px;overflow:hidden;">
                  <div style="background:var(--primary-lt);height:8px;width:<?= $pct ?>%;border-radius:4px;transition:width .5s;"></div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Items by Location -->
    <div class="card">
      <div class="card-header"><h2><i class="fa fa-location-dot" style="color:var(--danger)"></i> Hotspot Locations</h2></div>
      <div class="card-body" style="padding:0;">
        <table>
          <thead><tr><th>Building</th><th>Lost</th><th>Found</th><th>Total</th><th>Bar</th></tr></thead>
          <tbody>
            <?php
            $maxLoc = max(array_map(fn($r) => $r['lost_count'] + $r['found_count'], $byLocation) ?: [1]);
            foreach ($byLocation as $row):
              $total = $row['lost_count'] + $row['found_count'];
              if ($total === 0) continue;
              $pct   = $maxLoc > 0 ? round(($total / $maxLoc) * 100) : 0;
            ?>
            <tr>
              <td><strong><?= sanitize($row['building']) ?></strong></td>
              <td style="color:var(--danger)"><?= $row['lost_count'] ?></td>
              <td style="color:var(--success)"><?= $row['found_count'] ?></td>
              <td><?= $total ?></td>
              <td style="width:120px;">
                <div style="background:var(--border);border-radius:4px;height:8px;overflow:hidden;">
                  <div style="background:var(--accent);height:8px;width:<?= $pct ?>%;border-radius:4px;"></div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">

    <!-- Monthly Trend -->
    <div class="card">
      <div class="card-header"><h2><i class="fa fa-chart-line" style="color:var(--success)"></i> Monthly Trend (Last 6 Months)</h2></div>
      <div class="card-body">
        <?php if ($monthlyLost || $monthlyFound):
          // Merge months
          $months = [];
          foreach ($monthlyLost  as $r) $months[$r['month_key']]['month'] = $r['month'], $months[$r['month_key']]['lost']  = $r['total'];
          foreach ($monthlyFound as $r) $months[$r['month_key']]['month'] = $r['month'], $months[$r['month_key']]['found'] = $r['total'];
          ksort($months);
          $maxMonth = max(array_map(fn($m) => max($m['lost'] ?? 0, $m['found'] ?? 0), $months) ?: [1]);
        ?>
        <div style="display:flex;flex-direction:column;gap:14px;">
          <?php foreach ($months as $m): ?>
          <div>
            <div style="font-size:.8rem;font-weight:600;margin-bottom:5px;color:var(--text-muted)"><?= $m['month'] ?></div>
            <div style="display:flex;gap:8px;align-items:center;font-size:.78rem;">
              <span style="width:40px;color:var(--danger);text-align:right;"><?= $m['lost'] ?? 0 ?></span>
              <div style="flex:1;height:10px;background:var(--border);border-radius:4px;overflow:hidden;">
                <div style="height:10px;width:<?= $maxMonth>0?round((($m['lost']??0)/$maxMonth)*100):0 ?>%;background:var(--danger);border-radius:4px;"></div>
              </div>
              <span style="color:var(--danger);font-size:.72rem;">Lost</span>
            </div>
            <div style="display:flex;gap:8px;align-items:center;font-size:.78rem;margin-top:3px;">
              <span style="width:40px;color:var(--success);text-align:right;"><?= $m['found'] ?? 0 ?></span>
              <div style="flex:1;height:10px;background:var(--border);border-radius:4px;overflow:hidden;">
                <div style="height:10px;width:<?= $maxMonth>0?round((($m['found']??0)/$maxMonth)*100):0 ?>%;background:var(--success);border-radius:4px;"></div>
              </div>
              <span style="color:var(--success);font-size:.72rem;">Found</span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding:30px 0;"><p>Not enough data yet.</p></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Top Users -->
    <div class="card">
      <div class="card-header"><h2><i class="fa fa-trophy" style="color:var(--accent)"></i> Most Active Users</h2></div>
      <div class="card-body" style="padding:0;">
        <table>
          <thead><tr><th>#</th><th>User</th><th>Lost</th><th>Found</th><th>Total</th></tr></thead>
          <tbody>
            <?php foreach ($topUsers as $i => $u): ?>
            <tr>
              <td>
                <span style="font-weight:700;color:<?= $i===0?'var(--accent)':($i===1?'#94a3b8':($i===2?'#b45309':'var(--text-muted)')) ?>">
                  <?= $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : ($i+1))) ?>
                </span>
              </td>
              <td>
                <strong><?= sanitize($u['full_name']) ?></strong>
                <div style="font-size:.75rem;color:var(--text-muted)"><?= sanitize($u['email']) ?></div>
              </td>
              <td style="color:var(--danger);font-weight:600"><?= $u['lost_count'] ?></td>
              <td style="color:var(--success);font-weight:600"><?= $u['found_count'] ?></td>
              <td style="font-weight:700"><?= $u['lost_count'] + $u['found_count'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$topUsers): ?>
            <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted);">No data yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Claim Status Breakdown -->
  <div class="card mt-3">
    <div class="card-header"><h2><i class="fa fa-pie-chart" style="color:var(--primary-lt)"></i> Claim Status Breakdown</h2></div>
    <div class="card-body">
      <?php
      $claimStats = $db->query("SELECT status, COUNT(*) as cnt FROM claims GROUP BY status")->fetchAll();
      $claimMap   = [];
      foreach ($claimStats as $cs) $claimMap[$cs['status']] = $cs['cnt'];
      $pending  = $claimMap['pending']  ?? 0;
      $approved = $claimMap['approved'] ?? 0;
      $rejected = $claimMap['rejected'] ?? 0;
      $total    = $pending + $approved + $rejected;
      ?>
      <div style="display:flex;gap:28px;flex-wrap:wrap;align-items:center;">
        <?php
        $blocks = [
          ['Pending',  $pending,  'var(--warning)', 'fa-clock'],
          ['Approved', $approved, 'var(--success)', 'fa-circle-check'],
          ['Rejected', $rejected, 'var(--danger)',  'fa-circle-xmark'],
        ];
        foreach ($blocks as [$label, $count, $color, $icon]):
          $pct = $total > 0 ? round(($count / $total) * 100) : 0;
        ?>
        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;min-width:100px;">
          <div style="width:72px;height:72px;border-radius:50%;background:conic-gradient(<?= $color ?> <?= $pct ?>%, var(--border) <?= $pct ?>%);display:flex;align-items:center;justify-content:center;position:relative;">
            <div style="position:absolute;width:52px;height:52px;border-radius:50%;background:var(--card);display:flex;align-items:center;justify-content:center;">
              <i class="fa <?= $icon ?>" style="color:<?= $color ?>;font-size:1.1rem;"></i>
            </div>
          </div>
          <div style="font-family:var(--font-display);font-size:1.3rem;font-weight:800;"><?= $count ?></div>
          <div style="font-size:.78rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;"><?= $label ?></div>
          <div style="font-size:.78rem;color:<?= $color ?>;font-weight:600;"><?= $pct ?>%</div>
        </div>
        <?php endforeach; ?>
        <?php if ($total === 0): ?>
        <p style="color:var(--text-muted);font-size:.88rem;">No claims submitted yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
