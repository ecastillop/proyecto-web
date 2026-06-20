<?php
/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - session_start(): Inicia o reanuda la sesión actual para poder acceder a sus datos o, en este caso, prepararla para su destrucción.
 * - $_SESSION: Variable superglobal tipo array que contiene las variables de sesión registradas (como el ID de usuario o rol).
 * - session_destroy(): Elimina permanentemente toda la información de la sesión registrada en los archivos temporales del servidor.
 * - header(): Envía un encabezado HTTP sin formato al navegador, utilizado frecuentemente para forzar una redirección de página.
 * - exit: Detiene de forma inmediata y definitiva la ejecución del script actual para asegurar que la redirección se procese sin interferencias.
 * ==============================================================================
 */

/**
 * Cierra la sesion del administrador y redirige al login
 */

// session_start(): Recupera la sesión activa en la memoria para tener los permisos necesarios antes de destruirla
session_start();

// Elimina todas las variables de sesion almacenadas
// $_SESSION: Vacía por completo el arreglo superglobal, borrando los datos de identidad (nombre, rol) al instante en el código
$_SESSION = [];

// Destruye la sesion en el servidor
// session_destroy(): Ordena al motor PHP que borre físicamente el archivo de rastreo de esta sesión en el disco del servidor
session_destroy();

// Redirige a la pagina de login
// header(): Envía la cabecera HTTP indicándole al navegador del usuario que salte a la interfaz de autenticación
header('Location: login.php');

// exit: Asegura el cierre del script cortando cualquier ejecución residual mientras el navegador ejecuta el salto de página
exit;