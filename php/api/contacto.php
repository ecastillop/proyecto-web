<?php
/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - require_once: Importa un archivo externo asegurando que se incluya solo una vez.
 * - $_SERVER: Variable superglobal con información del entorno del servidor y la petición actual.
 * - try...catch: Estructura de control que intenta ejecutar código y captura errores (excepciones).
 * - PDOException: Clase que representa un error emitido por la base de datos a través de PDO.
 * - empty(): Determina si una variable está vacía, es cero o no ha sido definida.
 * - $_POST: Variable superglobal que recolecta datos enviados a través del método HTTP POST.
 * - ?? (Fusión null): Operador que asigna un valor por defecto si la variable a su izquierda es nula.
 * - in_array(): Comprueba si un valor específico existe dentro de un array determinado.
 * - prepare(): Prepara una sentencia SQL en el servidor usando parámetros, previniendo inyección SQL.
 * - execute(): Ejecuta la consulta SQL previamente preparada inyectando los valores de forma segura.
 * - lastInsertId(): Obtiene el ID autogenerado del último registro insertado en la base de datos.
 * ==============================================================================
 */

/**
 * API de contacto - Procesamiento del formulario de contacto
 * Endpoint: POST con JSON { nombre, correo, tipo_consulta, mensaje }
 */

// require_once: Carga dependencias esenciales y bloquea su re-inclusión accidental
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/respuesta.php';
require_once __DIR__ . '/../includes/validaciones.php';

configurarCors();

// $_SERVER: Verifica que el cliente haya usado estrictamente el método POST para enviar los datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarError('Metodo no permitido. Use POST.', 405);
}

// try...catch: Abre el bloque seguro para intentar la conexión y escritura en la base de datos
try {
    $conexion = obtenerConexion();
    guardarContacto($conexion);
// PDOException: Captura exclusivamente errores arrojados por el motor de la base de datos
} catch (PDOException $e) {
    enviarError('Error al guardar contacto: ' . $e->getMessage(), 500);
}

/**
 * Valida y guarda un mensaje de contacto en la base de datos.
 */
function guardarContacto($conexion)
{
    // Lee los datos del formulario enviados como JSON
    $datos = leerJsonEntrada();

    // Tambien acepta datos enviados como formulario HTML tradicional
    // empty(): Evalúa si el JSON llegó vacío o no se envió correctamente
    if (empty($datos)) {
        // $_POST: Captura los datos directamente desde las cabeceras estándar del formulario HTML
        $datos = $_POST;
    }

    // ?? (Fusión null): Evita errores de variable indefinida asignando '' (vacío) si el índice no existe
    $nombre = limpiarTexto($datos['nombre'] ?? '');
    $correo = limpiarTexto($datos['correo'] ?? '');
    $tipo = normalizarTipoConsulta(limpiarTexto($datos['tipo_consulta'] ?? ''));
    $mensaje = limpiarTexto($datos['mensaje'] ?? '');

    // Valida que todos los campos requeridos tengan contenido
    // empty(): Verifica campo por campo que no estén vacíos antes de continuar
    if (empty($nombre) || empty($correo) || empty($tipo) || empty($mensaje)) {
        enviarError('Todos los campos son obligatorios.');
    }

    if (!esCorreoValido($correo)) {
        enviarError('El correo electronico no es valido.');
    }

    // Tipos de consulta permitidos segun el formulario HTML
    $tiposValidos = ['Compra', 'Cotizacion', 'Compatibilidad', 'Postventa'];
    
    // in_array(): Busca el tipo ingresado por el usuario dentro del arreglo $tiposValidos de manera estricta
    if (!in_array($tipo, $tiposValidos, true)) {
        enviarError('Tipo de consulta no valido.');
    }

    // Inserta el mensaje en la tabla contactos con consulta preparada
    $sql = "INSERT INTO contactos (nombre, correo, tipo_consulta, mensaje)
            VALUES (:nombre, :correo, :tipo, :mensaje)";

    // prepare(): Envía la estructura SQL al motor de base de datos para que la analice y proteja
    $stmt = $conexion->prepare($sql);
    
    // execute(): Reemplaza las variables anidadas (:nombre, :correo, etc.) por los valores reales limpios
    $stmt->execute([
        'nombre' => $nombre,
        'correo' => $correo,
        'tipo' => $tipo,
        'mensaje' => $mensaje,
    ]);

    // Devuelve confirmacion con el ID del registro guardado
    enviarExito('Mensaje enviado correctamente. Te responderemos pronto.', [
        // lastInsertId(): Retorna el número de ID que la base de datos acaba de asignar a este nuevo contacto
        'id_contacto' => (int) $conexion->lastInsertId(),
    ]);
}