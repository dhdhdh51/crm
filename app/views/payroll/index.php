<div class="page-header">
  <div>
    <h1 class="page-title">Payroll</h1>
    <p class="page-subtitle">Process and manage salaries</p>
  </div>
  <a href="<?= url('payroll/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Process Salary</a>
</div>

<div class="card table-card">
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Employee</th><th>Month/Year</th><th>Base</th><th>Incentives</th><th>Deductions</th><th>Net Salary</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($salaries)): ?>
          <tr><td colspan="9" class="text-center py-4">No salary records found.</td></tr>
        <?php else: ?>
          <?php $months = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; ?>
          <?php foreach ($salaries as $i => $s): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td>
              <strong><?= e($s['name']) ?></strong>
              <div class="table-sub"><?= e($s['employee_id']) ?> · <?= e($s['role_name']) ?></div>
            </td>
            <td><?= $months[$s['month']] ?? $s['month'] ?> <?= $s['year'] ?></td>
            <td><?= formatMoney((float)$s['base_salary']) ?></td>
            <td><?= formatMoney((float)($s['incentives']+$s['bonus'])) ?></td>
            <td class="text-danger"><?= formatMoney((float)$s['deductions']) ?></td>
            <td class="text-success fw-600"><?= formatMoney((float)$s['net_salary']) ?></td>
            <td>
              <span class="badge <?= $s['payment_status']==='paid'?'badge-success':($s['payment_status']==='processing'?'badge-warning':'badge-secondary') ?>">
                <?= ucfirst($s['payment_status']) ?>
              </span>
            </td>
            <td class="table-actions">
              <a href="<?= url('payroll/'.$s['id'].'/slip') ?>" class="action-btn" title="View Slip" target="_blank"><i class="fa fa-file-invoice"></i></a>
              <form method="POST" action="<?= url('payroll/'.$s['id'].'/delete') ?>" class="inline-form" onsubmit="return confirm('Delete record?')">
                <?= csrf_field() ?>
                <button type="submit" class="action-btn action-btn-danger"><i class="fa fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
