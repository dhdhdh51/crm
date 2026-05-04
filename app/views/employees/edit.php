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

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
      <a href="<?= url('employees/'.$employee['id']) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
