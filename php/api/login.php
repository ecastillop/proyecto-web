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
 * - prepare(): Prepara una sentencia SQL en el servidor usando parámetros, previniendo inyección SQL.
 * - execute(): Ejecuta la consulta SQL previamente preparada inyectando los valores de forma segura.
 * - fetch(): Extrae una única fila del conjunto de resultados de la consulta SQL (ideal para un solo usuario).
 * - password_verify(): Comprueba que la contraseña plana ingresada coincida con un hash seguro encriptado.
 * - session_start(): Inicia una nueva sesión o reanuda la existente para mantener al usuario logueado en el servidor.
 * - $_SESSION: Variable superglobal usada para almacenar y acceder a datos temporales del usuario entre páginas.
 * - unset(): Destruye una variable o un índice específico dentro de un array para borrarlo de la memoria.
 * ==============================================================================
 */

/**
 * API de autenticacion - Login de usuarios
 * Endpoint: POST con JSON { correo, contrasena }
 */

// require_once: Carga dependencias esenciales y bloquea su re-inclusión accidental
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/respuesta.php';
require_once __DIR__ . '/../includes/validaciones.php';

configurarCors();

// $_SERVER: Verifica que el cliente haya usado estrictamente el método POST para enviar las credenciales
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarError('Metodo no permitido. Use POST.', 405);
}

// try...catch: Abre el bloque seguro para intentar la conexión y consulta a la base de datos
try {
    $conexion = obtenerConexion();
    iniciarSesion($conexion);
// PDOException: Captura exclusivamente errores arrojados por el motor de la base de datos
} catch (PDOException $e) {
    enviarError('Error de autenticacion: ' . $e->getMessage(), 500);
}

/**
 * Verifica credenciales y devuelve datos del usuario autenticado.
 */
function iniciarSesion($conexion)
{
    $datos = leerJsonEntrada();

    // empty(): Evalúa si el JSON llegó vacío o no se envió correctamente
    if (empty($datos)) {
        // $_POST: Captura los datos directamente desde las cabeceras estándar del formulario HTML
        $datos = $_POST;
    }

    // ?? (Fusión null): Asigna un string vacío si 'correo' no viene definido para evitar errores
    $correo = limpiarTexto($datos['correo'] ?? '');
    $contrasena = $datos['contrasena'] ?? '';

    // empty(): Verifica que las credenciales no estén en blanco antes de hacer la consulta
    if (empty($correo) || empty($contrasena)) {
        enviarError('Correo y contrasena son obligatorios.');
    }

    // Busca el usuario activo por su correo electronico
    $sql = "SELECT id_usuario, nombre, correo, contrasena, rol
            FROM usuarios WHERE correo = :correo AND activo = 1";

    // prepare(): Envía la estructura SQL al motor para prevenir inyección de código
    $stmt = $conexion->prepare($sql);
    
    // execute(): Ejecuta la consulta inyectando el correo limpio de forma segura
    $stmt->execute(['correo' => $correo]);
    
    // fetch(): Extrae un solo registro de la base de datos (el usuario encontrado)
    $usuario = $stmt->fetch();

    // password_verify(): Compara de forma segura la contraseña plana del login con el hash guardado en la BD
    if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
        enviarError('Credenciales incorrectas.', 401);
    }

    // session_start(): Crea o reanuda el espacio de memoria temporal en el servidor para este usuario
    session_start();
    
    // $_SESSION: Guarda los datos de identidad para que las demás rutas protegidas sepan quién es el usuario
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['rol'] = $usuario['rol'];

    // unset(): Elimina el hash de la contraseña por estricta seguridad antes de devolver el array al frontend
    unset($usuario['contrasena']);

    enviarExito('Sesion iniciada correctamente.', ['usuario' => $usuario]);
}