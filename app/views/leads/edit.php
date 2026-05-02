<div class="page-header">
  <div>
    <h1 class="page-title">Edit Lead</h1>
    <p class="page-subtitle"><a href="<?= url('leads') ?>">Leads</a> / <?= e($lead['name']) ?> / Edit</p>
  </div>
</div>

<div class="card form-card">
  <form method="POST" action="<?= url('leads/'.$lead['id'].'/update') ?>">
    <?= csrf_field() ?>

    <div class="form-section-title">Contact Information</div>
    <div class="form-grid">
      <div class="form-group">
        <label>Full Name <span class="req">*</span></label>
        <input type="text" name="name" class="form-control" value="<?= e($lead['name']) ?>" required>
      </div>
      <div class="form-group">
        <label>Phone <span class="req">*</span></label>
        <input type="tel" name="phone" class="form-control" value="<?= e($lead['phone']) ?>" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($lead['email']) ?>">
      </div>
      <div class="form-group">
        <label>Lead Source</label>
        <select name="source" class="form-control">
          <?php foreach (['website','referral','social_media','walk_in','call','direct','portal','exhibition'] as $src): ?>
            <option value="<?= $src ?>" <?= $lead['source']===$src?'selected':'' ?>><?= ucwords(str_replace('_',' ',$src)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-section-title">Lead Details</div>
    <div class="form-grid">
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <?php foreach (['new','hot','warm','cold','closed','lost'] as $s): ?>
            <option value="<?= $s ?>" <?= $lead['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Interested Project</label>
        <select name="interested_project_id" class="form-control">
          <option value="">– Select Project –</option>
          <?php foreach ($projects as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $lead['interested_project_id']==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Budget Min (₹)</label>
        <input type="number" name="budget_min" class="form-control" step="1000" value="<?= e($lead['budget_min']) ?>">
      </div>
      <div class="form-group">
        <label>Budget Max (₹)</label>
        <input type="number" name="budget_max" class="form-control" step="1000" value="<?= e($lead['budget_max']) ?>">
      </div>
      <?php if (\Core\Session::can(['admin','manager'])): ?>
      <div class="form-group">
        <label>Assign To</label>
        <select name="assigned_to" class="form-control">
          <option value="">– Unassigned –</option>
          <?php foreach ($employees as $emp): ?>
            <option value="<?= $emp['id'] ?>" <?= $lead['assigned_to']==$emp['id']?'selected':'' ?>><?= e($emp['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="form-group">
        <label>Next Follow-up</label>
        <input type="date" name="next_followup_date" class="form-control"
               value="<?= e(date('Y-m-d', strtotime($lead['next_followup_date'] ?: 'now'))) ?>">
      </div>
      <div class="form-group">
        <label>Closed Value (₹)</label>
        <input type="number" name="closed_value" class="form-control" step="1000" value="<?= e($lead['closed_value']) ?>">
      </div>
    </div>

    <div class="form-group">
      <label>Notes</label>
      <textarea name="notes" class="form-control" rows="3"><?= e($lead['notes']) ?></textarea>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Lead</button>
      <a href="<?= url('leads/'.$lead['id']) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>
