<?php /** @var array $summary @var int $month @var int $year */ ?>

<div class="page-header">
  <h2 class="page-title">Attendance Report</h2>
  <a href="<?= url('attendance') ?>" class="btn btn-outline"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<!-- Month/Year selector -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" class="form-row">
      <div class="form-group">
        <label>Month</label>
        <select name="month" class="form-control">
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $month == $m ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Year</label>
        <input type="number" name="year" class="form-control" value="<?= $year ?>" min="2020" max="2099">
      </div>
      <div class="form-group" style="align-self:flex-end">
        <button type="submit" class="btn btn-primary">View Report</button>
      </div>
    </form>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><?= date('F Y', mktime(0,0,0,$month,1,$year)) ?> — Team Attendance Summary</span>
    <button class="btn btn-sm btn-outline" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
  </div>
  <div class="card-body p-0">
    <table class="data-table">
      <thead>
        <tr>
          <th>Employee</th>
          <th>Emp ID</th>
          <th class="text-center">Total Days</th>
          <th class="text-center text-success">Present</th>
          <th class="text-center text-danger">Absent</th>
          <th class="text-center text-warning">Late</th>
          <th class="text-center">Half Day</th>
          <th class="text-center">Attendance %</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($summary)): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">No data for this period.</td></tr>
        <?php else: ?>
          <?php foreach ($summary as $row): ?>
          <?php
            $total   = max(1, (int)$row['total_days']);
            $present = (int)($row['present'] ?? 0);
            $absent  = (int)($row['absent'] ?? 0);
            $late    = (int)($row['late'] ?? 0);
            $half    = (int)($row['half_day'] ?? 0);
            $pct     = $total > 0 ? round(($present + $late + $half * 0.5) / $total * 100) : 0;
          ?>
          <tr>
            <td><strong><?= e($row['name']) ?></strong></td>
            <td><code><?= e($row['emp_code']) ?></code></td>
            <td class="text-center"><?= $total ?></td>
            <td class="text-center"><span class="badge badge-success"><?= $present ?></span></td>
            <td class="text-center"><span class="badge badge-danger"><?= $absent ?></span></td>
            <td class="text-center"><span class="badge badge-warning"><?= $late ?></span></td>
            <td class="text-center"><span class="badge badge-secondary"><?= $half ?></span></td>
            <td class="text-center">
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;background:#e9ecef;border-radius:4px;height:8px">
                  <div style="width:<?= $pct ?>%;background:<?= $pct >= 80 ? '#198754' : ($pct >= 60 ? '#fd7e14' : '#dc3545') ?>;height:8px;border-radius:4px"></div>
                </div>
                <span style="min-width:35px;font-size:.85rem"><?= $pct ?>%</span>
              </div>
            </td>
            <td>
              <a href="<?= url("attendance?user_id={$row['id']}&month={$month}&year={$year}") ?>" class="btn btn-sm btn-outline">
                Details
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Summary cards -->
<?php
$totPresent = array_sum(array_column($summary, 'present'));
$totAbsent  = array_sum(array_column($summary, 'absent'));
$totLate    = array_sum(array_column($summary, 'late'));
$totHalf    = array_sum(array_column($summary, 'half_day'));
?>
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
  <div class="stat-card">
    <div class="stat-value text-success"><?= $totPresent ?></div>
    <div class="stat-label">Total Present</div>
  </div>
  <div class="stat-card">
    <div class="stat-value text-danger"><?= $totAbsent ?></div>
    <div class="stat-label">Total Absent</div>
  </div>
  <div class="stat-card">
    <div class="stat-value text-warning"><?= $totLate ?></div>
    <div class="stat-label">Late Arrivals</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= $totHalf ?></div>
    <div class="stat-label">Half Days</div>
  </div>
</div>
