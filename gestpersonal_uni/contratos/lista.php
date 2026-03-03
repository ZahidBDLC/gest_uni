<?php
// contratos/lista.php
session_start();
require_once '../config/conexion.php';

$msg = $_SESSION['msg'] ?? ''; unset($_SESSION['msg']);

// Vista vw_personal_contrato (PUNTO 1)
$contratos = $pdo->query("SELECT * FROM vw_personal_contrato ORDER BY fin_contrato ASC")->fetchAll();

$modulo_activo = 'contratos';
$titulo_pagina = 'Gestión de Contratos';
$breadcrumb    = [['Contratos', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Contratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <?php if ($msg): ?>
    <div class="alert-flash <?= strpos($msg,'Error')!==false?'alert-error':'alert-success' ?>">
        <?= strpos($msg,'Error')!==false?'❌':'✅' ?> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div style="font-size:0.85rem; color:#64748b;"><?= count($contratos) ?> contrato(s) registrado(s)</div>
        <a href="nuevo.php" class="btn-uni-primary">➕ Nuevo Contrato</a>
    </div>

    <div class="card-uni">
        <div style="overflow-x:auto;">
            <table class="tabla-uni">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Tipo Personal</th>
                        <th>Tipo Contrato</th>
                        <th>Dedicación</th>
                        <th>Sueldo</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado Contrato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contratos)): ?>
                    <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:30px;">Sin contratos registrados.</td></tr>
                    <?php else: ?>
                    <?php foreach ($contratos as $c):
                        // PUNTO 4: fn_estado_contrato
                        $est = $pdo->query("SELECT fn_estado_contrato({$c['idpers']})")->fetchColumn();
                        $cls = 'badge-activo';
                        if (strpos($est,'VENCIDO')!==false) $cls='badge-falta';
                        if (strpos($est,'VENCER') !==false) $cls='badge-vencer';
                        if (strpos($est,'Sin')    !==false) $cls='badge-inactivo';
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['nombre_completo']) ?></strong></td>
                        <td>
                            <span class="badge-uni <?= $c['tipo_personal']==='docente'?'badge-docente':'badge-admin' ?>">
                                <?= ucfirst($c['tipo_personal']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($c['tipo_contrato']) ?></td>
                        <td><?= htmlspecialchars($c['dedicacion'] ?? '—') ?></td>
                        <td><strong>S/ <?= number_format($c['sueldo'],2) ?></strong></td>
                        <td><?= $c['inicio_contrato'] ? date('d/m/Y',strtotime($c['inicio_contrato'])) : '—' ?></td>
                        <td><?= $c['fin_contrato']   ? date('d/m/Y',strtotime($c['fin_contrato']))   : '—' ?></td>
                        <td><span class="badge-uni <?= $cls ?>"><?= htmlspecialchars($est) ?></span></td>
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
