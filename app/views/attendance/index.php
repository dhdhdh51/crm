<?php /** @var array $records @var array $filters @var array|false $today @var bool $isAdmin @var array $employees */ ?>
<div class="page-header">
  <h2 class="page-title">Attendance</h2>
  <a href="<?= url('attendance/checkin') ?>" class="btn btn-primary"><i class="fa fa-camera"></i> Check In / Out</a>
</div>

<?php $s=\Core\Session::flash('success'); if($s): ?><div class="alert alert-success"><?= e($s) ?></div><?php endif; ?>
<?php $e=\Core\Session::flash('error');   if($e): ?><div class="alert alert-danger"><?= e($e) ?></div><?php endif; ?>

<!-- Today status card -->
<div class="card mb-4" style="border-left:4px solid var(--maroon)">
  <div class="card-body" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
    <div style="flex:1">
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:1px;text-transform:uppercase">Today — <?= date('D, d M Y') ?></div>
      <?php if (!$today): ?>
        <span style="color:#dc3545;font-weight:600"><i class="fa fa-circle-xmark"></i> Not checked in</span>
      <?php elseif (!$today['check_out']): ?>
        <span style="color:#198754;font-weight:600"><i class="fa fa-circle-check"></i> In at <?= date('h:i A', strtotime($today['check_in'])) ?></span>
        <span style="margin-left:12px;color:#fd7e14;font-weight:600"><i class="fa fa-clock"></i> Not checked out</span>
      <?php else: ?>
        <span style="color:#198754;font-weight:600"><i class="fa fa-circle-check"></i> <?= date('h:i A', strtotime($today['check_in'])) ?> → <?= date('h:i A', strtotime($today['check_out'])) ?></span>
      <?php endif; ?>
    </div>
    <a href="<?= url('attendance/checkin') ?>" class="btn btn-primary btn-sm"><i class="fa fa-camera"></i> Camera Check-In</a>
  </div>
</div>

<!-- Filters -->
<div class="card mb-4"><div class="card-body">
  <form method="GET" class="form-row">
    <?php if ($isAdmin): ?>
    <div class="form-group"><label>Employee</label>
      <select name="user_id" class="form-control">
        <option value="">All</option>
        <?php foreach ($employees as $emp): ?>
          <option value="<?= $emp['id'] ?>" <?= ($filters['user_id']??0)==$emp['id']?'selected':'' ?>><?= e($emp['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="form-group"><label>Date</label><input type="date" name="date" class="form-control" value="<?= e($filters['date']??'') ?>"></div>
    <div class="form-group"><label>Month</label>
      <select name="month" class="form-control"><?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= ($filters['month']??date('m'))==$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?></select>
    </div>
    <div class="form-group"><label>Year</label><input type="number" name="year" class="form-control" value="<?= $filters['year']??date('Y') ?>" style="width:90px"></div>
    <div class="form-group" style="align-self:flex-end"><button class="btn btn-primary">Filter</button> <a href="<?= url('attendance') ?>" class="btn btn-outline">Reset</a></div>
  </form>
</div></div>

<!-- Table -->
<div class="card"><div class="card-body p-0">
  <table class="data-table">
    <thead><tr>
      <?php if ($isAdmin): ?><th>Employee</th><?php endif; ?>
      <th>Date</th><th>Check In</th><th>Check Out</th><th>Hours</th><th>Geo</th><th>Method</th>
    </tr></thead>
    <tbody>
    <?php if (empty($records)): ?>
      <tr><td colspan="7" class="text-center text-muted py-4">No records.</td></tr>
    <?php else: foreach ($records as $r): ?>
    <tr>
      <?php if ($isAdmin): ?><td><strong><?= e($r['emp_name']) ?></strong><small class="d-block text-muted"><?= e($r['emp_code']) ?></small></td><?php endif; ?>
      <td><?= formatDate($r['date']) ?></td>
      <td><?= $r['check_in']  ? date('h:i A', strtotime($r['check_in']))  : '–' ?></td>
      <td><?= $r['check_out'] ? date('h:i A', strtotime($r['check_out'])) : '–' ?></td>
      <td><?php if ($r['check_in'] && $r['check_out']) { $d=strtotime($r['check_out'])-strtotime($r['check_in']); echo floor($d/3600).'h '.floor(($d%3600)/60).'m'; } else echo '–'; ?></td>
      <td><?= $r['geo_valid'] ? '<span class="badge badge-success">✓</span>' : '<span class="badge badge-danger">!</span>' ?></td>
      <td><span class="badge badge-<?= $r['method']==='camera'?'info':'secondary' ?>"><?= e($r['method']) ?></span></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div></div>
