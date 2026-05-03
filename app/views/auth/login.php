<?php
$vLogo = '<svg width="52" height="44" viewBox="0 0 80 66" fill="none" xmlns="http://www.w3.org/2000/svg">
  <line x1="0" y1="20" x2="22" y2="20" stroke="#D4AF37" stroke-width="1.8"/>
  <line x1="58" y1="20" x2="80" y2="20" stroke="#D4AF37" stroke-width="1.8"/>
  <line x1="0" y1="27" x2="15" y2="27" stroke="#D4AF37" stroke-width="1"/>
  <line x1="65" y1="27" x2="80" y2="27" stroke="#D4AF37" stroke-width="1"/>
  <polyline points="20,5 40,60 60,5" fill="none" stroke="#D4AF37" stroke-width="3.2" stroke-linejoin="round" stroke-linecap="round"/>
  <polyline points="28,5 40,38 52,5" fill="none" stroke="#D4AF37" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round"/>
</svg>';

$vLogoSm = '<svg width="34" height="28" viewBox="0 0 80 66" fill="none" xmlns="http://www.w3.org/2000/svg">
  <line x1="0" y1="20" x2="22" y2="20" stroke="#D4AF37" stroke-width="1.8"/>
  <line x1="58" y1="20" x2="80" y2="20" stroke="#D4AF37" stroke-width="1.8"/>
  <line x1="0" y1="27" x2="15" y2="27" stroke="#D4AF37" stroke-width="1"/>
  <line x1="65" y1="27" x2="80" y2="27" stroke="#D4AF37" stroke-width="1"/>
  <polyline points="20,5 40,60 60,5" fill="none" stroke="#D4AF37" stroke-width="3.2" stroke-linejoin="round" stroke-linecap="round"/>
  <polyline points="28,5 40,38 52,5" fill="none" stroke="#D4AF37" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round"/>
</svg>';
?>

<div class="login-page">

  <!-- ── Left hero ─────────────────────────────────────── -->
  <div class="login-hero">
    <div class="login-hero-content">

      <div class="lh-top">
        <div class="lh-logo">
          <div class="lh-logo-icon"><?= $vLogo ?></div>
          <div>
            <div class="lh-logo-name">VASTUVEDA</div>
            <div class="lh-logo-sub">— REALTY —</div>
          </div>
        </div>
      </div>

      <div class="lh-middle">
        <h1 class="lh-headline">
          BUILDING<br>RELATIONSHIPS.<br>
          <span>CREATING VALUE.</span>
        </h1>
        <p class="lh-tagline">Premium Real Estate Solutions</p>
      </div>

      <div>
        <div class="lh-features">
          <div class="lh-feature">
            <div class="lh-feat-icon"><i class="fa fa-building-columns"></i></div>
            <div class="lh-feat-label">PREMIUM<br>PROPERTIES</div>
          </div>
          <div class="lh-feature">
            <div class="lh-feat-icon"><i class="fa fa-handshake"></i></div>
            <div class="lh-feat-label">TRUST &amp;<br>TRANSPARENCY</div>
          </div>
          <div class="lh-feature">
            <div class="lh-feat-icon"><i class="fa fa-medal"></i></div>
            <div class="lh-feat-label">COMMITMENT TO<br>EXCELLENCE</div>
          </div>
        </div>
        <p class="lh-copyright">© 2024 VastuVeda Realty CRM. All Rights Reserved.</p>
      </div>

    </div>
  </div>

  <!-- ── Right panel ───────────────────────────────────── -->
  <div class="login-panel">

    <div class="login-form-wrap">

      <h1 class="lp-heading">WELCOME BACK</h1>
      <p class="lp-subheading">Sign in to your account</p>

      <div class="lp-logo-divider">
        <hr><?= $vLogoSm ?><hr>
      </div>

      <?php $err = \Core\Session::flash('error'); ?>
      <?php if ($err): ?>
        <div class="lp-flash"><i class="fa fa-circle-xmark"></i> <?= e($err) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= url('login') ?>" novalidate>
        <?= csrf_field() ?>

        <div class="lp-field">
          <input type="text" name="employee_id" placeholder="Employee ID or Email"
                 value="<?= old('employee_id') ?>" required autocomplete="username">
          <i class="fa fa-user lp-field-icon"></i>
        </div>

        <div class="lp-field">
          <input type="password" id="lp-pw" name="password"
                 placeholder="Password" required autocomplete="current-password">
          <button type="button" class="lp-pw-toggle" onclick="togglePw()">
            <i class="fa fa-eye" id="lp-pw-icon"></i>
          </button>
        </div>

        <div class="lp-row">
          <label class="lp-remember">
            <input type="checkbox" name="remember"> Remember Me
          </label>
          <a href="#" class="lp-forgot">Forgot Password?</a>
        </div>

        <button type="submit" class="lp-signin-btn">SIGN IN</button>
      </form>

      <div class="lp-or"><hr><span>OR</span><hr></div>

      <button class="lp-support-btn">
        <i class="fa fa-headset"></i> Need Help? Contact Support
      </button>

    </div>

    <div class="login-panel-footer">
      <?= $vLogoSm ?>
      <p class="lp-footer-text">INTEGRITY &nbsp;|&nbsp; INNOVATION &nbsp;|&nbsp; EXCELLENCE</p>
    </div>

  </div>
</div>

<script>
function togglePw() {
  const inp = document.getElementById('lp-pw');
  const ico = document.getElementById('lp-pw-icon');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  ico.className = inp.type === 'password' ? 'fa fa-eye' : 'fa fa-eye-slash';
}
</script>
