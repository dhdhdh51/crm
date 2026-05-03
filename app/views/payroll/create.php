<div class="page-header">
  <h1 class="page-title">Process Salary</h1>
</div>
<div class="card form-card">
  <form method="POST" action="<?= url('payroll/store') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="form-group span-2">
        <label>Employee <span class="req">*</span></label>
        <select name="user_id" class="form-control" required>
          <option value="">– Select Employee –</option>
          <?php foreach ($employees as $emp): ?>
            <option value="<?= $emp['id'] ?>"><?= e($emp['name']) ?> (<?= e($emp['employee_id']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Month</label>
        <select name="month" class="form-control">
          <?php for ($m=1;$m<=12;$m++): ?>
            <option value="<?= $m ?>" <?= date('n')==$m?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Year</label>
        <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" min="2020" max="2099">
      </div>
      <div class="form-group">
        <label>Base Salary (₹)</label>
        <input type="number" name="base_salary" class="form-control" step="100" min="0" required>
      </div>
      <div class="form-group">
        <label>Incentives (₹)</label>
        <input type="number" name="incentives" class="form-control" step="100" min="0" value="0">
      </div>
      <div class="form-group">
        <label>Bonus (₹)</label>
        <input type="number" name="bonus" class="form-control" step="100" min="0" value="0">
      </div>
      <div class="form-group">
        <label>Deductions (₹)</label>
        <input type="number" name="deductions" class="form-control" step="100" min="0" value="0">
      </div>
      <div class="form-group">
        <label>Payment Status</label>
        <select name="payment_status" class="form-control">
          <option value="pending">Pending</option>
          <option value="paid">Paid</option>
        </select>
      </div>
      <div class="form-group">
        <label>Payment Date</label>
        <input type="date" name="payment_date" class="form-control">
      </div>
      <div class="form-group span-2">
        <label>Notes / Remarks</label>
        <textarea name="notes" class="form-control" rows="2"></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Record</button>
      <a href="<?= url('payroll') ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
