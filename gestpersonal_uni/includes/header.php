<?php
// includes/header.php
// Barra superior reutilizable
// Variables esperadas: $titulo_pagina, $breadcrumb (array)
// Ej: $breadcrumb = [['Personal', '../personal/lista.php'], ['Nuevo Empleado', '']];

if (!isset($titulo_pagina)) $titulo_pagina = 'Panel';
if (!isset($breadcrumb))    $breadcrumb    = [];
?>
<style>
    .topbar {
        background: #fff;
        padding: 14px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e2e8f0;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    .topbar-left h5 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1a2535;
    }
    .topbar-left .breadcrumb {
        margin: 0;
        font-size: 0.78rem;
    }
    .topbar-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .topbar-user {
        display: flex;
        align-items: center;
        gap: 9px;
        background: #f0f4f8;
        padding: 7px 14px;
        border-radius: 20px;
        font-size: 0.82rem;
        color: #1a2535;
        font-weight: 600;
    }
    .topbar-date {
        font-size: 0.78rem;
        color: #64748b;
    }

    /* Contenedor principal del contenido */
    .page-content {
        padding: 28px;
    }

    /* Cards estilo uniforme */
    .card-uni {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 6px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .card-uni-header {
        padding: 16px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fafbfc;
    }
    .card-uni-header h6 {
        margin: 0;
        font-weight: 700;
        font-size: 0.9rem;
        color: #1a2535;
    }
    .card-uni-body {
        padding: 22px;
    }

    /* Botones estilo uniforme */
    .btn-uni-primary {
        background: #1a6fc4;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
    }
    .btn-uni-primary:hover { background: #155da0; color: #fff; }

    .btn-uni-secondary {
        background: #f0f4f8;
        color: #1a2535;
        border: 1px solid #e2e8f0;
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
    }
    .btn-uni-secondary:hover { background: #e2e8f0; color: #1a2535; }

    .btn-uni-danger {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-uni-danger:hover { background: #fecaca; color: #dc2626; }

    .btn-uni-edit {
        background: #e0f2fe;
        color: #0369a1;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-uni-edit:hover { background: #bae6fd; color: #0369a1; }

    /* Tablas estilo uniforme */
    .tabla-uni {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }
    .tabla-uni thead th {
        background: #f0f4f8;
        padding: 11px 14px;
        text-align: left;
        font-weight: 700;
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
    }
    .tabla-uni tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }
    .tabla-uni tbody tr:hover { background: #f8fafc; }
    .tabla-uni tbody td { padding: 11px 14px; color: #334155; vertical-align: middle; }

    /* Badges */
    .badge-uni {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        display: inline-block;
    }
    .badge-activo    { background: #dcfce7; color: #16a34a; }
    .badge-inactivo  { background: #fee2e2; color: #dc2626; }
    .badge-docente   { background: #dbeafe; color: #1d4ed8; }
    .badge-admin     { background: #fef3c7; color: #d97706; }
    .badge-vencer    { background: #ffedd5; color: #c2410c; }
    .badge-vigente   { background: #dcfce7; color: #16a34a; }
    .badge-tardanza  { background: #fef9c3; color: #a16207; }
    .badge-falta     { background: #fee2e2; color: #dc2626; }

    /* Alertas flash */
    .alert-flash {
        padding: 12px 18px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .alert-warning { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }

    /* Filtros */
    .filtros-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 18px 22px;
        margin-bottom: 22px;
    }
    .filtros-box label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display: block;
        margin-bottom: 5px;
    }
    .filtros-box input,
    .filtros-box select {
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        padding: 8px 12px;
        font-size: 0.85rem;
        width: 100%;
        background: #fff;
        color: #1a2535;
        transition: border-color 0.2s;
    }
    .filtros-box input:focus,
    .filtros-box select:focus {
        border-color: #1a6fc4;
        outline: none;
        box-shadow: 0 0 0 3px rgba(26,111,196,0.1);
    }

    /* Formularios */
    .form-uni label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        display: block;
        margin-bottom: 5px;
    }
    .form-uni input,
    .form-uni select,
    .form-uni textarea {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 9px 13px;
        font-size: 0.875rem;
        width: 100%;
        background: #fff;
        color: #1a2535;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }
    .form-uni input:focus,
    .form-uni select:focus,
    .form-uni textarea:focus {
        border-color: #1a6fc4;
        outline: none;
        box-shadow: 0 0 0 3px rgba(26,111,196,0.1);
    }
    .form-uni .form-group { margin-bottom: 18px; }
    .form-uni .form-row {
        display: grid;
        gap: 18px;
    }
    .form-uni .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
    .form-uni .form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
    .form-uni .form-row.cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }

    /* KPI Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 28px;
    }
    .kpi-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 6px rgba(0,0,0,0.05);
    }
    .kpi-card .kpi-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .kpi-card .kpi-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 6px;
    }
    .kpi-card .kpi-sub {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    .kpi-blue   { border-top: 3px solid #1a6fc4; }
    .kpi-green  { border-top: 3px solid #16a34a; }
    .kpi-orange { border-top: 3px solid #d97706; }
    .kpi-red    { border-top: 3px solid #dc2626; }
    .kpi-blue   .kpi-value { color: #1a6fc4; }
    .kpi-green  .kpi-value { color: #16a34a; }
    .kpi-orange .kpi-value { color: #d97706; }
    .kpi-red    .kpi-value { color: #dc2626; }

    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: 1fr 1fr; }
        .form-uni .form-row.cols-3,
        .form-uni .form-row.cols-4 { grid-template-columns: 1fr 1fr; }
        .form-uni .form-row.cols-2 { grid-template-columns: 1fr; }
    }
</style>

<div class="topbar">
    <div class="topbar-left">
        <h5><?= htmlspecialchars($titulo_pagina) ?></h5>
        <?php if (!empty($breadcrumb)): ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <?php foreach ($breadcrumb as $i => $crumb): ?>
                    <?php if ($i == count($breadcrumb) - 1): ?>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($crumb[0]) ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item"><a href="<?= $crumb[1] ?>"><?= htmlspecialchars($crumb[0]) ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
    </div>
    <div class="topbar-right">
        <span class="topbar-date">📅 <?= date('d/m/Y') ?></span>
        <div class="topbar-user">
            <span>👤</span> Administrador (DBA)
        </div>
    </div>
</div>
