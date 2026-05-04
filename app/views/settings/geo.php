<?php /** @var array $office */ ?>
<div class="page-header"><h1 class="page-title">Office Geo-fence</h1></div>

<?php $s=\Core\Session::flash('success'); if($s): ?><div class="alert alert-success"><?= e($s) ?></div><?php endif; ?>

<div class="card form-card" style="max-width:520px">
  <form method="POST" action="<?= url('settings/geo') ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label>Office Name</label>
      <input type="text" name="name" class="form-control" value="<?= e($office['name']) ?>" required>
    </div>
    <div class="form-grid" style="grid-template-columns:1fr 1fr">
      <div class="form-group"><label>Latitude</label>
        <input type="number" step="any" name="lat" id="geo_lat" class="form-control" value="<?= e($office['lat']??'') ?>" placeholder="e.g. 19.0760">
      </div>
      <div class="form-group"><label>Longitude</label>
        <input type="number" step="any" name="lng" id="geo_lng" class="form-control" value="<?= e($office['lng']??'') ?>" placeholder="e.g. 72.8777">
      </div>
    </div>
    <div class="form-group"><label>Radius (meters)</label>
      <input type="number" name="radius" class="form-control" value="<?= (int)($office['radius']??100) ?>" min="50" max="5000">
    </div>
    <div class="d-flex gap-2 mb-3">
      <button type="button" class="btn btn-outline" onclick="useMyLocation()"><i class="fa fa-crosshairs"></i> Use My Location</button>
    </div>
    <div id="geoMsg" style="font-size:12px;margin-bottom:10px;color:var(--text-muted)"></div>
    <?php if ($office['lat'] && $office['lng']): ?>
    <div class="alert alert-info" style="font-size:12px">
      <i class="fa fa-circle-info"></i> Current: <strong><?= e($office['name']) ?></strong> — <?= e($office['lat']) ?>, <?= e($office['lng']) ?> — <?= (int)$office['radius'] ?>m radius
    </div>
    <?php endif; ?>
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
  </form>
</div>
<script>
function useMyLocation() {
  const m=document.getElementById('geoMsg'); m.textContent='Getting location…';
  navigator.geolocation.getCurrentPosition(p=>{
    document.getElementById('geo_lat').value=p.coords.latitude.toFixed(7);
    document.getElementById('geo_lng').value=p.coords.longitude.toFixed(7);
    m.style.color='#198754'; m.textContent='✓ Captured (±'+Math.round(p.coords.accuracy)+'m). Click Save.';
  },()=>{ m.style.color='#dc3545'; m.textContent='Location denied.'; },{enableHighAccuracy:true,timeout:8000});
}
</script>
