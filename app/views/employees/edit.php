<div class="page-header">
  <h1 class="page-title">Edit Employee</h1>
</div>
<div class="card form-card">
  <form method="POST" action="<?= url('employees/'.$employee['id'].'/update') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="form-group">
        <label>Employee ID</label>
        <input type="text" class="form-control" value="<?= e($employee['employee_id']) ?>" readonly>
      </div>
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" class="form-control" value="<?= e($employee['name']) ?>" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($employee['email']) ?>">
      </div>
      <div class="form-group">
        <label>Phone</label>
        <input type="tel" name="phone" class="form-control" value="<?= e($employee['phone']) ?>">
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role_id" class="form-control">
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>" <?= $employee['role_id']==$r['id']?'selected':'' ?>><?= e($r['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Designation</label>
        <input type="text" name="designation" class="form-control" value="<?= e($employee['designation'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Department</label>
        <input type="text" name="department" class="form-control" value="<?= e($employee['department'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>New Password <small>(leave blank to keep)</small></label>
        <input type="password" name="password" class="form-control">
      </div>
      <div class="form-group">
        <label>Active Status</label>
        <div class="form-check">
          <input type="checkbox" name="is_active" id="is_active" value="1" <?= $employee['is_active']?'checked':'' ?>>
          <label for="is_active">Account Active</label>
        </div>
      </div>
    </div>

    <!-- Geo-fence -->
    <hr style="margin:1.5rem 0;border-color:var(--border)">
    <h4 style="font-size:14px;font-weight:700;color:var(--maroon);margin-bottom:1rem"><i class="fa fa-location-dot"></i> Geo-fence (Attendance)</h4>
    <div class="form-grid">
      <div class="form-group">
        <label>Office Latitude</label>
        <input type="number" step="any" name="geo_lat" id="geo_lat" class="form-control"
               value="<?= e($employee['geo_lat'] ?? '') ?>" placeholder="e.g. 19.0760">
      </div>
      <div class="form-group">
        <label>Office Longitude</label>
        <input type="number" step="any" name="geo_lng" id="geo_lng" class="form-control"
               value="<?= e($employee['geo_lng'] ?? '') ?>" placeholder="e.g. 72.8777">
      </div>
      <div class="form-group">
        <label>Check-in / Checkout Radius (meters)</label>
        <input type="number" name="geo_radius" class="form-control" min="50" max="5000"
               value="<?= (int)($employee['geo_radius'] ?? 100) ?>" placeholder="100">
        <small class="text-muted">Distance in meters within which check-in & checkout are valid.</small>
      </div>
      <div class="form-group" style="display:flex;align-items:flex-end">
        <button type="button" class="btn btn-ghost" onclick="captureLocation()">
          <i class="fa fa-crosshairs"></i> Use My Current Location
        </button>
        <?php if ($employee['geo_lat']): ?>
        <button type="button" class="btn btn-outline" style="margin-left:8px;color:var(--danger,#dc3545);border-color:var(--danger,#dc3545)" onclick="clearGeo()">
          <i class="fa fa-trash"></i> Remove Geo-fence
        </button>
        <?php endif; ?>
      </div>
    </div>
    <div id="geoStatus" style="font-size:12px;color:var(--text-muted);margin-top:-8px;margin-bottom:8px"></div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
      <a href="<?= url('employees/'.$employee['id']) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
<script>
function captureLocation() {
  const s = document.getElementById('geoStatus');
  s.textContent = 'Getting location…';
  if (!navigator.geolocation) { s.textContent = 'Geolocation not supported.'; return; }
  navigator.geolocation.getCurrentPosition(pos => {
    document.getElementById('geo_lat').value = pos.coords.latitude.toFixed(7);
    document.getElementById('geo_lng').value = pos.coords.longitude.toFixed(7);
    s.textContent = '✓ Location captured (accuracy ±' + Math.round(pos.coords.accuracy) + 'm). Save to apply.';
    s.style.color = '#198754';
  }, err => {
    s.textContent = 'Location denied or unavailable.';
    s.style.color = '#dc3545';
  }, { enableHighAccuracy: true, timeout: 8000 });
}
function clearGeo() {
  if (!confirm('Remove geo-fence for this employee?')) return;
  document.getElementById('geo_lat').value = '';
  document.getElementById('geo_lng').value = '';
  document.getElementById('geoStatus').textContent = 'Geo-fence cleared. Save to apply.';
}
</script>
