<div class="page-header">
  <h1 class="page-title">Add Employee</h1>
</div>
<div class="card form-card">
  <form method="POST" action="<?= url('employees/store') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="form-group">
        <label>Employee ID <span class="req">*</span></label>
        <input type="text" name="employee_id" class="form-control" placeholder="e.g. EMP006" required>
      </div>
      <div class="form-group">
        <label>Full Name <span class="req">*</span></label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control">
      </div>
      <div class="form-group">
        <label>Phone</label>
        <input type="tel" name="phone" class="form-control">
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role_id" class="form-control">
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Designation</label>
        <input type="text" name="designation" class="form-control">
      </div>
      <div class="form-group">
        <label>Department</label>
        <input type="text" name="department" class="form-control">
      </div>
      <div class="form-group">
        <label>Join Date</label>
        <input type="date" name="join_date" class="form-control" value="<?= date('Y-m-d') ?>">
      </div>
      <div class="form-group">
        <label>Password <span class="req">*</span></label>
        <input type="password" name="password" class="form-control" placeholder="Min 6 chars" required>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create Employee</button>
      <a href="<?= url('employees') ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
