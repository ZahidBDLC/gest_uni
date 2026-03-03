<?php
// asistencia/registrar.php
session_start();
require_once '../config/conexion.php';

$errores = [];
$datos   = [];

$personal_list = $pdo->query("SELECT idpers, CONCAT(nompers,' ',apepers) AS nombre FROM personal_uni WHERE estlab='Activo' ORDER BY apepers")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'idpers'   => intval($_POST['idpers']   ?? 0),
        'fech'     => $_POST['fech']             ?? '',
        'hrentr1'  => $_POST['hrentr1']          ?? null,
        'hrsali1'  => $_POST['hrsali1']          ?? null,
        'hrentr2'  => $_POST['hrentr2']          ?? null,
        'hrsali2'  => $_POST['hrsali2']          ?? null,
        'estasist' => $_POST['estasist']         ?? '',
    ];

    if (!$datos['idpers'])    $errores[] = 'Seleccione un empleado.';
    if (!$datos['fech'])      $errores[] = 'La fecha es obligatoria.';
    if (!$datos['estasist'])  $errores[] = 'El estado es obligatorio.';

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO asistencia(idpers, fech, hrentr1, hrsali1, hrentr2, hrsali2, estasist)
                VALUES(:idpers, :fech, NULLIF(:hrentr1,''), NULLIF(:hrsali1,''), NULLIF(:hrentr2,''), NULLIF(:hrsali2,''), :estasist)
            ");
            $stmt->execute($datos);
            $_SESSION['msg'] = 'Asistencia registrada correctamente.';
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            // PUNTO 6: Trigger SIGNAL trg_signal_asistencia_insert mostrará error aquí si fecha es futura
            $errores[] = $e->getMessage();
        }
    }
}

$modulo_activo = 'asistencia';
$titulo_pagina = 'Registrar Asistencia';
$breadcrumb    = [['Asistencia', 'lista.php'], ['Registrar', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Registrar Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <?php if (!empty($errores)): ?>
    <div class="alert-flash alert-error">
        <div>❌ <strong>Error:</strong>
            <ul style="margin:4px 0 0 16px;"><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="card-uni" style="max-width:700px;">
        <div class="card-uni-header">
            <h6>📅 Registrar Asistencia</h6>
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
                            <option value="<?= $p['idpers'] ?>" <?= ($datos['idpers']??0)==$p['idpers']?'selected':'' ?>>
                                <?= htmlspecialchars($p['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha *</label>
                        <input type="date" name="fech" value="<?= $datos['fech'] ?? date('Y-m-d') ?>"
                               max="<?= date('Y-m-d') ?>" required>
                        <small style="color:#64748b;">⚠️ No se permite fecha futura (Trigger SIGNAL)</small>
                    </div>
                </div>

                <div style="background:#f8fafc; border-radius:8px; padding:16px; margin-bottom:18px;">
                    <div style="font-size:0.78rem; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:12px;">
                        🕐 Horario Turno 1
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label>Hora Entrada</label>
                            <input type="time" name="hrentr1" value="<?= $datos['hrentr1'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Hora Salida</label>
                            <input type="time" name="hrsali1" value="<?= $datos['hrsali1'] ?? '' ?>">
                        </div>
                    </div>
                </div>

                <div style="background:#f8fafc; border-radius:8px; padding:16px; margin-bottom:18px;">
                    <div style="font-size:0.78rem; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:12px;">
                        🕐 Horario Turno 2 (opcional)
                    </div>
                    <div class="form-row cols-2">
                        <div class="form-group">
                            <label>Hora Entrada 2</label>
                            <input type="time" name="hrentr2" value="<?= $datos['hrentr2'] ?? '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Hora Salida 2</label>
                            <input type="time" name="hrsali2" value="<?= $datos['hrsali2'] ?? '' ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:24px;">
                    <label>Estado de Asistencia *</label>
                    <select name="estasist" required>
                        <option value="">— Seleccione —</option>
                        <option value="Asistio"  <?= ($datos['estasist']??'')==='Asistio'  ?'selected':'' ?>>✅ Asistió</option>
                        <option value="Tardanza" <?= ($datos['estasist']??'')==='Tardanza' ?'selected':'' ?>>⏰ Tardanza</option>
                        <option value="Falta"    <?= ($datos['estasist']??'')==='Falta'    ?'selected':'' ?>>❌ Falta</option>
                        <option value="Licencia" <?= ($datos['estasist']??'')==='Licencia' ?'selected':'' ?>>📋 Licencia</option>
                    </select>
                </div>

                <div style="display:flex; gap:12px; border-top:1px solid #e2e8f0; padding-top:18px;">
                    <button type="submit" class="btn-uni-primary">💾 Registrar</button>
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
