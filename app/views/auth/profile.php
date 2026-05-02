<div class="page-header">
  <h1 class="page-title">My Profile</h1>
</div>

<div class="detail-grid">
  <div class="card form-card">
    <div class="card-header"><h3>Update Profile</h3></div>
    <form method="POST" action="<?= url('profile/update') ?>">
      <?= csrf_field() ?>
      <div class="card-body">
        <div class="form-grid">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($user['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Employee ID</label>
            <input type="text" class="form-control" value="<?= e($user['employee_id']) ?>" readonly>
          </div>
        </div>
        <hr style="margin:1rem 0;border-color:var(--border)">
        <p class="form-section-title">Change Password <small>(leave blank to keep)</small></p>
        <div class="form-grid">
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control" minlength="6">
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card detail-card">
    <div class="card-body text-center" style="padding:2rem">
      <div class="avatar-lg"><?= strtoupper(substr($user['name'],0,1)) ?></div>
      <h2 style="margin:.75rem 0 .25rem"><?= e($user['name']) ?></h2>
      <p class="text-muted"><?= e($user['role_name'] ?? '') ?></p>
    </div>
    <div class="card-body" style="border-top:1px solid var(--border)">
      <dl class="detail-list">
        <dt>Employee ID</dt><dd><?= e($user['employee_id']) ?></dd>
        <dt>Role</dt><dd><?= e($user['role_name'] ?? '') ?></dd>
        <dt>Last Login</dt><dd><?= formatDate($user['last_login'] ?? null, 'd M Y h:i A') ?></dd>
      </dl>
    </div>
  </div>
</div>
