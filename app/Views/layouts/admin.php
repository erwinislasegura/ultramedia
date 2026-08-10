<?php $section = $adminSection ?? ''; ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Panel') ?> | Ultra Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/admin-dashboard.css') ?>">
    <?php if ($section === 'photos'): ?>
        <link rel="stylesheet" href="<?= url('assets/admin-upload.css') ?>">
        <link rel="stylesheet" href="<?= url('assets/admin-packs.css') ?>">
        <link rel="stylesheet" href="<?= url('assets/admin-set-gallery.css') ?>">
        <link rel="stylesheet" href="<?= url('assets/admin-watermark.css') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= url('assets/admin-users.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/admin-modules.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/admin-polish.css') ?>">
    <?php if ($section === 'events'): ?>
        <link rel="stylesheet" href="<?= url('assets/admin-events.css') ?>">
    <?php endif; ?>
    <?php if (in_array($section, ['flow', 'email'], true)): ?>
        <link rel="stylesheet" href="<?= url('assets/admin-flow.css') ?>">
    <?php endif; ?>
    <?php if ($section === 'email'): ?>
        <link rel="stylesheet" href="<?= url('assets/admin-email.css') ?>">
    <?php endif; ?>
    <?php if ($section === 'hero'): ?>
        <link rel="stylesheet" href="<?= url('assets/admin-hero.css') ?>">
    <?php endif; ?>
    <?php if ($section === 'cta'): ?>
        <link rel="stylesheet" href="<?= url('assets/admin-cta.css') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= url('assets/security.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/ui-fixes.css') ?>">
    <link rel="stylesheet" href="<?= url('assets/admin-mobile.css') ?>">
</head>
<body>
    <aside class="sidebar" id="sidebar" aria-label="Navegación del panel">
        <button class="sidebar-close" id="sidebarClose" type="button" aria-label="Cerrar menú">×</button>
        <a class="logo" href="<?= url() ?>">
            <img src="<?= url('assets/ultra-logo.png') ?>" alt="Ultra Media Digital">
            <span>PANEL ADMIN</span>
        </a>
        <nav>
            <small>GESTIÓN</small>
            <a class="<?= $section === 'dashboard' ? 'active' : '' ?>" href="<?= url('admin') ?>"><i>▦</i> Resumen</a>
            <a class="<?= $section === 'events' ? 'active' : '' ?>" href="<?= url('admin/eventos') ?>"><i>◫</i> Eventos</a>
            <a class="<?= $section === 'photos' ? 'active' : '' ?>" href="<?= url('admin/fotos') ?>"><i>↑</i> Subir fotografías</a>
            <a href="#"><i>▧</i> Galerías</a>
            <a class="<?= $section === 'orders' ? 'active' : '' ?>" href="<?= url('admin/pedidos') ?>"><i>◇</i> Pedidos</a>

            <small>CONFIGURACIÓN</small>
            <a class="<?= $section === 'hero' ? 'active' : '' ?>" href="<?= url('admin/hero') ?>"><i>▣</i> Hero de portada</a>
            <a class="<?= $section === 'cta' ? 'active' : '' ?>" href="<?= url('admin/cta') ?>"><i>★</i> CTA de portada</a>
            <a class="<?= $section === 'flow' ? 'active' : '' ?>" href="<?= url('admin/flow') ?>"><i>◇</i> Pasarela Flow</a>
            <a class="<?= $section === 'email' ? 'active' : '' ?>" href="<?= url('admin/correo') ?>"><i>@</i> Correo SMTP</a>
            <a class="<?= $section === 'users' ? 'active' : '' ?>" href="<?= url('admin/usuarios') ?>"><i>◎</i> Usuarios y roles</a>
            <a href="#"><i>⚙</i> Ajustes</a>
        </nav>
        <div class="user">
            <span><?= strtoupper(substr(admin_user()['name'] ?? 'A', 0, 2)) ?></span>
            <div>
                <strong><?= htmlspecialchars(admin_user()['name'] ?? 'Administrador') ?></strong>
                <small><?= htmlspecialchars(admin_user()['role_name'] ?? 'Administrador') ?></small>
            </div>
            <form method="post" action="<?= url('admin/logout') ?>">
                <input type="hidden" name="_token" value="<?= csrf() ?>">
                <button title="Cerrar sesión" aria-label="Cerrar sesión">↪</button>
            </form>
        </div>
    </aside>

    <button class="admin-nav-overlay" id="adminNavOverlay" type="button" aria-label="Cerrar menú"></button>

    <main>
        <header class="topbar">
            <div class="topbar-path">
                <button id="menuBtn" type="button" aria-controls="sidebar" aria-expanded="false" aria-label="Abrir menú">☰</button>
                <span>Panel de control</span>
                <i>/</i>
                <b><?= htmlspecialchars($pageTitle ?? 'Resumen') ?></b>
            </div>
            <div class="top-actions">
                <a href="<?= url() ?>" target="_blank" rel="noopener">VER TIENDA ↗</a>
            </div>
        </header>
        <?php require $view; ?>
    </main>

    <script src="<?= url('assets/admin-shell.js') ?>"></script>
    <?php if ($section === 'dashboard'): ?>
        <script src="<?= url('assets/admin-dashboard.js') ?>"></script>
    <?php endif; ?>
    <?php if ($section === 'photos'): ?>
        <script>
            window.ultraWatermarkConfig = <?= json_encode([
                'scale' => (int)($photo['watermark_scale'] ?? 90),
                'opacity' => (int)($photo['watermark_opacity'] ?? 65),
            ], JSON_UNESCAPED_SLASHES) ?>;
        </script>
        <script src="<?= url('assets/admin-upload.js') ?>"></script>
    <?php endif; ?>
    <?php if ($section === 'events'): ?>
        <script src="<?= url('assets/admin-events.js') ?>"></script>
    <?php endif; ?>
    <?php if ($section === 'cta'): ?>
        <script src="<?= url('assets/admin-cta.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
