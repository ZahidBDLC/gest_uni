<?php
require_once __DIR__ . '/../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$dni = isset($input['dni']) ? trim($input['dni']) : '';

if (!preg_match('/^\d{8}$/', $dni)) {
  echo json_encode(['ok' => false, 'msg' => 'DNI inválido']);
  exit;
}

try {
  // Ajusta $pdo según tu conexion.php (PDO)
  $stmt = $pdo->prepare("SELECT idpers, nompers, apepers FROM personal_uni WHERE dni = :dni LIMIT 1");
  $stmt->execute([':dni' => $dni]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    echo json_encode(['ok' => false, 'msg' => 'No existe personal con ese DNI']);
    exit;
  }

  echo json_encode([
    'ok' => true,
    'idpers' => (int)$row['idpers'],
    'nombre' => $row['nompers'] . ' ' . $row['apepers']
  ]);
} catch (Exception $e) {
  echo json_encode(['ok' => false, 'msg' => 'Error en servidor']);
}