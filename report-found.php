<?php
// report-found.php
define('PAGE_TITLE', 'Report Found Item');
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
        'name'        => trim($_POST['name']         ?? ''),
        'description' => trim($_POST['description']  ?? ''),
        'date_found'  => $_POST['date_found']        ?? '',
    ];

    if (!$data['name'] || !$data['category_id'] || !$data['location_id'] || !$data['date_found']) {
        $error = 'Please fill in all required fields.';
    } else {
        $imageName = null;
        if (!empty($_FILES['item_image']['name'])) {
            $imageName = uploadImage($_FILES['item_image']);
            if ($imageName === null) $error = 'Invalid image. Use JPG/PNG under 5MB.';
        }
        if (!$error) {
            if (reportFoundItem($data, $imageName)) {
                $success = 'Found item reported! The owner may be notified if a match is detected.';
            } else {
                $error = 'Failed to submit. Please try again.';
            }
        }
    }
}
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header" style="background:linear-gradient(135deg,#065f46 0%,#059669 100%);">
  <div class="container">
    <h1><i class="fa fa-box-open"></i> Report a Found Item</h1>
    <p>Help reunite lost items with their owners by reporting what you found.</p>
  </div>
</div>

<div class="page-body">
<div class="container">
<div class="two-col">

  <div>
    <?php if ($error):   ?><div class="alert alert-error"  data-auto-dismiss="1"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success" data-auto-dismiss="1"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="card">
      <div class="card-header"><h2>Found Item Details</h2></div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">

          <div class="form-group">
            <label class="form-label">Item Name <span style="color:var(--danger)">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Blue Backpack"
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
              <label class="form-label">Date Found <span style="color:var(--danger)">*</span></label>
              <input type="date" name="date_found" class="form-control"
                     value="<?= htmlspecialchars($_POST['date_found'] ?? date('Y-m-d')) ?>"
                     max="<?= date('Y-m-d') ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Location Where Found <span style="color:var(--danger)">*</span></label>
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
              placeholder="Color, condition, contents, brand, any identifiers..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Photo of Found Item (recommended)</label>
            <div class="upload-area" id="uploadArea">
              <div class="upload-icon"><i class="fa fa-camera"></i></div>
              <p>Upload a clear photo of the item</p>
              <p style="font-size:.78rem;margin-top:4px">JPG, PNG, WEBP · Max 5 MB</p>
              <input type="file" name="item_image" id="itemImage" accept="image/*" style="display:none">
            </div>
            <img id="imagePreview" class="hidden" alt="Preview" style="max-width:200px;border-radius:8px;margin-top:12px;">
          </div>

          <div style="display:flex;gap:12px;margin-top:8px;">
            <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-paper-plane"></i> Submit Report</button>
            <a href="<?= SITE_URL ?>/dashboard.php" class="btn btn-outline btn-lg">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div>
    <div class="card sidebar-card" style="border-top:3px solid var(--success);">
      <div class="card-header"><h2><i class="fa fa-heart" style="color:var(--success)"></i> Thank You!</h2></div>
      <div class="card-body" style="font-size:.88rem;">
        <p style="color:var(--text-muted);">By reporting a found item, you help someone recover their belongings. Here's what happens next:</p>
        <ul style="margin-top:12px;display:flex;flex-direction:column;gap:10px;color:var(--text-muted);">
          <li>1️⃣ Your report is saved and matched automatically.</li>
          <li>2️⃣ The potential owner is notified if a match is found.</li>
          <li>3️⃣ The owner submits a claim with proof of ownership.</li>
          <li>4️⃣ Admin verifies and approves the claim.</li>
          <li>5️⃣ You hand over the item to the Admin Office.</li>
        </ul>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header"><h2>Important Notes</h2></div>
      <div class="card-body" style="font-size:.88rem;color:var(--text-muted);">
        <ul style="display:flex;flex-direction:column;gap:8px;">
          <li>⚠️ Please hand in valuable items to the Admin Office or Security.</li>
          <li>⚠️ Do not keep found items for more than 3 days.</li>
          <li>⚠️ Do not share item details publicly to prevent fraudulent claims.</li>
          <li>✅ Your report is confidential.</li>
        </ul>
      </div>
    </div>
  </div>

</div>
</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
