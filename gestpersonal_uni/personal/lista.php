<?php
// personal/lista.php
require_once '../config/conexion.php';

// ── Filtros GET (enlazados al SP sp_buscar_personal_tipo_estado) ──
$filtro_tipo  = $_GET['tipo']  ?? '';
$filtro_estab = $_GET['estlab'] ?? '';

// Mensaje flash
$msg = $_SESSION['msg'] ?? '';
if (!empty($msg)) unset($_SESSION['msg']);

// ── Consulta: con filtros usa el SP, sin filtros trae todo ────────
if ($filtro_tipo !== '' && $filtro_estab !== '') {
    // PUNTO 3 de la tarea: SP con 2 parámetros enlazados a controles del formulario
    $stmt = $pdo->prepare("CALL sp_buscar_personal_tipo_estado(:tipo, :estlab)");
    $stmt->execute([':tipo' => $filtro_tipo, ':estlab' => $filtro_estab]);
    $personal = $stmt->fetchAll();
} else {
    // Vista con JOIN (PUNTO 1 de la tarea)
    $personal = $pdo->query("
        SELECT p.idpers, p.dni,
               CONCAT(p.nompers,' ',p.apepers) AS nombre_completo,
               p.tipo_personal, p.departamento, p.escalafon_doc,
               p.estlab, p.fechingr,
               tc.nomcontr AS tipo_contrato,
               c.suelpers AS sueldo, c.fechfin AS fin_contrato
        FROM personal_uni p
        LEFT JOIN contrato c ON p.idpers = c.idpers
        LEFT JOIN tipo_contrato tc ON c.idtpcontr = tc.idtpcontr
        ORDER BY p.apepers, p.nompers
    ")->fetchAll();
}

// Función fn_estado_contrato (PUNTO 4): se llama por fila
function estadoContrato($pdo, $idpers) {
    return $pdo->query("SELECT fn_estado_contrato($idpers)")->fetchColumn();
}

$modulo_activo = 'personal';
$titulo_pagina = 'Gestión de Personal';
$breadcrumb    = [['Personal', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Personal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <?php if (!empty($msg)): ?>
    <div class="alert-flash <?= strpos($msg,'Error') !== false ? 'alert-error' : 'alert-success' ?>">
        <?= strpos($msg,'Error') !== false ? '❌' : '✅' ?> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <!-- Filtros — PUNTO 3: controles enlazados al SP -->
    <div class="filtros-box">
        <form method="GET" action="">
            <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:16px; align-items:end;">
                <div>
                    <label>Tipo de Personal</label>
                    <select name="tipo">
                        <option value="">— Todos —</option>
                        <option value="docente"        <?= $filtro_tipo=='docente'        ? 'selected':'' ?>>Docente</option>
                        <option value="administrativo" <?= $filtro_tipo=='administrativo' ? 'selected':'' ?>>Administrativo</option>
                    </select>
                </div>
                <div>
                    <label>Estado Laboral</label>
                    <select name="estlab">
                        <option value="">— Todos —</option>
                        <option value="Activo"    <?= $filtro_estab=='Activo'    ? 'selected':'' ?>>Activo</option>
                        <option value="Inactivo"  <?= $filtro_estab=='Inactivo'  ? 'selected':'' ?>>Inactivo</option>
                        <option value="Licencia"  <?= $filtro_estab=='Licencia'  ? 'selected':'' ?>>Con Licencia</option>
                        <option value="Cesado"    <?= $filtro_estab=='Cesado'    ? 'selected':'' ?>>Cesado</option>
                    </select>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn-uni-primary">🔍 Filtrar</button>
                    <a href="lista.php" class="btn-uni-secondary">✖ Limpiar</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Cabecera con botón nuevo -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div style="font-size:0.85rem; color:#64748b;">
            <?= count($personal) ?> registro(s) encontrado(s)
        </div>
        <a href="nuevo.php" class="btn-uni-primary">➕ Nuevo Empleado</a>
    </div>

    <!-- Tabla principal -->
    <div class="card-uni">
        <div style="overflow-x:auto;">
            <table class="tabla-uni">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>DNI</th>
                        <th>Nombre Completo</th>
                        <th>Tipo</th>
                        <th>Departamento / Facultad</th>
                        <th>Escalafón</th>
                        <th>Estado Laboral</th>
                        <th>Estado Contrato</th>
                        <th>Sueldo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($personal)): ?>
                    <tr>
                        <td colspan="10" style="text-align:center; color:#94a3b8; padding:30px;">
                            No se encontraron registros con los filtros aplicados.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($personal as $p): ?>
                    <?php
                        // PUNTO 4: Llamada a función de BD para mostrar estado del contrato
                        $est_contrato = estadoContrato($pdo, $p['idpers']);
                        $clase_contrato = 'badge-activo';
                        if (strpos($est_contrato, 'VENCIDO')     !== false) $clase_contrato = 'badge-falta';
                        if (strpos($est_contrato, 'VENCER')      !== false) $clase_contrato = 'badge-vencer';
                        if (strpos($est_contrato, 'Sin contrato') !== false) $clase_contrato = 'badge-inactivo';
                    ?>
                    <tr>
                        <td><?= $p['idpers'] ?></td>
                        <td><code><?= htmlspecialchars($p['dni'] ?? '—') ?></code></td>
                        <td><strong><?= htmlspecialchars($p['nombre_completo']) ?></strong></td>
                        <td>
                            <span class="badge-uni <?= $p['tipo_personal']=='docente' ? 'badge-docente' : 'badge-admin' ?>">
                                <?= ucfirst($p['tipo_personal']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($p['departamento'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['escalafon_doc'] ?? '—') ?></td>
                        <td>
                            <span class="badge-uni <?= $p['estlab']=='Activo' ? 'badge-activo' : 'badge-inactivo' ?>">
                                <?= htmlspecialchars($p['estlab'] ?? '—') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge-uni <?= $clase_contrato ?>" title="<?= htmlspecialchars($est_contrato) ?>">
                                <?= htmlspecialchars($est_contrato) ?>
                            </span>
                        </td>
                        <td>
                            <?= isset($p['sueldo']) ? 'S/ '.number_format($p['sueldo'],2) : '—' ?>
                        </td>
                        <td>
                            <a href="editar.php?id=<?= $p['idpers'] ?>" class="btn-uni-edit">✏️ Editar</a>
                            <a href="eliminar.php?id=<?= $p['idpers'] ?>"
                               class="btn-uni-danger"
                               onclick="return confirm('¿Eliminar a <?= addslashes($p['nombre_completo']) ?>? Esta acción verificará si tiene contrato activo.')">
                               🗑️ Eliminar
                            </a>
                        </td>
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
