<?php
// asistencia/lista.php
session_start();
require_once '../config/conexion.php';

$filtro_idpers  = intval($_GET['idpers']   ?? 0);
$filtro_fechini = $_GET['fechini'] ?? date('Y-m-01');
$filtro_fechfin = $_GET['fechfin'] ?? date('Y-m-d');

// Lista de personal para el select
$personal_list = $pdo->query("SELECT idpers, CONCAT(nompers,' ',apepers) AS nombre FROM personal_uni ORDER BY apepers")->fetchAll();

$asistencias = [];
$nombre_filtrado = '';

if ($filtro_idpers > 0) {
    // PUNTO 3: SP con 3 parámetros (idpers + rango de fechas) enlazados a controles
    $stmt = $pdo->prepare("CALL sp_buscar_asistencia_rango(:idpers, :fechini, :fechfin)");
    $stmt->execute([':idpers'=>$filtro_idpers, ':fechini'=>$filtro_fechini, ':fechfin'=>$filtro_fechfin]);
    $asistencias = $stmt->fetchAll();
    // Nombre para encabezado
    foreach ($personal_list as $p) {
        if ($p['idpers'] == $filtro_idpers) { $nombre_filtrado = $p['nombre']; break; }
    }
} else {
    // Vista general del día
    $asistencias = $pdo->query("
        SELECT CONCAT(p.nompers,' ',p.apepers) AS nombre_completo,
               a.fech, a.hrentr1, a.hrsali1, a.hrentr2, a.hrsali2, a.estasist
        FROM asistencia a
        JOIN personal_uni p ON a.idpers = p.idpers
        WHERE a.fech BETWEEN '$filtro_fechini' AND '$filtro_fechfin'
        ORDER BY a.fech DESC, p.apepers
    ")->fetchAll();
}

// Función fn_estado_asistencia_hoy (PUNTO 4)
$msg = $_SESSION['msg'] ?? ''; unset($_SESSION['msg']);

$modulo_activo = 'asistencia';
$titulo_pagina = 'Control de Asistencia';
$breadcrumb    = [['Asistencia', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <?php if ($msg): ?>
    <div class="alert-flash <?= strpos($msg,'Error')!==false ? 'alert-error':'alert-success' ?>">
        <?= strpos($msg,'Error')!==false ? '❌':'✅' ?> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <!-- Filtros — PUNTO 3: controles enlazados al SP -->
    <div class="filtros-box">
        <form method="GET">
            <div style="display:grid; grid-template-columns:2fr 1fr 1fr auto; gap:16px; align-items:end;">
                <div>
                    <label>Empleado</label>
                    <select name="idpers">
                        <option value="0">— Todos los empleados —</option>
                        <?php foreach ($personal_list as $p): ?>
                        <option value="<?= $p['idpers'] ?>" <?= $filtro_idpers==$p['idpers'] ? 'selected':'' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Fecha Inicio</label>
                    <input type="date" name="fechini" value="<?= $filtro_fechini ?>">
                </div>
                <div>
                    <label>Fecha Fin</label>
                    <input type="date" name="fechfin" value="<?= $filtro_fechfin ?>">
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn-uni-primary">🔍 Buscar</button>
                    <a href="lista.php" class="btn-uni-secondary">✖</a>
                </div>
            </div>
        </form>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div style="font-size:0.85rem; color:#64748b;">
            <?= count($asistencias) ?> registro(s)
            <?= $nombre_filtrado ? '· Empleado: <strong>'.htmlspecialchars($nombre_filtrado).'</strong>' : '' ?>
        </div>
        <a href="registrar.php" class="btn-uni-primary">➕ Registrar Asistencia</a>
    </div>

    <div class="card-uni">
        <div style="overflow-x:auto;">
            <table class="tabla-uni">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Fecha</th>
                        <th>Entrada 1</th>
                        <th>Salida 1</th>
                        <th>Entrada 2</th>
                        <th>Salida 2</th>
                        <th>Estado</th>
                        <?php if ($filtro_idpers > 0): ?>
                        <th>Msg. Sistema (fn BD)</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($asistencias)): ?>
                    <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:30px;">Sin registros para los filtros seleccionados.</td></tr>
                    <?php else: ?>
                    <?php foreach ($asistencias as $a): ?>
                    <?php
                        $cls = 'badge-activo';
                        if ($a['estasist'] === 'Tardanza') $cls = 'badge-tardanza';
                        if ($a['estasist'] === 'Falta')    $cls = 'badge-falta';
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($a['nombre_completo']) ?></strong></td>
                        <td><?= date('d/m/Y', strtotime($a['fech'])) ?></td>
                        <td><?= $a['hrentr1'] ?? '—' ?></td>
                        <td><?= $a['hrsali1'] ?? '—' ?></td>
                        <td><?= $a['hrentr2'] ?? '—' ?></td>
                        <td><?= $a['hrsali2'] ?? '—' ?></td>
                        <td><span class="badge-uni <?= $cls ?>"><?= htmlspecialchars($a['estasist'] ?? '—') ?></span></td>
                        <?php if ($filtro_idpers > 0): ?>
                        <td>
                            <?php
                                // PUNTO 4: Función de BD para mensaje al usuario
                                if ($a['fech'] === date('Y-m-d')) {
                                    $msg_fn = $pdo->query("SELECT fn_estado_asistencia_hoy($filtro_idpers)")->fetchColumn();
                                    echo '<small style="color:#64748b;">'.$msg_fn.'</small>';
                                } else {
                                    echo '<small style="color:#94a3b8;">—</small>';
                                }
                            ?>
                        </td>
                        <?php endif; ?>
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
