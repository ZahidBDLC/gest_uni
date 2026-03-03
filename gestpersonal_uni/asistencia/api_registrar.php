<?php
require_once __DIR__ . '/../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$idpers = isset($input['idpers']) ? (int)$input['idpers'] : 0;

if ($idpers <= 0) {
  echo json_encode(['ok' => false, 'msg' => 'ID personal inválido']);
  exit;
}

date_default_timezone_set('America/Lima');

$fecha = date('Y-m-d');
$hora  = date('H:i:s');

try {
  // 1) Buscar si ya hay registro hoy
  $stmt = $pdo->prepare("SELECT idasist, hrentr1, hrsali1 FROM asistencia WHERE idpers = :idpers AND fech = :fech LIMIT 1");
  $stmt->execute([':idpers' => $idpers, ':fech' => $fecha]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    // Crear registro con entrada 1
    $ins = $pdo->prepare("INSERT INTO asistencia (idpers, fech, hrentr1, estasist) VALUES (:idpers, :fech, :hr, 'ASISTIO')");
    $ins->execute([':idpers' => $idpers, ':fech' => $fecha, ':hr' => $hora]);

    echo json_encode(['ok' => true, 'hora_servidor' => $hora, 'accion' => 'ENTRADA']);
    exit;
  }

  // Si existe y no tiene salida, poner salida
  if (!empty($row['hrentr1']) && empty($row['hrsali1'])) {
    $upd = $pdo->prepare("UPDATE asistencia SET hrsali1 = :hr WHERE idasist = :idasist");
    $upd->execute([':hr' => $hora, ':idasist' => $row['idasist']]);

    echo json_encode(['ok' => true, 'hora_servidor' => $hora, 'accion' => 'SALIDA']);
    exit;
  }

  echo json_encode(['ok' => false, 'msg' => 'Registro excitoso.']);
} catch (Exception $e) {
  echo json_encode(['ok' => false, 'msg' => 'Error en servidor']);
}