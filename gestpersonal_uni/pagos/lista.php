<?php
// pagos/lista.php
session_start();
require_once '../config/conexion.php';

$msg = $_SESSION['msg'] ?? ''; unset($_SESSION['msg']);

// Filtros (PUNTO 3: SP buscar_pagos_tipo_fecha)
$filtro_tipo  = intval($_GET['tipo'] ?? 0);
$filtro_ini   = $_GET['fechini'] ?? date('Y-m-01');
$filtro_fin   = $_GET['fechfin'] ?? date('Y-m-d');

$tipos_pago = $pdo->query("SELECT * FROM tipo_pago ORDER BY nompag")->fetchAll();

if ($filtro_tipo > 0) {
    $stmt = $pdo->prepare("CALL sp_buscar_pagos_tipo_fecha(:tipo, :ini, :fin)");
    $stmt->execute([':tipo'=>$filtro_tipo, ':ini'=>$filtro_ini, ':fin'=>$filtro_fin]);
    $pagos = $stmt->fetchAll();
} else {
    // Vista vw_pagos_personal (PUNTO 1)
    $pagos = $pdo->query("SELECT * FROM vw_pagos_personal ORDER BY fecha_pago DESC LIMIT 100")->fetchAll();
}

$modulo_activo = 'pagos';
$titulo_pagina = 'Pagos y AFP';
$breadcrumb    = [['Pagos', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <?php if ($msg): ?>
    <div class="alert-flash <?= strpos($msg,'Error')!==false?'alert-error':'alert-success' ?>">
        <?= strpos($msg,'Error')!==false?'❌':'✅' ?> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <!-- Filtros PUNTO 3 -->
    <div class="filtros-box">
        <form method="GET">
            <div style="display:grid; grid-template-columns:1.5fr 1fr 1fr auto; gap:16px; align-items:end;">
                <div>
                    <label>Tipo de Pago</label>
                    <select name="tipo">
                        <option value="0">— Todos —</option>
                        <?php foreach ($tipos_pago as $tp): ?>
                        <option value="<?= $tp['idtppag'] ?>" <?= $filtro_tipo==$tp['idtppag']?'selected':'' ?>>
                            <?= htmlspecialchars($tp['nompag']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Desde</label>
                    <input type="date" name="fechini" value="<?= $filtro_ini ?>">
                </div>
                <div>
                    <label>Hasta</label>
                    <input type="date" name="fechfin" value="<?= $filtro_fin ?>">
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn-uni-primary">🔍 Filtrar</button>
                    <a href="lista.php" class="btn-uni-secondary">✖</a>
                </div>
            </div>
        </form>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div style="font-size:0.85rem; color:#64748b;">
            <?= count($pagos) ?> pago(s) ·
            Total: <strong>S/ <?= number_format(array_sum(array_column($pagos,'monto')),2) ?></strong>
        </div>
        <a href="nuevo.php" class="btn-uni-primary">➕ Registrar Pago</a>
    </div>

    <div class="card-uni">
        <div style="overflow-x:auto;">
            <table class="tabla-uni">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Tipo Personal</th>
                        <th>Departamento</th>
                        <th>Tipo Pago</th>
                        <th>Monto</th>
                        <th>Fecha Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pagos)): ?>
                    <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:30px;">Sin pagos registrados.</td></tr>
                    <?php else: ?>
                    <?php foreach ($pagos as $p): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($p['nombre_completo']) ?></strong></td>
                        <td><span class="badge-uni <?= $p['tipo_personal']==='docente'?'badge-docente':'badge-admin' ?>"><?= ucfirst($p['tipo_personal']) ?></span></td>
                        <td><?= htmlspecialchars($p['departamento'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['tipo_pago']) ?></td>
                        <td><strong>S/ <?= number_format($p['monto'],2) ?></strong></td>
                        <td><?= $p['fecha_pago'] ?></td>
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
