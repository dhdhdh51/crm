<?php /** @var array|false $today */ ?>

<div class="page-header">
  <h2 class="page-title">Attendance Check-In</h2>
  <a href="<?= url('attendance') ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<?php if ($today): ?>
<div class="alert alert-info mb-4">
  <i class="fa fa-circle-info"></i>
  <strong>Today's Record:</strong>
  Check-in at <?= $today['check_in'] ? date('h:i A', strtotime($today['check_in'])) : '–' ?>
  <?= $today['check_out'] ? ' | Check-out at ' . date('h:i A', strtotime($today['check_out'])) : ' (not checked out yet)' ?>
  — <?= attendanceStatusBadge($today['status']) ?>
</div>
<?php endif; ?>

<!-- Status banner — shown on HTTP -->
<div id="httpsWarning" style="display:none" class="alert alert-warning mb-3">
  <i class="fa fa-triangle-exclamation"></i>
  <strong>HTTP detected.</strong> Face recognition requires HTTPS.
  Using <strong>Photo Check-In</strong> mode instead.
  To enable full face recognition: cPanel → SSL/TLS → AutoSSL (free).
</div>

<!-- Shared status message -->
<div id="statusMsg" class="alert alert-secondary mb-3" style="font-size:.95rem">
  <i class="fa fa-spinner fa-spin"></i> Initializing…
</div>

<div class="row">

  <!-- ══ FACE RECOGNITION (HTTPS only) ══════════════════════════ -->
  <div id="faceSection" class="col-lg-7" style="display:none">
    <div class="card">
      <div class="card-header" style="font-weight:700;color:var(--maroon)">
        <i class="fa fa-face-smile"></i> Face Recognition Check-In
      </div>
      <div class="card-body text-center">
        <div style="position:relative;display:inline-block;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.15)">
          <video id="video" width="480" height="360" autoplay muted playsinline style="display:block;background:#111"></video>
          <canvas id="overlay" width="480" height="360" style="position:absolute;top:0;left:0"></canvas>
        </div>

        <div class="mt-4 d-flex gap-3 justify-content-center flex-wrap">
          <button id="btnCheckin" class="btn btn-primary btn-lg" disabled>
            <i class="fa fa-camera"></i> Check In
          </button>
          <?php if ($today && !$today['check_out']): ?>
          <button id="btnCheckout" class="btn btn-warning btn-lg">
            <i class="fa fa-clock"></i> Check Out
          </button>
          <?php endif; ?>
          <a href="<?= url('attendance/enroll') ?>" class="btn btn-ghost">
            <i class="fa fa-user-plus"></i> Enroll Face
          </a>
        </div>
      </div>
    </div>

    <div class="card mt-3" id="matchCard" style="display:none">
      <div class="card-header">Identified As</div>
      <div class="card-body text-center">
        <div class="user-avatar mx-auto mb-2" style="width:60px;height:60px;font-size:1.5rem" id="matchAvatar"></div>
        <h4 id="matchName" class="mb-0"></h4>
        <small id="matchEmpId" class="text-muted"></small>
        <div id="matchConfidence" class="mt-1"></div>
      </div>
    </div>
  </div>

  <!-- ══ PHOTO CHECK-IN (HTTP fallback) ════════════════════════ -->
  <div id="photoSection" class="col-lg-7" style="display:none">
    <div class="card">
      <div class="card-header" style="font-weight:700;color:var(--maroon)">
        <i class="fa fa-camera"></i> Photo Check-In
        <span class="badge badge-warning ms-2" style="font-size:11px;margin-left:8px">HTTP Mode</span>
      </div>
      <div class="card-body text-center">
        <p class="text-muted mb-3" style="font-size:13px">
          Take a selfie using your device camera. The photo is saved with your attendance record.
        </p>

        <!-- Preview -->
        <div id="selfiePreviewWrap" style="display:none;margin-bottom:16px">
          <img id="selfieImg" style="max-width:300px;max-height:280px;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.2)">
          <div style="margin-top:8px;font-size:12px;color:#198754"><i class="fa fa-circle-check"></i> Photo captured</div>
        </div>

        <!-- Capture button -->
        <label for="selfieInput" class="btn btn-outline btn-lg" style="cursor:pointer">
          <i class="fa fa-camera"></i> Take Selfie
        </label>
        <input type="file" id="selfieInput" accept="image/*" capture="user" style="display:none">
        <div style="font-size:12px;color:var(--text-muted);margin-top:6px">Opens your device camera</div>

        <div class="mt-4 d-flex gap-3 justify-content-center flex-wrap">
          <button id="btnPhotoCheckin" class="btn btn-primary btn-lg" disabled>
            <i class="fa fa-right-to-bracket"></i> Check In
          </button>
          <?php if ($today && !$today['check_out']): ?>
          <button id="btnPhotoCheckout" class="btn btn-warning btn-lg">
            <i class="fa fa-clock"></i> Check Out
          </button>
          <?php endif; ?>
        </div>
        <div style="font-size:12px;color:var(--text-muted);margin-top:8px">
          <i class="fa fa-location-dot"></i> Location will be captured automatically if allowed.
        </div>
      </div>
    </div>
  </div>

  <!-- ══ SIDEBAR ═══════════════════════════════════════════════ -->
  <div class="col-lg-5">

    <!-- Manual fallback (always available) -->
    <div class="card mb-3">
      <div class="card-header">Manual Check-In (No Camera)</div>
      <div class="card-body">
        <p class="text-muted mb-3" style="font-size:13px">Use if camera is unavailable.</p>
        <div class="d-flex gap-2 flex-wrap">
          <?php if (!$today): ?>
          <form method="POST" action="<?= url('attendance/manual') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= \Core\Session::user()['id'] ?>">
            <input type="hidden" name="date"    value="<?= date('Y-m-d') ?>">
            <input type="hidden" name="status"  value="present">
            <input type="hidden" name="notes"   value="Self check-in (no camera)">
            <button type="submit" class="btn btn-primary btn-sm"
                    onclick="return confirm('Mark yourself as Present today?')">
              <i class="fa fa-check"></i> Mark Present
            </button>
          </form>
          <?php elseif ($today && !$today['check_out']): ?>
          <button class="btn btn-warning btn-sm" id="btnManualOut">
            <i class="fa fa-clock"></i> Mark Check-Out
          </button>
          <?php else: ?>
          <span class="badge badge-success" style="padding:10px 16px;font-size:13px">
            <i class="fa fa-circle-check"></i> Attendance complete for today
          </span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Troubleshooting -->
    <div class="card" id="troubleshootCard">
      <div class="card-header">Troubleshooting</div>
      <div class="card-body" style="font-size:13px">
        <div style="display:flex;flex-direction:column;gap:10px">
          <div>
            <strong style="color:var(--maroon)"><i class="fa fa-lock"></i> HTTPS Required for Face Recognition</strong>
            <p class="text-muted mt-1">Enable free SSL in cPanel → SSL/TLS → AutoSSL, then reload.</p>
          </div>
          <div>
            <strong style="color:var(--maroon)"><i class="fa fa-mobile"></i> Photo Check-In works on HTTP</strong>
            <p class="text-muted mt-1">On HTTP, use the Photo Check-In above — opens native camera on mobile.</p>
          </div>
          <div>
            <strong style="color:var(--maroon)"><i class="fa fa-video"></i> Camera in Use</strong>
            <p class="text-muted mt-1">Close other apps using the camera (Zoom, Teams) and reload.</p>
          </div>
        </div>
        <hr>
        <p class="text-muted" style="font-size:12px">
          <i class="fa fa-shield-halved"></i>
          Face processing happens entirely in your browser. Photos are stored only on your server.
        </p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const MODEL_URL       = '<?= url("assets/face-models") ?>';
let   CSRF_TOKEN      = '<?= \Core\CSRF::token() ?>';
const DESCRIPTORS_URL = '<?= url("attendance/descriptors") ?>';
const CHECKIN_URL     = '<?= url("attendance/mark-face") ?>';

let video, canvas, ctx, knownDescriptors = [], currentMatch = null, detecting = false;
let bootGeo  = null;  // cached from boot() location request
let selfieB64 = null; // base64 photo for HTTP mode

// ── Boot: detect HTTP vs HTTPS and route accordingly ──────────
(async function boot() {
  setStatus('Initializing…', 'info');

  // 1. Request location immediately (triggers popup)
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      pos => { bootGeo = { lat: pos.coords.latitude, lng: pos.coords.longitude }; },
      () => {},
      { enableHighAccuracy: true, timeout: 10000 }
    );
  }

  // 2. Decide mode based on protocol
  const isSecure = location.protocol === 'https:'
    || location.hostname === 'localhost'
    || location.hostname === '127.0.0.1';

  if (!isSecure) {
    // ── HTTP mode: photo check-in ──────────────────────────────
    document.getElementById('httpsWarning').style.display = 'flex';
    document.getElementById('photoSection').style.display = 'block';
    setStatus('Photo Check-In mode (HTTP). Take a selfie to check in.', 'warning');
    initPhotoCapture();
    return;
  }

  // ── HTTPS mode: face recognition ──────────────────────────────
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    document.getElementById('httpsWarning').style.display = 'flex';
    document.getElementById('photoSection').style.display = 'block';
    setStatus('Camera API unavailable. Using Photo Check-In.', 'warning');
    initPhotoCapture();
    return;
  }

  let camState = 'prompt';
  try { camState = (await navigator.permissions.query({ name: 'camera' })).state; } catch(_) {}

  if (camState === 'denied') {
    // Camera explicitly blocked — fall back to photo check-in
    document.getElementById('photoSection').style.display = 'block';
    setStatus('Camera blocked in browser settings. Using Photo Check-In instead.', 'warning');
    initPhotoCapture();
    return;
  }

  // Camera available — start face recognition
  document.getElementById('faceSection').style.display = 'block';
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'user' }, width: { ideal: 480 }, height: { ideal: 360 } },
      audio: false
    });
    video = document.getElementById('video');
    video.onplaying = () => { if (!detecting) startDetection(); };
    video.srcObject = stream;
    const p = video.play();
    if (p instanceof Promise) p.catch(() => {});
    setTimeout(() => { if (!detecting && !video.paused) startDetection(); }, 2000);
    setStatus('Camera active. Loading face models…', 'info');
    loadModels();
  } catch (e) {
    const msgs = {
      NotAllowedError:      'Camera denied. Switching to Photo Check-In.',
      NotFoundError:        'No camera found. Switching to Photo Check-In.',
      NotReadableError:     'Camera in use by another app. Close it and reload.',
      OverconstrainedError: 'Camera not supported.',
    };
    // Fall back to photo check-in
    document.getElementById('faceSection').style.display = 'none';
    document.getElementById('photoSection').style.display = 'block';
    setStatus(msgs[e.name] || 'Camera error: ' + e.message + ' — using Photo Check-In.', 'warning');
    initPhotoCapture();
  }
})();

// ── Photo Check-In ──────────────────────────────────────────────
function initPhotoCapture() {
  const input    = document.getElementById('selfieInput');
  const btnCI    = document.getElementById('btnPhotoCheckin');
  const btnCO    = document.getElementById('btnPhotoCheckout');

  input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      selfieB64 = e.target.result;
      document.getElementById('selfieImg').src = selfieB64;
      document.getElementById('selfiePreviewWrap').style.display = 'block';
      if (btnCI) btnCI.disabled = false;
      setStatus('Photo captured. Click Check In to record attendance.', 'success');
    };
    reader.readAsDataURL(file);
  });

  if (btnCI) {
    btnCI.addEventListener('click', async () => {
      if (!selfieB64) { setStatus('Please take a selfie first.', 'warning'); return; }
      await postAttendance('checkin', selfieB64);
    });
  }
  if (btnCO) {
    btnCO.addEventListener('click', async () => await postAttendance('checkout', selfieB64));
  }
}

// ── Face recognition models ─────────────────────────────────────
async function loadModels() {
  setStatus('<i class="fa fa-spinner fa-spin"></i> Loading face recognition models…', 'info');
  try {
    await Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
      faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
      faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
  } catch (e) {
    setStatus('Failed to load face models. Check internet and reload.', 'error');
    return;
  }
  try {
    const res  = await fetch(DESCRIPTORS_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();
    knownDescriptors = data.map(d => ({
      id: d.id, name: d.name, employee_id: d.employee_id,
      descriptor: new Float32Array(d.descriptor),
    }));
    setStatus(
      knownDescriptors.length
        ? 'Ready. Position your face in the frame.'
        : 'Camera active. No faces enrolled — click "Enroll Face".',
      knownDescriptors.length ? 'info' : 'warning'
    );
  } catch (e) {
    setStatus('Could not load enrolled faces.', 'error');
  }
}

function startDetection() {
  canvas = document.getElementById('overlay');
  ctx    = canvas.getContext('2d');
  detectFaces();
}

async function detectFaces() {
  if (detecting) return;
  detecting = true;
  const detect = async () => {
    if (!video || video.ended) { detecting = false; return; }
    if (video.paused) { setTimeout(() => requestAnimationFrame(detect), 200); return; }
    let result;
    try {
      result = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
        .withFaceLandmarks(true)
        .withFaceDescriptor();
    } catch (e) { requestAnimationFrame(detect); return; }

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    if (result) {
      const { box } = result.detection;
      ctx.strokeStyle = '#4CAF50'; ctx.lineWidth = 2;
      ctx.strokeRect(box.x, box.y, box.width, box.height);
      if (knownDescriptors.length) {
        let bestMatch = null, bestDist = 0.6;
        for (const k of knownDescriptors) {
          const d = faceapi.euclideanDistance(result.descriptor, k.descriptor);
          if (d < bestDist) { bestDist = d; bestMatch = k; }
        }
        if (bestMatch) {
          currentMatch = bestMatch;
          showMatch(bestMatch, bestDist);
          document.getElementById('btnCheckin').disabled = false;
          ctx.fillStyle = '#4CAF50'; ctx.font = '14px Montserrat,sans-serif';
          ctx.fillText(bestMatch.name, box.x, box.y - 8);
          setStatus('Face recognized! Click Check In.', 'success');
        } else {
          currentMatch = null;
          document.getElementById('btnCheckin').disabled = true;
          hideMatch();
          setStatus('Face not recognized. Enroll first.', 'warning');
        }
      } else {
        ctx.strokeStyle = '#fd7e14';
      }
    } else {
      currentMatch = null;
      document.getElementById('btnCheckin').disabled = true;
      if (knownDescriptors.length) setStatus('Looking for your face…', 'info');
      hideMatch();
    }
    requestAnimationFrame(detect);
  };
  detect();
}

function showMatch(m, dist) {
  document.getElementById('matchCard').style.display = 'block';
  document.getElementById('matchAvatar').textContent = m.name.charAt(0).toUpperCase();
  document.getElementById('matchName').textContent   = m.name;
  document.getElementById('matchEmpId').textContent  = m.employee_id;
  const conf = Math.round((1 - dist) * 100);
  document.getElementById('matchConfidence').innerHTML =
    `<span class="badge badge-success">Confidence: ${conf}%</span>`;
}
function hideMatch() { document.getElementById('matchCard').style.display = 'none'; }

// ── Shared helpers ──────────────────────────────────────────────
function setStatus(msg, type) {
  const el = document.getElementById('statusMsg');
  const icons  = { info:'fa-circle-info', success:'fa-circle-check', warning:'fa-triangle-exclamation', error:'fa-circle-xmark' };
  const colors = { info:'#0c63e4', success:'#198754', warning:'#cc7a00', error:'#dc3545' };
  const bgs    = { info:'#e8f0fe', success:'#d1edda', warning:'#fff3cd', error:'#fde8e8' };
  el.innerHTML = `<i class="fa ${icons[type]||'fa-circle-info'}"></i> ${msg}`;
  el.style.color      = colors[type] || '#6c757d';
  el.style.background = bgs[type]    || '#f8f9fa';
  el.style.border     = '1px solid ' + (colors[type] || '#dee2e6');
}

function getPosition() {
  return new Promise((resolve) => {
    if (!navigator.geolocation) return resolve(null);
    if (bootGeo) { const g = bootGeo; bootGeo = null; return resolve(g); }
    navigator.geolocation.getCurrentPosition(
      pos => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
      err => { if (err.code === 1) setStatus('Location denied — attendance will be flagged.', 'warning'); resolve(null); },
      { enableHighAccuracy: true, timeout: 8000, maximumAge: 30000 }
    );
  });
}

async function postAttendance(action, imageData = null) {
  setStatus('Processing…', 'info');
  const geo  = await getPosition();
  const form = new FormData();
  form.append('_csrf',  CSRF_TOKEN);
  form.append('action', action);
  if (geo)       { form.append('lat', geo.lat); form.append('lng', geo.lng); }
  if (imageData) { form.append('image_data', imageData); }
  await send(form);
}

async function send(form) {
  try {
    const res  = await fetch(CHECKIN_URL, {
      method: 'POST', body: form,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();
    if (data._csrf) CSRF_TOKEN = data._csrf;
    setStatus(data.message, data.success ? 'success' : 'error');
    if (data.csrf_error) { setStatus('Session expired — please reload.', 'error'); return; }
    if (data.success) setTimeout(() => location.reload(), 1800);
  } catch (e) {
    setStatus('Network error. Please try again.', 'error');
  }
}

// Face recognition check-in button
const btnCI = document.getElementById('btnCheckin');
if (btnCI) btnCI.addEventListener('click', () => {
  if (!currentMatch) { setStatus('No face recognized yet.', 'warning'); return; }
  postAttendance('checkin');
});
const btnOut = document.getElementById('btnCheckout');
if (btnOut) btnOut.addEventListener('click', () => postAttendance('checkout'));

// Manual checkout button (sidebar)
const btnManualOut = document.getElementById('btnManualOut');
if (btnManualOut) {
  btnManualOut.addEventListener('click', async () => {
    const form = new FormData();
    form.append('_csrf',  CSRF_TOKEN);
    form.append('action', 'checkout');
    await send(form);
  });
}
</script>
