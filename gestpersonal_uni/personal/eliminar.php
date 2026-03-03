<?php
// personal/eliminar.php
session_start();
require_once '../config/conexion.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: lista.php'); exit; }

try {
    // PUNTO 2: SP de eliminación (valida internamente si tiene contrato activo)
    $stmt = $pdo->prepare("CALL sp_eliminar_personal(:id)");
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch();
    $_SESSION['msg'] = $result['mensaje'] ?? 'Operación completada.';
} catch (PDOException $e) {
    $_SESSION['msg'] = 'Error BD: ' . $e->getMessage();
}

header('Location: lista.php');
exit;
