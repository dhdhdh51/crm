<div class="page-header">
  <h1 class="page-title">Edit Project</h1>
</div>
<div class="card form-card">
  <form method="POST" action="<?= url('projects/'.$project['id'].'/update') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="form-group span-2">
        <label>Project Name</label>
        <input type="text" name="name" class="form-control" value="<?= e($project['name']) ?>" required>
      </div>
      <div class="form-group span-2">
        <label>Location</label>
        <input type="text" name="location" class="form-control" value="<?= e($project['location']) ?>">
      </div>
      <div class="form-group">
        <label>Total Units</label>
        <input type="number" name="total_units" class="form-control" value="<?= e($project['total_units']) ?>">
      </div>
      <div class="form-group">
        <label>Available Units</label>
        <input type="number" name="available_units" class="form-control" value="<?= e($project['available_units']) ?>">
      </div>
      <div class="form-group">
        <label>Price / Sqft (₹)</label>
        <input type="number" name="price_per_sqft" class="form-control" step="0.01" value="<?= e($project['price_per_sqft']) ?>">
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <?php foreach (['upcoming','ongoing','completed','archived'] as $s): ?>
            <option value="<?= $s ?>" <?= $project['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group span-2">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3"><?= e($project['description']) ?></textarea>
      </div>
      <div class="form-group">
        <label>Replace Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
      <a href="<?= url('projects/'.$project['id']) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
