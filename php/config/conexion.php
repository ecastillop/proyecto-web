<?php
/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - define(): Crea una constante global con nombre cuyo valor no puede cambiar durante la ejecución del script.
 * - PDO::ATTR_ERRMODE: Atributo de configuración que determina cómo PDO debe manejar y reportar los errores.
 * - PDO::ERRMODE_EXCEPTION: Configuración que obliga a PDO a lanzar una excepción estructurada (PDOException) si algo falla.
 * - PDO::ATTR_DEFAULT_FETCH_MODE: Atributo que define el formato por defecto en que se extraerán las filas de la base de datos.
 * - PDO::FETCH_ASSOC: Configuración para que las consultas devuelvan los datos en un array indexado por el nombre de las columnas.
 * - PDO::ATTR_EMULATE_PREPARES: Atributo de seguridad; al desactivarlo, fuerza a que el motor de MySQL maneje las sentencias preparadas nativamente.
 * - new PDO(): Instancia un objeto nativo de PHP que establece y representa la conexión física con el servidor de base de datos.
 * ==============================================================================
 */

/**
 * Archivo de configuracion y conexion a MySQL
 * Mundo Tech - Taller de Programacion Web
 */

// define(): Establece las credenciales fijas de conexión que se usarán en todo el proyecto
define('DB_HOST', 'localhost');
define('DB_PUERTO', '3306');
define('DB_NOMBRE', 'mundo_tech');
define('DB_USUARIO', 'homestead');
define('DB_CONTRASENA', 'secret');
define('DB_CHARSET', 'utf8mb4');

/**
 * Crea y devuelve una conexion PDO a la base de datos MySQL.
 */
function obtenerConexion()
{
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PUERTO . ';dbname=' . DB_NOMBRE . ';charset=' . DB_CHARSET;

    // Opciones para manejo seguro de errores y resultados asociativos
    $opciones = [
        // PDO::ATTR_ERRMODE y PDO::ERRMODE_EXCEPTION: Activa el lanzamiento estricto de errores para capturarlos con try...catch
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        
        // PDO::ATTR_DEFAULT_FETCH_MODE y PDO::FETCH_ASSOC: Formatea por defecto cada fila obtenida como un array asociativo (clave-valor)
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        
        // PDO::ATTR_EMULATE_PREPARES: Se establece en false para delegar la protección contra inyecciones SQL directamente al motor de MySQL
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Retorna el objeto PDO listo para ejecutar consultas
    // new PDO(): Abre la conexión real utilizando el DSN, las credenciales definidas y el array de opciones seguras
    return new PDO($dsn, DB_USUARIO, DB_CONTRASENA, $opciones);
}