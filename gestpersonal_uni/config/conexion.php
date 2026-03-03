<?php
// config/conexion.php
// Conexión centralizada - se incluye en todas las páginas

$host     = 'localhost';
$dbname   = 'dbgestpersonal_uni';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("
    <div style='font-family:sans-serif;padding:30px;background:#fff3cd;border:1px solid #ffc107;border-radius:8px;margin:20px;'>
        <h3 style='color:#856404;'>⚠️ Error de Conexión</h3>
        <p>No se pudo conectar a la base de datos <b>dbgestpersonal_uni</b>.</p>
        <p><small>" . $e->getMessage() . "</small></p>
        <p>Verifica que XAMPP (MySQL) esté corriendo.</p>
    </div>");
}
