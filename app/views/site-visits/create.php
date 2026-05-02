<div class="page-header">
  <h1 class="page-title">Schedule Site Visit</h1>
</div>
<div class="card form-card">
  <form method="POST" action="<?= url('site-visits/store') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="form-group">
        <label>Lead <span class="req">*</span></label>
        <select name="lead_id" class="form-control" required>
          <option value="">– Select Lead –</option>
          <?php foreach ($leads as $l): ?>
            <option value="<?= $l['id'] ?>"><?= e($l['name']) ?> (<?= e($l['phone']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Project <span class="req">*</span></label>
        <select name="project_id" class="form-control" required>
          <option value="">– Select Project –</option>
          <?php foreach ($projects as $p): ?>
            <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Visit Date & Time <span class="req">*</span></label>
        <input type="datetime-local" name="visit_date" class="form-control"
               value="<?= date('Y-m-d\TH:i') ?>" required>
      </div>
      <div class="form-group">
        <label>Assign To</label>
        <select name="assigned_to" class="form-control">
          <option value="">– Select Employee –</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group span-2">
        <label>Notes</label>
        <textarea name="notes" class="form-control" rows="3"></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa fa-calendar-plus"></i> Schedule</button>
      <a href="<?= url('site-visits') ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
