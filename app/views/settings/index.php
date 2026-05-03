<?php /** @var array $office */ ?>
<div class="page-header">
  <h1 class="page-title">Settings</h1>
</div>

<?php $s = \Core\Session::flash('success'); if($s): ?>
  <div class="flash flash-success"><?= e($s) ?></div>
<?php endif; ?>

<div class="card form-card" style="max-width:560px">
  <h3 style="font-size:15px;font-weight:700;color:var(--maroon);margin-bottom:4px">
    <i class="fa fa-location-dot"></i> Office Geo-fence Location
  </h3>
  <p class="text-muted" style="font-size:12px;margin-bottom:1.25rem">
    Set one global office location. All employees will be validated against this location during check-in and check-out.
  </p>

  <form method="POST" action="<?= url('settings/office') ?>">
    <?= csrf_field() ?>

    <div class="form-group">
      <label>Office Name</label>
      <input type="text" name="office_name" class="form-control"
             value="<?= e($office['name']) ?>" placeholder="e.g. Head Office Mumbai">
    </div>

    <div class="form-grid" style="grid-template-columns:1fr 1fr">
      <div class="form-group">
        <label>Latitude</label>
        <input type="number" step="any" name="office_lat" id="office_lat" class="form-control"
               value="<?= e($office['lat'] ?? '') ?>" placeholder="e.g. 19.0760">
      </div>
      <div class="form-group">
        <label>Longitude</label>
        <input type="number" step="any" name="office_lng" id="office_lng" class="form-control"
               value="<?= e($office['lng'] ?? '') ?>" placeholder="e.g. 72.8777">
      </div>
    </div>

    <div class="form-group">
      <label>Check-in / Checkout Radius <span style="font-weight:400;color:var(--text-muted)">(meters)</span></label>
      <input type="number" name="office_radius" class="form-control"
             value="<?= (int)($office['radius'] ?? 100) ?>" min="50" max="5000">
      <small class="text-muted">Employees must be within this distance to mark attendance.</small>
    </div>

    <div class="d-flex gap-2 flex-wrap mb-3">
      <button type="button" class="btn btn-outline" onclick="captureMyLocation()">
        <i class="fa fa-crosshairs"></i> Use My Current Location
      </button>
      <?php if ($office['lat']): ?>
      <button type="button" class="btn btn-ghost" style="color:#dc3545;border-color:#dc3545" onclick="clearLocation()">
        <i class="fa fa-trash"></i> Clear Geo-fence
      </button>
      <?php endif; ?>
    </div>
    <div id="geoMsg" style="font-size:12px;margin-bottom:12px;color:var(--text-muted)"></div>

    <?php if ($office['lat'] && $office['lng']): ?>
    <div class="alert alert-info" style="font-size:12px;margin-bottom:1rem">
      <i class="fa fa-circle-info"></i>
      <strong>Current:</strong> <?= e($office['name']) ?> —
      <?= e($office['lat']) ?>, <?= e($office['lng']) ?> —
      Radius: <?= (int)$office['radius'] ?>m
    </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Office Location</button>
  </form>
</div>

<script>
function captureMyLocation() {
  const msg = document.getElementById('geoMsg');
  msg.textContent = 'Getting location…';
  if (!navigator.geolocation) { msg.textContent = 'Geolocation not supported.'; return; }
  navigator.geolocation.getCurrentPosition(pos => {
    document.getElementById('office_lat').value = pos.coords.latitude.toFixed(7);
    document.getElementById('office_lng').value = pos.coords.longitude.toFixed(7);
    msg.style.color = '#198754';
    msg.textContent = '✓ Location captured (±' + Math.round(pos.coords.accuracy) + 'm accuracy). Click Save to apply.';
  }, err => {
    msg.style.color = '#dc3545';
    msg.textContent = 'Location denied or unavailable.';
  }, { enableHighAccuracy: true, timeout: 8000 });
}
function clearLocation() {
  if (!confirm('Remove the global office geo-fence?')) return;
  document.getElementById('office_lat').value = '';
  document.getElementById('office_lng').value = '';
  document.getElementById('geoMsg').textContent = 'Cleared. Click Save to apply.';
}
</script>
