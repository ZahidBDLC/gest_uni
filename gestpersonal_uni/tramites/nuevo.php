<?php
// tramites/nuevo.php
session_start();
require_once '../config/conexion.php';

$errores = [];
$datos   = [];

$personal_list = $pdo->query("SELECT idpers, CONCAT(nompers,' ',apepers) AS nombre FROM personal_uni WHERE estlab='Activo' ORDER BY apepers")->fetchAll();
$tipos_tramite = $pdo->query("SELECT * FROM tipo_tramite ORDER BY nomtrmt")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'idpers'    => intval($_POST['idpers']    ?? 0),
        'idtptrmt'  => intval($_POST['idtptrmt']  ?? 0),
        'fechsoli'  => $_POST['fechsoli'] ?? date('Y-m-d'),
        'fechini'   => $_POST['fechini']  ?? '',
        'fechfin'   => $_POST['fechfin']  ?? '',
        'esttrmt'   => $_POST['esttrmt']  ?? 'Pendiente',
        'obstrmt'   => trim($_POST['obstrmt'] ?? ''),
    ];

    if (!$datos['idpers'])   $errores[] = 'Seleccione un empleado.';
    if (!$datos['idtptrmt']) $errores[] = 'Seleccione tipo de trámite.';
    if (!$datos['fechini'])  $errores[] = 'Fecha de inicio requerida.';
    if (!$datos['fechfin'])  $errores[] = 'Fecha de fin requerida.';

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tramite_personal(idpers,idtptrmt,fechsoli,fechini,fechfin,esttrmt,obstrmt) VALUES(:idpers,:idtptrmt,:fechsoli,:fechini,:fechfin,:esttrmt,NULLIF(:obstrmt,''))");
            $stmt->execute($datos);
            $_SESSION['msg'] = 'Trámite registrado correctamente.';
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $errores[] = $e->getMessage();
        }
    }
}

$modulo_activo = 'tramites';
$titulo_pagina = 'Nuevo Trámite';
$breadcrumb    = [['Trámites','lista.php'],['Nuevo','']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Nuevo Trámite</title>
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

    <div class="card-uni" style="max-width:700px;">
        <div class="card-uni-header">
            <h6>📋 Registrar Trámite</h6>
            <a href="lista.php" class="btn-uni-secondary">← Volver</a>
        </div>
        <div class="card-uni-body">
            <form method="POST" class="form-uni">
                <div class="form-row cols-2" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Empleado *</label>
                        <select name="idpers" required>
                            <option value="">— Seleccione —</option>
                            <?php foreach ($personal_list as $p): ?>
                            <option value="<?= $p['idpers'] ?>" <?= ($datos['idpers']??0)==$p['idpers']?'selected':'' ?>><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipo de Trámite *</label>
                        <select name="idtptrmt" required>
                            <option value="">— Seleccione —</option>
                            <?php foreach ($tipos_tramite as $tt): ?>
                            <option value="<?= $tt['idtptrmt'] ?>" <?= ($datos['idtptrmt']??0)==$tt['idtptrmt']?'selected':'' ?>><?= htmlspecialchars($tt['nomtrmt']) ?> — <?= htmlspecialchars($tt['mottrmt']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row cols-3" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Fecha Solicitud</label>
                        <input type="date" name="fechsoli" value="<?= $datos['fechsoli']??date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Fecha Inicio *</label>
                        <input type="date" name="fechini" value="<?= $datos['fechini']??'' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha Fin *</label>
                        <input type="date" name="fechfin" value="<?= $datos['fechfin']??'' ?>" required>
                    </div>
                </div>

                <div class="form-row cols-2" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Estado</label>
                        <select name="esttrmt">
                            <option value="Pendiente" <?= ($datos['esttrmt']??'')==='Pendiente'?'selected':'' ?>>Pendiente</option>
                            <option value="Aprobado"  <?= ($datos['esttrmt']??'')==='Aprobado'?'selected':'' ?>>Aprobado</option>
                            <option value="Rechazado" <?= ($datos['esttrmt']??'')==='Rechazado'?'selected':'' ?>>Rechazado</option>
                            <option value="En proceso"<?= ($datos['esttrmt']??'')==='En proceso'?'selected':'' ?>>En proceso</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observaciones</label>
                        <input type="text" name="obstrmt" placeholder="Opcional" value="<?= htmlspecialchars($datos['obstrmt']??'') ?>">
                    </div>
                </div>

                <div style="display:flex; gap:12px; border-top:1px solid #e2e8f0; padding-top:18px;">
                    <button type="submit" class="btn-uni-primary">💾 Registrar Trámite</button>
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
