<div class="auth-container">
  <div class="auth-left">
    <div class="auth-brand">
      <div class="auth-logo">VV</div>
      <h1 class="auth-title">VastuVeda<br><span>Realty CRM</span></h1>
      <p class="auth-tagline">Premium Real Estate Management Platform</p>
    </div>
    <div class="auth-features">
      <div class="feature-item"><i class="fa fa-chart-line"></i> Lead & Pipeline Management</div>
      <div class="feature-item"><i class="fa fa-building"></i> Project & Inventory Control</div>
      <div class="feature-item"><i class="fa fa-users"></i> Team Performance Analytics</div>
      <div class="feature-item"><i class="fa fa-shield-halved"></i> Role-Based Secure Access</div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-card glass">
      <div class="auth-card-header">
        <h2>Welcome Back</h2>
        <p>Sign in to your CRM account</p>
      </div>

      <?php $err = \Core\Session::flash('error'); ?>
      <?php if ($err): ?>
        <div class="flash flash-error" style="margin-bottom:1rem;">
          <i class="fa fa-circle-xmark"></i> <?= e($err) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= url('login') ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
          <label for="employee_id">Employee ID</label>
          <div class="input-icon-wrap">
            <i class="fa fa-id-card"></i>
            <input type="text" id="employee_id" name="employee_id"
                   class="form-control" placeholder="e.g. EMP001"
                   value="<?= old('employee_id') ?>" required autocomplete="username">
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-icon-wrap">
            <i class="fa fa-lock"></i>
            <input type="password" id="password" name="password"
                   class="form-control" placeholder="Enter your password"
                   required autocomplete="current-password">
            <button type="button" class="pw-toggle" onclick="togglePw(this)" tabindex="-1">
              <i class="fa fa-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full">
          <i class="fa fa-right-to-bracket"></i> Sign In
        </button>
      </form>

      <p class="auth-hint">Default: <strong>EMP001</strong> / <strong>Admin@1234</strong></p>
    </div>
  </div>
</div>

<script>
function togglePw(btn) {
  const inp = btn.closest('.input-icon-wrap').querySelector('input');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.querySelector('i').className = inp.type === 'password' ? 'fa fa-eye' : 'fa fa-eye-slash';
}
</script>
