<div class="page-header">
  <div>
    <h1 class="page-title">Projects</h1>
    <p class="page-subtitle">Manage inventory and availability</p>
  </div>
  <?php if (\Core\Session::can(['admin','manager'])): ?>
  <a href="<?= url('projects/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Add Project</a>
  <?php endif; ?>
</div>

<div class="projects-grid">
  <?php if (empty($projects)): ?>
    <div class="empty-state card"><i class="fa fa-building"></i><p>No projects yet.</p></div>
  <?php else: ?>
  <?php foreach ($projects as $prj): ?>
  <div class="project-card card">
    <?php if ($prj['image']): ?>
      <div class="project-img" style="background-image:url('<?= url($prj['image']) ?>')"></div>
    <?php else: ?>
      <div class="project-img project-img-placeholder"><i class="fa fa-building"></i></div>
    <?php endif; ?>
    <div class="project-body">
      <div class="project-status-row">
        <span class="project-status status-<?= e($prj['status']) ?>"><?= ucfirst(e($prj['status'])) ?></span>
      </div>
      <h3 class="project-name"><?= e($prj['name']) ?></h3>
      <p class="project-location"><i class="fa fa-location-dot"></i> <?= e($prj['location']) ?></p>
      <div class="project-stats">
        <div class="pstat"><span><?= $prj['available'] ?? 0 ?></span><small>Available</small></div>
        <div class="pstat"><span><?= $prj['booked'] ?? 0 ?></span><small>Booked</small></div>
        <div class="pstat"><span><?= $prj['sold'] ?? 0 ?></span><small>Sold</small></div>
        <div class="pstat"><span>₹<?= number_format((float)$prj['price_per_sqft']) ?></span><small>/sqft</small></div>
      </div>
    </div>
    <div class="project-footer">
      <a href="<?= url('projects/'.$prj['id']) ?>" class="btn btn-sm btn-secondary">View</a>
      <?php if (\Core\Session::can(['admin','manager'])): ?>
        <a href="<?= url('projects/'.$prj['id'].'/units') ?>" class="btn btn-sm btn-ghost">Units</a>
        <a href="<?= url('projects/'.$prj['id'].'/edit') ?>" class="btn btn-sm btn-ghost">Edit</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
