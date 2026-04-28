(function () {
  function shortName(name) {
    if (!name) return 'Account';
    const trimmed = String(name).trim();
    if (!trimmed) return 'Account';
    const first = trimmed.split(' ')[0];
    return first.length > 14 ? first.slice(0, 14) + '...' : first;
  }

  function addAuthStyles() {
    if (document.getElementById('auth-nav-style')) return;
    const style = document.createElement('style');
    style.id = 'auth-nav-style';
    style.textContent = '.auth-user-pill{pointer-events:none;opacity:.92}.auth-link{text-decoration:none;display:inline-flex;align-items:center;justify-content:center}';
    document.head.appendChild(style);
  }

  async function initAuthNav() {
    const navActions = document.querySelector('.nav-actions');
    if (!navActions) return;

    try {
      const response = await fetch('auth_status.php', { credentials: 'same-origin' });
      if (!response.ok) return;

      const data = await response.json();
      if (!data.success || !data.logged_in) return;

      addAuthStyles();
      navActions.innerHTML = '';

      const userPill = document.createElement('button');
      userPill.type = 'button';
      userPill.className = 'btn-ghost auth-user-pill';
      userPill.textContent = 'Hi, ' + shortName(data.name);
      navActions.appendChild(userPill);

      if (data.role === 'vendor') {
        const dashboardLink = document.createElement('a');
        dashboardLink.href = 'vendor-dashboard.php';
        dashboardLink.className = 'btn-ghost auth-link';
        dashboardLink.textContent = 'Dashboard';
        navActions.appendChild(dashboardLink);
      }

      const logoutLink = document.createElement('a');
      logoutLink.href = 'logout.php';
      logoutLink.className = 'btn-primary auth-link';
      logoutLink.textContent = 'Logout';
      navActions.appendChild(logoutLink);
    } catch (_err) {
      // Leave default guest buttons when auth endpoint is unavailable.
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthNav);
  } else {
    initAuthNav();
  }
})();
