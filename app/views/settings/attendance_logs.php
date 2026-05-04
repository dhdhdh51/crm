<?php /** @var array $records @var array $employees @var array $filters */ ?>
<div class="page-header">
  <h2 class="page-title">Attendance Logs</h2>
  <a href="<?= url('settings/geo') ?>" class="btn btn-outline"><i class="fa fa-location-dot"></i> Geo-fence Settings</a>
</div>

<div class="card mb-4"><div class="card-body">
  <form method="GET" class="form-row">
    <div class="form-group"><label>Employee</label>
      <select name="user_id" class="form-control">
        <option value="">All</option>
        <?php foreach ($employees as $e): ?>
        <option value="<?= $e['id'] ?>" <?= ($filters['user_id']??0)==$e['id']?'selected':'' ?>><?= e($e['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label>Month</label>
      <select name="month" class="form-control"><?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= ($filters['month']??date('m'))==$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?></select>
    </div>
    <div class="form-group"><label>Year</label><input type="number" name="year" class="form-control" value="<?= $filters['year']??date('Y') ?>" style="width:90px"></div>
    <div class="form-group" style="align-self:flex-end"><button class="btn btn-primary">Filter</button></div>
  </form>
</div></div>

<div class="card"><div class="card-body p-0">
  <table class="data-table">
    <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Geo</th><th>Photo</th><th>Method</th></tr></thead>
    <tbody>
    <?php if (empty($records)): ?>
      <tr><td colspan="7" class="text-center text-muted py-4">No records.</td></tr>
    <?php else: foreach ($records as $r): ?>
    <tr>
      <td><strong><?= e($r['emp_name']) ?></strong><small class="d-block text-muted"><?= e($r['emp_code']) ?></small></td>
      <td><?= formatDate($r['date']) ?></td>
      <td><?= $r['check_in']  ? date('h:i A', strtotime($r['check_in']))  : '–' ?></td>
      <td><?= $r['check_out'] ? date('h:i A', strtotime($r['check_out'])) : '–' ?></td>
      <td><?= $r['geo_valid'] ? '<span class="badge badge-success">✓ Valid</span>' : '<span class="badge badge-danger">⚠ Outside</span>' ?></td>
      <td><?php if ($r['image']): ?><a href="<?= url('storage/uploads/'.$r['image']) ?>" target="_blank"><img src="<?= url('storage/uploads/'.$r['image']) ?>" style="height:36px;border-radius:4px"></a><?php else: echo '–'; endif; ?></td>
      <td><span class="badge badge-<?= $r['method']==='camera'?'info':($r['method']==='photo'?'warning':'secondary') ?>"><?= e($r['method']) ?></span></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div></div>
