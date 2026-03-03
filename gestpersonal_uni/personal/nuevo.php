<?php
// personal/nuevo.php
session_start();
require_once '../config/conexion.php';

$errores = [];
$datos   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $datos = [
        'dni'           => trim($_POST['dni']           ?? ''),
        'nompers'       => trim($_POST['nompers']        ?? ''),
        'apepers'       => trim($_POST['apepers']        ?? ''),
        'fechnac'       => $_POST['fechnac']             ?? '',
        'dirpers'       => trim($_POST['dirpers']        ?? ''),
        'telfpers'      => trim($_POST['telfpers']       ?? ''),
        'correopers'    => trim($_POST['correopers']     ?? ''),
        'profpers'      => trim($_POST['profpers']       ?? ''),
        'fechingr'      => $_POST['fechingr']            ?? '',
        'tipo_personal' => $_POST['tipo_personal']       ?? '',
        'departamento'  => trim($_POST['departamento']   ?? ''),
        'escalafon_doc' => trim($_POST['escalafon_doc']  ?? ''),
        'estlab'        => $_POST['estlab']              ?? 'Activo',
    ];

    // Validaciones básicas en PHP
    if (strlen($datos['dni']) !== 8)          $errores[] = 'El DNI debe tener exactamente 8 dígitos.';
    if (empty($datos['nompers']))             $errores[] = 'El nombre es obligatorio.';
    if (empty($datos['apepers']))             $errores[] = 'El apellido es obligatorio.';
    if (empty($datos['telfpers']))            $errores[] = 'El teléfono es obligatorio.';
    if (empty($datos['correopers']))          $errores[] = 'El correo es obligatorio.';
    if (empty($datos['profpers']))            $errores[] = 'La profesión es obligatoria.';
    if (empty($datos['tipo_personal']))       $errores[] = 'El tipo de personal es obligatorio.';

    if (empty($errores)) {
        try {
            // PUNTO 2: Llamada al SP de inserción
            $stmt = $pdo->prepare("CALL sp_insertar_personal(
                :dni, :nompers, :apepers, :fechnac, :dirpers,
                :telfpers, :correopers, :profpers, :fechingr,
                :tipo_personal, :departamento, :escalafon_doc, :estlab
            )");
            $stmt->execute($datos);
            $result = $stmt->fetch();
            $_SESSION['msg'] = 'Empleado registrado correctamente. ID asignado: ' . ($result['nuevo_idpers'] ?? '');
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            // Los Triggers SIGNAL muestran sus mensajes aquí (PUNTO 6)
            $errores[] = 'Error BD: ' . $e->getMessage();
        }
    }
}

// Cargar lista de departamentos únicos existentes
$deptos = $pdo->query("SELECT DISTINCT departamento FROM personal_uni WHERE departamento IS NOT NULL ORDER BY departamento")->fetchAll(PDO::FETCH_COLUMN);

$modulo_activo = 'personal';
$titulo_pagina = 'Registrar Nuevo Empleado';
$breadcrumb    = [['Personal', 'lista.php'], ['Nuevo Empleado', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Nuevo Empleado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <?php if (!empty($errores)): ?>
    <div class="alert-flash alert-error">
        <div>
            <strong>❌ Se encontraron errores:</strong>
            <ul style="margin:6px 0 0 16px;">
                <?php foreach ($errores as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <div class="card-uni" style="max-width:900px;">
        <div class="card-uni-header">
            <h6>👤 Datos del Nuevo Empleado</h6>
            <a href="lista.php" class="btn-uni-secondary">← Volver</a>
        </div>
        <div class="card-uni-body">
            <form method="POST" class="form-uni">

                <!-- Sección: Datos personales -->
                <div style="margin-bottom:8px; padding-bottom:8px; border-bottom:1px solid #e2e8f0;">
                    <strong style="color:#475569; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">
                        📋 Datos Personales
                    </strong>
                </div>

                <div class="form-row cols-3" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>DNI *</label>
                        <input type="text" name="dni" maxlength="8" placeholder="12345678"
                               value="<?= htmlspecialchars($datos['dni'] ?? '') ?>"
                               pattern="[0-9]{8}" title="8 dígitos numéricos" required>
                    </div>
                    <div class="form-group">
                        <label>Nombres *</label>
                        <input type="text" name="nompers" placeholder="Ej: Juan Carlos"
                               value="<?= htmlspecialchars($datos['nompers'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Apellidos *</label>
                        <input type="text" name="apepers" placeholder="Ej: García López"
                               value="<?= htmlspecialchars($datos['apepers'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row cols-3" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" name="fechnac" value="<?= htmlspecialchars($datos['fechnac'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Teléfono *</label>
                        <input type="text" name="telfpers" maxlength="9" placeholder="987654321"
                               value="<?= htmlspecialchars($datos['telfpers'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico *</label>
                        <input type="email" name="correopers" placeholder="empleado@universidad.edu.pe"
                               value="<?= htmlspecialchars($datos['correopers'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:18px;">
                    <label>Dirección</label>
                    <input type="text" name="dirpers" placeholder="Av. Universitaria 123, Lima"
                           value="<?= htmlspecialchars($datos['dirpers'] ?? '') ?>">
                </div>

                <!-- Sección: Datos laborales -->
                <div style="margin-bottom:8px; padding-bottom:8px; border-bottom:1px solid #e2e8f0; margin-top:8px;">
                    <strong style="color:#475569; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px;">
                        🏛️ Datos Laborales
                    </strong>
                </div>

                <div class="form-row cols-2" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Profesión / Titulación *</label>
                        <input type="text" name="profpers" placeholder="Ej: Ing. de Sistemas, Lic. en Educación"
                               value="<?= htmlspecialchars($datos['profpers'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Ingreso</label>
                        <input type="date" name="fechingr" value="<?= htmlspecialchars($datos['fechingr'] ?? date('Y-m-d')) ?>">
                    </div>
                </div>

                <div class="form-row cols-3" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Tipo de Personal *</label>
                        <select name="tipo_personal" id="tipo_personal" onchange="toggleEscalafon()" required>
                            <option value="">— Seleccione —</option>
                            <option value="docente"        <?= ($datos['tipo_personal'] ?? '')==='docente'        ? 'selected':'' ?>>Docente</option>
                            <option value="administrativo" <?= ($datos['tipo_personal'] ?? '')==='administrativo' ? 'selected':'' ?>>Administrativo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Departamento / Facultad</label>
                        <input type="text" name="departamento" list="lista_deptos"
                               placeholder="Ej: Facultad de Ingeniería"
                               value="<?= htmlspecialchars($datos['departamento'] ?? '') ?>">
                        <datalist id="lista_deptos">
                            <?php foreach ($deptos as $d): ?>
                            <option value="<?= htmlspecialchars($d) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group" id="campo_escalafon" style="display:none;">
                        <label>Escalafón Docente</label>
                        <select name="escalafon_doc">
                            <option value="">— Seleccione —</option>
                            <option value="Principal"  <?= ($datos['escalafon_doc'] ?? '')==='Principal'  ? 'selected':'' ?>>Principal</option>
                            <option value="Asociado"   <?= ($datos['escalafon_doc'] ?? '')==='Asociado'   ? 'selected':'' ?>>Asociado</option>
                            <option value="Auxiliar"   <?= ($datos['escalafon_doc'] ?? '')==='Auxiliar'   ? 'selected':'' ?>>Auxiliar</option>
                            <option value="Hora cátedra" <?= ($datos['escalafon_doc'] ?? '')==='Hora cátedra' ? 'selected':'' ?>>Hora cátedra</option>
                        </select>
                    </div>
                </div>

                <div class="form-row cols-2" style="margin-bottom:28px;">
                    <div class="form-group">
                        <label>Estado Laboral</label>
                        <select name="estlab">
                            <option value="Activo"   <?= ($datos['estlab'] ?? 'Activo')==='Activo'   ? 'selected':'' ?>>Activo</option>
                            <option value="Inactivo" <?= ($datos['estlab'] ?? '')==='Inactivo' ? 'selected':'' ?>>Inactivo</option>
                            <option value="Licencia" <?= ($datos['estlab'] ?? '')==='Licencia' ? 'selected':'' ?>>Con Licencia</option>
                            <option value="Cesado"   <?= ($datos['estlab'] ?? '')==='Cesado'   ? 'selected':'' ?>>Cesado</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; gap:12px; border-top:1px solid #e2e8f0; padding-top:20px;">
                    <button type="submit" class="btn-uni-primary">💾 Guardar Empleado</button>
                    <a href="lista.php" class="btn-uni-secondary">Cancelar</a>
                </div>

            </form>
        </div>
    </div>

</div>
</div>

<script>
function toggleEscalafon() {
    const tipo = document.getElementById('tipo_personal').value;
    document.getElementById('campo_escalafon').style.display = tipo === 'docente' ? 'block' : 'none';
}
// Ejecutar al cargar si ya hay un valor seleccionado
toggleEscalafon();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
