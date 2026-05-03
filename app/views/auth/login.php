<?php
$logo = '<svg width="32" height="27" viewBox="0 0 80 66" fill="none" xmlns="http://www.w3.org/2000/svg">
  <line x1="0" y1="20" x2="22" y2="20" stroke="#D4AF37" stroke-width="2"/>
  <line x1="58" y1="20" x2="80" y2="20" stroke="#D4AF37" stroke-width="2"/>
  <line x1="0" y1="27" x2="15" y2="27" stroke="#D4AF37" stroke-width="1.2"/>
  <line x1="65" y1="27" x2="80" y2="27" stroke="#D4AF37" stroke-width="1.2"/>
  <polyline points="20,5 40,60 60,5" fill="none" stroke="#D4AF37" stroke-width="3.5" stroke-linejoin="round" stroke-linecap="round"/>
  <polyline points="28,5 40,38 52,5" fill="none" stroke="#D4AF37" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
</svg>';
?>

<!-- Animated bg blobs -->
<div class="ln-blob ln-blob-1"></div>
<div class="ln-blob ln-blob-2"></div>
<div class="ln-blob ln-blob-3"></div>

<div class="ln-card">

  <div class="ln-brand">
    <div class="ln-brand-icon"><?= $logo ?></div>
    <div>
      <div class="ln-brand-name">VASTUVEDA</div>
      <div class="ln-brand-sub">REALTY CRM</div>
    </div>
  </div>

  <span class="ln-line"></span>

  <?php $err = \Core\Session::flash('error'); ?>
  <?php if ($err): ?>
    <div class="lp-flash"><i class="fa fa-circle-xmark"></i> <?= e($err) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= url('login') ?>" novalidate>
    <?= csrf_field() ?>

    <div class="lp-field">
      <label class="lp-field-label">Employee ID or Email</label>
      <div class="lp-field-inner">
        <input type="text" name="employee_id" placeholder="EMP001 or email@company.com"
               value="<?= old('employee_id') ?>" required autocomplete="username">
        <i class="fa fa-user lp-field-icon"></i>
      </div>
    </div>

    <div class="lp-field">
      <label class="lp-field-label">Password</label>
      <div class="lp-field-inner">
        <input type="password" id="lp-pw" name="password"
               placeholder="Enter password" required autocomplete="current-password">
        <i class="fa fa-lock lp-field-icon"></i>
        <button type="button" class="lp-pw-toggle" onclick="togglePw()" tabindex="-1">
          <i class="fa fa-eye" id="lp-pw-icon"></i>
        </button>
      </div>
    </div>

    <div class="lp-row">
      <label class="lp-remember"><input type="checkbox" name="remember"> Remember Me</label>
      <a href="#" class="lp-forgot">Forgot Password?</a>
    </div>

    <button type="submit" class="lp-signin-btn">Sign In</button>
  </form>

  <div class="lp-or"><hr><span>OR</span><hr></div>
  <a href="mailto:support@vastuveda.com" class="lp-support-btn">
    <i class="fa fa-headset"></i> Contact Support
  </a>

  <div class="ln-footer">
    <p class="ln-footer-text">© <?= date('Y') ?> VastuVeda Realty &nbsp;·&nbsp; All Rights Reserved</p>
  </div>

</div>

<script>
function togglePw() {
  const i = document.getElementById('lp-pw');
  const c = document.getElementById('lp-pw-icon');
  i.type = i.type === 'password' ? 'text' : 'password';
  c.className = i.type === 'password' ? 'fa fa-eye' : 'fa fa-eye-slash';
}
</script>
