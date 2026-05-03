<?php
$vLogo = '<svg width="52" height="44" viewBox="0 0 80 66" fill="none" xmlns="http://www.w3.org/2000/svg">
  <line x1="0" y1="20" x2="22" y2="20" stroke="#D4AF37" stroke-width="1.8"/>
  <line x1="58" y1="20" x2="80" y2="20" stroke="#D4AF37" stroke-width="1.8"/>
  <line x1="0" y1="27" x2="15" y2="27" stroke="#D4AF37" stroke-width="1"/>
  <line x1="65" y1="27" x2="80" y2="27" stroke="#D4AF37" stroke-width="1"/>
  <polyline points="20,5 40,60 60,5" fill="none" stroke="#D4AF37" stroke-width="3.2" stroke-linejoin="round" stroke-linecap="round"/>
  <polyline points="28,5 40,38 52,5" fill="none" stroke="#D4AF37" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round"/>
</svg>';

$vLogoSm = '<svg width="30" height="26" viewBox="0 0 80 66" fill="none" xmlns="http://www.w3.org/2000/svg">
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
    <!-- Decorative layers -->
    <div class="lh-grid"></div>
    <div class="lh-geo1"></div>
    <div class="lh-geo2"></div>
    <div class="lh-geo3"></div>

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
        <div class="lh-eyebrow">Premium Real Estate CRM</div>
        <h1 class="lh-headline">
          BUILDING<br>RELATIONSHIPS.<br>
          <span>CREATING VALUE.</span>
        </h1>
        <p class="lh-tagline">Where Trust Meets Excellence</p>

        <div class="lh-stats">
          <div class="lh-stat">
            <div class="lh-stat-num">500+</div>
            <div class="lh-stat-label">Properties</div>
          </div>
          <div class="lh-stat">
            <div class="lh-stat-num">12K+</div>
            <div class="lh-stat-label">Clients</div>
          </div>
          <div class="lh-stat">
            <div class="lh-stat-num">98%</div>
            <div class="lh-stat-label">Satisfaction</div>
          </div>
        </div>
      </div>

      <div>
        <div class="lh-features">
          <div class="lh-feature">
            <div class="lh-feat-icon"><i class="fa fa-building-columns"></i></div>
            <div class="lh-feat-text">
              <div class="lh-feat-label">Premium Properties</div>
              <div class="lh-feat-sub">Curated real estate</div>
            </div>
          </div>
          <div class="lh-feature">
            <div class="lh-feat-icon"><i class="fa fa-shield-halved"></i></div>
            <div class="lh-feat-text">
              <div class="lh-feat-label">Trusted & Secure</div>
              <div class="lh-feat-sub">Verified transactions</div>
            </div>
          </div>
          <div class="lh-feature">
            <div class="lh-feat-icon"><i class="fa fa-medal"></i></div>
            <div class="lh-feat-text">
              <div class="lh-feat-label">Award Winning</div>
              <div class="lh-feat-sub">Industry excellence</div>
            </div>
          </div>
        </div>
        <p class="lh-copyright">© <?= date('Y') ?> VastuVeda Realty CRM. All Rights Reserved.</p>
      </div>

    </div>
  </div>

  <!-- ── Right panel ───────────────────────────────────── -->
  <div class="login-panel">

    <div class="login-form-wrap">

      <div class="lp-card">

        <h1 class="lp-heading">WELCOME BACK</h1>
        <p class="lp-subheading">Sign in to your workspace</p>

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
            <label class="lp-field-label">Employee ID or Email</label>
            <div class="lp-field-inner">
              <input type="text" name="employee_id"
                     placeholder="EMP001 or name@company.com"
                     value="<?= old('employee_id') ?>"
                     required autocomplete="username">
              <i class="fa fa-user lp-field-icon"></i>
            </div>
          </div>

          <div class="lp-field">
            <label class="lp-field-label">Password</label>
            <div class="lp-field-inner">
              <input type="password" id="lp-pw" name="password"
                     placeholder="Enter your password"
                     required autocomplete="current-password">
              <i class="fa fa-lock lp-field-icon"></i>
              <button type="button" class="lp-pw-toggle" onclick="togglePw()" tabindex="-1">
                <i class="fa fa-eye" id="lp-pw-icon"></i>
              </button>
            </div>
          </div>

          <div class="lp-row">
            <label class="lp-remember">
              <input type="checkbox" name="remember"> Remember Me
            </label>
            <a href="#" class="lp-forgot">Forgot Password?</a>
          </div>

          <button type="submit" class="lp-signin-btn">
            <i class="fa fa-right-to-bracket" style="margin-right:8px;font-size:13px"></i>SIGN IN
          </button>
        </form>

        <div class="lp-or"><hr><span>OR</span><hr></div>

        <a href="mailto:support@vastuveda.com" class="lp-support-btn">
          <i class="fa fa-headset"></i> Contact Support
        </a>

      </div>

      <div class="lp-badges">
        <div class="lp-badge"><i class="fa fa-lock"></i> SSL Secured</div>
        <div class="lp-badge"><i class="fa fa-shield-halved"></i> CSRF Protected</div>
        <div class="lp-badge"><i class="fa fa-fingerprint"></i> Face Auth</div>
      </div>

    </div>

    <div class="login-panel-footer">
      <?= $vLogoSm ?>
      <p class="lp-footer-text">INTEGRITY &nbsp;·&nbsp; INNOVATION &nbsp;·&nbsp; EXCELLENCE</p>
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
// Animate stats counter
document.querySelectorAll('.lh-stat-num').forEach(el => {
  const target = el.textContent.replace(/\D/g,'');
  const suffix = el.textContent.replace(/[\d]/g,'');
  if (!target) return;
  let n = 0, end = parseInt(target), dur = 1200;
  const step = Math.ceil(end / (dur / 16));
  const t = setInterval(() => {
    n = Math.min(n + step, end);
    el.textContent = n + suffix;
    if (n >= end) clearInterval(t);
  }, 16);
});
</script>
