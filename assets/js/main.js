/* ============================================================
   main.js — Lost & Found Management System
   ============================================================ */

/* ── Image Upload Preview ────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {

  const uploadArea = document.getElementById('uploadArea');
  const fileInput  = document.getElementById('itemImage');
  const preview    = document.getElementById('imagePreview');

  if (uploadArea && fileInput) {
    uploadArea.addEventListener('click', () => fileInput.click());

    uploadArea.addEventListener('dragover', e => {
      e.preventDefault();
      uploadArea.classList.add('drag-over');
    });
    uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('drag-over'));
    uploadArea.addEventListener('drop', e => {
      e.preventDefault();
      uploadArea.classList.remove('drag-over');
      if (e.dataTransfer.files[0]) {
        fileInput.files = e.dataTransfer.files;
        showPreview(e.dataTransfer.files[0]);
      }
    });

    fileInput.addEventListener('change', () => {
      if (fileInput.files[0]) showPreview(fileInput.files[0]);
    });

    function showPreview(file) {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = e => {
        if (preview) {
          preview.src = e.target.result;
          preview.classList.remove('hidden');
        }
      };
      reader.readAsDataURL(file);
    }
  }

  /* ── Score Rings ──────────────────────────────────────── */
  document.querySelectorAll('.score-ring').forEach(ring => {
    const score = parseFloat(ring.dataset.score) || 0;
    ring.style.setProperty('--pct', score + '%');
  });

  /* ── Alert Auto-dismiss ───────────────────────────────── */
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .4s';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 400);
    }, 4000);
  });

  /* ── Confirm Deletes ──────────────────────────────────── */
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm(btn.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });

  /* ── Table Row Click ──────────────────────────────────── */
  document.querySelectorAll('tr[data-href]').forEach(row => {
    row.style.cursor = 'pointer';
    row.addEventListener('click', () => window.location.href = row.dataset.href);
  });
});

/* ── Leaflet Map Init ────────────────────────────────────── */
function initMap(lat, lng, label, zoom = 16) {
  const map = L.map('map').setView([lat, lng], zoom);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  const icon = L.divIcon({
    html: `<div style="background:var(--primary,#1a3c5e);color:#fff;border-radius:50% 50% 50% 0;
           width:36px;height:36px;display:flex;align-items:center;justify-content:center;
           font-size:16px;transform:rotate(-45deg);box-shadow:0 2px 8px rgba(0,0,0,.3)">
           <i class="fa fa-location-dot" style="transform:rotate(45deg)"></i></div>`,
    iconSize: [36, 36], iconAnchor: [18, 36], popupAnchor: [0, -36],
    className: ''
  });

  L.marker([lat, lng], { icon }).addTo(map).bindPopup(label).openPopup();
  return map;
}

/* Picker map (for report forms) */
function initPickerMap(defaultLat, defaultLng, latInput, lngInput) {
  const map = L.map('pickerMap').setView([defaultLat, defaultLng], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  let marker = null;
  map.on('click', e => {
    if (marker) map.removeLayer(marker);
    marker = L.marker(e.latlng).addTo(map);
    document.getElementById(latInput).value = e.latlng.lat.toFixed(6);
    document.getElementById(lngInput).value = e.latlng.lng.toFixed(6);
  });
  return map;
}

/* ── Flash Messages ──────────────────────────────────────── */
function showFlash(msg, type = 'info') {
  const el = document.createElement('div');
  el.className = `alert alert-${type}`;
  el.setAttribute('data-auto-dismiss', '1');
  el.innerHTML = `<i class="fa fa-circle-info"></i> ${msg}`;
  const main = document.querySelector('.page-body .container') || document.body;
  main.prepend(el);
  setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 4000);
}
