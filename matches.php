<?php
// matches.php
define('PAGE_TITLE', 'Matches');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// Normal users see only their matches; admins see all
$matches = isAdmin() ? getMatches() : getMatches(currentUserId());
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header" style="background:linear-gradient(135deg,#78350f 0%,#d97706 100%);">
  <div class="container">
    <h1><i class="fa fa-link"></i> Item Matches</h1>
    <p>Automatic matches detected by the system based on category, location, date, and keywords.</p>
  </div>
</div>

<div class="page-body">
<div class="container">

<?php if ($matches): ?>
<div style="display:flex;flex-direction:column;gap:20px;">
  <?php foreach ($matches as $m): ?>
  <div class="card">
    <div class="card-header">
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <span class="badge" style="background:#fef3c7;color:#78350f;font-size:.78rem;">
          <i class="fa fa-tag"></i> &nbsp;<?= sanitize($m['category']) ?>
        </span>
        <?= statusBadge($m['status']) ?>
        <span style="font-size:.82rem;color:var(--text-muted);margin-left:4px;">
          Matched: <?= date('d M Y', strtotime($m['matched_at'])) ?>
        </span>
      </div>
      <!-- Score -->
      <div class="match-score">
        <div class="score-ring" data-score="<?= round($m['score']) ?>" style="--pct:<?= round($m['score']) ?>%">
          <span style="position:absolute;font-weight:700;font-size:.68rem;z-index:1"><?= round($m['score']) ?>%</span>
        </div>
        <span class="score-label">Match</span>
      </div>
    </div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:20px;align-items:center;">

        <!-- Lost Item -->
        <div style="background:var(--surface);border-radius:10px;padding:16px;border:1px solid var(--border);">
          <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--danger);margin-bottom:8px;">
            <i class="fa fa-circle-question"></i> LOST ITEM
          </div>
          <?php if ($m['lost_image'] && file_exists(UPLOAD_DIR . $m['lost_image'])): ?>
          <img src="<?= UPLOAD_URL . $m['lost_image'] ?>" style="width:100%;height:120px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
          <?php endif; ?>
          <div style="font-weight:700;font-family:var(--font-display);"><?= sanitize($m['lost_name']) ?></div>
          <div style="font-size:.8rem;color:var(--text-muted);margin-top:4px;">
            <i class="fa fa-location-dot"></i> <?= sanitize($m['lost_loc']) ?><br>
            <i class="fa fa-calendar"></i> <?= date('d M Y', strtotime($m['date_lost'])) ?>
          </div>
          <a href="<?= SITE_URL ?>/item-detail.php?type=lost&id=<?= $m['lost_item_id'] ?>" class="btn btn-outline btn-sm mt-2">
            <i class="fa fa-eye"></i> View
          </a>
        </div>

        <!-- Arrow -->
        <div style="text-align:center;color:var(--accent);font-size:1.5rem;">
          <i class="fa fa-arrows-left-right"></i>
        </div>

        <!-- Found Item -->
        <div style="background:var(--surface);border-radius:10px;padding:16px;border:1px solid var(--border);">
          <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--success);margin-bottom:8px;">
            <i class="fa fa-box-open"></i> FOUND ITEM
          </div>
          <?php if ($m['found_image'] && file_exists(UPLOAD_DIR . $m['found_image'])): ?>
          <img src="<?= UPLOAD_URL . $m['found_image'] ?>" style="width:100%;height:120px;object-fit:cover;border-radius:8px;margin-bottom:10px;">
          <?php endif; ?>
          <div style="font-weight:700;font-family:var(--font-display);"><?= sanitize($m['found_name']) ?></div>
          <div style="font-size:.8rem;color:var(--text-muted);margin-top:4px;">
            <i class="fa fa-location-dot"></i> <?= sanitize($m['found_loc']) ?><br>
            <i class="fa fa-calendar"></i> <?= date('d M Y', strtotime($m['date_found'])) ?>
          </div>
          <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
            <a href="<?= SITE_URL ?>/item-detail.php?type=found&id=<?= $m['found_item_id'] ?>" class="btn btn-outline btn-sm">
              <i class="fa fa-eye"></i> View
            </a>
            <?php if ($m['status'] !== 'verified'): ?>
            <a href="<?= SITE_URL ?>/claim.php?id=<?= $m['found_item_id'] ?>" class="btn btn-primary btn-sm">
              <i class="fa fa-hand"></i> Claim
            </a>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <?php if (isAdmin() && $m['status'] === 'pending'): ?>
      <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;gap:10px;">
        <a href="<?= SITE_URL ?>/admin/verify-match.php?id=<?= $m['match_id'] ?>&action=verified" class="btn btn-success btn-sm"
           data-confirm="Mark this match as verified?">
          <i class="fa fa-check"></i> Verify Match
        </a>
        <a href="<?= SITE_URL ?>/admin/verify-match.php?id=<?= $m['match_id'] ?>&action=rejected" class="btn btn-danger btn-sm"
           data-confirm="Reject this match?">
          <i class="fa fa-times"></i> Reject
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
  <i class="fa fa-link"></i>
  <h3>No Matches Yet</h3>
  <p>Matches are generated automatically when a lost and found item share category, location, or description keywords.</p>
  <a href="<?= SITE_URL ?>/report-lost.php" class="btn btn-primary mt-2">Report a Lost Item</a>
</div>
<?php endif; ?>

</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
