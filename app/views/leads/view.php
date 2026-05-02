<div class="page-header">
  <div>
    <h1 class="page-title"><?= e($lead['name']) ?></h1>
    <p class="page-subtitle"><a href="<?= url('leads') ?>">Leads</a> / <?= e($lead['name']) ?></p>
  </div>
  <div class="page-actions">
    <a href="<?= url('leads/'.$lead['id'].'/edit') ?>" class="btn btn-secondary"><i class="fa fa-pen"></i> Edit</a>
  </div>
</div>

<div class="detail-grid">
  <!-- Lead info -->
  <div class="card detail-card">
    <div class="card-header">
      <h3>Lead Information</h3>
      <?= leadStatusBadge($lead['status']) ?>
    </div>
    <div class="card-body">
      <dl class="detail-list">
        <dt>Phone</dt>       <dd><?= e($lead['phone']) ?></dd>
        <dt>Email</dt>       <dd><?= e($lead['email'] ?: '–') ?></dd>
        <dt>Source</dt>      <dd><?= ucwords(str_replace('_',' ',e($lead['source']))) ?></dd>
        <dt>Project</dt>     <dd><?= e($lead['project_name'] ?? '–') ?></dd>
        <dt>Budget</dt>      <dd>
          <?php if ($lead['budget_min'] || $lead['budget_max']): ?>
            <?= formatMoney((float)$lead['budget_min']) ?> – <?= formatMoney((float)$lead['budget_max']) ?>
          <?php else: echo '–'; endif; ?>
        </dd>
        <dt>Assigned To</dt> <dd><?= e($lead['assigned_name'] ?? '–') ?></dd>
        <dt>Next Follow-up</dt>
        <dd class="<?= $lead['next_followup_date'] && strtotime($lead['next_followup_date']) < time() ? 'text-danger' : '' ?>">
          <?= formatDate($lead['next_followup_date']) ?>
        </dd>
        <dt>Created</dt>     <dd><?= formatDate($lead['created_at'], 'd M Y, h:i A') ?></dd>
        <?php if ($lead['closed_value']): ?>
          <dt>Deal Value</dt><dd class="text-success"><?= formatMoney((float)$lead['closed_value']) ?></dd>
        <?php endif; ?>
      </dl>
      <?php if ($lead['notes']): ?>
        <div class="notes-box"><strong>Notes:</strong><p><?= nl2br(e($lead['notes'])) ?></p></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add followup -->
  <div class="card detail-card">
    <div class="card-header"><h3>Log Follow-up</h3></div>
    <div class="card-body">
      <form method="POST" action="<?= url('leads/'.$lead['id'].'/followup') ?>">
        <?= csrf_field() ?>
        <div class="form-grid cols-2">
          <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control">
              <?php foreach (['call','meeting','email','whatsapp','visit'] as $t): ?>
                <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Update Lead Status</label>
            <select name="lead_status" class="form-control">
              <?php foreach (['new','hot','warm','cold','closed','lost'] as $s): ?>
                <option value="<?= $s ?>" <?= $lead['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Notes / Outcome</label>
          <textarea name="notes" class="form-control" rows="3" placeholder="What was discussed?"></textarea>
        </div>
        <div class="form-group">
          <label>Next Follow-up Date</label>
          <input type="date" name="next_followup_date" class="form-control"
                 value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
        </div>
        <button type="submit" class="btn btn-primary btn-full"><i class="fa fa-check"></i> Save Follow-up</button>
      </form>
    </div>
  </div>
</div>

<!-- Followup history -->
<div class="card">
  <div class="card-header"><h3>Follow-up History (<?= count($followups) ?>)</h3></div>
  <div class="card-body p-0">
    <?php if (empty($followups)): ?>
      <div class="empty-state"><i class="fa fa-history"></i><p>No follow-ups recorded yet.</p></div>
    <?php else: ?>
      <ul class="timeline">
        <?php foreach ($followups as $f): ?>
          <li class="timeline-item">
            <div class="timeline-icon type-<?= e($f['type']) ?>">
              <i class="fa <?= $f['type']==='call'?'fa-phone':($f['type']==='email'?'fa-envelope':'fa-comment') ?>"></i>
            </div>
            <div class="timeline-body">
              <div class="timeline-header">
                <strong><?= ucfirst(e($f['type'])) ?></strong>
                <span class="timeline-date"><?= formatDate($f['followup_date'], 'd M Y') ?></span>
                <small>by <?= e($f['done_by']) ?></small>
              </div>
              <?php if ($f['notes']): ?>
                <p class="timeline-notes"><?= nl2br(e($f['notes'])) ?></p>
              <?php endif; ?>
              <?php if ($f['next_followup_date']): ?>
                <small class="text-muted">Next: <?= formatDate($f['next_followup_date']) ?></small>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>
