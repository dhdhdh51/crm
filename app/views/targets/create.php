<?php /** @var array $employees */ ?>
<div class="page-header">
  <h1 class="page-title">Set Monthly Target</h1>
  <a href="<?=url('targets')?>" class="btn btn-ghost"><i class="fa fa-arrow-left"></i> Back</a>
</div>
<div class="card form-card">
  <form method="POST" action="<?=url('targets/store')?>">
    <?=csrf_field()?>
    <div class="form-grid">
      <div class="form-group span-2">
        <label>Employee <span class="req">*</span></label>
        <select name="user_id" class="form-control" required>
          <option value="">– Select –</option>
          <?php foreach($employees as $e): ?>
            <option value="<?=$e['id']?>"><?=e($e['name'])?> (<?=e($e['employee_id'])?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Month</label>
        <select name="month" class="form-control">
          <?php for($m=1;$m<=12;$m++): ?>
            <option value="<?=$m?>" <?=$m==date('n')?'selected':''?>><?=date('F',mktime(0,0,0,$m,1))?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Year</label>
        <input type="number" name="year" class="form-control" value="<?=date('Y')?>" min="2020" max="2099">
      </div>
      <div class="form-group">
        <label>Lead Target</label>
        <input type="number" name="target_leads" class="form-control" value="0" min="0">
      </div>
      <div class="form-group">
        <label>Visit Target</label>
        <input type="number" name="target_visits" class="form-control" value="0" min="0">
      </div>
      <div class="form-group">
        <label>Sales Target (closings)</label>
        <input type="number" name="target_sales" class="form-control" value="0" min="0">
      </div>
      <div class="form-group">
        <label>Revenue Target (₹)</label>
        <input type="number" name="target_revenue" class="form-control" value="0" min="0" step="1000">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Target</button>
      <a href="<?=url('targets')?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
