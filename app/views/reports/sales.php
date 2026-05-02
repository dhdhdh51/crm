<div class="page-header">
  <div>
    <h1 class="page-title">Sales Report</h1>
    <p class="page-subtitle"><a href="<?= url('reports') ?>">Reports</a> / Sales</p>
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

<div class="stat-card stat-gold" style="max-width:300px;margin-bottom:1.5rem">
  <div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
  <div class="stat-info">
    <div class="stat-value">₹<?= number_format($totalRevenue/100000,2) ?>L</div>
    <div class="stat-label">Total Revenue (<?= formatDate($fromDate) ?> – <?= formatDate($toDate) ?>)</div>
  </div>
</div>

<div class="card table-card">
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Lead</th><th>Project</th><th>Unit</th><th>Total Amount</th><th>Booking Amt</th><th>Payment</th><th>Handled By</th><th>Date</th></tr>
      </thead>
      <tbody>
        <?php if (empty($bookings)): ?>
          <tr><td colspan="9" class="text-center py-4">No bookings in this period.</td></tr>
        <?php else: ?>
          <?php foreach ($bookings as $i => $b): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= e($b['lead_name']) ?> <div class="table-sub"><?= e($b['lead_phone']) ?></div></td>
            <td><?= e($b['project_name']) ?></td>
            <td><?= e($b['unit_number']) ?></td>
            <td class="fw-600"><?= formatMoney((float)$b['total_amount']) ?></td>
            <td><?= formatMoney((float)$b['booking_amount']) ?></td>
            <td><span class="badge <?= $b['payment_status']==='full_paid'?'badge-success':'badge-warning' ?>"><?= ucwords(str_replace('_',' ',$b['payment_status'])) ?></span></td>
            <td><?= e($b['handled_by_name'] ?? '–') ?></td>
            <td><?= formatDate($b['booking_date']) ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
