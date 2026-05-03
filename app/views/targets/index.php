<?php /** @var array $targets @var int $month @var int $year */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Monthly Targets</h1>
    <p class="page-subtitle"><?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></p>
  </div>
  <div class="page-actions">
    <form class="d-flex gap-2" method="GET">
      <select name="month" class="form-control" onchange="this.form.submit()">
        <?php for($m=1;$m<=12;$m++): ?>
          <option value="<?=$m?>" <?=$m==$month?'selected':''?>><?=date('M',mktime(0,0,0,$m,1))?></option>
        <?php endfor; ?>
      </select>
      <input type="number" name="year" class="form-control" value="<?=$year?>" style="width:90px" onchange="this.form.submit()">
    </form>
    <?php if(\Core\Session::can(['admin','manager','super_admin'])): ?>
      <a href="<?=url('targets/create')?>" class="btn btn-primary"><i class="fa fa-plus"></i> Set Target</a>
    <?php endif; ?>
  </div>
</div>

<?php $s = \Core\Session::flash('success'); if($s): ?>
  <div class="flash flash-success"><?=e($s)?></div>
<?php endif; ?>

<div class="card">
  <div class="table-responsive">
    <table class="data-table">
      <thead><tr>
        <th>Employee</th><th>Leads Target</th><th>Visits Target</th>
        <th>Sales Target</th><th>Revenue Target</th><th>Lead Progress</th><th>Visit Progress</th>
      </tr></thead>
      <tbody>
        <?php if(empty($targets)): ?>
          <tr><td colspan="7" class="text-center py-4" style="color:var(--text-muted)">No targets set for this month.</td></tr>
        <?php else: foreach($targets as $t):
          $lPct = $t['target_leads'] ? min(100, round($t['actual_leads']/$t['target_leads']*100)) : 0;
          $vPct = $t['target_visits'] ? min(100, round($t['actual_visits']/$t['target_visits']*100)) : 0;
        ?>
          <tr>
            <td><strong><?=e($t['name'])?></strong><div class="table-sub"><?=e($t['employee_id'])?></div></td>
            <td><?=$t['target_leads']?></td>
            <td><?=$t['target_visits']?></td>
            <td><?=$t['target_sales']?></td>
            <td>₹<?=number_format($t['target_revenue'])?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;height:6px;background:#e5e7eb;border-radius:3px">
                  <div style="width:<?=$lPct?>%;height:100%;background:<?=$lPct>=100?'#10b981':'#3b82f6'?>;border-radius:3px"></div>
                </div>
                <span style="font-size:11px;width:36px"><?=$t['actual_leads']?>/<?=$t['target_leads']?></span>
              </div>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;height:6px;background:#e5e7eb;border-radius:3px">
                  <div style="width:<?=$vPct?>%;height:100%;background:<?=$vPct>=100?'#10b981':'#f59e0b'?>;border-radius:3px"></div>
                </div>
                <span style="font-size:11px;width:36px"><?=$t['actual_visits']?>/<?=$t['target_visits']?></span>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
