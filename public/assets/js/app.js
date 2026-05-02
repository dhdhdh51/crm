/* VastuVeda Realty CRM — app.js */
(function () {
  'use strict';

  // ── Sidebar toggle ─────────────────────────────────────────────
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar       = document.getElementById('sidebar');
  const body          = document.body;

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      // On mobile (<768px): open class; on desktop: collapsed class
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle('open');
      } else {
        body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', body.classList.contains('sidebar-collapsed'));
      }
    });

    // Restore saved state
    if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth > 768) {
      body.classList.add('sidebar-collapsed');
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && e.target !== sidebarToggle) {
          sidebar.classList.remove('open');
        }
      }
    });
  }

  // ── User dropdown ──────────────────────────────────────────────
  const userMenuBtn  = document.getElementById('userMenuBtn');
  const userDropdown = document.getElementById('userDropdown');

  if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle('show');
    });
    document.addEventListener('click', () => {
      userDropdown.classList.remove('show');
    });
  }

  // ── Flash dismiss ──────────────────────────────────────────────
  document.querySelectorAll('.flash').forEach((el) => {
    setTimeout(() => {
      el.style.transition = 'opacity .5s';
      el.style.opacity    = '0';
      setTimeout(() => el.remove(), 500);
    }, 5000);
  });

  // ── AJAX unread count (notification bell) ─────────────────────
  function refreshUnreadCount() {
    fetch(window._baseUrl ? window._baseUrl + '/notifications/unread' : '/notifications/unread')
      .then((r) => r.json())
      .then((data) => {
        const dot = document.getElementById('notifDot');
        if (data.count > 0) {
          if (!dot) {
            const btn = document.getElementById('notifBell');
            if (btn) {
              const span = document.createElement('span');
              span.id = 'notifDot';
              span.className = 'notif-dot';
              span.textContent = data.count;
              btn.appendChild(span);
            }
          } else {
            dot.textContent = data.count;
          }
        } else if (dot) {
          dot.remove();
        }
      })
      .catch(() => {});
  }

  // Poll every 60 seconds if bell exists
  if (document.getElementById('notifBell')) {
    setInterval(refreshUnreadCount, 60000);
  }

  // ── Notification bell click → go to notifications ──────────────
  const notifBell = document.getElementById('notifBell');
  if (notifBell) {
    notifBell.addEventListener('click', () => {
      window.location.href = (window._baseUrl || '') + '/notifications';
    });
  }

  // ── Auto-dismiss search form on select change ──────────────────
  document.querySelectorAll('.filter-form select').forEach((sel) => {
    sel.addEventListener('change', () => {
      sel.closest('form').submit();
    });
  });

  // ── Confirm delete (data-confirm) ─────────────────────────────
  document.addEventListener('submit', (e) => {
    const msg = e.target.dataset.confirm;
    if (msg && !confirm(msg)) {
      e.preventDefault();
    }
  });

})();
