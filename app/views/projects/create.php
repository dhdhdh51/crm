<div class="page-header">
  <div>
    <h1 class="page-title">Add Project</h1>
    <p class="page-subtitle"><a href="<?= url('projects') ?>">Projects</a> / New</p>
  </div>
</div>
<div class="card form-card">
  <form method="POST" action="<?= url('projects/store') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="form-group span-2">
        <label>Project Name <span class="req">*</span></label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="form-group span-2">
        <label>Location</label>
        <input type="text" name="location" class="form-control">
      </div>
      <div class="form-group">
        <label>Total Units</label>
        <input type="number" name="total_units" class="form-control" min="0">
      </div>
      <div class="form-group">
        <label>Available Units</label>
        <input type="number" name="available_units" class="form-control" min="0">
      </div>
      <div class="form-group">
        <label>Price / Sqft (₹)</label>
        <input type="number" name="price_per_sqft" class="form-control" step="0.01" min="0">
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <?php foreach (['upcoming','ongoing','completed','archived'] as $s): ?>
            <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group span-2">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
      </div>
      <div class="form-group">
        <label>Project Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Project</button>
      <a href="<?= url('projects') ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
