<?php /** @var array $logs @var array $employees */ ?>
<div class="page-header">
  <h1 class="page-title">Import Leads (CSV)</h1>
  <a href="<?=url('leads')?>" class="btn btn-ghost"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<?php $s=\Core\Session::flash('success'); $e=\Core\Session::flash('error'); ?>
<?php if($s): ?><div class="flash flash-success"><?=e($s)?></div><?php endif; ?>
<?php if($e): ?><div class="flash flash-error"><?=e($e)?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;align-items:start">

  <div class="card form-card">
    <h3 style="font-size:15px;font-weight:700;color:var(--maroon);margin-bottom:1rem">Upload CSV File</h3>
    <div class="alert alert-info" style="font-size:12.5px;margin-bottom:1rem">
      <i class="fa fa-circle-info"></i>
      CSV columns (in order): <strong>Name, Email, Phone, City, Project</strong><br>
      First row = header (skipped). Phone and Name are required.
    </div>
    <form method="POST" action="<?=url('leads/import')?>" enctype="multipart/form-data">
      <?=csrf_field()?>
      <div class="form-group mb-3">
        <label>CSV File <span class="req">*</span></label>
        <input type="file" name="csv" class="form-control" accept=".csv,.txt" required>
      </div>
      <div class="form-group mb-3">
        <label>Assign Leads To</label>
        <select name="assign_to" class="form-control">
          <option value="0">– Unassigned –</option>
          <?php foreach($employees as $emp): ?>
            <option value="<?=$emp['id']?>"><?=e($emp['name'])?> (<?=e($emp['employee_id'])?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary btn-full"><i class="fa fa-upload"></i> Import Leads</button>
    </form>

    <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border)">
      <p style="font-size:12px;color:var(--text-muted);margin-bottom:.5rem">Download template:</p>
      <a href="data:text/csv;charset=utf-8,Name,Email,Phone,City,Project%0AJohn%20Doe,john@example.com,9876543210,Mumbai,Skyline%20Tower"
         download="leads_template.csv" class="btn btn-ghost btn-sm"><i class="fa fa-download"></i> CSV Template</a>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Import History</h3></div>
    <div class="table-responsive">
      <table class="data-table">
        <thead><tr><th>Date</th><th>File</th><th>Imported</th><th>Failed</th><th>By</th></tr></thead>
        <tbody>
          <?php if(empty($logs)): ?>
            <tr><td colspan="5" class="text-center py-4" style="color:var(--text-muted)">No imports yet.</td></tr>
          <?php else: foreach($logs as $l): ?>
            <tr>
              <td><?=formatDate($l['created_at'],'d M Y H:i')?></td>
              <td><?=e($l['filename'])?></td>
              <td><span class="badge badge-success"><?=$l['imported']?></span></td>
              <?php $cls = $l['failed']>0 ? 'badge-danger' : 'badge-secondary'; ?>
              <td><span class="badge <?=$cls?>"><?=$l['failed']?></span></td>
              <td><?=e($l['uploader']??'–')?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
