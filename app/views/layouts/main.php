<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Dashboard') ?> — VastuVeda CRM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="app-body">

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">VV</div>
    <div class="brand-text">
      <span class="brand-name">VastuVeda</span>
      <span class="brand-sub">Realty CRM</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <a href="<?= url('dashboard') ?>" class="nav-item <?= isActive('dashboard') ?>">
      <i class="fa fa-gauge-high"></i><span>Dashboard</span>
    </a>

    <div class="nav-section-label">SALES</div>
    <a href="<?= url('leads') ?>" class="nav-item <?= isActive('leads') ?>">
      <i class="fa fa-users"></i><span>Leads</span>
    </a>
    <?php if (\Core\Session::can(['admin','manager','super_admin','hr'])): ?>
    <a href="<?= url('leads/upload') ?>" class="nav-item <?= isActive('leads/upload') ?>">
      <i class="fa fa-file-csv"></i><span>Import Leads</span>
    </a>
    <?php endif; ?>
    <a href="<?= url('site-visits') ?>" class="nav-item <?= isActive('site-visits') ?>">
      <i class="fa fa-calendar-check"></i><span>Site Visits</span>
    </a>
    <a href="<?= url('targets') ?>" class="nav-item <?= isActive('targets') ?>">
      <i class="fa fa-bullseye"></i><span>Targets</span>
    </a>

    <div class="nav-section-label">INVENTORY</div>
    <a href="<?= url('projects') ?>" class="nav-item <?= isActive('projects') ?>">
      <i class="fa fa-building"></i><span>Projects</span>
    </a>

    <div class="nav-section-label">HR</div>
    <a href="<?= url('my-slips') ?>" class="nav-item <?= isActive('my-slips') ?>">
      <i class="fa fa-file-invoice-dollar"></i><span>My Slips</span>
    </a>

    <?php if (\Core\Session::can(['admin','manager','super_admin','hr'])): ?>
    <div class="nav-section-label">MANAGEMENT</div>
    <a href="<?= url('employees') ?>" class="nav-item <?= isActive('employees') ?>">
      <i class="fa fa-id-badge"></i><span>Employees</span>
    </a>
    <a href="<?= url('reports') ?>" class="nav-item <?= isActive('reports') ?>">
      <i class="fa fa-chart-bar"></i><span>Reports</span>
    </a>
    <a href="<?= url('expenses') ?>" class="nav-item <?= isActive('expenses') ?>">
      <i class="fa fa-receipt"></i><span>Expenses</span>
    </a>
    <?php endif; ?>

    <?php if (\Core\Session::can(['admin','super_admin'])): ?>
    <a href="<?= url('payroll') ?>" class="nav-item <?= isActive('payroll') ?>">
      <i class="fa fa-money-bill-wave"></i><span>Payroll</span>
    </a>
    <?php endif; ?>

    <div class="nav-section-label">ACCOUNT</div>
    <a href="<?= url('notifications') ?>" class="nav-item <?= isActive('notifications') ?>">
      <i class="fa fa-bell"></i><span>Notifications</span>
      <?php
      $__unread = (new \App\Models\Notification())->unreadCount(\Core\Session::user()['id']);
      if ($__unread > 0): ?>
        <span class="badge-bubble"><?= $__unread ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= url('profile') ?>" class="nav-item <?= isActive('profile') ?>">
      <i class="fa fa-user-circle"></i><span>Profile</span>
    </a>
    <a href="<?= url('logout') ?>" class="nav-item nav-logout">
      <i class="fa fa-right-from-bracket"></i><span>Logout</span>
    </a>
  </nav>
</aside>

<!-- Main wrapper -->
<div class="main-wrapper" id="mainWrapper">

  <!-- Topbar -->
  <header class="topbar">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
      <i class="fa fa-bars"></i>
    </button>

    <div class="topbar-title"><?= e($title ?? 'Dashboard') ?></div>

    <div class="topbar-actions">
      <!-- Notification bell -->
      <div class="notif-wrapper">
        <button class="icon-btn" id="notifBell" aria-label="Notifications">
          <i class="fa fa-bell"></i>
          <?php if (($unreadCount ?? 0) > 0): ?>
            <span class="notif-dot" id="notifDot"><?= $unreadCount ?></span>
          <?php endif; ?>
        </button>
      </div>

      <!-- User menu -->
      <div class="user-menu-wrapper">
        <button class="user-chip" id="userMenuBtn">
          <div class="user-avatar"><?= strtoupper(substr(\Core\Session::user()['name'] ?? 'U', 0, 1)) ?></div>
          <span class="user-name-short"><?= e(explode(' ', \Core\Session::user()['name'] ?? '')[0]) ?></span>
          <i class="fa fa-chevron-down fa-xs"></i>
        </button>
        <div class="user-dropdown" id="userDropdown">
          <div class="dropdown-header">
            <strong><?= e(\Core\Session::user()['name'] ?? '') ?></strong>
            <small><?= e(\Core\Session::user()['employee_id'] ?? '') ?></small>
            <span class="role-tag"><?= e(ucfirst(\Core\Session::role())) ?></span>
          </div>
          <a href="<?= url('profile') ?>"><i class="fa fa-user fa-fw"></i> Profile</a>
          <a href="<?= url('logout') ?>" class="logout-link"><i class="fa fa-sign-out-alt fa-fw"></i> Logout</a>
        </div>
      </div>
    </div>
  </header>

  <!-- Flash messages -->
  <?php foreach (['success','error','warning','info'] as $__ft): ?>
    <?php $__msg = \Core\Session::flash($__ft); ?>
    <?php if ($__msg): ?>
      <div class="flash flash-<?= $__ft ?>" id="flash-<?= $__ft ?>">
        <i class="fa <?= $__ft==='success'?'fa-circle-check':($__ft==='error'?'fa-circle-xmark':'fa-circle-info') ?>"></i>
        <?= e($__msg) ?>
        <button onclick="this.parentElement.remove()" class="flash-close">×</button>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <!-- Page content -->
  <main class="page-content">
    <?= $content ?>
  </main>

</div><!-- /main-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
