<div class="page-header">
  <div>
    <h1 class="page-title">Notifications</h1>
  </div>
  <form method="POST" action="<?= url('notifications/read-all') ?>" class="no-print">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-ghost"><i class="fa fa-check-double"></i> Mark All Read</button>
  </form>
</div>

<div class="card">
  <?php if (empty($notifs)): ?>
    <div class="empty-state py-5">
      <i class="fa fa-bell-slash fa-2x"></i>
      <p>No notifications yet.</p>
    </div>
  <?php else: ?>
    <ul class="notif-list">
      <?php foreach ($notifs as $n): ?>
      <li class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
        <div class="notif-icon type-<?= e($n['type']) ?>">
          <i class="fa <?= $n['type']==='lead'?'fa-user-plus':'fa-bell' ?>"></i>
        </div>
        <div class="notif-body">
          <?php if ($n['link']): ?>
            <a href="<?= e($n['link']) ?>" class="notif-msg"><?= e($n['message']) ?></a>
          <?php else: ?>
            <span class="notif-msg"><?= e($n['message']) ?></span>
          <?php endif; ?>
          <span class="notif-time"><?= formatDate($n['created_at'], 'd M Y, h:i A') ?></span>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
