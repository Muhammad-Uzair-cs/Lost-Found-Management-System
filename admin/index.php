<?php
// admin/index.php
define('PAGE_TITLE', 'Admin Dashboard');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$stats        = getAdminStats();
$recentLost   = getLostItems(0, '');
$recentFound  = getFoundItems(0, '');
$pendingClaims = getClaims();
$pendingClaims = array_filter($pendingClaims, fn($c) => $c['status'] === 'pending');
$allMatches   = getMatches();
?>
<?php require __DIR__ . '/../includes/header.php'; ?>

<div class="dash-hero" style="background:linear-gradient(135deg,#1e1b4b 0%,#3730a3 100%);">
  <div class="container">
    <div style="display:flex;align-items:center;gap:12px;">
      <span style="background:rgba(255,255,255,.15);padding:8px 14px;border-radius:8px;font-size:.8rem;font-weight:600;letter-spacing:.05em;">ADMIN PANEL</span>
    </div>
    <h1 style="margin-top:10px;">Control Center</h1>
    <p>Manage all reports, verify matches, review claims, and maintain system integrity.</p>
    <div class="dash-actions">
      <a href="<?= SITE_URL ?>/admin/manage-claims.php"  class="btn btn-accent"><i class="fa fa-file-circle-check"></i> Review Claims (<?= count($pendingClaims) ?>)</a>
      <a href="<?= SITE_URL ?>/admin/manage-matches.php" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.4);"><i class="fa fa-link"></i> Verify Matches</a>
      <a href="<?= SITE_URL ?>/admin/manage-users.php"   class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.4);"><i class="fa fa-users"></i> Manage Users</a>
    </div>
  </div>
</div>

<div class="page-body">
<div class="container">

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card blue">
      <i class="fa fa-users stat-icon"></i>
      <div class="stat-number"><?= $stats['total_users'] ?></div>
      <div class="stat-label">Total Users</div>
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
    <div class="stat-card red">
      <i class="fa fa-clock stat-icon"></i>
      <div class="stat-number"><?= $stats['pending_claims'] ?></div>
      <div class="stat-label">Pending Claims</div>
    </div>
    <div class="stat-card purple">
      <i class="fa fa-circle-check stat-icon"></i>
      <div class="stat-number"><?= $stats['resolved'] ?></div>
      <div class="stat-label">Resolved Cases</div>
    </div>
  </div>

  <!-- Pending Claims Table -->
  <?php if ($pendingClaims): ?>
  <div class="card mt-3">
    <div class="card-header">
      <h2><i class="fa fa-triangle-exclamation" style="color:var(--warning)"></i> Pending Claims (<?= count($pendingClaims) ?>)</h2>
      <a href="<?= SITE_URL ?>/admin/manage-claims.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="card-body" style="padding:0;">
      <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>Item</th><th>Claimant</th><th>Location</th><th>Submitted</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($pendingClaims, 0, 5) as $c): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <?php if ($c['image_path'] && file_exists(UPLOAD_DIR . $c['image_path'])): ?>
                <img src="<?= UPLOAD_URL . $c['image_path'] ?>" class="td-img">
                <?php else: ?>
                <div style="width:48px;height:48px;background:var(--surface);border-radius:6px;display:flex;align-items:center;justify-content:center;color:#aab4cc;"><i class="fa fa-box-open"></i></div>
                <?php endif; ?>
                <strong><?= sanitize($c['item_name']) ?></strong>
              </div>
            </td>
            <td><?= sanitize($c['claimant_name']) ?><br><span style="font-size:.78rem;color:var(--text-muted)"><?= sanitize($c['claimant_email']) ?></span></td>
            <td><?= sanitize($c['building']) ?></td>
            <td><?= date('d M Y', strtotime($c['submitted_at'])) ?></td>
            <td>
              <a href="<?= SITE_URL ?>/admin/manage-claims.php?review=<?= $c['claim_id'] ?>" class="btn btn-primary btn-sm">Review</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Recent Reports Two Col -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
    <div class="card">
      <div class="card-header">
        <h2>Recent Lost Reports</h2>
        <a href="<?= SITE_URL ?>/admin/manage-items.php?type=lost" class="btn btn-outline btn-sm">All</a>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
        <table>
          <thead><tr><th>Item</th><th>User</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($recentLost, 0, 6) as $i): ?>
            <tr data-href="<?= SITE_URL ?>/item-detail.php?type=lost&id=<?= $i['item_id'] ?>">
              <td><?= sanitize($i['name']) ?></td>
              <td style="font-size:.82rem;color:var(--text-muted)"><?= sanitize($i['reporter']) ?></td>
              <td><?= statusBadge($i['status']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h2>Recent Found Reports</h2>
        <a href="<?= SITE_URL ?>/admin/manage-items.php?type=found" class="btn btn-outline btn-sm">All</a>
      </div>
      <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
        <table>
          <thead><tr><th>Item</th><th>User</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($recentFound, 0, 6) as $i): ?>
            <tr data-href="<?= SITE_URL ?>/item-detail.php?type=found&id=<?= $i['item_id'] ?>">
              <td><?= sanitize($i['name']) ?></td>
              <td style="font-size:.82rem;color:var(--text-muted)"><?= sanitize($i['reporter']) ?></td>
              <td><?= statusBadge($i['status']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>

</div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
