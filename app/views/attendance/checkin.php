<?php /** @var array|false $today @var array $office */ ?>
<div class="page-header">
  <h2 class="page-title">Check In / Out</h2>
  <a href="<?= url('attendance') ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<?php if ($today): ?>
<div class="alert alert-info mb-3">
  <i class="fa fa-circle-info"></i> Today: In <?= $today['check_in'] ? date('h:i A', strtotime($today['check_in'])) : '–' ?>
  <?= $today['check_out'] ? ' → Out ' . date('h:i A', strtotime($today['check_out'])) : ' · Not checked out yet' ?>
</div>
<?php endif; ?>

<div id="statusMsg" class="alert alert-secondary mb-3"><i class="fa fa-spinner fa-spin"></i> Starting…</div>

<div class="row">
  <div class="col-lg-7">

    <!-- HTTPS: Camera face capture -->
    <div id="cameraCard" class="card" style="display:none">
      <div class="card-header" style="font-weight:700;color:var(--maroon)"><i class="fa fa-camera"></i> Camera Check-In</div>
      <div class="card-body text-center">
        <div style="position:relative;display:inline-block;border-radius:10px;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,.15)">
          <video id="video" width="460" height="340" autoplay muted playsinline style="display:block;background:#111"></video>
          <canvas id="overlay" width="460" height="340" style="position:absolute;top:0;left:0"></canvas>
        </div>
        <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
          <button id="btnCI" class="btn btn-primary btn-lg" onclick="doMark('checkin')">
            <i class="fa fa-right-to-bracket"></i> Check In
          </button>
          <?php if ($today && !$today['check_out']): ?>
          <button class="btn btn-warning btn-lg" onclick="doMark('checkout')">
            <i class="fa fa-clock"></i> Check Out
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- HTTP: Photo capture fallback -->
    <div id="photoCard" class="card" style="display:none">
      <div class="card-header" style="font-weight:700;color:var(--maroon)">
        <i class="fa fa-camera"></i> Photo Check-In <span class="badge badge-warning" style="margin-left:6px;font-size:11px">HTTP Mode</span>
      </div>
      <div class="card-body text-center">
        <div id="photoPreview" style="display:none;margin-bottom:12px">
          <img id="photoImg" style="max-width:280px;border-radius:10px;box-shadow:0 3px 14px rgba(0,0,0,.2)">
          <div style="font-size:12px;color:#198754;margin-top:6px"><i class="fa fa-circle-check"></i> Photo ready</div>
        </div>
        <label for="photoInput" class="btn btn-outline btn-lg" style="cursor:pointer"><i class="fa fa-camera"></i> Take Selfie</label>
        <input type="file" id="photoInput" accept="image/*" capture="user" style="display:none">
        <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
          <button id="btnPhotoCI" class="btn btn-primary btn-lg" disabled onclick="doMark('checkin')">
            <i class="fa fa-right-to-bracket"></i> Check In
          </button>
          <?php if ($today && !$today['check_out']): ?>
          <button class="btn btn-warning btn-lg" onclick="doMark('checkout')">
            <i class="fa fa-clock"></i> Check Out
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Manual fallback -->
    <div class="card mt-3">
      <div class="card-header">Manual (No Camera)</div>
      <div class="card-body d-flex gap-2 flex-wrap">
        <?php if (!$today): ?>
        <form method="POST" action="<?= url('attendance/mark') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="checkin">
          <button class="btn btn-primary btn-sm" onclick="return confirm('Mark yourself present?')"><i class="fa fa-check"></i> Check In</button>
        </form>
        <?php elseif (!$today['check_out']): ?>
        <form method="POST" action="<?= url('attendance/mark') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="checkout">
          <button class="btn btn-warning btn-sm" onclick="return confirm('Mark checkout?')"><i class="fa fa-clock"></i> Check Out</button>
        </form>
        <?php else: ?>
        <span class="badge badge-success" style="padding:10px 16px;font-size:13px"><i class="fa fa-circle-check"></i> Complete for today</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <?php if ($office['lat']): ?>
    <div class="card mb-3">
      <div class="card-header"><i class="fa fa-location-dot"></i> Office Geo-fence</div>
      <div class="card-body" style="font-size:13px">
        <strong><?= e($office['name']) ?></strong><br>
        Radius: <strong><?= (int)$office['radius'] ?>m</strong>
        <div id="geoStatus" class="mt-2" style="font-size:12px;color:var(--text-muted)"><i class="fa fa-spinner fa-spin"></i> Getting your location…</div>
      </div>
    </div>
    <?php endif; ?>
    <div class="card">
      <div class="card-header">Tips</div>
      <div class="card-body" style="font-size:13px;display:flex;flex-direction:column;gap:10px">
        <div><strong style="color:var(--maroon)"><i class="fa fa-lock"></i> HTTPS = Full Camera</strong><p class="text-muted mt-1 mb-0">Face capture requires HTTPS. HTTP uses photo selfie mode.</p></div>
        <div><strong style="color:var(--maroon)"><i class="fa fa-location-dot"></i> Allow Location</strong><p class="text-muted mt-1 mb-0">Required for geo-fence validation.</p></div>
      </div>
    </div>
  </div>
</div>

<script>
const MARK_URL   = '<?= url("attendance/mark") ?>';
let   CSRF_TOKEN = '<?= \Core\CSRF::token() ?>';
const OFFICE     = { lat: <?= (float)($office['lat']??0) ?>, lng: <?= (float)($office['lng']??0) ?>, radius: <?= (int)$office['radius'] ?> };

let stream = null, photoB64 = null, geoCache = null;

// ── Boot ───────────────────────────────────────────────────────
(async function boot() {
  setStatus('Requesting permissions…', 'info');

  // 1. Location
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      pos => {
        geoCache = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        updateGeoStatus(geoCache);
      },
      () => updateGeoStatus(null),
      { enableHighAccuracy: true, timeout: 12000 }
    );
  }

  // 2. Protocol check
  const secure = location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
  if (!secure) { showPhoto(); return; }

  // 3. Camera
  if (!navigator.mediaDevices?.getUserMedia) { showPhoto(); return; }
  let camState = 'prompt';
  try { camState = (await navigator.permissions.query({ name: 'camera' })).state; } catch(_) {}
  if (camState === 'denied') { showPhoto(); setStatus('Camera blocked — using photo mode.', 'warning'); return; }

  try {
    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal:'user' }, width:{ideal:460}, height:{ideal:340} }, audio: false });
    const video = document.getElementById('video');
    video.srcObject = stream;
    video.play().catch(()=>{});
    document.getElementById('cameraCard').style.display = 'block';
    setStatus('Camera ready. Click Check In when ready.', 'success');
  } catch(e) {
    showPhoto();
    setStatus('Camera unavailable — using photo mode.', 'warning');
  }
})();

function showPhoto() {
  document.getElementById('photoCard').style.display = 'block';
  setStatus('Photo mode. Take a selfie then click Check In.', 'warning');
  document.getElementById('photoInput').addEventListener('change', function() {
    const f = this.files[0]; if (!f) return;
    const r = new FileReader();
    r.onload = e => {
      photoB64 = e.target.result;
      document.getElementById('photoImg').src = photoB64;
      document.getElementById('photoPreview').style.display = 'block';
      const btn = document.getElementById('btnPhotoCI');
      if (btn) btn.disabled = false;
      setStatus('Photo captured. Click Check In.', 'success');
    };
    r.readAsDataURL(f);
  });
}

// ── Geo status display ─────────────────────────────────────────
function updateGeoStatus(geo) {
  const el = document.getElementById('geoStatus'); if (!el) return;
  if (!geo) { el.innerHTML = '<span style="color:#dc3545"><i class="fa fa-xmark"></i> Location denied — attendance will be flagged</span>'; return; }
  if (!OFFICE.lat) { el.innerHTML = '<span style="color:#198754"><i class="fa fa-check"></i> Location captured</span>'; return; }
  const dist = haversine(OFFICE.lat, OFFICE.lng, geo.lat, geo.lng);
  const ok   = dist <= OFFICE.radius;
  el.innerHTML = `<span style="color:${ok?'#198754':'#dc3545'}"><i class="fa fa-${ok?'check':'xmark'}"></i> ${Math.round(dist)}m from office (${ok?'Within':'Outside'} ${OFFICE.radius}m radius)</span>`;
}

function haversine(lat1, lng1, lat2, lng2) {
  const R=6371000, dLat=rad(lat2-lat1), dLng=rad(lng2-lng1);
  const a=Math.sin(dLat/2)**2+Math.cos(rad(lat1))*Math.cos(rad(lat2))*Math.sin(dLng/2)**2;
  return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
}
function rad(d) { return d*Math.PI/180; }

// ── Mark attendance ────────────────────────────────────────────
async function doMark(action) {
  setStatus('Processing…', 'info');
  // Get fresh geo if not cached
  const geo = await getGeo();
  const form = new FormData();
  form.append('_csrf', CSRF_TOKEN);
  form.append('action', action);
  if (geo) { form.append('lat', geo.lat); form.append('lng', geo.lng); }
  // Capture camera snapshot if stream active
  const video = document.getElementById('video');
  if (stream && video) {
    const c = document.createElement('canvas');
    c.width = video.videoWidth || 460; c.height = video.videoHeight || 340;
    c.getContext('2d').drawImage(video, 0, 0);
    form.append('image_data', c.toDataURL('image/jpeg', 0.75));
  } else if (photoB64) {
    form.append('image_data', photoB64);
  }
  try {
    const res  = await fetch(MARK_URL, { method:'POST', body:form, headers:{'X-Requested-With':'XMLHttpRequest'} });
    const data = await res.json();
    if (data._csrf) CSRF_TOKEN = data._csrf;
    setStatus(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1600);
  } catch(e) {
    setStatus('Network error. Try again.', 'error');
  }
}

function getGeo() {
  return new Promise(resolve => {
    if (geoCache) { const g=geoCache; geoCache=null; return resolve(g); }
    if (!navigator.geolocation) return resolve(null);
    navigator.geolocation.getCurrentPosition(
      p => resolve({ lat: p.coords.latitude, lng: p.coords.longitude }),
      () => resolve(null),
      { enableHighAccuracy: true, timeout: 8000, maximumAge: 30000 }
    );
  });
}

function setStatus(msg, type) {
  const el=document.getElementById('statusMsg');
  const c={info:'#0c63e4',success:'#198754',warning:'#cc7a00',error:'#dc3545'};
  const b={info:'#e8f0fe',success:'#d1edda',warning:'#fff3cd',error:'#fde8e8'};
  const i={info:'fa-circle-info',success:'fa-circle-check',warning:'fa-triangle-exclamation',error:'fa-circle-xmark'};
  el.innerHTML=`<i class="fa ${i[type]||'fa-circle-info'}"></i> ${msg}`;
  el.style.color=c[type]||'#6c757d'; el.style.background=b[type]||'#f8f9fa';
  el.style.border='1px solid '+(c[type]||'#dee2e6');
}
</script>
