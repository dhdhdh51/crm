<div class="page-header">
  <h1 class="page-title">Edit Site Visit</h1>
</div>
<div class="card form-card">
  <form method="POST" action="<?= url('site-visits/'.$visit['id'].'/update') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="form-group">
        <label>Lead</label>
        <select name="lead_id" class="form-control">
          <?php foreach ($leads as $l): ?>
            <option value="<?= $l['id'] ?>" <?= $visit['lead_id']==$l['id']?'selected':'' ?>><?= e($l['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Project</label>
        <select name="project_id" class="form-control">
          <?php foreach ($projects as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $visit['project_id']==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Visit Date & Time</label>
        <input type="datetime-local" name="visit_date" class="form-control"
               value="<?= date('Y-m-d\TH:i', strtotime($visit['visit_date'])) ?>">
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <?php foreach (['scheduled','completed','cancelled','no_show'] as $s): ?>
            <option value="<?= $s ?>" <?= $visit['status']===$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Assigned To</label>
        <select name="assigned_to" class="form-control">
          <option value="">– None –</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $visit['assigned_to']==$u['id']?'selected':'' ?>><?= e($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group span-2">
        <label>Feedback / Notes</label>
        <textarea name="feedback" class="form-control" rows="3"><?= e($visit['feedback'] ?? '') ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update</button>
      <a href="<?= url('site-visits') ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
