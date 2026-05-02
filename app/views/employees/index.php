<div class="page-header">
  <div>
    <h1 class="page-title">Employees</h1>
    <p class="page-subtitle">Manage your team</p>
  </div>
  <?php if (\Core\Session::can('admin')): ?>
  <a href="<?= url('employees/create') ?>" class="btn btn-primary"><i class="fa fa-user-plus"></i> Add Employee</a>
  <?php endif; ?>
</div>

<div class="card table-card">
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Employee</th><th>ID</th><th>Role</th><th>Phone</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($employees)): ?>
          <tr><td colspan="7" class="text-center">No employees found.</td></tr>
        <?php else: ?>
          <?php foreach ($employees as $i => $emp): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td>
              <div class="user-cell">
                <div class="user-avatar-sm"><?= strtoupper(substr($emp['name'],0,1)) ?></div>
                <div>
                  <a href="<?= url('employees/'.$emp['id']) ?>" class="table-link"><?= e($emp['name']) ?></a>
                  <?php if ($emp['designation']): ?>
                    <div class="table-sub"><?= e($emp['designation']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td><?= e($emp['employee_id']) ?></td>
            <td><span class="role-badge role-<?= e($emp['role_slug']) ?>"><?= e($emp['role_name']) ?></span></td>
            <td><?= e($emp['phone'] ?: '–') ?></td>
            <td>
              <span class="badge <?= $emp['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                <?= $emp['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td class="table-actions">
              <a href="<?= url('employees/'.$emp['id']) ?>" class="action-btn" title="View"><i class="fa fa-eye"></i></a>
              <?php if (\Core\Session::can('admin')): ?>
                <a href="<?= url('employees/'.$emp['id'].'/edit') ?>" class="action-btn" title="Edit"><i class="fa fa-pen"></i></a>
                <form method="POST" action="<?= url('employees/'.$emp['id'].'/delete') ?>" class="inline-form" onsubmit="return confirm('Deactivate employee?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="action-btn action-btn-danger" title="Deactivate"><i class="fa fa-ban"></i></button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
