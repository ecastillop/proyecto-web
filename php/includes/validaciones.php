<?php
/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - trim(): Elimina los espacios en blanco (u otros caracteres invisibles) al inicio y final de una cadena de texto.
 * - htmlspecialchars(): Convierte caracteres especiales a entidades HTML para prevenir vulnerabilidades de Cross-Site Scripting (XSS).
 * - ENT_QUOTES: Constante que indica a htmlspecialchars que debe convertir tanto las comillas dobles como las simples.
 * - filter_var(): Filtra una variable usando validadores internos de PHP (como validación de correos o URLs).
 * - FILTER_VALIDATE_EMAIL: Constante de filtro para comprobar que una cadena de texto tiene el formato correcto de un correo electrónico.
 * - is_numeric(): Comprueba si el contenido de una variable es un número o un string numérico válido.
 * - file_get_contents(): Lee el contenido completo de un archivo o flujo de datos de entrada y lo convierte en un string.
 * - php://input: Flujo de lectura de solo lectura que permite leer los datos crudos del cuerpo de la petición HTTP (ideal para recibir JSON).
 * - json_decode(): Transforma una cadena de texto en formato JSON a un objeto o, si se especifica, a un array asociativo de PHP.
 * - is_array(): Comprueba estructuralmente si una variable es de tipo array.
 * - ?? (Fusión null): Operador que devuelve su primer operando si existe y no es nulo; de lo contrario, devuelve el segundo operando.
 * - in_array(): Comprueba si un valor específico existe dentro de un array determinado (soporta comprobación estricta de tipos).
 * ==============================================================================
 */

/**
 * Funciones de validacion y sanitizacion de datos de entrada
 */

/**
 * Limpia un texto recibido desde un formulario o JSON.
 */
function limpiarTexto($valor)
{
    // Elimina espacios al inicio y final del texto
    // trim(): Elimina espacios accidentales o intencionales al principio y al final del input para evitar vacíos lógicos
    $valor = trim($valor);

    // Convierte caracteres especiales HTML para evitar XSS basico
    // htmlspecialchars() y ENT_QUOTES: Bloquea intentos de inyección de código convirtiendo etiquetas como <script> en texto inofensivo
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Valida que un correo electronico tenga formato correcto.
 */
function esCorreoValido($correo)
{
    // filter_var verifica el formato estandar de email
    // filter_var() y FILTER_VALIDATE_EMAIL: Delega al motor de PHP la verificación estricta del formato (usuario@dominio.ext)
    return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida que un numero sea positivo mayor a cero.
 */
function esNumeroPositivo($numero)
{
    // is_numeric comprueba que sea numero y > 0 que sea positivo
    // is_numeric(): Valida que el dato ingresado sea tratable matemáticamente antes de aplicar operadores lógicos
    return is_numeric($numero) && $numero > 0;
}

/**
 * Lee el cuerpo de una peticion JSON y lo convierte a arreglo PHP.
 */
function leerJsonEntrada()
{
    // file_get_contents('php://input') lee el cuerpo crudo de la peticion
    // file_get_contents() y 'php://input': Captura el payload en texto crudo enviado asíncronamente desde el fetch() de JavaScript
    $entrada = file_get_contents('php://input');

    // json_decode transforma el JSON en arreglo asociativo PHP
    // json_decode(): Parsea el texto capturado y el segundo parámetro 'true' fuerza a que el resultado sea un array nativo asociativo
    $datos = json_decode($entrada, true);

    // Si no es JSON valido, devuelve arreglo vacio
    // is_array(): Asegura que la conversión anterior fue exitosa antes de devolver el contenido al controlador
    return is_array($datos) ? $datos : [];
}

/**
 * Mapea el nivel de la BD al formato mostrado en el frontend.
 */
function formatearNivel($nivelBd)
{
    // Convierte el valor de la BD al formato mostrado en el frontend
    $mapa = [
        'Basico' => 'Básico',
        'Intermedio' => 'Intermedio',
        'Avanzado' => 'Avanzado',
    ];

    // ?? (Fusión null): Busca la clave en el diccionario $mapa, y si no existe (nulo), devuelve el mismo string de entrada intacto
    return $mapa[$nivelBd] ?? $nivelBd;
}

/**
 * Mapea objetivos de la BD al formato del frontend.
 */
function formatearObjetivo($objetivoBd)
{
    if ($objetivoBd === 'Diseno') {
        return 'Diseño';
    }
    return $objetivoBd;
}

/**
 * Convierte un objetivo del frontend al valor almacenado en BD.
 * Uso: formulario asesor envia "Diseño", la BD guarda "Diseno".
 */
function normalizarObjetivoBd($objetivo)
{
    if ($objetivo === 'Diseño') {
        return 'Diseno';
    }
    return $objetivo;
}

/**
 * Determina el nivel de compra segun el presupuesto maximo.
 * Uso: recomendarProductos() para filtrar columna productos.nivel.
 */
function obtenerNivelPorPresupuestoBd($presupuesto)
{
    if ($presupuesto <= 1800) {
        return 'Basico';
    }
    if ($presupuesto <= 3500) {
        return 'Intermedio';
    }
    return 'Avanzado';
}

/**
 * Devuelve los niveles de producto validos para un perfil de presupuesto.
 * Uso: perfil Avanzado tambien acepta productos Intermedio.
 */
function obtenerNivelesConsultaAsesor($presupuesto)
{
    $nivel = obtenerNivelPorPresupuestoBd($presupuesto);

    if ($nivel === 'Avanzado') {
        return ['Intermedio', 'Avanzado'];
    }

    return [$nivel];
}

/**
 * Valida los criterios recibidos desde el asesor de compra.
 * Uso: recomendarProductos() antes de ejecutar la consulta SQL.
 */
function validarCriteriosAsesor($presupuesto, $objetivo, $preferencia)
{
    $objetivosValidos = ['Oficina', 'Estudio', 'Gaming', 'Diseño', 'Movilidad'];
    $preferenciasValidas = ['Precio', 'Equilibrio', 'Rendimiento', 'Portabilidad'];

    // is_numeric(): Bloquea la entrada si el presupuesto viene contaminado con letras o símbolos
    if (!is_numeric($presupuesto) || $presupuesto < 600) {
        return 'Presupuesto no valido. Debe ser un numero mayor o igual a 600.';
    }

    // in_array(): Comprueba de forma estricta (tercer parámetro 'true') que el objetivo provenga de la lista blanca permitida
    if (!in_array($objetivo, $objetivosValidos, true)) {
        return 'Objetivo de uso no valido.';
    }

    // in_array(): Verifica estrictamente que la preferencia enviada exista dentro de las opciones predefinidas
    if (!in_array($preferencia, $preferenciasValidas, true)) {
        return 'Preferencia de compra no valida.';
    }

    return null;
}

/**
 * Normaliza el tipo de consulta quitando tildes para almacenar en BD.
 */
function normalizarTipoConsulta($tipo)
{
    $mapa = [
        'Cotizacion' => 'Cotizacion',
        'Cotización' => 'Cotizacion',
    ];

    // ?? (Fusión null): Retorna la palabra normalizada si coincide, de lo contrario devuelve el texto original
    return $mapa[$tipo] ?? $tipo;
}