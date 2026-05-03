<div class="page-header">
  <div>
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Welcome back, <?= e(\Core\Session::user()['name'] ?? '') ?></p>
  </div>
  <div class="page-actions">
    <?php if (\Core\Session::can(['admin','super_admin','hr'])): ?>
    <a href="<?= url('leads/create') ?>" class="btn btn-primary"><i class="fa fa-plus"></i> New Lead</a>
    <?php endif; ?>
  </div>
</div>

<!-- Stat cards -->
<div class="stats-grid">
  <div class="stat-card stat-blue">
    <div class="stat-icon"><i class="fa fa-users"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $leadStats['total'] ?? 0 ?></div>
      <div class="stat-label">Total Leads</div>
      <div class="stat-sub">+<?= $leadStats['today'] ?? 0 ?> today</div>
    </div>
  </div>
  <div class="stat-card stat-red">
    <div class="stat-icon"><i class="fa fa-fire"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $leadStats['hot_count'] ?? 0 ?></div>
      <div class="stat-label">Hot Leads</div>
      <div class="stat-sub"><?= $leadStats['warm_count'] ?? 0 ?> warm</div>
    </div>
  </div>
  <div class="stat-card stat-green">
    <div class="stat-icon"><i class="fa fa-handshake"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $leadStats['closed_count'] ?? 0 ?></div>
      <div class="stat-label">Closed Deals</div>
      <div class="stat-sub"><?= $convRate ?>% conversion</div>
    </div>
  </div>
  <div class="stat-card stat-purple">
    <div class="stat-icon"><i class="fa fa-calendar-check"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $visitCount ?></div>
      <div class="stat-label">Site Visits</div>
      <div class="stat-sub">This month</div>
    </div>
  </div>
  <div class="stat-card stat-gold">
    <div class="stat-icon"><i class="fa fa-indian-rupee-sign"></i></div>
    <div class="stat-info">
      <div class="stat-value">₹<?= number_format($revenue / 100000, 1) ?>L</div>
      <div class="stat-label">Monthly Revenue</div>
      <div class="stat-sub">₹<?= number_format($totalRevenue / 100000, 1) ?>L total</div>
    </div>
  </div>
  <div class="stat-card stat-orange">
    <div class="stat-icon"><i class="fa fa-clock"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= count($pendingFups) ?></div>
      <div class="stat-label">Pending Follow-ups</div>
      <div class="stat-sub">Due today or overdue</div>
    </div>
  </div>
</div>

<!-- Charts row -->
<div class="charts-grid">
  <div class="card chart-card chart-wide">
    <div class="card-header"><h3>Lead Trend (6 Months)</h3></div>
    <div class="card-body">
      <canvas id="chartTrend" height="90"></canvas>
    </div>
  </div>
  <div class="card chart-card">
    <div class="card-header"><h3>Lead Status</h3></div>
    <div class="card-body chart-donut-wrap">
      <canvas id="chartDonut"></canvas>
    </div>
  </div>
</div>

<div class="charts-grid" style="margin-top:0">
  <div class="card chart-card chart-wide">
    <div class="card-header"><h3>Lead Sources</h3></div>
    <div class="card-body">
      <canvas id="chartSource" height="90"></canvas>
    </div>
  </div>

  <!-- Pending followups panel -->
  <div class="card">
    <div class="card-header">
      <h3>Pending Follow-ups</h3>
      <a href="<?= url('leads?status=') ?>" class="btn-link">View All</a>
    </div>
    <div class="card-body p-0">
      <?php if (empty($pendingFups)): ?>
        <div class="empty-state py-4"><i class="fa fa-check-circle"></i><p>All caught up!</p></div>
      <?php else: ?>
        <ul class="followup-list">
          <?php foreach ($pendingFups as $f): ?>
            <li class="followup-item">
              <a href="<?= url('leads/' . $f['id']) ?>" class="followup-name"><?= e($f['name']) ?></a>
              <div class="followup-meta">
                <span><?= e($f['phone']) ?></span>
                <span class="followup-date <?= strtotime($f['next_followup_date']) < time() ? 'overdue' : '' ?>">
                  <?= formatDate($f['next_followup_date']) ?>
                </span>
              </div>
              <?= leadStatusBadge($f['status']) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const GOLD    = '#D4AF37';
  const MAROON  = '#800000';
  const COLORS  = ['#3B82F6','#EF4444','#F59E0B','#94A3B8','#10B981','#6B7280'];

  // Trend chart
  new Chart(document.getElementById('chartTrend'), {
    type: 'line',
    data: {
      labels: <?= $chartMonths ?>,
      datasets: [
        { label: 'Total Leads', data: <?= $chartLeads ?>, borderColor: MAROON, backgroundColor: 'rgba(128,0,0,0.08)', fill: true, tension: 0.4 },
        { label: 'Closed',      data: <?= $chartClosed ?>, borderColor: GOLD, backgroundColor: 'rgba(212,175,55,0.08)', fill: true, tension: 0.4 }
      ]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
  });

  // Donut
  new Chart(document.getElementById('chartDonut'), {
    type: 'doughnut',
    data: { labels: <?= $donutLabels ?>, datasets: [{ data: <?= $donutData ?>, backgroundColor: COLORS, hoverOffset: 8 }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } }, cutout: '60%' }
  });

  // Source bar
  new Chart(document.getElementById('chartSource'), {
    type: 'bar',
    data: {
      labels: <?= $sourceLabels ?>,
      datasets: [{ label: 'Leads', data: <?= $sourceCounts ?>, backgroundColor: MAROON, borderRadius: 6 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });
});
</script>
