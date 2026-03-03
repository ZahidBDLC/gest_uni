<?php
// auditoria/lista.php
require_once '../config/conexion.php';

// Filtros
$filtro_tabla  = $_GET['tabla']  ?? '';
$filtro_accion = $_GET['accion'] ?? '';

$where = "WHERE 1=1";
$params = [];
if ($filtro_tabla)  { $where .= " AND tabla_afect = :tabla";  $params[':tabla']  = $filtro_tabla; }
if ($filtro_accion) { $where .= " AND accion = :accion";      $params[':accion'] = $filtro_accion; }

$stmt = $pdo->prepare("SELECT * FROM auditoria_bd $where ORDER BY fecha_hora DESC LIMIT 200");
$stmt->execute($params);
$registros = $stmt->fetchAll();

// Tablas y acciones disponibles
$tablas  = $pdo->query("SELECT DISTINCT tabla_afect FROM auditoria_bd ORDER BY tabla_afect")->fetchAll(PDO::FETCH_COLUMN);
$acciones= ['INSERT','UPDATE','DELETE'];

$modulo_activo = 'auditoria';
$titulo_pagina = 'Registro de Auditoría de BD';
$breadcrumb    = [['Auditoría', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Auditoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <div class="alert-flash" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; margin-bottom:20px;">
        🔍 Esta tabla es alimentada automáticamente por los <strong>Triggers de Auditoría</strong> (Punto 5 de la tarea) cada vez que se modifica un campo sensible en la BD.
    </div>

    <div class="filtros-box">
        <form method="GET" style="display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">
            <div>
                <label style="font-size:0.78rem; font-weight:700; color:#475569; text-transform:uppercase; display:block; margin-bottom:5px;">Tabla</label>
                <select name="tabla" style="border:1px solid #e2e8f0; border-radius:7px; padding:8px 12px; font-size:0.85rem;">
                    <option value="">— Todas —</option>
                    <?php foreach ($tablas as $t): ?>
                    <option value="<?= $t ?>" <?= $filtro_tabla===$t?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:0.78rem; font-weight:700; color:#475569; text-transform:uppercase; display:block; margin-bottom:5px;">Acción</label>
                <select name="accion" style="border:1px solid #e2e8f0; border-radius:7px; padding:8px 12px; font-size:0.85rem;">
                    <option value="">— Todas —</option>
                    <?php foreach ($acciones as $a): ?>
                    <option value="<?= $a ?>" <?= $filtro_accion===$a?'selected':'' ?>><?= $a ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-uni-primary">🔍 Filtrar</button>
            <a href="lista.php" class="btn-uni-secondary">✖ Limpiar</a>
        </form>
    </div>

    <div style="font-size:0.85rem; color:#64748b; margin-bottom:12px;"><?= count($registros) ?> registro(s) de auditoría</div>

    <div class="card-uni">
        <div style="overflow-x:auto;">
            <table class="tabla-uni">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha / Hora</th>
                        <th>Tabla Afectada</th>
                        <th>Acción</th>
                        <th>ID Registro</th>
                        <th>Campo</th>
                        <th>Valor Anterior</th>
                        <th>Valor Nuevo</th>
                        <th>Usuario BD</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center;color:#94a3b8;padding:40px;">
                            Sin registros de auditoría aún.<br>
                            <small>Los triggers registrarán cambios automáticamente al modificar sueldos, estado laboral, pagos o eliminar contratos.</small>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($registros as $r):
                        $cls_acc = ['INSERT'=>'badge-activo','UPDATE'=>'badge-tardanza','DELETE'=>'badge-falta'];
                        $cls = $cls_acc[$r['accion']] ?? 'badge-uni';
                    ?>
                    <tr>
                        <td><?= $r['idaudit'] ?></td>
                        <td><?= $r['fecha_hora'] ?></td>
                        <td><span class="badge-uni badge-docente"><?= htmlspecialchars($r['tabla_afect']) ?></span></td>
                        <td><span class="badge-uni <?= $cls ?>"><?= $r['accion'] ?></span></td>
                        <td><?= $r['idregistro'] ?></td>
                        <td><code><?= htmlspecialchars($r['campo_mod']) ?></code></td>
                        <td style="color:#dc2626;"><?= htmlspecialchars($r['valor_ant'] ?? '—') ?></td>
                        <td style="color:#16a34a;"><strong><?= htmlspecialchars($r['valor_nuevo'] ?? '—') ?></strong></td>
                        <td><small><?= htmlspecialchars($r['usuario']) ?></small></td>
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
