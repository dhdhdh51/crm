<?php /** @var array $employee */ ?>

<div class="page-header">
  <h2 class="page-title">Face Enrollment — <?= e($employee['name']) ?></h2>
  <a href="<?= url('attendance') ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<div class="row">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body text-center">
        <div id="statusMsg" class="mb-3 text-muted">
          <i class="fa fa-spinner fa-spin"></i> Initializing...
        </div>

        <div style="position:relative;display:inline-block;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.15)">
          <video id="video" width="480" height="360" autoplay muted playsinline style="display:block;background:#000"></video>
          <canvas id="overlay" width="480" height="360" style="position:absolute;top:0;left:0"></canvas>
        </div>

        <div class="mt-4">
          <button id="btnCapture" class="btn btn-primary btn-lg" disabled>
            <i class="fa fa-user-plus"></i> Capture & Enroll Face
          </button>
        </div>

        <p class="text-muted mt-3" style="font-size:.85rem">
          Look directly at the camera. Hold still and click Capture.
        </p>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">Enrollment Tips</div>
      <div class="card-body">
        <ul class="text-muted" style="line-height:2">
          <li>Ensure good lighting on your face</li>
          <li>Remove sunglasses or face coverings</li>
          <li>Look directly at the camera</li>
          <li>Keep a neutral expression</li>
          <li>Enroll in similar lighting to your workplace</li>
        </ul>
        <div class="alert alert-warning mt-3" style="font-size:.85rem">
          <i class="fa fa-triangle-exclamation"></i>
          Only one face descriptor is stored per employee. Re-enrolling will replace the previous one.
        </div>
      </div>
    </div>

    <div class="card mt-3" id="previewCard" style="display:none">
      <div class="card-header">Captured Face</div>
      <div class="card-body text-center">
        <canvas id="preview" width="200" height="200" style="border-radius:50%;border:3px solid #4CAF50"></canvas>
        <p class="mt-2 text-success"><i class="fa fa-circle-check"></i> Face captured successfully!</p>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const MODEL_URL   = '<?= url("assets/face-models") ?>';
const SAVE_URL    = '<?= url("attendance/save-descriptor") ?>';
const CSRF_TOKEN  = '<?= \Core\CSRF::token() ?>';
const EMPLOYEE_ID = <?= (int)$employee['id'] ?>;

let video, canvas, ctx, capturedDescriptor = null;

async function init() {
  setStatus('Loading models...', 'info');
  try {
    await Promise.all([
      faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
      faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
      faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
  } catch(e) {
    setStatus('Failed to load models.', 'error'); return;
  }

  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360, facingMode: 'user' } });
    video = document.getElementById('video');
    video.srcObject = stream;
    video.addEventListener('playing', startDetection);
    setStatus('Camera ready. Position your face in the frame.', 'info');
  } catch(e) {
    setStatus('Camera access denied.', 'error');
  }
}

function startDetection() {
  canvas = document.getElementById('overlay');
  ctx = canvas.getContext('2d');
  detectLoop();
}

async function detectLoop() {
  const detect = async () => {
    if (!video || video.paused || video.ended) return;

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
      capturedDescriptor = result;
      document.getElementById('btnCapture').disabled = false;
      setStatus('Face detected! Click Capture to enroll.', 'success');
    } else {
      capturedDescriptor = null;
      document.getElementById('btnCapture').disabled = true;
      setStatus('No face detected. Position yourself in front of the camera.', 'info');
    }

    requestAnimationFrame(detect);
  };
  detect();
}

document.getElementById('btnCapture').addEventListener('click', async () => {
  if (!capturedDescriptor) { setStatus('No face detected yet.', 'warning'); return; }

  const descriptor = Array.from(capturedDescriptor.descriptor);

  // Show preview
  const preview = document.getElementById('preview');
  const pCtx = preview.getContext('2d');
  const box = capturedDescriptor.detection.box;
  pCtx.drawImage(video, box.x, box.y, box.width, box.height, 0, 0, 200, 200);
  document.getElementById('previewCard').style.display = 'block';

  // Save to server
  const form = new FormData();
  form.append('_token', CSRF_TOKEN);
  form.append('user_id', EMPLOYEE_ID);
  form.append('descriptor', JSON.stringify(descriptor));

  setStatus('Saving face descriptor...', 'info');
  try {
    const res = await fetch(SAVE_URL, { method: 'POST', body: form });
    const data = await res.json();
    if (data.success) {
      setStatus(data.message + ' Redirecting...', 'success');
      setTimeout(() => window.location.href = '<?= url("attendance") ?>', 1800);
    } else {
      setStatus(data.message, 'error');
    }
  } catch(e) {
    setStatus('Network error. Please try again.', 'error');
  }
});

function setStatus(msg, type) {
  const el = document.getElementById('statusMsg');
  const icons = { info: 'fa-circle-info', success: 'fa-circle-check', warning: 'fa-triangle-exclamation', error: 'fa-circle-xmark' };
  const colors = { info: '#6c757d', success: '#198754', warning: '#fd7e14', error: '#dc3545' };
  el.innerHTML = `<i class="fa ${icons[type] || 'fa-circle-info'}"></i> ${msg}`;
  el.style.color = colors[type] || '#6c757d';
}

init();
</script>
