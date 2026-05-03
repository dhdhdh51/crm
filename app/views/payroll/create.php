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
        <div style="display:flex;gap:.5rem;align-items:center">
          <input type="number" id="deductions" name="deductions" class="form-control" step="0.01" min="0" value="0">
          <button type="button" class="btn btn-sm btn-secondary" onclick="autoCalc()" title="Auto-calculate from attendance"><i class="fa fa-calculator"></i></button>
        </div>
        <label style="margin-top:6px;display:flex;align-items:center;gap:6px;font-size:12px">
          <input type="checkbox" name="auto_calc" value="1"> Use attendance-based deduction on save
        </label>
        <div id="calc-result" style="font-size:11.5px;color:var(--text-muted);margin-top:4px"></div>
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
<script>
function autoCalc() {
  const uid  = document.querySelector('[name=user_id]').value;
  const mon  = document.querySelector('[name=month]').value;
  const yr   = document.querySelector('[name=year]').value;
  const base = document.querySelector('[name=base_salary]').value;
  if (!uid || !base) { alert('Select employee and enter base salary first.'); return; }
  fetch(`<?=url('payroll/calc-attendance')?>?user_id=${uid}&month=${mon}&year=${yr}&base=${base}`)
    .then(r=>r.json()).then(d=>{
      document.getElementById('deductions').value = d.deductions;
      document.getElementById('calc-result').innerHTML =
        `Present: ${d.present} | Half-day: ${d.half} | Absent: ${d.absent} | Earned: ₹${d.earned}`;
    });
}
</script>
