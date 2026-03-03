<?php
// capacitaciones/nuevo.php
session_start();
require_once '../config/conexion.php';

$errores = [];
$datos   = [];

$personal_list = $pdo->query("SELECT idpers, CONCAT(nompers,' ',apepers) AS nombre FROM personal_uni WHERE estlab='Activo' ORDER BY apepers")->fetchAll();
$capacitadores = $pdo->query("SELECT * FROM personal_capacitador ORDER BY apeperscap")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'idperscap' => intval($_POST['idperscap'] ?? 0),
        'idpers'    => intval($_POST['idpers']    ?? 0),
        'fechcap'   => $_POST['fechcap']          ?? '',
        'temcap'    => trim($_POST['temcap']      ?? ''),
        // Evaluación opcional
        'pntj'      => $_POST['pntj'] !== '' ? intval($_POST['pntj']) : null,
        'coment'    => trim($_POST['coment'] ?? ''),
        'fecheval'  => $_POST['fecheval'] ?? null,
    ];

    if (!$datos['idperscap']) $errores[] = 'Seleccione capacitador.';
    if (!$datos['idpers'])    $errores[] = 'Seleccione empleado.';
    if (empty($datos['temcap'])) $errores[] = 'El tema es obligatorio.';

    if (empty($errores)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO capacitacion(idperscap, idpers, fechcap, temcap) VALUES(:idperscap,:idpers,:fechcap,:temcap)");
            $stmt->execute(['idperscap'=>$datos['idperscap'],'idpers'=>$datos['idpers'],'fechcap'=>$datos['fechcap'],'temcap'=>$datos['temcap']]);
            $idcap = $pdo->lastInsertId();

            if ($datos['pntj'] !== null) {
                // PUNTO 6: trg_signal_evaluacion_insert valida puntaje 0-100
                $stmt2 = $pdo->prepare("INSERT INTO evaluacion(idpers,idcap,pntj,fecheval,coment) VALUES(:idpers,:idcap,:pntj,NULLIF(:fecheval,''),:coment)");
                $stmt2->execute(['idpers'=>$datos['idpers'],'idcap'=>$idcap,'pntj'=>$datos['pntj'],'fecheval'=>$datos['fecheval'],'coment'=>$datos['coment']]);
            }

            $pdo->commit();
            $_SESSION['msg'] = 'Capacitación registrada correctamente.';
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errores[] = $e->getMessage();
        }
    }
}

$modulo_activo = 'capacitaciones';
$titulo_pagina = 'Nueva Capacitación';
$breadcrumb    = [['Capacitaciones','lista.php'],['Nueva','']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniHR | Nueva Capacitación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/sidebar.php'; ?>
<div class="main-content">
<?php include '../includes/header.php'; ?>
<div class="page-content">

    <?php if (!empty($errores)): ?>
    <div class="alert-flash alert-error">❌ <?= implode('<br>', array_map('htmlspecialchars',$errores)) ?></div>
    <?php endif; ?>

    <div class="card-uni" style="max-width:700px;">
        <div class="card-uni-header">
            <h6>🎓 Registrar Capacitación</h6>
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
                            <option value="<?= $p['idpers'] ?>" <?= ($datos['idpers']??0)==$p['idpers']?'selected':'' ?>><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Capacitador *</label>
                        <select name="idperscap" required>
                            <option value="">— Seleccione —</option>
                            <?php foreach ($capacitadores as $cap): ?>
                            <option value="<?= $cap['idperscap'] ?>" <?= ($datos['idperscap']??0)==$cap['idperscap']?'selected':'' ?>><?= htmlspecialchars($cap['nomperscap'].' '.$cap['apeperscap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row cols-2" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label>Tema de Capacitación *</label>
                        <input type="text" name="temcap" placeholder="Ej: Manejo de plataformas e-learning" value="<?= htmlspecialchars($datos['temcap']??'') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fechcap" value="<?= $datos['fechcap']??date('Y-m-d') ?>">
                    </div>
                </div>

                <!-- Evaluación opcional -->
                <div style="background:#f8fafc; border-radius:8px; padding:16px; margin-bottom:24px;">
                    <div style="font-size:0.78rem; font-weight:700; color:#475569; text-transform:uppercase; margin-bottom:12px;">
                        📊 Evaluación (opcional)
                    </div>
                    <div class="form-row cols-3">
                        <div class="form-group">
                            <label>Puntaje (0-100)</label>
                            <input type="number" name="pntj" min="0" max="100" placeholder="85" value="<?= $datos['pntj']??'' ?>">
                            <small style="color:#64748b;">⚠️ Trigger SIGNAL valida rango 0-100</small>
                        </div>
                        <div class="form-group">
                            <label>Fecha Evaluación</label>
                            <input type="date" name="fecheval" value="<?= $datos['fecheval']??'' ?>">
                        </div>
                        <div class="form-group">
                            <label>Comentario</label>
                            <input type="text" name="coment" placeholder="Opcional" value="<?= htmlspecialchars($datos['coment']??'') ?>">
                        </div>
                    </div>
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
