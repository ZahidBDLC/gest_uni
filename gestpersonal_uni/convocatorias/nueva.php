<?php
// convocatorias/nueva.php
session_start();
require_once '../config/conexion.php';

$errores = [];
$datos   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nomconv'    => trim($_POST['nomconv']    ?? ''),
        'fechconv'   => $_POST['fechconv']        ?? '',
        'descrpconv' => trim($_POST['descrpconv'] ?? ''),
        'estconv'    => $_POST['estconv']         ?? 'ABIERTA',
    ];

    if (empty($datos['nomconv']))    $errores[] = 'El nombre es obligatorio.';
    if (empty($datos['fechconv']))   $errores[] = 'La fecha es obligatoria.';
    if (empty($datos['descrpconv'])) $errores[] = 'La descripción es obligatoria.';

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO convocatorias_doc(nomconv,fechconv,descrpconv,estconv) VALUES(:nomconv,:fechconv,:descrpconv,:estconv)");
            $stmt->execute($datos);
            $_SESSION['msg'] = 'Convocatoria publicada correctamente.';
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $errores[] = $e->getMessage();
        }
    }
}

$modulo_activo = 'convocatorias';
$titulo_pagina = 'Nueva Convocatoria';
$breadcrumb    = [['Convocatorias','lista.php'],['Nueva','']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Nueva Convocatoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <?php if (!empty($errores)): ?>
    <div class="alert-flash alert-error">❌ <?= implode('<br>', array_map('htmlspecialchars',$errores)) ?></div>
    <?php endif; ?>

    <div class="card-uni" style="max-width:650px;">
        <div class="card-uni-header">
            <h6>📢 Publicar Convocatoria</h6>
            <a href="lista.php" class="btn-uni-secondary">← Volver</a>
        </div>
        <div class="card-uni-body">
            <form method="POST" class="form-uni">
                <div class="form-row cols-2" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Nombre de la Convocatoria *</label>
                        <input type="text" name="nomconv" placeholder="Ej: Concurso Docente 2025-I" value="<?= htmlspecialchars($datos['nomconv']??'') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha *</label>
                        <input type="date" name="fechconv" value="<?= $datos['fechconv']??date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:18px;">
                    <label>Descripción *</label>
                    <textarea name="descrpconv" rows="4" placeholder="Descripción detallada de la convocatoria, requisitos, plazas disponibles..."><?= htmlspecialchars($datos['descrpconv']??'') ?></textarea>
                </div>
                <div class="form-group" style="margin-bottom:24px;">
                    <label>Estado</label>
                    <select name="estconv">
                        <option value="ABIERTA"  <?= ($datos['estconv']??'')==='ABIERTA' ?'selected':'' ?>>🟢 ABIERTA</option>
                        <option value="CERRADA"  <?= ($datos['estconv']??'')==='CERRADA' ?'selected':'' ?>>🔴 CERRADA</option>
                    </select>
                </div>
                <div style="display:flex; gap:12px; border-top:1px solid #e2e8f0; padding-top:18px;">
                    <button type="submit" class="btn-uni-primary">💾 Publicar</button>
                    <a href="lista.php" class="btn-uni-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
