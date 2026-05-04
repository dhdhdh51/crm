<div class="page-header">
  <div>
    <h1 class="page-title"><?= e($employee['name']) ?></h1>
    <p class="page-subtitle"><a href="<?= url('employees') ?>">Employees</a> / <?= e($employee['name']) ?></p>
  </div>
  <div class="d-flex gap-2">
<?php if (\Core\Session::can(['admin','super_admin','hr'])): ?>
    <a href="<?= url('employees/'.$employee['id'].'/edit') ?>" class="btn btn-secondary"><i class="fa fa-pen"></i> Edit</a>
    <?php endif; ?>
  </div>
</div>

<div class="detail-grid">
  <div class="card detail-card">
    <div class="card-body text-center" style="padding:2rem">
      <div class="avatar-lg"><?= strtoupper(substr($employee['name'],0,1)) ?></div>
      <h2 style="margin:.75rem 0 .25rem"><?= e($employee['name']) ?></h2>
      <p class="text-muted"><?= e($employee['designation'] ?? '') ?></p>
      <span class="role-badge role-<?= e($employee['role_slug']) ?>"><?= e($employee['role_name']) ?></span>
    </div>
    <div class="card-body" style="border-top:1px solid var(--border)">
      <dl class="detail-list">
        <dt>Employee ID</dt><dd><?= e($employee['employee_id']) ?></dd>
        <dt>Email</dt>      <dd><?= e($employee['email'] ?: '–') ?></dd>
        <dt>Phone</dt>      <dd><?= e($employee['phone'] ?: '–') ?></dd>
        <dt>Department</dt> <dd><?= e($employee['department'] ?? '–') ?></dd>
        <dt>Status</dt>     <dd><span class="badge <?= $employee['is_active']?'badge-success':'badge-secondary' ?>"><?= $employee['is_active']?'Active':'Inactive' ?></span></dd>
        <dt>Last Login</dt> <dd><?= formatDate($employee['last_login'] ?? null, 'd M Y h:i A') ?></dd>
      </dl>
    </div>
  </div>

  <div class="card detail-card">
    <div class="card-header"><h3>Performance Summary</h3></div>
    <div class="card-body">
      <div class="stats-grid cols-2">
        <div class="stat-card stat-blue">
          <div class="stat-icon"><i class="fa fa-users"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $leadStats['total'] ?? 0 ?></div>
            <div class="stat-label">Total Leads</div>
          </div>
        </div>
        <div class="stat-card stat-green">
          <div class="stat-icon"><i class="fa fa-handshake"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $leadStats['closed'] ?? 0 ?></div>
            <div class="stat-label">Closed Deals</div>
          </div>
        </div>
        <div class="stat-card stat-purple">
          <div class="stat-icon"><i class="fa fa-calendar-check"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= $visitCount ?></div>
            <div class="stat-label">Site Visits</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
