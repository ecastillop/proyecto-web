<?php
/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - session_start(): Inicia o reanuda la sesión actual para verificar si el usuario tiene permisos de acceso.
 * - require_once: Importa el archivo de conexión a la base de datos de forma segura y única.
 * - __DIR__: Constante mágica de PHP que devuelve la ruta absoluta del directorio donde se encuentra el archivo actual.
 * - empty(): Evalúa si una variable (como un dato de sesión) no existe, está vacía o es nula.
 * - $_SESSION: Variable superglobal que contiene los datos temporales del usuario autenticado.
 * - header(): Envía un encabezado HTTP, utilizado aquí para redirigir al usuario si no tiene acceso.
 * - exit: Detiene la ejecución del script instantáneamente, previniendo que el HTML inferior cargue sin autorización.
 * - $_SERVER: Superglobal que expone detalles de la petición actual, como si el método fue POST o GET.
 * - prepare(): Prepara una consulta SQL en el servidor (UPDATE en este caso) con parámetros seguros.
 * - execute(): Ejecuta la sentencia SQL inyectando el nuevo estado del pedido y el ID.
 * - query(): Lanza una consulta SQL directa (SELECT) a la base de datos sin parámetros externos.
 * - fetchAll(): Extrae todas las filas resultantes de la consulta de pedidos y las guarda en un array asociativo.
 * - count(): Cuenta el total de elementos (pedidos) dentro de un array.
 * - array_filter(): Filtra un array conservando solo los elementos que cumplan una condición definida.
 * - fn(): Sintaxis corta para definir una función anónima (arrow function) utilizada como filtro.
 * - array_sum(): Suma matemáticamente todos los valores numéricos dentro de un array unidimensional.
 * - array_column(): Extrae de un array multidimensional únicamente los valores de una columna específica (ej. 'total').
 * - htmlspecialchars(): Escapa caracteres especiales en texto HTML para evitar inyecciones XSS en la interfaz.
 * - number_format(): Formatea un número agregando decimales y separadores de miles para su visualización.
 * - foreach: Estructura iterativa que recorre el array de pedidos para generar las filas de la tabla HTML.
 * ==============================================================================
 */

/**
 * Panel de administracion - Gestion de pedidos
 */

// session_start(): Recupera la sesión para verificar si quien intenta entrar es un administrador real
session_start();

// require_once y __DIR__: Trae la función obtenerConexion() usando la ruta absoluta del sistema de archivos
require_once __DIR__ . '/../php/config/conexion.php';

// empty() y $_SESSION: Verifica que el rol exista en la memoria y sea estrictamente 'administrador'
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    // header(): Expulsa al usuario devolviéndolo a la página de login
    header('Location: login.php');
    // exit: Corta el script para que no se ejecuten las consultas SQL de abajo
    exit;
}

$conexion = obtenerConexion();
$mensaje = '';

// $_SERVER: Verifica si se envió el formulario para actualizar el estado de un pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE pedidos SET estado = :estado WHERE id_pedido = :id";
    
    // prepare(): Alista la consulta UPDATE protegiendo la base de datos
    $stmt = $conexion->prepare($sql);
    
    // execute(): Aplica el nuevo estado (ej. 'entregado') al pedido específico capturado por POST
    $stmt->execute([
        'estado' => $_POST['estado'],
        'id' => (int) $_POST['id_pedido'],
    ]);
    $mensaje = 'Estado del pedido actualizado.';
}

// query(): Solicita directamente todo el historial de pedidos ordenado por los más recientes
$pedidos = $conexion->query(
    "SELECT id_pedido, nombre_cliente, correo_cliente, telefono, total, estado, fecha_pedido
     FROM pedidos ORDER BY fecha_pedido DESC"
// fetchAll(): Convierte esa respuesta tabular de MySQL en un array multidimensional de PHP
)->fetchAll();

// count(): Calcula cuántos pedidos en total existen en la matriz
$totalPedidos = count($pedidos);

// array_filter() y fn(): Recorre los pedidos aislando solo aquellos cuyo estado sea exactamente 'nuevo'
// count(): Cuenta el resultado de ese filtro para saber cuántos pedidos faltan por revisar
$pedidosNuevos = count(array_filter($pedidos, fn($p) => $p['estado'] === 'nuevo'));

// array_column(): Crea un array lineal extrayendo solo la columna 'total' de todas las compras
// array_sum(): Suma todos esos totales para obtener el dinero acumulado en ventas
$ventasTotales = array_sum(array_column($pedidos, 'total'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Pedidos | Mundo Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <a class="admin-brand" href="productos.php">
        <span class="admin-brand-icon">MT</span>
        <span>
          <strong>Mundo Tech</strong>
          <small>Panel administrativo</small>
        </span>
      </a>

      <nav class="admin-nav" aria-label="Menu admin">
        <a href="productos.php"><i class="bi bi-box-seam"></i> Productos</a>
        <a class="activo" href="pedidos.php"><i class="bi bi-receipt"></i> Pedidos</a>
        <a href="../index.html"><i class="bi bi-shop"></i> Ver tienda</a>
      </nav>

      <div class="admin-sidebar-footer">
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesion</a>
      </div>
    </aside>

    <main class="admin-main">
      <div class="admin-topbar">
        <div>
          <h1>Pedidos</h1>
          <p>Supervisa compras registradas y actualiza su estado de entrega.</p>
        </div>
        <span class="admin-user-chip"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador'); ?></span>
      </div>

      <section class="admin-stats">
        <article class="admin-stat-card"><strong><?php echo $totalPedidos; ?></strong><span>Pedidos totales</span></article>
        <article class="admin-stat-card"><strong><?php echo $pedidosNuevos; ?></strong><span>Pendientes por revisar</span></article>
        <article class="admin-stat-card"><strong>S/ <?php echo number_format($ventasTotales, 2); ?></strong><span>Ventas acumuladas</span></article>
      </section>

      <section class="admin-panel">
        <div class="admin-panel-header">
          <h2>Pedidos registrados</h2>
          <span class="text-muted small">Ordenados por fecha</span>
        </div>
        <div class="admin-panel-body table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Correo</th>
                <th>Telefono</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Actualizar</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pedidos as $pedido): ?>
              <tr>
                <td>#<?php echo $pedido['id_pedido']; ?></td>
                <td><?php echo htmlspecialchars($pedido['nombre_cliente']); ?></td>
                <td><?php echo htmlspecialchars($pedido['correo_cliente']); ?></td>
                <td><?php echo htmlspecialchars($pedido['telefono'] ?? '-'); ?></td>
                <td>S/ <?php echo number_format($pedido['total'], 2); ?></td>
                <td><span class="admin-badge <?php echo htmlspecialchars($pedido['estado']); ?>"><?php echo $pedido['estado']; ?></span></td>
                <td><?php echo $pedido['fecha_pedido']; ?></td>
                <td>
                  <form method="POST" class="d-flex gap-2">
                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                    <select name="estado" class="form-select form-select-sm">
                      <option value="nuevo" <?php echo $pedido['estado'] === 'nuevo' ? 'selected' : ''; ?>>nuevo</option>
                      <option value="en_proceso" <?php echo $pedido['estado'] === 'en_proceso' ? 'selected' : ''; ?>>en_proceso</option>
                      <option value="entregado" <?php echo $pedido['estado'] === 'entregado' ? 'selected' : ''; ?>>entregado</option>
                      <option value="cancelado" <?php echo $pedido['estado'] === 'cancelado' ? 'selected' : ''; ?>>cancelado</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-admin-primary">Guardar</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <?php if ($mensaje): ?>
  <div class="admin-toast-wrap">
    <div id="toastExito" class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body"><?php echo htmlspecialchars($mensaje); ?></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="admin.js"></script>
</body>
</html>