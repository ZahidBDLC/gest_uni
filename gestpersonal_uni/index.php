<?php
// index.php - Dashboard principal

session_start();

// ✅ Bloqueo: si no ingresó como admin, primero registrar asistencia
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: asistencia/checkin.php");
    exit;
}

require_once 'config/conexion.php';

// ── KPIs principales ──────────────────────────────────────────
$total_personal    = $pdo->query("SELECT COUNT(*) FROM personal_uni")->fetchColumn();
$docentes_activos  = $pdo->query("SELECT COUNT(*) FROM personal_uni WHERE tipo_personal='docente' AND estlab='Activo'")->fetchColumn();
$admin_activos     = $pdo->query("SELECT COUNT(*) FROM personal_uni WHERE tipo_personal='administrativo' AND estlab='Activo'")->fetchColumn();
$contratos_vencer  = $pdo->query("SELECT COUNT(*) FROM contrato WHERE fechfin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
$tardanzas_hoy     = $pdo->query("SELECT COUNT(*) FROM asistencia WHERE estasist='Tardanza' AND fech=CURDATE()")->fetchColumn();
$faltas_hoy        = $pdo->query("SELECT COUNT(*) FROM asistencia WHERE estasist='Falta' AND fech=CURDATE()")->fetchColumn();
$pagos_mes         = $pdo->query("SELECT COALESCE(SUM(montpag),0) FROM pagos WHERE MONTH(fechpag)=MONTH(CURDATE()) AND YEAR(fechpag)=YEAR(CURDATE())")->fetchColumn();

// ── Últimas auditorías ────────────────────────────────────────
$auditorias = $pdo->query("SELECT * FROM auditoria_bd ORDER BY fecha_hora DESC LIMIT 8")->fetchAll();

// ── Personal reciente ─────────────────────────────────────────
$personal_reciente = $pdo->query("
    SELECT p.idpers, CONCAT(p.nompers,' ',p.apepers) AS nombre,
           p.tipo_personal, p.departamento, p.estlab, p.fechingr
    FROM personal_uni p
    ORDER BY p.fechingr DESC LIMIT 6
")->fetchAll();

// ── Contratos por vencer (próximos 60 días) ───────────────────
$contratos_proximos = $pdo->query("
    SELECT CONCAT(p.nompers,' ',p.apepers) AS nombre,
           tc.nomcontr, c.suelpers, c.fechfin,
           DATEDIFF(c.fechfin, CURDATE()) AS dias_restantes
    FROM contrato c
    JOIN personal_uni p ON c.idpers = p.idpers
    JOIN tipo_contrato tc ON c.idtpcontr = tc.idtpcontr
    WHERE c.fechfin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)
    ORDER BY c.fechfin ASC LIMIT 5
")->fetchAll();

$modulo_activo  = 'dashboard';
$titulo_pagina  = 'Dashboard — Panel de Control';
$breadcrumb     = [['Dashboard', '']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <?php include 'includes/header.php'; ?>

    <div class="page-content">

        <!-- KPI Grid -->
        <div class="kpi-grid">
            <div class="kpi-card kpi-blue">
                <div class="kpi-label">Total Personal</div>
                <div class="kpi-value"><?= $total_personal ?></div>
                <div class="kpi-sub">Registrados en el sistema</div>
            </div>
            <div class="kpi-card kpi-green">
                <div class="kpi-label">Docentes Activos</div>
                <div class="kpi-value"><?= $docentes_activos ?></div>
                <div class="kpi-sub"><?= $admin_activos ?> administrativos activos</div>
            </div>
            <div class="kpi-card kpi-orange">
                <div class="kpi-label">Contratos por Vencer</div>
                <div class="kpi-value"><?= $contratos_vencer ?></div>
                <div class="kpi-sub">En los próximos 30 días</div>
            </div>
            <div class="kpi-card kpi-red">
                <div class="kpi-label">Alertas Asistencia Hoy</div>
                <div class="kpi-value"><?= $tardanzas_hoy + $faltas_hoy ?></div>
                <div class="kpi-sub"><?= $tardanzas_hoy ?> tardanzas · <?= $faltas_hoy ?> faltas</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:22px; margin-bottom:22px;">

            <!-- Personal reciente -->
            <div class="card-uni">
                <div class="card-uni-header">
                    <h6>👥 Personal Registrado Recientemente</h6>
                    <a href="personal/lista.php" class="btn-uni-secondary" style="font-size:0.78rem; padding:5px 12px;">Ver todo</a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="tabla-uni">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Departamento</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($personal_reciente)): ?>
                            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px;">Sin registros aún</td></tr>
                            <?php else: ?>
                            <?php foreach ($personal_reciente as $p): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                                <td>
                                    <span class="badge-uni <?= $p['tipo_personal']=='docente' ? 'badge-docente' : 'badge-admin' ?>">
                                        <?= ucfirst($p['tipo_personal']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($p['departamento'] ?? '—') ?></td>
                                <td>
                                    <span class="badge-uni <?= $p['estlab']=='Activo' ? 'badge-activo' : 'badge-inactivo' ?>">
                                        <?= htmlspecialchars($p['estlab'] ?? '—') ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contratos por vencer -->
            <div class="card-uni">
                <div class="card-uni-header">
                    <h6>⚠️ Contratos Próximos a Vencer</h6>
                    <a href="contratos/lista.php" class="btn-uni-secondary" style="font-size:0.78rem; padding:5px 12px;">Ver todo</a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="tabla-uni">
                        <thead>
                            <tr>
                                <th>Personal</th>
                                <th>Tipo Contrato</th>
                                <th>Vence</th>
                                <th>Días</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($contratos_proximos)): ?>
                            <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:20px;">Sin contratos próximos a vencer</td></tr>
                            <?php else: ?>
                            <?php foreach ($contratos_proximos as $c): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars($c['nomcontr']) ?></td>
                                <td><?= date('d/m/Y', strtotime($c['fechfin'])) ?></td>
                                <td>
                                    <span class="badge-uni <?= $c['dias_restantes'] <= 15 ? 'badge-falta' : 'badge-vencer' ?>">
                                        <?= $c['dias_restantes'] ?> días
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Auditoría BD (Triggers en acción) -->
        <div class="card-uni">
            <div class="card-uni-header">
                <h6>🔍 Registro de Auditoría — Actividad de Triggers</h6>
                <a href="auditoria/lista.php" class="btn-uni-secondary" style="font-size:0.78rem; padding:5px 12px;">Ver todo</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="tabla-uni">
                    <thead>
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Tabla</th>
                            <th>Acción</th>
                            <th>Campo Modificado</th>
                            <th>Valor Anterior</th>
                            <th>Valor Nuevo</th>
                            <th>Usuario BD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($auditorias)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;color:#94a3b8;padding:24px;">
                                Sin registros de auditoría aún. Los triggers registrarán automáticamente cambios en campos sensibles.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($auditorias as $a): ?>
                        <tr>
                            <td><?= $a['fecha_hora'] ?></td>
                            <td><span class="badge-uni badge-docente"><?= htmlspecialchars($a['tabla_afect']) ?></span></td>
                            <td>
                                <?php
                                    $colores = ['INSERT'=>'badge-activo','UPDATE'=>'badge-vencer','DELETE'=>'badge-falta'];
                                    $clase   = $colores[$a['accion']] ?? 'badge-uni';
                                ?>
                                <span class="badge-uni <?= $clase ?>"><?= $a['accion'] ?></span>
                            </td>
                            <td><?= htmlspecialchars($a['campo_mod']) ?></td>
                            <td><?= htmlspecialchars($a['valor_ant'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($a['valor_nuevo'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($a['usuario']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /page-content -->
</div><!-- /main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>