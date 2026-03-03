<?php
// pagos/nuevo.php
session_start();
require_once '../config/conexion.php';

$errores = [];
$datos   = [];

$personal_list = $pdo->query("SELECT idpers, CONCAT(nompers,' ',apepers) AS nombre FROM personal_uni WHERE estlab='Activo' ORDER BY apepers")->fetchAll();
$tipos_pago    = $pdo->query("SELECT * FROM tipo_pago ORDER BY nompag")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'idpers'   => intval($_POST['idpers']   ?? 0),
        'idtppag'  => intval($_POST['idtppag']  ?? 0),
        'montpag'  => floatval($_POST['montpag'] ?? 0),
    ];

    if (!$datos['idpers'])  $errores[] = 'Seleccione un empleado.';
    if (!$datos['idtppag']) $errores[] = 'Seleccione tipo de pago.';
    if ($datos['montpag'] <= 0) $errores[] = 'El monto debe ser mayor a cero.';

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO pagos(idpers, idtppag, montpag) VALUES(:idpers,:idtppag,:montpag)");
            $stmt->execute($datos);
            // PUNTO 5: trg_audit_pago_insert se activa automáticamente aquí
            $_SESSION['msg'] = 'Pago registrado. El trigger de auditoría registró este movimiento automáticamente.';
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $errores[] = $e->getMessage();
        }
    }
}

$modulo_activo = 'pagos';
$titulo_pagina = 'Registrar Pago';
$breadcrumb    = [['Pagos', 'lista.php'], ['Nuevo Pago', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Nuevo Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <?php if (!empty($errores)): ?>
    <div class="alert-flash alert-error">❌ <?= implode('<br>', array_map('htmlspecialchars', $errores)) ?></div>
    <?php endif; ?>

    <div class="alert-flash" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">
        ℹ️ Al registrar un pago, el <strong>Trigger trg_audit_pago_insert</strong> registrará automáticamente este movimiento en la tabla de auditoría.
    </div>

    <div class="card-uni" style="max-width:600px;">
        <div class="card-uni-header">
            <h6>💰 Registrar Nuevo Pago</h6>
            <a href="lista.php" class="btn-uni-secondary">← Volver</a>
        </div>
        <div class="card-uni-body">
            <form method="POST" class="form-uni">

                <div class="form-group" style="margin-bottom:18px;">
                    <label>Empleado *</label>
                    <select name="idpers" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($personal_list as $p): ?>
                        <option value="<?= $p['idpers'] ?>" <?= ($datos['idpers']??0)==$p['idpers']?'selected':'' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row cols-2" style="margin-bottom:24px;">
                    <div class="form-group">
                        <label>Tipo de Pago *</label>
                        <select name="idtppag" required>
                            <option value="">— Seleccione —</option>
                            <?php foreach ($tipos_pago as $tp): ?>
                            <option value="<?= $tp['idtppag'] ?>" <?= ($datos['idtppag']??0)==$tp['idtppag']?'selected':'' ?>>
                                <?= htmlspecialchars($tp['nompag']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Monto (S/) *</label>
                        <input type="number" name="montpag" step="0.01" min="0.01"
                               placeholder="1500.00"
                               value="<?= $datos['montpag'] ?? '' ?>" required>
                    </div>
                </div>

                <div style="display:flex; gap:12px; border-top:1px solid #e2e8f0; padding-top:18px;">
                    <button type="submit" class="btn-uni-primary">💾 Registrar Pago</button>
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
