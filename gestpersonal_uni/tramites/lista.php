<?php
// tramites/lista.php
session_start();
require_once '../config/conexion.php';

$msg = $_SESSION['msg'] ?? ''; unset($_SESSION['msg']);

$tramites = $pdo->query("
    SELECT t.idtrmt, CONCAT(p.nompers,' ',p.apepers) AS nombre,
           p.tipo_personal, tt.nomtrmt, tt.mottrmt,
           t.fechsoli, t.fechini, t.fechfin, t.esttrmt, t.obstrmt
    FROM tramite_personal t
    JOIN personal_uni p ON t.idpers = p.idpers
    JOIN tipo_tramite tt ON t.idtptrmt = tt.idtptrmt
    ORDER BY t.fechsoli DESC
")->fetchAll();

$modulo_activo = 'tramites';
$titulo_pagina = 'Gestión de Trámites';
$breadcrumb    = [['Trámites', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Trámites</title>
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
        <div style="font-size:0.85rem; color:#64748b;"><?= count($tramites) ?> trámite(s)</div>
        <a href="nuevo.php" class="btn-uni-primary">➕ Nuevo Trámite</a>
    </div>

    <div class="card-uni">
        <div style="overflow-x:auto;">
            <table class="tabla-uni">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Empleado</th>
                        <th>Tipo Trámite</th>
                        <th>Motivo</th>
                        <th>Fecha Solicitud</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tramites)): ?>
                    <tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:30px;">Sin trámites registrados.</td></tr>
                    <?php else: ?>
                    <?php foreach ($tramites as $t):
                        $cls = 'badge-activo';
                        if ($t['esttrmt']==='Pendiente') $cls = 'badge-tardanza';
                        if ($t['esttrmt']==='Rechazado') $cls = 'badge-falta';
                    ?>
                    <tr>
                        <td><?= $t['idtrmt'] ?></td>
                        <td><strong><?= htmlspecialchars($t['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($t['nomtrmt']) ?></td>
                        <td><?= htmlspecialchars($t['mottrmt']) ?></td>
                        <td><?= date('d/m/Y',strtotime($t['fechsoli'])) ?></td>
                        <td><?= date('d/m/Y',strtotime($t['fechini'])) ?></td>
                        <td><?= date('d/m/Y',strtotime($t['fechfin'])) ?></td>
                        <td><span class="badge-uni <?= $cls ?>"><?= htmlspecialchars($t['esttrmt']) ?></span></td>
                        <td><?= htmlspecialchars($t['obstrmt'] ?? '—') ?></td>
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
