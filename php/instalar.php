<?php
/**
 * Script de instalacion - Ejecutar una vez despues de importar mundo_tech.sql
 * Acceder desde: http://localhost/proyecto-web-main/php/instalar.php
 * Genera el hash correcto de la contrasena admin123
 */

require_once __DIR__ . '/config/conexion.php';

$contrasenaPlana = 'admin123';
$hash = password_hash($contrasenaPlana, PASSWORD_DEFAULT);

try {
    $conexion = obtenerConexion();

    // Actualiza la contrasena de los usuarios demo con hash seguro
    $sql = "UPDATE usuarios SET contrasena = :hash WHERE correo IN ('admin@mundotech.pe', 'cliente@mundotech.pe')";
    $stmt = $conexion->prepare($sql);
    $stmt->execute(['hash' => $hash]);

    echo 'Instalacion completada. Contrasena actualizada a: admin123';
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
