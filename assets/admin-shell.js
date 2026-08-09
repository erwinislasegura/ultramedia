(function () {
    const sidebar = document.getElementById('sidebar');
    const menuButton = document.getElementById('menuBtn');
    const closeButton = document.getElementById('sidebarClose');
    const overlay = document.getElementById('adminNavOverlay');

    if (!sidebar || !menuButton) return;

    function setMenu(open) {
        sidebar.classList.toggle('open', open);
        document.body.classList.toggle('admin-menu-open', open);
        menuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        menuButton.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    }

    menuButton.addEventListener('click', function () {
        setMenu(!sidebar.classList.contains('open'));
    });
    closeButton?.addEventListener('click', function () { setMenu(false); });
    overlay?.addEventListener('click', function () { setMenu(false); });

    sidebar.querySelectorAll('nav a').forEach(function (link) {
        link.addEventListener('click', function () { setMenu(false); });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') setMenu(false);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 900) setMenu(false);
    });
})();
