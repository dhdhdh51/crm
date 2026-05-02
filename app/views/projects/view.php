<div class="page-header">
  <div>
    <h1 class="page-title"><?= e($project['name']) ?></h1>
    <p class="page-subtitle"><a href="<?= url('projects') ?>">Projects</a> / <?= e($project['name']) ?></p>
  </div>
  <?php if (\Core\Session::can(['admin','manager'])): ?>
  <div class="page-actions">
    <a href="<?= url('projects/'.$project['id'].'/units') ?>" class="btn btn-secondary">Manage Units</a>
    <a href="<?= url('projects/'.$project['id'].'/edit') ?>" class="btn btn-ghost">Edit</a>
  </div>
  <?php endif; ?>
</div>

<div class="detail-grid">
  <div class="card detail-card">
    <div class="card-header"><h3>Project Details</h3></div>
    <div class="card-body">
      <dl class="detail-list">
        <dt>Location</dt>   <dd><?= e($project['location']) ?></dd>
        <dt>Status</dt>     <dd><span class="project-status status-<?= e($project['status']) ?>"><?= ucfirst(e($project['status'])) ?></span></dd>
        <dt>Total Units</dt><dd><?= e($project['total_units']) ?></dd>
        <dt>Available</dt>  <dd><?= e($project['available_units']) ?></dd>
        <dt>Price/sqft</dt> <dd><?= formatMoney((float)$project['price_per_sqft']) ?></dd>
      </dl>
      <?php if ($project['description']): ?>
        <p class="mt-2"><?= nl2br(e($project['description'])) ?></p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header"><h3>Units (<?= count($units) ?>)</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>Unit No.</th><th>Type</th><th>Floor</th><th>Area (sqft)</th><th>Price</th><th>Facing</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php if (empty($units)): ?>
          <tr><td colspan="7" class="text-center">No units added yet.</td></tr>
        <?php else: ?>
          <?php foreach ($units as $u): ?>
          <tr>
            <td><?= e($u['unit_number']) ?></td>
            <td><?= ucfirst(e($u['type'])) ?></td>
            <td><?= e($u['floor']) ?></td>
            <td><?= number_format((float)$u['area_sqft']) ?></td>
            <td><?= formatMoney((float)$u['price']) ?></td>
            <td><?= e($u['facing'] ?: '–') ?></td>
            <td><?= unitStatusBadge($u['status']) ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
