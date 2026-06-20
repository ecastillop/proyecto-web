<?php
/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - session_start(): Inicia una nueva sesión o reanuda la existente para mantener datos temporales del usuario entre páginas.
 * - empty(): Determina si una variable está vacía, es nula o no ha sido definida.
 * - $_SESSION: Variable superglobal que almacena la información de la sesión del usuario actual en el servidor.
 * - header(): Envía un encabezado HTTP sin formato al navegador, comúnmente usado para redirigir a otra URL.
 * - exit: Detiene inmediata y definitivamente la ejecución de todo el script actual.
 * - $_SERVER: Variable superglobal que contiene información del entorno, como el método HTTP utilizado en la petición.
 * - require_once: Incluye y evalúa un archivo externo asegurando que se cargue solo una vez, evitando conflictos.
 * - trim(): Elimina los espacios en blanco al inicio y final de una cadena de texto.
 * - $_POST: Variable superglobal que recolecta los datos enviados silenciosamente a través de un formulario HTML.
 * - ?? (Fusión null): Operador que asigna un valor por defecto si la variable a su izquierda es nula o indefinida.
 * - try...catch: Estructura de control que intenta ejecutar código crítico y captura errores (excepciones) si fallan.
 * - prepare(): Prepara una sentencia SQL en el servidor usando parámetros, previniendo inyección SQL.
 * - execute(): Ejecuta la consulta SQL previamente preparada inyectando los valores de forma segura.
 * - fetch(): Extrae una única fila del conjunto de resultados de la consulta SQL.
 * - password_verify(): Comprueba de forma segura que una contraseña plana ingresada coincida con un hash guardado.
 * - Exception: Clase base nativa de PHP utilizada para capturar errores generales o lógicos.
 * - htmlspecialchars(): Convierte caracteres especiales a entidades HTML para prevenir vulnerabilidades XSS en la vista.
 * ==============================================================================
 */

/**
 * Pagina de inicio de sesion para el panel de administracion
 */

// session_start(): Reanuda el espacio en memoria del servidor para verificar si el usuario ya está logueado
session_start();

// empty() y $_SESSION: Verifica si existe la clave 'rol' en la sesión actual y asegura que tenga contenido
if (!empty($_SESSION['rol']) && $_SESSION['rol'] === 'administrador') {
    // header(): Redirige automáticamente al usuario al panel interno porque ya tiene una sesión válida activa
    header('Location: productos.php');
    // exit: Corta la ejecución del código restante para evitar que se cargue y procese el formulario visual de login
    exit;
}

$error = '';

// $_SERVER: Verifica si el cliente envió el formulario comprobando si el método HTTP de la petición es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // require_once: Carga el archivo de conexión a la base de datos de forma estricta
    require_once __DIR__ . '/../php/config/conexion.php';

    // trim(), $_POST y ?? (Fusión null): Captura el correo tipeado, borra espacios al inicio/fin y asigna texto vacío si no existe
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    // try...catch: Inicia el bloque seguro para intentar la lectura en la base de datos sin colapsar la página si hay fallos
    try {
        $conexion = obtenerConexion();

        $sql = "SELECT id_usuario, nombre, correo, contrasena, rol FROM usuarios WHERE correo = :correo AND activo = 1";
        
        // prepare(): Prepara la consulta de validación en el motor SQL para protegerse de inyecciones
        $stmt = $conexion->prepare($sql);
        
        // execute(): Dispara la búsqueda inyectando el correo digitado de forma segura
        $stmt->execute(['correo' => $correo]);
        
        // fetch(): Rescata únicamente la fila correspondiente al usuario si se encontró una coincidencia en la tabla
        $usuario = $stmt->fetch();

        // password_verify(): Compara algorítmicamente la contraseña escrita por el admin con el hash indescifrable de la BD
        if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
            // $_SESSION: Guarda los identificadores clave del administrador en la memoria del servidor para mantenerlo logueado
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            if ($usuario['rol'] === 'administrador') {
                // header(): Autoriza el acceso real y envía al navegador las instrucciones para saltar a la tabla de productos
                header('Location: productos.php');
                // exit: Finaliza la ejecución del login inmediatamente tras despachar la orden de redirección
                exit;
            }
            $error = 'No tienes permisos de administrador.';
        } else {
            $error = 'Correo o contrasena incorrectos.';
        }
    // Exception: Atrapa de forma genérica si el motor de base de datos se cayó o hubo un error de sintaxis SQL
    } catch (Exception $e) {
        $error = 'Error de conexion a la base de datos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin | Mundo Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">
  <div class="admin-login-page">
    <section class="admin-login-hero">
      <p class="etiqueta mb-3 text-uppercase" style="letter-spacing:0.12em;color:#c9a87c;">Mundo Tech Admin</p>
      <h1>Control total del catalogo y los pedidos.</h1>
      <p>
        Panel interno para gestionar productos, revisar compras y mantener la tienda actualizada
        con una experiencia mas clara para el equipo administrativo.
      </p>
      <ul class="list-unstyled mt-4" style="color:#a89f94;">
        <li class="mb-2"><i class="bi bi-check2-circle me-2"></i>CRUD de productos en tiempo real</li>
        <li class="mb-2"><i class="bi bi-check2-circle me-2"></i>Seguimiento de pedidos por estado</li>
        <li><i class="bi bi-check2-circle me-2"></i>Acceso seguro con sesion PHP</li>
      </ul>
    </section>

    <section class="admin-login-card-wrap">
      <div class="admin-login-card">
        <div class="d-flex align-items-center gap-3 mb-4">
          <span class="admin-brand-icon">MT</span>
          <div>
            <h1 class="h4 mb-0">Iniciar sesion</h1>
            <p class="text-muted mb-0">Acceso restringido</p>
          </div>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" class="form-control" required value="admin@mundotech.pe">
          </div>
          <div class="mb-3">
            <label class="form-label">Contrasena</label>
            <input type="password" name="contrasena" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-admin-primary w-100">Ingresar al panel</button>
        </form>

        <p class="mt-3 small text-muted mb-2">Usuario demo: admin@mundotech.pe / admin123</p>
        <a href="../index.html" class="small text-decoration-none">Volver a la tienda</a>
      </div>
    </section>
  </div>
</body>
</html>