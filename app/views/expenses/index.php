<?php /** @var array $expenses @var array $byCategory @var float $monthTotal @var array $categories @var array $filters */ ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Expenses</h1>
    <p class="page-subtitle">This month: <strong>₹<?=number_format($monthTotal,2)?></strong></p>
  </div>
</div>

<?php $s=\Core\Session::flash('success'); if($s): ?>
  <div class="flash flash-success"><?=e($s)?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.25rem;align-items:start">

  <div>
    <!-- Filter -->
    <div class="card filter-card mb-4">
      <form class="filter-form" method="GET">
        <select name="category" class="form-control">
          <option value="">All Categories</option>
          <?php foreach($categories as $c): ?>
            <option value="<?=$c?>" <?=$filters['category']==$c?'selected':''?>><?=$c?></option>
          <?php endforeach; ?>
        </select>
        <input type="date" name="from" class="form-control" value="<?=e($filters['from'])?>">
        <input type="date" name="to"   class="form-control" value="<?=e($filters['to'])?>">
        <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i> Filter</button>
        <a href="<?=url('expenses')?>" class="btn btn-ghost">Clear</a>
      </form>
    </div>

    <!-- Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="data-table">
          <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Added By</th><th></th></tr></thead>
          <tbody>
            <?php if(empty($expenses)): ?>
              <tr><td colspan="6" class="text-center py-4" style="color:var(--text-muted)">No expenses found.</td></tr>
            <?php else: foreach($expenses as $e): ?>
              <tr>
                <td><?=formatDate($e['expense_date'])?></td>
                <td><span class="badge badge-secondary"><?=e($e['category'])?></span></td>
                <td><?=e($e['description'] ?: '–')?></td>
                <td><strong>₹<?=number_format($e['amount'],2)?></strong></td>
                <td><?=e($e['added_by_name'] ?? '–')?></td>
                <td>
                  <?php if(\Core\Session::can(['admin','super_admin'])): ?>
                    <form method="POST" action="<?=url('expenses/'.$e['id'].'/delete')?>" class="inline-form" onsubmit="return confirm('Delete?')">
                      <?=csrf_field()?>
                      <button class="action-btn action-btn-danger"><i class="fa fa-trash"></i></button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add + Summary -->
  <div>
    <?php if(\Core\Session::can(['admin','super_admin','manager'])): ?>
    <div class="card form-card mb-4">
      <h3 style="font-size:15px;font-weight:700;color:var(--maroon);margin-bottom:1rem">Add Expense</h3>
      <form method="POST" action="<?=url('expenses/store')?>" enctype="multipart/form-data">
        <?=csrf_field()?>
        <div class="form-group mb-3">
          <label>Category</label>
          <select name="category" class="form-control" required>
            <?php foreach($categories as $c): ?><option><?=$c?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group mb-3">
          <label>Amount (₹)</label>
          <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
        </div>
        <div class="form-group mb-3">
          <label>Date</label>
          <input type="date" name="expense_date" class="form-control" value="<?=date('Y-m-d')?>">
        </div>
        <div class="form-group mb-3">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
        <div class="form-group mb-3">
          <label>Receipt (optional)</label>
          <input type="file" name="receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
        </div>
        <button type="submit" class="btn btn-primary btn-full"><i class="fa fa-plus"></i> Add Expense</button>
      </form>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><h3>By Category</h3></div>
      <div class="card-body p-0">
        <?php foreach($byCategory as $bc): ?>
          <div style="display:flex;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--border);font-size:13px">
            <span><?=e($bc['category'])?></span>
            <strong>₹<?=number_format($bc['total'],2)?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>
