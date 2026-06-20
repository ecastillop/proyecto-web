<?php
/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - require_once: Importa un archivo externo asegurando que se incluya solo una vez para evitar errores de redefinición.
 * - $_SERVER: Variable superglobal que contiene información sobre el entorno del servidor y la petición actual.
 * - try...catch: Estructura de control para manejo de errores; intenta ejecutar código y captura excepciones si fallan.
 * - PDOException: Clase que representa un error emitido por la extensión PDO al interactuar con la base de datos.
 * - query(): Ejecuta una sentencia SQL directamente en el servidor y devuelve un objeto con los resultados.
 * - fetchAll(): Extrae todas las filas del conjunto de resultados de la consulta SQL y las agrupa en un array nativo.
 * ==============================================================================
 */

/**
 * API de categorias - Listado para filtros y panel admin
 * Endpoint: GET ?accion=listar
 */

// require_once: Carga de forma estricta e irrepetible las configuraciones y dependencias
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/respuesta.php';

configurarCors();

// $_SERVER: Verifica qué método HTTP (como GET o POST) se utilizó para invocar este script
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    enviarError('Metodo no permitido. Use GET.', 405);
}

// try...catch: Inicia el bloque protegido para manejar la base de datos de forma segura
try {
    $conexion = obtenerConexion();
    listarCategorias($conexion);
// PDOException: Intercepta específicamente cualquier fallo crítico generado por la base de datos
} catch (PDOException $e) {
    enviarError('Error al obtener categorias: ' . $e->getMessage(), 500);
}

/**
 * Devuelve todas las categorias activas ordenadas por nombre.
 */
function listarCategorias($conexion)
{
    // Consulta simple de categorias activas
    $sql = "SELECT id_categoria, nombre, descripcion FROM categorias WHERE activo = 1 ORDER BY nombre";
    
    // query(): Ejecuta la consulta SQL directamente (es seguro aquí porque no hay variables inyectables del usuario)
    $stmt = $conexion->query($sql);
    
    // fetchAll(): Convierte la respuesta cruda de la base de datos en un array procesable por PHP
    $categorias = $stmt->fetchAll();

    enviarExito('Categorias obtenidas correctamente.', ['categorias' => $categorias]);
}