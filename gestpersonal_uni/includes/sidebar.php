<?php
// includes/sidebar.php
// Menú lateral reutilizable - se incluye en TODAS las páginas
// Uso: define $modulo_activo = 'personal'; antes de incluir este archivo

if (!isset($modulo_activo)) $modulo_activo = '';

// Detectar la ruta base según desde dónde se llama
$depth = substr_count($_SERVER['PHP_SELF'], '/') - 2;
$base  = str_repeat('../', max($depth, 0));
?>

<style>
    :root {
        --sidebar-bg: #1a2535;
        --sidebar-hover: #243447;
        --sidebar-active: #1a6fc4;
        --sidebar-text: #c8d6e5;
        --sidebar-heading: #6b8cae;
        --sidebar-width: 260px;
    }

    .sidebar {
        width: var(--sidebar-width);
        min-height: 100vh;
        background: var(--sidebar-bg);
        position: fixed;
        top: 0; left: 0;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        box-shadow: 3px 0 15px rgba(0,0,0,0.3);
        overflow-y: auto;
    }

    .sidebar-brand {
        padding: 20px 20px 15px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        text-decoration: none;
    }
    .sidebar-brand .brand-icon {
        width: 40px; height: 40px;
        background: var(--sidebar-active);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 8px;
    }
    .sidebar-brand .brand-title {
        color: #fff;
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.2;
        display: block;
    }
    .sidebar-brand .brand-sub {
        color: var(--sidebar-heading);
        font-size: 0.72rem;
        display: block;
    }

    .sidebar-section-title {
        padding: 18px 20px 6px;
        color: var(--sidebar-heading);
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 20px;
        color: var(--sidebar-text);
        text-decoration: none;
        font-size: 0.875rem;
        border-left: 3px solid transparent;
        transition: all 0.2s;
    }
    .sidebar-link:hover {
        background: var(--sidebar-hover);
        color: #fff;
        text-decoration: none;
    }
    .sidebar-link.active {
        background: rgba(26,111,196,0.2);
        border-left-color: var(--sidebar-active);
        color: #fff;
        font-weight: 600;
    }
    .sidebar-link .nav-icon {
        width: 18px;
        text-align: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .sidebar-link .badge-count {
        margin-left: auto;
        background: var(--sidebar-active);
        color: #fff;
        font-size: 0.65rem;
        padding: 2px 7px;
        border-radius: 10px;
    }

    .sidebar-divider {
        border-color: rgba(255,255,255,0.08);
        margin: 8px 0;
    }

    .sidebar-footer {
        margin-top: auto;
        padding: 15px 20px;
        border-top: 1px solid rgba(255,255,255,0.08);
    }
    .sidebar-footer a {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #e74c3c;
        text-decoration: none;
        font-size: 0.85rem;
    }
    .sidebar-footer a:hover { color: #ff6b6b; }

    /* Layout general */
    body {
        background: #f0f4f8;
        margin: 0;
        font-family: 'Segoe UI', system-ui, sans-serif;
    }
    .main-content {
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        padding: 0;
    }
</style>

<div class="sidebar">

    <!-- Marca / Logo -->
    <a href="<?= $base ?>index.php" class="sidebar-brand" style="display:block;">
        <div class="brand-icon">🎓</div>
        <span class="brand-title">UniHR System</span>
        <span class="brand-sub">Gestión de Personal Universitario</span>
    </a>

    <!-- Sección Principal -->
    <div class="sidebar-section-title">Principal</div>
    <a href="<?= $base ?>index.php" class="sidebar-link <?= $modulo_activo == 'dashboard' ? 'active' : '' ?>">
        <span class="nav-icon">📊</span> Dashboard
    </a>

    <!-- Sección Personal -->
    <div class="sidebar-section-title">Recursos Humanos</div>
    <a href="<?= $base ?>personal/lista.php" class="sidebar-link <?= $modulo_activo == 'personal' ? 'active' : '' ?>">
        <span class="nav-icon">👥</span> Gestión de Personal
    </a>
    <a href="<?= $base ?>contratos/lista.php" class="sidebar-link <?= $modulo_activo == 'contratos' ? 'active' : '' ?>">
        <span class="nav-icon">📄</span> Contratos
    </a>
    <a href="<?= $base ?>asistencia/lista.php" class="sidebar-link <?= $modulo_activo == 'asistencia' ? 'active' : '' ?>">
        <span class="nav-icon">📅</span> Asistencia
    </a>
    <a href="<?= $base ?>pagos/lista.php" class="sidebar-link <?= $modulo_activo == 'pagos' ? 'active' : '' ?>">
        <span class="nav-icon">💰</span> Pagos y AFP
    </a>

    <!-- Sección Académica -->
    <div class="sidebar-section-title">Desarrollo Académico</div>
    <a href="<?= $base ?>capacitaciones/lista.php" class="sidebar-link <?= $modulo_activo == 'capacitaciones' ? 'active' : '' ?>">
        <span class="nav-icon">🎓</span> Capacitaciones
    </a>
    <a href="<?= $base ?>convocatorias/lista.php" class="sidebar-link <?= $modulo_activo == 'convocatorias' ? 'active' : '' ?>">
        <span class="nav-icon">📢</span> Convocatorias
    </a>
    <a href="<?= $base ?>tramites/lista.php" class="sidebar-link <?= $modulo_activo == 'tramites' ? 'active' : '' ?>">
        <span class="nav-icon">📋</span> Trámites
    </a>

    <!-- Sección Sistema -->
    <div class="sidebar-section-title">Sistema</div>
    <a href="<?= $base ?>auditoria/lista.php" class="sidebar-link <?= $modulo_activo == 'auditoria' ? 'active' : '' ?>">
        <span class="nav-icon">🔍</span> Auditoría BD
    </a>

    <hr class="sidebar-divider">

    <!-- Footer -->
    <div class="sidebar-footer">
       <a href="/gestpersonal_uni/logout.php" class="btn btn-sm btn-danger">Cerrar sesión</a>
    </div>

</div>
