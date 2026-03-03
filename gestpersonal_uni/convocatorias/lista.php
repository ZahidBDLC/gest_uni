<?php
// convocatorias/lista.php
session_start();
require_once '../config/conexion.php';

$msg = $_SESSION['msg'] ?? ''; unset($_SESSION['msg']);

$convs = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM postulacion_personal pp WHERE pp.idselec = c.idselec) AS total_postulantes
    FROM convocatorias_doc c
    ORDER BY c.fechconv DESC
")->fetchAll();

$modulo_activo = 'convocatorias';
$titulo_pagina = 'Convocatorias Docentes';
$breadcrumb    = [['Convocatorias', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Convocatorias</title>
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
        <div style="font-size:0.85rem; color:#64748b;"><?= count($convs) ?> convocatoria(s)</div>
        <a href="nueva.php" class="btn-uni-primary">➕ Nueva Convocatoria</a>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fill,minmax(340px,1fr)); gap:18px;">
        <?php if (empty($convs)): ?>
        <div style="grid-column:1/-1; text-align:center; color:#94a3b8; padding:40px;">Sin convocatorias registradas.</div>
        <?php else: ?>
        <?php foreach ($convs as $conv): ?>
        <div class="card-uni">
            <div class="card-uni-header">
                <h6 style="font-size:0.9rem;"><?= htmlspecialchars($conv['nomconv']) ?></h6>
                <span class="badge-uni <?= $conv['estconv']==='ABIERTA'?'badge-activo':'badge-inactivo' ?>">
                    <?= $conv['estconv'] ?>
                </span>
            </div>
            <div class="card-uni-body">
                <p style="font-size:0.83rem; color:#64748b; margin:0 0 12px;">
                    <?= htmlspecialchars($conv['descrpconv']) ?>
                </p>
                <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:#475569;">
                    <span>📅 <?= date('d/m/Y', strtotime($conv['fechconv'])) ?></span>
                    <span>👥 <?= $conv['total_postulantes'] ?> postulante(s)</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
