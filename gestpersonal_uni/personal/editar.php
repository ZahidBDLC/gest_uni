<?php
// personal/editar.php
session_start();
require_once '../config/conexion.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

$errores = [];

// Cargar datos actuales
$stmt = $pdo->prepare("SELECT * FROM personal_uni WHERE idpers = ?");
$stmt->execute([$id]);
$empleado = $stmt->fetch();
if (!$empleado) { header('Location: lista.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'idpers'       => $id,
        'telfpers'     => trim($_POST['telfpers']     ?? ''),
        'correopers'   => trim($_POST['correopers']   ?? ''),
        'dirpers'      => trim($_POST['dirpers']      ?? ''),
        'departamento' => trim($_POST['departamento'] ?? ''),
        'estlab'       => $_POST['estlab']            ?? '',
        'escalafon_doc'=> trim($_POST['escalafon_doc']?? ''),
    ];

    if (empty($datos['telfpers']))  $errores[] = 'El teléfono es obligatorio.';
    if (empty($datos['correopers']))$errores[] = 'El correo es obligatorio.';

    if (empty($errores)) {
        try {
            // PUNTO 2: SP de actualización
            $stmt2 = $pdo->prepare("CALL sp_actualizar_personal(:idpers, :telfpers, :correopers, :dirpers, :departamento, :estlab, :escalafon_doc)");
            $stmt2->execute($datos);
            $_SESSION['msg'] = 'Datos del empleado actualizados correctamente.';
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $errores[] = 'Error BD: ' . $e->getMessage();
        }
    }
    // Actualizar vista previa con los datos del POST
    $empleado = array_merge($empleado, $datos);
}

$deptos = $pdo->query("SELECT DISTINCT departamento FROM personal_uni WHERE departamento IS NOT NULL ORDER BY departamento")->fetchAll(PDO::FETCH_COLUMN);

$modulo_activo = 'personal';
$titulo_pagina = 'Editar Empleado';
$breadcrumb    = [['Personal', 'lista.php'], ['Editar', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Editar Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <?php if (!empty($errores)): ?>
    <div class="alert-flash alert-error">
        <div>❌ <strong>Errores:</strong>
            <ul style="margin:4px 0 0 16px;"><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- Info del empleado (solo lectura) -->
    <div class="alert-flash" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; margin-bottom:20px;">
        ℹ️ Editando: <strong><?= htmlspecialchars($empleado['nompers'].' '.$empleado['apepers']) ?></strong>
        &nbsp;|&nbsp; DNI: <code><?= htmlspecialchars($empleado['dni']) ?></code>
        &nbsp;|&nbsp; <?= ucfirst($empleado['tipo_personal']) ?>
        &nbsp;|&nbsp; Ingresó: <?= $empleado['fechingr'] ? date('d/m/Y', strtotime($empleado['fechingr'])) : '—' ?>
    </div>

    <div class="card-uni" style="max-width:800px;">
        <div class="card-uni-header">
            <h6>✏️ Actualizar Datos de Contacto y Estado</h6>
            <a href="lista.php" class="btn-uni-secondary">← Volver</a>
        </div>
        <div class="card-uni-body">
            <form method="POST" class="form-uni">

                <div class="form-row cols-2" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Teléfono *</label>
                        <input type="text" name="telfpers" maxlength="9"
                               value="<?= htmlspecialchars($empleado['telfpers']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico *</label>
                        <input type="email" name="correopers"
                               value="<?= htmlspecialchars($empleado['correopers']) ?>" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:18px;">
                    <label>Dirección</label>
                    <input type="text" name="dirpers"
                           value="<?= htmlspecialchars($empleado['dirpers'] ?? '') ?>">
                </div>

                <div class="form-row cols-3" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Departamento / Facultad</label>
                        <input type="text" name="departamento" list="lista_deptos"
                               value="<?= htmlspecialchars($empleado['departamento'] ?? '') ?>">
                        <datalist id="lista_deptos">
                            <?php foreach ($deptos as $d): ?>
                            <option value="<?= htmlspecialchars($d) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Estado Laboral</label>
                        <select name="estlab">
                            <?php foreach (['Activo','Inactivo','Licencia','Cesado'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= $empleado['estlab']===$opt ? 'selected':'' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($empleado['tipo_personal'] === 'docente'): ?>
                    <div class="form-group">
                        <label>Escalafón Docente</label>
                        <select name="escalafon_doc">
                            <?php foreach (['Principal','Asociado','Auxiliar','Hora cátedra'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($empleado['escalafon_doc'] ?? '')===$opt ? 'selected':'' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="escalafon_doc" value="">
                    <?php endif; ?>
                </div>

                <div style="display:flex; gap:12px; border-top:1px solid #e2e8f0; padding-top:20px;">
                    <button type="submit" class="btn-uni-primary">💾 Guardar Cambios</button>
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
