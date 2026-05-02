<div class="page-header">
  <div>
    <h1 class="page-title">Performance Report</h1>
    <p class="page-subtitle"><a href="<?= url('reports') ?>">Reports</a> / Performance</p>
  </div>
  <button onclick="window.print()" class="btn btn-secondary no-print"><i class="fa fa-print"></i> Print</button>
</div>

<div class="card filter-card no-print">
  <form method="GET" class="filter-form">
    <div class="form-group"><label>From</label><input type="date" name="from_date" class="form-control" value="<?= e($fromDate) ?>"></div>
    <div class="form-group"><label>To</label><input type="date" name="to_date" class="form-control" value="<?= e($toDate) ?>"></div>
    <button type="submit" class="btn btn-primary self-end"><i class="fa fa-filter"></i> Apply</button>
  </form>
</div>

<div class="card table-card">
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>Employee</th><th>Role</th><th>Total Leads</th><th>Closed</th><th>Conv %</th><th>Site Visits</th><th>Follow-ups</th><th>Revenue</th></tr>
      </thead>
      <tbody>
        <?php foreach ($perf as $p): ?>
        <tr>
          <td>
            <strong><?= e($p['name']) ?></strong>
            <div class="table-sub"><?= e($p['employee_id']) ?></div>
          </td>
          <td><?= e($p['role_name']) ?></td>
          <td><?= $p['total_leads'] ?></td>
          <td><?= $p['closed_leads'] ?></td>
          <td><?= $p['total_leads'] > 0 ? round(($p['closed_leads']/$p['total_leads'])*100,1) : 0 ?>%</td>
          <td><?= $p['site_visits'] ?></td>
          <td><?= $p['followups'] ?></td>
          <td class="fw-600"><?= formatMoney((float)($p['revenue'] ?? 0)) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
