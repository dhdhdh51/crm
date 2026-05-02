<div class="page-header">
  <div>
    <h1 class="page-title">Leads</h1>
    <p class="page-subtitle">Manage your sales pipeline</p>
  </div>
  <a href="<?= url('leads/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Add Lead</a>
</div>

<!-- Filters -->
<div class="card filter-card">
  <form method="GET" action="<?= url('leads') ?>" class="filter-form">
    <input type="text" name="search" placeholder="Search name, phone, email…"
           class="form-control" value="<?= e($filters['search']) ?>">

    <select name="status" class="form-control">
      <option value="">All Statuses</option>
      <?php foreach (['new','hot','warm','cold','closed','lost'] as $s): ?>
        <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>

    <select name="source" class="form-control">
      <option value="">All Sources</option>
      <?php foreach (['website','referral','social_media','walk_in','call','direct','portal','exhibition'] as $src): ?>
        <option value="<?= $src ?>" <?= $filters['source']===$src?'selected':'' ?>><?= ucwords(str_replace('_',' ',$src)) ?></option>
      <?php endforeach; ?>
    </select>

    <?php if (\Core\Session::can(['admin','manager'])): ?>
    <select name="assigned_to" class="form-control">
      <option value="">All Employees</option>
      <?php foreach ($employees as $emp): ?>
        <option value="<?= $emp['id'] ?>" <?= $filters['assigned_to']==$emp['id']?'selected':'' ?>><?= e($emp['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <select name="project_id" class="form-control">
      <option value="">All Projects</option>
      <?php foreach ($projects as $prj): ?>
        <option value="<?= $prj['id'] ?>" <?= $filters['project_id']==$prj['id']?'selected':'' ?>><?= e($prj['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <input type="date" name="from_date" class="form-control" value="<?= e($filters['from_date']) ?>">
    <input type="date" name="to_date"   class="form-control" value="<?= e($filters['to_date']) ?>">

    <button type="submit" class="btn btn-secondary"><i class="fa fa-filter"></i> Filter</button>
    <a href="<?= url('leads') ?>" class="btn btn-ghost">Reset</a>
  </form>
</div>

<!-- Table -->
<div class="card table-card">
  <div class="table-meta">
    <span><?= $result['total'] ?> leads found</span>
  </div>
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Phone</th>
          <th>Source</th>
          <th>Status</th>
          <th>Project</th>
          <th>Assigned To</th>
          <th>Follow-up</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($result['data'])): ?>
          <tr><td colspan="9" class="text-center py-4">No leads found.</td></tr>
        <?php else: ?>
          <?php foreach ($result['data'] as $i => $lead): ?>
          <tr>
            <td><?= (($result['currentPage']-1)*$result['perPage']) + $i + 1 ?></td>
            <td>
              <a href="<?= url('leads/' . $lead['id']) ?>" class="table-link"><?= e($lead['name']) ?></a>
              <?php if ($lead['email']): ?>
                <div class="table-sub"><?= e($lead['email']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= e($lead['phone']) ?></td>
            <td><span class="source-tag"><?= ucwords(str_replace('_',' ',e($lead['source']))) ?></span></td>
            <td><?= leadStatusBadge($lead['status']) ?></td>
            <td><?= e($lead['project_name'] ?? '–') ?></td>
            <td><?= e($lead['assigned_name'] ?? '–') ?></td>
            <td class="<?= $lead['next_followup_date'] && strtotime($lead['next_followup_date']) < time() ? 'text-danger' : '' ?>">
              <?= formatDate($lead['next_followup_date']) ?>
            </td>
            <td class="table-actions">
              <a href="<?= url('leads/'.$lead['id']) ?>" class="action-btn" title="View"><i class="fa fa-eye"></i></a>
              <a href="<?= url('leads/'.$lead['id'].'/edit') ?>" class="action-btn" title="Edit"><i class="fa fa-pen"></i></a>
              <?php if (\Core\Session::can(['admin','manager'])): ?>
              <form method="POST" action="<?= url('leads/'.$lead['id'].'/delete') ?>" class="inline-form" onsubmit="return confirm('Delete this lead?')">
                <?= csrf_field() ?>
                <button type="submit" class="action-btn action-btn-danger" title="Delete"><i class="fa fa-trash"></i></button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php
  $pagination = paginate($result['total'], $result['perPage'], $result['currentPage'], url('leads') . ($qStr ? '?' . ltrim($qStr, '&') : ''));
  echo $pagination['html'];
  ?>
</div>
