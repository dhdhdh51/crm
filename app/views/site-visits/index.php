<div class="page-header">
  <div>
    <h1 class="page-title">Site Visits</h1>
    <p class="page-subtitle">Track & manage property visits</p>
  </div>
  <a href="<?= url('site-visits/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Schedule Visit</a>
</div>

<div class="card table-card">
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Lead</th><th>Project</th><th>Visit Date</th><th>Assigned To</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($visits)): ?>
          <tr><td colspan="7" class="text-center py-4">No visits found.</td></tr>
        <?php else: ?>
          <?php foreach ($visits as $i => $v): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td>
              <a href="<?= url('leads/'.$v['lead_id']) ?>" class="table-link"><?= e($v['lead_name']) ?></a>
              <div class="table-sub"><?= e($v['lead_phone']) ?></div>
            </td>
            <td><?= e($v['project_name']) ?></td>
            <td><?= formatDate($v['visit_date'], 'd M Y, h:i A') ?></td>
            <td><?= e($v['assigned_name'] ?? '–') ?></td>
            <td><?= visitStatusBadge($v['status']) ?></td>
            <td class="table-actions">
              <a href="<?= url('site-visits/'.$v['id'].'/edit') ?>" class="action-btn" title="Edit"><i class="fa fa-pen"></i></a>
              <?php if (\Core\Session::can(['admin','manager'])): ?>
              <form method="POST" action="<?= url('site-visits/'.$v['id'].'/delete') ?>" class="inline-form" onsubmit="return confirm('Delete this visit?')">
                <?= csrf_field() ?>
                <button type="submit" class="action-btn action-btn-danger"><i class="fa fa-trash"></i></button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
