<?php
// contratos/nuevo.php
session_start();
require_once '../config/conexion.php';

$errores = [];
$datos   = [];

$personal_list   = $pdo->query("SELECT idpers, CONCAT(nompers,' ',apepers) AS nombre FROM personal_uni WHERE estlab='Activo' ORDER BY apepers")->fetchAll();
$tipo_contratos  = $pdo->query("SELECT * FROM tipo_contrato ORDER BY nomcontr")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'idpers'    => intval($_POST['idpers']    ?? 0),
        'idtpcontr' => intval($_POST['idtpcontr'] ?? 0),
        'suelpers'  => floatval($_POST['suelpers'] ?? 0),
        'fechini'   => $_POST['fechini'] ?? '',
        'fechfin'   => $_POST['fechfin'] ?? null,
    ];

    if (!$datos['idpers'])    $errores[] = 'Seleccione un empleado.';
    if (!$datos['idtpcontr']) $errores[] = 'Seleccione tipo de contrato.';
    if ($datos['suelpers'] <= 0) $errores[] = 'El sueldo debe ser mayor a cero.';
    if (!$datos['fechini'])   $errores[] = 'La fecha de inicio es obligatoria.';

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contrato(idpers, idtpcontr, suelpers, fechini, fechfin) VALUES(:idpers,:idtpcontr,:suelpers,:fechini,NULLIF(:fechfin,''))");
            $stmt->execute($datos);
            $_SESSION['msg'] = 'Contrato registrado correctamente. (Trigger auditó el sueldo automáticamente)';
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            // PUNTO 6: Trigger SIGNAL trg_signal_sueldo_insert o fechas inválidas
            $errores[] = $e->getMessage();
        }
    }
}

$modulo_activo = 'contratos';
$titulo_pagina = 'Nuevo Contrato';
$breadcrumb    = [['Contratos', 'lista.php'], ['Nuevo', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Nuevo Contrato</title>
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

    <div class="card-uni" style="max-width:700px;">
        <div class="card-uni-header">
            <h6>📄 Registrar Contrato</h6>
            <a href="lista.php" class="btn-uni-secondary">← Volver</a>
        </div>
        <div class="card-uni-body">
            <form method="POST" class="form-uni">

                <div class="form-group" style="margin-bottom:18px;">
                    <label>Empleado *</label>
                    <select name="idpers" required>
                        <option value="">— Seleccione empleado —</option>
                        <?php foreach ($personal_list as $p): ?>
                        <option value="<?= $p['idpers'] ?>" <?= ($datos['idpers']??0)==$p['idpers']?'selected':'' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row cols-2" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Tipo de Contrato *</label>
                        <select name="idtpcontr" required>
                            <option value="">— Seleccione —</option>
                            <?php foreach ($tipo_contratos as $tc): ?>
                            <option value="<?= $tc['idtpcontr'] ?>" <?= ($datos['idtpcontr']??0)==$tc['idtpcontr']?'selected':'' ?>>
                                <?= htmlspecialchars($tc['nomcontr']) ?> — <?= htmlspecialchars($tc['dedicacion'] ?? '') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sueldo Mensual (S/) *</label>
                        <input type="number" name="suelpers" step="0.01" min="0.01"
                               placeholder="3000.00"
                               value="<?= $datos['suelpers'] ?? '' ?>" required>
                        <small style="color:#64748b;">⚠️ Trigger valida que sea mayor a cero</small>
                    </div>
                </div>

                <div class="form-row cols-2" style="margin-bottom:24px;">
                    <div class="form-group">
                        <label>Fecha Inicio *</label>
                        <input type="date" name="fechini" value="<?= $datos['fechini'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha Fin (dejar vacío si es indefinido)</label>
                        <input type="date" name="fechfin" value="<?= $datos['fechfin'] ?? '' ?>">
                        <small style="color:#64748b;">⚠️ Trigger valida que no sea anterior al inicio</small>
                    </div>
                </div>

                <div style="display:flex; gap:12px; border-top:1px solid #e2e8f0; padding-top:18px;">
                    <button type="submit" class="btn-uni-primary">💾 Guardar Contrato</button>
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
