<div class="page-header">
  <div>
    <h1 class="page-title">Lead Report</h1>
    <p class="page-subtitle"><a href="<?= url('reports') ?>">Reports</a> / Leads</p>
  </div>
  <button onclick="window.print()" class="btn btn-secondary"><i class="fa fa-print"></i> Print</button>
</div>

<!-- Date filter -->
<div class="card filter-card no-print">
  <form method="GET" class="filter-form">
    <div class="form-group">
      <label>From Date</label>
      <input type="date" name="from_date" class="form-control" value="<?= e($fromDate) ?>">
    </div>
    <div class="form-group">
      <label>To Date</label>
      <input type="date" name="to_date" class="form-control" value="<?= e($toDate) ?>">
    </div>
    <button type="submit" class="btn btn-primary self-end"><i class="fa fa-filter"></i> Apply</button>
  </form>
</div>

<!-- Summary cards -->
<div class="stats-grid" style="grid-template-columns:repeat(5,1fr)">
  <div class="stat-card stat-blue"><div class="stat-icon"><i class="fa fa-users"></i></div><div class="stat-info"><div class="stat-value"><?= $summary['total'] ?? 0 ?></div><div class="stat-label">Total</div></div></div>
  <div class="stat-card stat-red"><div class="stat-icon"><i class="fa fa-fire"></i></div><div class="stat-info"><div class="stat-value"><?= $summary['hot'] ?? 0 ?></div><div class="stat-label">Hot</div></div></div>
  <div class="stat-card stat-orange"><div class="stat-icon"><i class="fa fa-thermometer-half"></i></div><div class="stat-info"><div class="stat-value"><?= $summary['warm'] ?? 0 ?></div><div class="stat-label">Warm</div></div></div>
  <div class="stat-card stat-green"><div class="stat-icon"><i class="fa fa-check"></i></div><div class="stat-info"><div class="stat-value"><?= $summary['closed'] ?? 0 ?></div><div class="stat-label">Closed</div></div></div>
  <div class="stat-card stat-secondary"><div class="stat-icon"><i class="fa fa-times"></i></div><div class="stat-info"><div class="stat-value"><?= $summary['lost'] ?? 0 ?></div><div class="stat-label">Lost</div></div></div>
</div>

<div class="card table-card mt-3">
  <div class="card-header"><h3>Lead Details (<?= count($data) ?>)</h3></div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Name</th><th>Phone</th><th>Source</th><th>Status</th><th>Project</th><th>Assigned To</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php foreach ($data as $i => $l): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= e($l['name']) ?></td>
          <td><?= e($l['phone']) ?></td>
          <td><?= ucwords(str_replace('_',' ',e($l['source']))) ?></td>
          <td><?= leadStatusBadge($l['status']) ?></td>
          <td><?= e($l['project_name'] ?? '–') ?></td>
          <td><?= e($l['assigned_name'] ?? '–') ?></td>
          <td><?= formatDate($l['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
