<?php
// report-lost.php
define('PAGE_TITLE', 'Report Lost Item');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$error = $success = '';
$categories = getCategories();
$locations  = getLocations();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id'     => currentUserId(),
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'location_id' => (int)($_POST['location_id'] ?? 0),
        'name'        => trim($_POST['name']        ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'date_lost'   => $_POST['date_lost'] ?? '',
    ];

    if (!$data['name'] || !$data['category_id'] || !$data['location_id'] || !$data['date_lost']) {
        $error = 'Please fill in all required fields.';
    } else {
        $imageName = null;
        if (!empty($_FILES['item_image']['name'])) {
            $imageName = uploadImage($_FILES['item_image']);
            if ($imageName === null) $error = 'Invalid image. Use JPG/PNG under 5MB.';
        }
        if (!$error) {
            if (reportLostItem($data, $imageName)) {
                $success = 'Lost item reported successfully! We will notify you if a match is found.';
            } else {
                $error = 'Failed to submit report. Please try again.';
            }
        }
    }
}
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header">
  <div class="container">
    <h1><i class="fa fa-circle-question"></i> Report a Lost Item</h1>
    <p>Fill in the details below. The system will automatically search for matches.</p>
  </div>
</div>

<div class="page-body">
<div class="container">
<div class="two-col">

  <!-- Form -->
  <div>
    <?php if ($error):   ?><div class="alert alert-error"  data-auto-dismiss="1"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success" data-auto-dismiss="1"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="card">
      <div class="card-header"><h2>Item Information</h2></div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">

          <div class="form-group">
            <label class="form-label">Item Name <span style="color:var(--danger)">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Black Dell Laptop"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Category <span style="color:var(--danger)">*</span></label>
              <select name="category_id" class="form-control" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"
                  <?= (($_POST['category_id'] ?? 0) == $cat['category_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Date Lost <span style="color:var(--danger)">*</span></label>
              <input type="date" name="date_lost" class="form-control"
                     value="<?= htmlspecialchars($_POST['date_lost'] ?? date('Y-m-d')) ?>"
                     max="<?= date('Y-m-d') ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Location <span style="color:var(--danger)">*</span></label>
            <select name="location_id" class="form-control" required>
              <option value="">-- Select Location --</option>
              <?php foreach ($locations as $loc): ?>
              <option value="<?= $loc['location_id'] ?>"
                <?= (($_POST['location_id'] ?? 0) == $loc['location_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($loc['building']) ?> — <?= htmlspecialchars($loc['room'] ?? '') ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"
              placeholder="Color, brand, distinguishing features, what was inside, etc."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            <div class="form-hint">The more details you provide, the better the matching accuracy.</div>
          </div>

          <div class="form-group">
            <label class="form-label">Item Photo (optional)</label>
            <div class="upload-area" id="uploadArea">
              <div class="upload-icon"><i class="fa fa-cloud-arrow-up"></i></div>
              <p>Click to upload or drag &amp; drop</p>
              <p style="font-size:.78rem;margin-top:4px">JPG, PNG, WEBP · Max 5 MB</p>
              <input type="file" name="item_image" id="itemImage" accept="image/*" style="display:none">
            </div>
            <img id="imagePreview" class="hidden" alt="Preview" style="max-width:200px;border-radius:8px;margin-top:12px;">
          </div>

          <div style="display:flex;gap:12px;margin-top:8px;">
            <button type="submit" class="btn btn-accent btn-lg"><i class="fa fa-paper-plane"></i> Submit Report</button>
            <a href="<?= SITE_URL ?>/dashboard.php" class="btn btn-outline btn-lg">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Sidebar tips -->
  <div>
    <div class="card sidebar-card">
      <div class="card-header"><h2><i class="fa fa-lightbulb" style="color:var(--accent)"></i> Tips</h2></div>
      <div class="card-body">
        <ul style="display:flex;flex-direction:column;gap:12px;font-size:.88rem;">
          <li><i class="fa fa-check-circle" style="color:var(--success)"></i> &nbsp;Be as specific as possible about the item description.</li>
          <li><i class="fa fa-check-circle" style="color:var(--success)"></i> &nbsp;Upload a photo for better matching accuracy.</li>
          <li><i class="fa fa-check-circle" style="color:var(--success)"></i> &nbsp;Include brand names, colors, and unique identifiers.</li>
          <li><i class="fa fa-check-circle" style="color:var(--success)"></i> &nbsp;Select the exact building/location where you last saw it.</li>
          <li><i class="fa fa-check-circle" style="color:var(--success)"></i> &nbsp;The system auto-matches with found reports immediately.</li>
        </ul>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header"><h2>How Matching Works</h2></div>
      <div class="card-body" style="font-size:.88rem;color:var(--text-muted);">
        <p>Our system scores matches based on:</p>
        <ul style="margin-top:10px;display:flex;flex-direction:column;gap:8px;">
          <li>🏷️ <strong>Category</strong> — 40 points</li>
          <li>📍 <strong>Location</strong> — 25 points</li>
          <li>📅 <strong>Date Proximity</strong> — up to 20 points</li>
          <li>🔤 <strong>Keyword Overlap</strong> — up to 15 points</li>
        </ul>
        <p style="margin-top:12px;">Matches scoring 70+ are flagged as high-confidence and you will be notified automatically.</p>
      </div>
    </div>
  </div>

</div>
</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
