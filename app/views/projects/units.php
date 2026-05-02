<div class="page-header">
  <div>
    <h1 class="page-title">Units – <?= e($project['name']) ?></h1>
    <p class="page-subtitle"><a href="<?= url('projects') ?>">Projects</a> / <?= e($project['name']) ?> / Units</p>
  </div>
</div>

<div class="detail-grid">
  <div class="card form-card">
    <div class="card-header"><h3>Add Unit</h3></div>
    <form method="POST" action="<?= url('projects/'.$project['id'].'/units/store') ?>">
      <?= csrf_field() ?>
      <div class="card-body">
        <div class="form-grid cols-3">
          <div class="form-group">
            <label>Unit Number</label>
            <input type="text" name="unit_number" class="form-control" placeholder="e.g. A-101">
          </div>
          <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control">
              <?php foreach (['apartment','villa','plot','shop','office'] as $t): ?>
                <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Floor</label>
            <input type="number" name="floor" class="form-control" min="0" value="0">
          </div>
          <div class="form-group">
            <label>Area (sqft)</label>
            <input type="number" name="area_sqft" class="form-control" step="0.01" min="0">
          </div>
          <div class="form-group">
            <label>Price (₹)</label>
            <input type="number" name="price" class="form-control" step="1000" min="0">
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
              <?php foreach (['available','booked','sold','held'] as $s): ?>
                <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Facing</label>
            <input type="text" name="facing" class="form-control" placeholder="North, East…">
          </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Add Unit</button>
      </div>
    </form>
  </div>
</div>

<div class="card mt-3">
  <div class="card-header"><h3>All Units (<?= count($units) ?>)</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>Unit No.</th><th>Type</th><th>Floor</th><th>Area</th><th>Price</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($units as $u): ?>
        <tr>
          <td><?= e($u['unit_number']) ?></td>
          <td><?= ucfirst(e($u['type'])) ?></td>
          <td><?= e($u['floor']) ?></td>
          <td><?= number_format((float)$u['area_sqft']) ?> sqft</td>
          <td><?= formatMoney((float)$u['price']) ?></td>
          <td><?= unitStatusBadge($u['status']) ?></td>
          <td class="table-actions">
            <form method="POST" action="<?= url('units/'.$u['id'].'/delete') ?>" class="inline-form" onsubmit="return confirm('Delete unit?')">
              <?= csrf_field() ?>
              <button type="submit" class="action-btn action-btn-danger" title="Delete"><i class="fa fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
