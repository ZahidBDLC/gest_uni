<?php
// capacitaciones/lista.php
require_once '../config/conexion.php';

// Vista vw_capacitaciones_evaluaciones (PUNTO 1)
$caps = $pdo->query("SELECT * FROM vw_capacitaciones_evaluaciones ORDER BY fecha_capacitacion DESC")->fetchAll();

$modulo_activo = 'capacitaciones';
$titulo_pagina = 'Capacitaciones del Personal';
$breadcrumb    = [['Capacitaciones', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Capacitaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div style="font-size:0.85rem; color:#64748b;"><?= count($caps) ?> registro(s)</div>
        <a href="nuevo.php" class="btn-uni-primary">➕ Nueva Capacitación</a>
    </div>

    <div class="card-uni">
        <div style="overflow-x:auto;">
            <table class="tabla-uni">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Tipo</th>
                        <th>Capacitador</th>
                        <th>Tema</th>
                        <th>Fecha</th>
                        <th>Puntaje</th>
                        <th>Rendimiento (fn BD)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($caps)): ?>
                    <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:30px;">Sin capacitaciones registradas.</td></tr>
                    <?php else: ?>
                    <?php
                    $rendimientos_cache = [];
                    foreach ($caps as $c):
                        // PUNTO 4: fn_nivel_rendimiento — se cachea por empleado
                        if (!isset($rendimientos_cache[$c['idpers']])) {
                            $rendimientos_cache[$c['idpers']] = $pdo->query("SELECT fn_nivel_rendimiento({$c['idpers']})")->fetchColumn();
                        }
                        $rend = $rendimientos_cache[$c['idpers']];
                        $cls_rend = 'badge-activo';
                        if (strpos($rend,'BAJO')    !== false) $cls_rend = 'badge-falta';
                        if (strpos($rend,'REGULAR')  !== false) $cls_rend = 'badge-tardanza';
                        if (strpos($rend,'Sin eval') !== false) $cls_rend = 'badge-inactivo';
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['nombre_completo']) ?></strong></td>
                        <td><span class="badge-uni <?= $c['tipo_personal']==='docente'?'badge-docente':'badge-admin' ?>"><?= ucfirst($c['tipo_personal']) ?></span></td>
                        <td><?= htmlspecialchars($c['capacitador']) ?></td>
                        <td><?= htmlspecialchars($c['tema']) ?></td>
                        <td><?= $c['fecha_capacitacion'] ? date('d/m/Y',strtotime($c['fecha_capacitacion'])) : '—' ?></td>
                        <td>
                            <?php if ($c['puntaje'] !== null): ?>
                                <strong><?= $c['puntaje'] ?>/100</strong>
                            <?php else: ?>
                                <span style="color:#94a3b8;">Sin evaluar</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge-uni <?= $cls_rend ?>"><?= htmlspecialchars($rend) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
