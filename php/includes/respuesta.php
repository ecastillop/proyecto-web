<?php
/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - http_response_code(): Define el código de estado HTTP (ej. 200 OK, 404 Error) que el servidor enviará al navegador.
 * - header(): Envía encabezados HTTP sin formato para configurar el tipo de contenido o reglas de acceso como CORS.
 * - echo: Imprime o da salida a una o más cadenas de texto para que el cliente (frontend) las reciba físicamente.
 * - json_encode(): Convierte un array u objeto nativo de PHP a una cadena de texto en formato JSON estructurado.
 * - JSON_UNESCAPED_UNICODE: Constante de json_encode que evita que caracteres especiales (acentos, ñ) se codifiquen incorrectamente.
 * - exit: Detiene inmediata y definitivamente la ejecución de todo el script actual.
 * - array_merge(): Une o combina dos o más arrays en uno solo, fusionando sus claves y valores.
 * - $_SERVER: Variable superglobal que contiene información del entorno, como el método HTTP utilizado en la petición.
 * ==============================================================================
 */

/**
 * Funciones auxiliares para responder en formato JSON
 */

/**
 * Envia una respuesta JSON al navegador y termina la ejecucion.
 */
function enviarJson($datos, $codigoHttp = 200)
{
    // Establece el codigo de estado HTTP (200, 400, 500, etc.)
    // http_response_code(): Asigna el código de estado para que el navegador sepa cómo tratar la respuesta
    http_response_code($codigoHttp);

    // Indica que el contenido de la respuesta es JSON
    // header(): Especifica al cliente que los datos adjuntos están en formato JSON puro y codificados en UTF-8
    header('Content-Type: application/json; charset=utf-8');

    // Convierte el arreglo PHP a texto JSON y lo imprime
    // echo: Expulsa el contenido final hacia el frontend
    // json_encode() y JSON_UNESCAPED_UNICODE: Transforma el array a texto JSON respetando la ortografía en español
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);

    // Detiene el script para no enviar mas salida
    // exit: Corta la ejecución aquí mismo para asegurar que no se imprima basura HTML u otros datos por error
    exit;
}

/**
 * Envia una respuesta de error en formato JSON.
 */
function enviarError($mensaje, $codigoHttp = 400)
{
    // Usa enviarJson con la estructura estandar de error
    enviarJson(['exito' => false, 'mensaje' => $mensaje], $codigoHttp);
}

/**
 * Envia una respuesta exitosa en formato JSON.
 */
function enviarExito($mensaje, $datos = [])
{
    // Combina el mensaje de exito con datos adicionales opcionales
    // array_merge(): Junta el array base de éxito y mensaje con el array de datos adicionales que quieras devolver
    enviarJson(array_merge(['exito' => true, 'mensaje' => $mensaje], $datos));
}

/**
 * Permite peticiones desde el frontend (CORS basico para desarrollo local).
 */
function configurarCors()
{
    // Permite solicitudes desde cualquier origen en entorno de desarrollo
    // header(): Inyecta reglas de seguridad que le dan permiso a tu frontend (en otro puerto) para consumir esta API
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Responde OK a peticiones preflight OPTIONS del navegador
    // $_SERVER: Verifica si el navegador está haciendo una petición previa de seguridad (preflight) antes del POST o GET real
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        // http_response_code(): Confirma que la ruta existe y los permisos son válidos (Código 200)
        http_response_code(200);
        // exit: Termina la petición preflight inmediatamente porque no necesita recibir datos, solo la autorización
        exit;
    }
}