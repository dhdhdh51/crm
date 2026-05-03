<?php /** @var array $slips */ ?>
<div class="page-header">
  <h1 class="page-title">My Salary Slips</h1>
</div>
<div class="card table-card">
  <div class="table-responsive">
    <table class="data-table">
      <thead><tr><th>Month</th><th>Year</th><th>Base</th><th>Incentives</th><th>Bonus</th><th>Deductions</th><th>Net</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if(empty($slips)): ?>
          <tr><td colspan="9" class="text-center py-4" style="color:var(--text-muted)">No salary records yet.</td></tr>
        <?php else: foreach($slips as $s): ?>
          <tr>
            <td><?=date('F', mktime(0,0,0,$s['month'],1))?></td>
            <td><?=$s['year']?></td>
            <td>₹<?=number_format($s['base_salary'],2)?></td>
            <td>₹<?=number_format($s['incentives'],2)?></td>
            <td>₹<?=number_format($s['bonus'],2)?></td>
            <td>₹<?=number_format($s['deductions'],2)?></td>
            <td><strong>₹<?=number_format($s['net_salary'],2)?></strong></td>
            <td><span class="badge <?=$s['payment_status']=='paid'?'badge-success':'badge-warning'?>"><?=ucfirst($s['payment_status'])?></span></td>
            <td><a href="<?=url('payroll/'.$s['id'].'/slip')?>" class="btn btn-sm btn-ghost" target="_blank"><i class="fa fa-file-pdf"></i> Slip</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
