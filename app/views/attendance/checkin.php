<?php /** @var array|false $today */ ?>

<div class="page-header">
  <h2 class="page-title">Face Recognition Check-In</h2>
  <a href="<?= url('attendance') ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<?php if ($today): ?>
<div class="alert alert-info mb-4">
  <i class="fa fa-circle-info"></i>
  <strong>Today's Record:</strong>
  Check-in at <?= date('h:i A', strtotime($today['check_in'])) ?>
  <?= $today['check_out'] ? ' | Check-out at ' . date('h:i A', strtotime($today['check_out'])) : ' (not checked out yet)' ?>
  — <?= attendanceStatusBadge($today['status']) ?>
</div>
<?php endif; ?>

<!-- HTTPS warning banner (shown by JS if on HTTP) -->
<div id="httpsWarning" style="display:none" class="alert alert-warning mb-4">
  <i class="fa fa-triangle-exclamation"></i>
  <strong>Camera requires HTTPS.</strong>
  Your site is running on plain HTTP — browsers block webcam access for security.
  <br><strong>Fix:</strong> Go to <em>cPanel → SSL/TLS → AutoSSL</em> and enable free SSL for your domain.
  <br>In the meantime, use the <strong>Manual Check-In</strong> button below.
</div>

<div class="row">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body text-center">
        <div id="statusMsg" class="mb-3 text-muted" style="font-size:1rem">
          <i class="fa fa-spinner fa-spin"></i> Initializing camera...
        </div>

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

    <!-- Fallback manual check-in (always visible) -->
    <div class="card mt-3" id="fallbackCard">
      <div class="card-header"><h3>Manual Check-In (No Camera)</h3></div>
      <div class="card-body">
        <p class="text-muted mb-3" style="font-size:13px">
          Use this if your camera isn't working or the site is on HTTP.
        </p>
        <div class="d-flex gap-2 flex-wrap">
          <?php if (!$today): ?>
          <form method="POST" action="<?= url('attendance/manual') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id"  value="<?= \Core\Session::user()['id'] ?>">
            <input type="hidden" name="date"     value="<?= date('Y-m-d') ?>">
            <input type="hidden" name="status"   value="present">
            <input type="hidden" name="notes"    value="Self check-in (no camera)">
            <button type="submit" class="btn btn-primary" onclick="return confirm('Mark yourself as Present today?')">
              <i class="fa fa-check"></i> Mark Present
            </button>
          </form>
          <?php elseif ($today && !$today['check_out']): ?>
          <button class="btn btn-warning" id="btnManualOut">
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
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">Camera Troubleshooting</div>
      <div class="card-body" style="font-size:13px">
        <div style="display:flex;flex-direction:column;gap:12px">
          <div>
            <strong style="color:var(--maroon)"><i class="fa fa-lock"></i> HTTPS Required</strong>
            <p class="text-muted mt-1">Browsers block camera on HTTP. Enable SSL in cPanel → AutoSSL (free).</p>
          </div>
          <div>
            <strong style="color:var(--maroon)"><i class="fa fa-chrome"></i> Browser Permission</strong>
            <p class="text-muted mt-1">Click the 🔒 lock icon in your browser address bar → Allow Camera.</p>
          </div>
          <div>
            <strong style="color:var(--maroon)"><i class="fa fa-video"></i> Camera in Use</strong>
            <p class="text-muted mt-1">Close other apps (Zoom, Teams) using the camera and reload.</p>
          </div>
        </div>
        <hr>
        <p class="text-muted" style="font-size:12px">
          <i class="fa fa-shield-halved"></i>
          Face processing happens in your browser. No images are sent to the server unless GPS/image capture is enabled.
        </p>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const MODEL_URL       = '<?= url("assets/face-models") ?>';
let   CSRF_TOKEN      = '<?= \Core\CSRF::token() ?>';
const DESCRIPTORS_URL = '<?= url("attendance/descriptors") ?>';
const CHECKIN_URL     = '<?= url("attendance/mark-face") ?>';

let video, canvas, ctx, knownDescriptors = [], currentMatch = null, detecting = false;

// ── Permission + camera boot ───────────────────────────────────
(async function boot() {
  // Show permission status panel
  setStatus('<i class="fa fa-spinner fa-spin"></i> Requesting camera & location access…', 'info');

  // 1. Location — trigger pop-up immediately
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      () => {},
      () => {},
      { enableHighAccuracy: true, timeout: 10000 }
    );
  }

  // 2. Camera
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    document.getElementById('httpsWarning').style.display = 'flex';
    setStatus('Camera requires HTTPS. Use Manual Check-In below.', 'error');
    return;
  }

  // Use Permissions API to check state first (avoids silent failure if already denied)
  let camState = 'prompt';
  try { camState = (await navigator.permissions.query({ name: 'camera' })).state; } catch(_) {}

  if (camState === 'denied') {
    setStatus('Camera blocked. Click the 🔒 icon in your address bar → Site Settings → Allow Camera → reload.', 'error');
    return;
  }

  // ONE getUserMedia call — triggers the pop-up if not yet granted, then reuse stream
  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'user' }, width: { ideal: 480 }, height: { ideal: 360 } },
      audio: false
    });
    // Attach stream to video immediately (user sees live feed)
    video = document.getElementById('video');
    video.onplaying = () => { if (!detecting) startDetection(); };
    video.srcObject = stream;
    const p = video.play();
    if (p instanceof Promise) p.catch(() => {});
    setTimeout(() => { if (!detecting && !video.paused) startDetection(); }, 2000);
    setStatus('Camera active. Loading face models…', 'info');
    // Load models + descriptors in background
    loadModels();
  } catch (e) {
    const msgs = {
      NotAllowedError:      'Camera permission denied. Click 🔒 → Allow Camera → reload.',
      NotFoundError:        'No camera found on this device.',
      NotReadableError:     'Camera in use by another app. Close Zoom/Teams and reload.',
      OverconstrainedError: 'Camera not supported on this device.',
    };
    setStatus(msgs[e.name] || 'Camera error: ' + e.message, 'error');
  }
})();

// ── Load models + descriptors after camera is live ─────────────
async function loadModels() {
  try {
    await Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
      faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
      faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
  } catch (e) {
    setStatus('Failed to load face models. Check internet connection.', 'error');
    return;
  }
  try {
    const res  = await fetch(DESCRIPTORS_URL);
    const data = await res.json();
    knownDescriptors = data.map(d => ({
      id: d.id, name: d.name, employee_id: d.employee_id,
      descriptor: new Float32Array(d.descriptor),
    }));
    setStatus(
      knownDescriptors.length
        ? 'Ready. Position your face in the frame.'
        : 'Camera active. No faces enrolled yet — click "Enroll Face".',
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
    // Skip frame if paused but keep loop alive (don't permanently exit)
    if (video.paused) { setTimeout(() => requestAnimationFrame(detect), 200); return; }

    let result;
    try {
      result = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
        .withFaceLandmarks(true)
        .withFaceDescriptor();
    } catch (e) {
      requestAnimationFrame(detect); return;
    }

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
          setStatus('Face detected but not recognized. Please enroll first.', 'warning');
        }
      } else {
        // No enrolled faces — show box but don't overwrite the enroll warning
        ctx.strokeStyle = '#fd7e14';
        setStatus('Face detected. Click "Enroll Face" to register yourself first.', 'warning');
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

function setStatus(msg, type) {
  const el = document.getElementById('statusMsg');
  const icons  = { info:'fa-circle-info', success:'fa-circle-check', warning:'fa-triangle-exclamation', error:'fa-circle-xmark' };
  const colors = { info:'#6c757d', success:'#198754', warning:'#fd7e14', error:'#dc3545' };
  el.innerHTML = `<i class="fa ${icons[type]||'fa-circle-info'}"></i> ${msg}`;
  el.style.color = colors[type] || '#6c757d';
}

// ── Geolocation helper ─────────────────────────────────────────
function getPosition() {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) return resolve(null);
    navigator.geolocation.getCurrentPosition(
      pos => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
      err => {
        if (err.code === 1) setStatus('Location denied — check-in will be flagged.', 'warning');
        resolve(null); // proceed without geo
      },
      { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
    );
  });
}

// ── AJAX attendance ────────────────────────────────────────────
async function postAttendance(action) {
  setStatus('Processing…', 'info');
  const geo  = await getPosition();
  const form = new FormData();
  form.append('_csrf',  CSRF_TOKEN);   // use _csrf (correct key)
  form.append('action', action);
  if (geo) {
    form.append('lat', geo.lat);
    form.append('lng', geo.lng);
  }
  await send(form);
}

async function send(form) {
  try {
    const res  = await fetch(CHECKIN_URL, { method: 'POST', body: form });
    const data = await res.json();
    if (data._csrf) CSRF_TOKEN = data._csrf; // refresh token for next call
    setStatus(data.message, data.success ? 'success' : 'error');
    if (data.csrf_error) { setStatus('Session expired — please reload.', 'error'); return; }
    if (data.success) setTimeout(() => location.reload(), 1800);
  } catch (e) {
    setStatus('Network error. Please try again.', 'error');
  }
}

document.getElementById('btnCheckin').addEventListener('click', () => {
  if (!currentMatch) { setStatus('No face recognized yet.', 'warning'); return; }
  postAttendance('checkin');
});
const btnOut = document.getElementById('btnCheckout');
if (btnOut) btnOut.addEventListener('click', () => postAttendance('checkout'));

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
