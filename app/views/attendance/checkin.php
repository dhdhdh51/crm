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

<div class="row">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body text-center">
        <div id="statusMsg" class="mb-3 text-muted" style="font-size:1rem">
          <i class="fa fa-spinner fa-spin"></i> Initializing camera...
        </div>

        <!-- Video feed -->
        <div style="position:relative;display:inline-block;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.15)">
          <video id="video" width="480" height="360" autoplay muted playsinline style="display:block;background:#000"></video>
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
          <a href="<?= url('attendance/enroll') ?>" class="btn btn-outline">
            <i class="fa fa-user-plus"></i> Enroll My Face
          </a>
        </div>

        <p class="text-muted mt-3" style="font-size:.85rem">
          Position your face within the frame. Recognition is automatic.
        </p>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">How It Works</div>
      <div class="card-body">
        <ol class="text-muted" style="line-height:2">
          <li>Allow camera access when prompted</li>
          <li>Position your face in the frame</li>
          <li>The system auto-detects your face</li>
          <li>Click <strong>Check In</strong> to record attendance</li>
          <li>Click <strong>Check Out</strong> when leaving</li>
        </ol>
        <hr>
        <p class="text-muted" style="font-size:.85rem">
          <i class="fa fa-shield-halved"></i>
          Face processing happens entirely in your browser. No images are stored on the server.
        </p>
        <p class="text-muted" style="font-size:.85rem">
          <i class="fa fa-circle-info"></i>
          If not enrolled yet, click <em>Enroll My Face</em> first.
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

<!-- face-api.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const MODEL_URL = '<?= url("assets/face-models") ?>';
const CSRF_TOKEN = '<?= \Core\CSRF::token() ?>';
const DESCRIPTORS_URL = '<?= url("attendance/descriptors") ?>';
const CHECKIN_URL = '<?= url("attendance/mark-face") ?>';

let video, canvas, ctx, knownDescriptors = [], currentMatch = null, detecting = false;

async function init() {
  setStatus('Loading face detection models...', 'info');
  try {
    await Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
      faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
      faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
  } catch(e) {
    setStatus('Failed to load models. Check network connection.', 'error');
    console.error(e);
    return;
  }

  // Load enrolled descriptors
  try {
    const res = await fetch(DESCRIPTORS_URL);
    const data = await res.json();
    knownDescriptors = data.map(d => ({
      id: d.id,
      name: d.name,
      employee_id: d.employee_id,
      descriptor: new Float32Array(d.descriptor),
    }));
    if (knownDescriptors.length === 0) {
      setStatus('No enrolled faces found. Please enroll first.', 'warning');
    }
  } catch(e) {
    setStatus('Could not load enrolled faces.', 'error');
  }

  // Start camera
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360, facingMode: 'user' } });
    video = document.getElementById('video');
    video.srcObject = stream;
    video.addEventListener('playing', startDetection);
    setStatus('Camera ready. Looking for your face...', 'info');
  } catch(e) {
    setStatus('Camera access denied. Please allow camera permissions.', 'error');
  }
}

function startDetection() {
  canvas = document.getElementById('overlay');
  ctx = canvas.getContext('2d');
  detectFaces();
}

async function detectFaces() {
  if (detecting) return;
  detecting = true;

  const detect = async () => {
    if (!video || video.paused || video.ended) { detecting = false; return; }

    const result = await faceapi
      .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
      .withFaceLandmarks(true)
      .withFaceDescriptor();

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (result) {
      const { box } = result.detection;
      ctx.strokeStyle = '#4CAF50';
      ctx.lineWidth = 2;
      ctx.strokeRect(box.x, box.y, box.width, box.height);

      if (knownDescriptors.length > 0) {
        let bestMatch = null, bestDist = 0.6;
        for (const known of knownDescriptors) {
          const dist = faceapi.euclideanDistance(result.descriptor, known.descriptor);
          if (dist < bestDist) { bestDist = dist; bestMatch = known; }
        }

        if (bestMatch) {
          currentMatch = bestMatch;
          showMatch(bestMatch, bestDist);
          document.getElementById('btnCheckin').disabled = false;
          ctx.fillStyle = '#4CAF50';
          ctx.font = '14px Inter';
          ctx.fillText(bestMatch.name, box.x, box.y - 8);
          setStatus('Face recognized! Click Check In to record attendance.', 'success');
        } else {
          currentMatch = null;
          document.getElementById('btnCheckin').disabled = true;
          hideMatch();
          setStatus('Face detected but not recognized. Please enroll first.', 'warning');
        }
      } else {
        setStatus('Face detected. No enrolled employees to match against.', 'warning');
      }
    } else {
      currentMatch = null;
      document.getElementById('btnCheckin').disabled = true;
      setStatus('Looking for your face...', 'info');
      hideMatch();
    }

    requestAnimationFrame(detect);
  };

  detect();
}

function showMatch(match, dist) {
  document.getElementById('matchCard').style.display = 'block';
  document.getElementById('matchAvatar').textContent = match.name.charAt(0).toUpperCase();
  document.getElementById('matchName').textContent = match.name;
  document.getElementById('matchEmpId').textContent = match.employee_id;
  const conf = Math.round((1 - dist) * 100);
  document.getElementById('matchConfidence').innerHTML =
    `<span class="badge badge-success">Confidence: ${conf}%</span>`;
}

function hideMatch() {
  document.getElementById('matchCard').style.display = 'none';
}

function setStatus(msg, type) {
  const el = document.getElementById('statusMsg');
  const icons = { info: 'fa-circle-info', success: 'fa-circle-check', warning: 'fa-triangle-exclamation', error: 'fa-circle-xmark' };
  const colors = { info: '#6c757d', success: '#198754', warning: '#fd7e14', error: '#dc3545' };
  el.innerHTML = `<i class="fa ${icons[type] || 'fa-circle-info'}"></i> ${msg}`;
  el.style.color = colors[type] || '#6c757d';
}

async function postAttendance(action) {
  const form = new FormData();
  form.append('_token', CSRF_TOKEN);
  form.append('action', action);

  const res = await fetch(CHECKIN_URL, { method: 'POST', body: form });
  const data = await res.json();

  if (data.success) {
    setStatus(data.message, 'success');
    setTimeout(() => location.reload(), 1500);
  } else {
    setStatus(data.message, 'error');
  }
}

document.getElementById('btnCheckin').addEventListener('click', () => {
  if (!currentMatch) { setStatus('No face recognized yet.', 'warning'); return; }
  postAttendance('checkin');
});

const btnOut = document.getElementById('btnCheckout');
if (btnOut) btnOut.addEventListener('click', () => postAttendance('checkout'));

init();
</script>
