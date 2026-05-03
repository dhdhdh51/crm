<?php
/** @var array $records @var array $employees @var array $filters @var array|false $todayRecord */
$uid   = \Core\Session::user()['id'];
$today = $todayRecord ?? false;
?>

<div class="page-header">
  <h2 class="page-title">Attendance</h2>
  <div class="page-actions">
    <a href="<?= url('attendance/checkin') ?>" class="btn btn-primary">
      <i class="fa fa-camera"></i> Face Check-In / Out
    </a>
    <?php if (\Core\Session::can(['admin','manager','super_admin','hr'])): ?>
    <button class="btn btn-secondary" onclick="toggleManualForm()">
      <i class="fa fa-pen"></i> Mark Manual
    </button>
    <a href="<?= url('attendance/report') ?>" class="btn btn-outline">
      <i class="fa fa-chart-bar"></i> Monthly Report
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Today's quick status for every employee -->
<div class="card mb-4" style="border-left:4px solid var(--maroon)">
  <div class="card-body" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
    <div style="flex:1;min-width:180px">
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:1px;text-transform:uppercase;margin-bottom:4px">Today — <?= date('D, d M Y') ?></div>
      <?php if (!$today): ?>
        <span style="color:#dc3545;font-weight:600"><i class="fa fa-circle-xmark"></i> Not checked in yet</span>
      <?php elseif ($today && !$today['check_out']): ?>
        <span style="color:#198754;font-weight:600"><i class="fa fa-circle-check"></i> Checked in at <?= date('h:i A', strtotime($today['check_in'])) ?></span>
        <span style="margin-left:10px;color:#fd7e14;font-weight:600"><i class="fa fa-clock"></i> Not checked out</span>
      <?php else: ?>
        <span style="color:#198754;font-weight:600"><i class="fa fa-circle-check"></i> <?= date('h:i A', strtotime($today['check_in'])) ?> → <?= date('h:i A', strtotime($today['check_out'])) ?></span>
        &nbsp;<?= attendanceStatusBadge($today['status']) ?>
      <?php endif; ?>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap">
      <?php if (!$today): ?>
        <form method="POST" action="<?= url('attendance/manual') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="user_id" value="<?= $uid ?>">
          <input type="hidden" name="date"    value="<?= date('Y-m-d') ?>">
          <input type="hidden" name="status"  value="present">
          <input type="hidden" name="notes"   value="Self check-in">
          <button class="btn btn-primary btn-sm" onclick="return confirm('Mark yourself Present for today?')">
            <i class="fa fa-right-to-bracket"></i> Check In
          </button>
        </form>
      <?php elseif ($today && !$today['check_out']): ?>
        <form method="POST" action="<?= url('attendance/checkout-manual') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="user_id" value="<?= $uid ?>">
          <button class="btn btn-warning btn-sm" onclick="return confirm('Confirm check-out?')">
            <i class="fa fa-right-from-bracket"></i> Check Out
          </button>
        </form>
      <?php else: ?>
        <span class="badge badge-success" style="padding:8px 14px">
          <i class="fa fa-circle-check"></i> Attendance complete
        </span>
      <?php endif; ?>
      <a href="<?= url('attendance/checkin') ?>" class="btn btn-outline btn-sm">
        <i class="fa fa-camera"></i> Use Camera
      </a>
    </div>
  </div>
</div>

<!-- Manual Mark Form (hidden by default) -->
<?php if (\Core\Session::can(['admin','manager'])): ?>
<div id="manualForm" class="card mb-4" style="display:none">
  <div class="card-body">
    <h4 class="mb-3">Manual Attendance</h4>
    <form method="POST" action="<?= url('attendance/manual') ?>">
      <?= csrf_field() ?>
      <div class="form-row">
        <div class="form-group">
          <label>Employee</label>
          <select name="user_id" class="form-control" required>
            <option value="">Select Employee</option>
            <?php foreach ($employees as $emp): ?>
              <option value="<?= $emp['id'] ?>"><?= e($emp['name']) ?> (<?= e($emp['employee_id']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Date</label>
          <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status" class="form-control" required>
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
            <option value="half_day">Half Day</option>
            <option value="holiday">Holiday</option>
          </select>
        </div>
        <div class="form-group">
          <label>Notes</label>
          <input type="text" name="notes" class="form-control" placeholder="Optional notes">
        </div>
        <div class="form-group" style="align-self:flex-end">
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" class="form-row">
      <?php if (\Core\Session::can(['admin','manager'])): ?>
      <div class="form-group">
        <label>Employee</label>
        <select name="user_id" class="form-control">
          <option value="">All Employees</option>
          <?php foreach ($employees as $emp): ?>
            <option value="<?= $emp['id'] ?>" <?= ($filters['user_id'] ?? 0) == $emp['id'] ? 'selected' : '' ?>>
              <?= e($emp['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="form-group">
        <label>Date</label>
        <input type="date" name="date" class="form-control" value="<?= e($filters['date'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Month</label>
        <select name="month" class="form-control">
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= ($filters['month'] ?? date('m')) == $m ? 'selected' : '' ?>>
              <?= date('F', mktime(0,0,0,$m,1)) ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Year</label>
        <input type="number" name="year" class="form-control" value="<?= e($filters['year'] ?? date('Y')) ?>" min="2020" max="2099">
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="">All</option>
          <?php foreach (['present','absent','late','half_day','holiday'] as $s): ?>
            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>>
              <?= ucfirst(str_replace('_',' ', $s)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="align-self:flex-end">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="<?= url('attendance') ?>" class="btn btn-outline">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Records Table -->
<div class="card">
  <div class="card-body p-0">
    <table class="data-table">
      <thead>
        <tr>
          <th>Employee</th>
          <th>Date</th>
          <th>Check In</th>
          <th>Check Out</th>
          <th>Hours</th>
          <th>Status</th>
          <th>Method</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($records)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">No attendance records found.</td></tr>
        <?php else: ?>
          <?php foreach ($records as $r): ?>
          <tr>
            <td>
              <strong><?= e($r['employee_name']) ?></strong>
              <small class="text-muted d-block"><?= e($r['emp_code']) ?></small>
            </td>
            <td><?= formatDate($r['date']) ?></td>
            <td><?= $r['check_in'] ? date('h:i A', strtotime($r['check_in'])) : '–' ?></td>
            <td><?= $r['check_out'] ? date('h:i A', strtotime($r['check_out'])) : '–' ?></td>
            <td>
              <?php if ($r['check_in'] && $r['check_out']):
                $diff = strtotime($r['check_out']) - strtotime($r['check_in']);
                echo floor($diff/3600) . 'h ' . floor(($diff%3600)/60) . 'm';
              else: echo '–'; endif; ?>
            </td>
            <td><?= attendanceStatusBadge($r['status']) ?></td>
            <td>
              <?php if ($r['method'] === 'face'): ?>
                <span class="badge badge-info"><i class="fa fa-camera"></i> Face</span>
              <?php else: ?>
                <span class="badge badge-secondary"><i class="fa fa-pen"></i> Manual</span>
              <?php endif; ?>
            </td>
            <td><?= e($r['notes'] ?? '–') ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function toggleManualForm() {
  const f = document.getElementById('manualForm');
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
</script>
