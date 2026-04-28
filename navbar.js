(function () {
  const STYLE_ID = 'site-navbar-styles';

  function currentPage() {
    return (location.pathname.split('/').pop() || 'index.html').toLowerCase();
  }

  function injectStyles() {
    if (document.getElementById(STYLE_ID)) return;

    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = `
      .site-nav{position:fixed;top:0;left:0;right:0;z-index:1000;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:0 5%;height:72px;background:rgba(10,10,10,.97);backdrop-filter:blur(16px);border-bottom:1px solid rgba(255,255,255,.07)}
      .site-nav__logo{display:flex;align-items:center;gap:10px;text-decoration:none;flex-shrink:0}
      .site-nav__logo-icon{width:38px;height:38px;background:#FF4D00;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#fff;flex-shrink:0}
      .site-nav__brand{font-family:'Syne',sans-serif;font-size:1.35rem;font-weight:800;color:#fff;letter-spacing:-.5px}
      .site-nav__brand em{color:#FF4D00;font-style:normal}
      .site-nav__links{display:flex;align-items:center;justify-content:center;gap:32px;list-style:none;margin:0;padding:0;flex:1}
      .site-nav__links a{color:rgba(255,255,255,.75);text-decoration:none;font-size:.92rem;font-weight:500;transition:color .2s;white-space:nowrap}
      .site-nav__links a:hover,.site-nav__links a.is-active{color:#fff}
      .site-nav__actions{display:flex;align-items:center;gap:12px;flex-shrink:0}
      .site-nav__btn{display:inline-flex;align-items:center;justify-content:center;border-radius:50px;padding:9px 20px;font-size:.88rem;font-family:'DM Sans',sans-serif;font-weight:600;transition:all .2s;text-decoration:none;white-space:nowrap}
      .site-nav__btn--ghost{background:transparent;border:1.5px solid rgba(255,255,255,.2);color:#fff}
      .site-nav__btn--ghost:hover{border-color:#FF4D00;color:#FF4D00}
      .site-nav__btn--primary{background:#FF4D00;border:1.5px solid #FF4D00;color:#fff}
      .site-nav__btn--primary:hover{background:#cc3d00;border-color:#cc3d00;transform:translateY(-1px)}
      .site-nav__user{border:1.5px solid rgba(255,255,255,.2);background:transparent;color:#fff;cursor:default;pointer-events:none}
      .site-nav__user em{font-style:normal;color:#FF4D00}
      @media (max-width: 1040px){.site-nav__links{gap:22px}.site-nav__btn{padding:8px 16px;font-size:.84rem}}
      @media (max-width: 880px){.site-nav__links{display:none}}
      @media (max-width: 640px){.site-nav{gap:12px;height:68px;padding:0 4%}.site-nav__brand{font-size:1.1rem}.site-nav__logo-icon{width:34px;height:34px;border-radius:9px;font-size:1rem}.site-nav__actions{gap:8px}.site-nav__btn{padding:8px 13px;font-size:.8rem}}
    `;
    document.head.appendChild(style);
  }

  function shortName(name) {
    if (!name) return 'Account';
    const trimmed = String(name).trim();
    if (!trimmed) return 'Account';
    const first = trimmed.split(' ')[0];
    return first.length > 14 ? first.slice(0, 14) + '...' : first;
  }

  function isActiveLink(href) {
    const page = currentPage();
    const target = new URL(href, location.href);
    const targetPage = (target.pathname.split('/').pop() || '').toLowerCase();

    if (target.hash) {
      return page === targetPage && location.hash.toLowerCase() === target.hash.toLowerCase();
    }

    return page === targetPage;
  }

  function navLink(label, href) {
    const link = document.createElement('a');
    link.href = href;
    link.textContent = label;
    if (isActiveLink(href)) {
      link.classList.add('is-active');
    }
    return link;
  }

  function buildNav() {
    const nav = document.createElement('div');
    nav.className = 'site-nav';

    const logo = document.createElement('a');
    logo.className = 'site-nav__logo';
    logo.href = 'index.html';
    logo.innerHTML = '<span class="site-nav__logo-icon"><i class="fas fa-bolt"></i></span><span class="site-nav__brand">Smart<em>Rent</em></span>';

    const links = document.createElement('ul');
    links.className = 'site-nav__links';
    const items = [
      ['Home', 'index.html'],
      ['Listings', 'listings.html'],
      ['Become Vendor', 'vendor-register.html'],
      ['Booking', 'booking.html'],
      ['Categories', 'index.html#categories'],
      ['How It Works', 'index.html#how-it-works'],
      ['List Your Item', 'vendor-register.html']
    ];

    items.forEach(([label, href]) => {
      const li = document.createElement('li');
      li.appendChild(navLink(label, href));
      links.appendChild(li);
    });

    const actions = document.createElement('div');
    actions.className = 'site-nav__actions';

    const signIn = document.createElement('a');
    signIn.className = 'site-nav__btn site-nav__btn--ghost';
    signIn.href = 'index.html#signin';
    signIn.textContent = 'Sign In';

    const getStarted = document.createElement('a');
    getStarted.className = 'site-nav__btn site-nav__btn--primary';
    getStarted.href = 'index.html#signup';
    getStarted.textContent = 'Get Started';

    actions.appendChild(signIn);
    actions.appendChild(getStarted);

    nav.appendChild(logo);
    nav.appendChild(links);
    nav.appendChild(actions);

    return nav;
  }

  async function hydrateAuthNav() {
    const actions = document.querySelector('.site-nav__actions');
    if (!actions) return;

    try {
      const response = await fetch('php/auth_status.php', { credentials: 'same-origin' });
      if (!response.ok) return;

      const data = await response.json();
      if (!data.logged_in) return;

      actions.innerHTML = '';

      const userPill = document.createElement('span');
      userPill.className = 'site-nav__btn site-nav__btn--ghost site-nav__user';
      userPill.textContent = 'Hi, ' + shortName(data.name);
      actions.appendChild(userPill);

      if (data.role === 'vendor') {
        const dashboardLink = document.createElement('a');
        dashboardLink.className = 'site-nav__btn site-nav__btn--ghost';
        dashboardLink.href = 'vendor-dashboard.php';
        dashboardLink.textContent = 'Dashboard';
        actions.appendChild(dashboardLink);
      }

      const logoutLink = document.createElement('a');
      logoutLink.className = 'site-nav__btn site-nav__btn--primary';
      logoutLink.href = 'php/logout.php';
      logoutLink.textContent = 'Logout';
      actions.appendChild(logoutLink);
    } catch (_error) {
      // Keep guest nav when auth lookup fails.
    }
  }

  function init() {
    injectStyles();
    document.body.prepend(buildNav());
    hydrateAuthNav();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();