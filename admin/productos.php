<?php
/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - session_start(): Inicia o reanuda la sesión actual para verificar el acceso y permisos del usuario.
 * - require_once: Importa un archivo externo asegurando que se cargue una única vez para evitar errores.
 * - __DIR__: Constante mágica de PHP que devuelve la ruta absoluta del directorio del archivo actual.
 * - empty(): Verifica si una variable no ha sido definida, es nula o está vacía.
 * - $_SESSION: Variable superglobal que almacena los datos de la sesión del usuario en el servidor.
 * - header(): Envía un encabezado HTTP, comúnmente usado para forzar la redirección a otra URL.
 * - exit: Detiene de manera inmediata y definitiva la ejecución del script actual.
 * - $_SERVER: Variable superglobal que contiene información del entorno, como el método HTTP de la petición.
 * - $_POST: Variable superglobal que recolecta los datos enviados a través de un formulario con el método POST.
 * - ?? (Fusión null): Operador que asigna un valor por defecto si la variable a evaluar no existe o es nula.
 * - query(): Ejecuta una sentencia SQL directamente en el servidor sin preparación previa.
 * - fetchAll(): Extrae todas las filas del conjunto de resultados de la consulta y las convierte en un array.
 * - count(): Cuenta la cantidad de elementos dentro de un array de PHP.
 * - array_filter(): Filtra un array conservando únicamente los elementos que cumplan una condición específica.
 * - fn(): Sintaxis corta para definir una función anónima (arrow function) utilizada en filtros.
 * - array_column(): Extrae y devuelve los valores de una sola columna específica dentro de un array multidimensional.
 * - array_sum(): Suma matemáticamente todos los valores numéricos dentro de un array.
 * - prepare(): Prepara una sentencia SQL en el servidor usando parámetros, protegiendo contra inyección SQL.
 * - execute(): Ejecuta la consulta SQL previamente preparada inyectando los datos de forma segura.
 * - htmlspecialchars(): Convierte caracteres especiales en entidades HTML para evitar ataques XSS al renderizar la vista.
 * - number_format(): Formatea un número agregando decimales y separadores de miles.
 * - ENT_QUOTES: Constante utilizada para indicar a htmlspecialchars que debe escapar comillas simples y dobles.
 * ==============================================================================
 */

/**
 * Panel de administracion - Gestion de productos (CRUD)
 * Requiere sesion de administrador activa
 */

// session_start(): Recupera la sesión en memoria para verificar la identidad del usuario
session_start();

// require_once y __DIR__: Cargan las dependencias del sistema de base de datos y validaciones usando rutas absolutas
require_once __DIR__ . '/../php/config/conexion.php';
require_once __DIR__ . '/../php/includes/validaciones.php';

// empty() y $_SESSION: Valida que el rol exista y que sea estrictamente el de 'administrador'
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    // header(): Redirige al usuario al login si no cumple con los permisos
    header('Location: login.php');
    // exit: Bloquea la carga del resto del panel para usuarios no autorizados
    exit;
}

$mensaje = '';
$conexion = obtenerConexion();

// $_SERVER: Verifica si el panel recibió una petición mediante el envío de un formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $_POST y ?? (Fusión null): Captura la acción solicitada por el formulario, o asigna texto vacío si no existe
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        crearProductoAdmin($conexion);
        $mensaje = 'Producto creado correctamente.';
    } elseif ($accion === 'actualizar') {
        actualizarProductoAdmin($conexion);
        $mensaje = 'Producto actualizado correctamente.';
    } elseif ($accion === 'eliminar') {
        eliminarProductoAdmin($conexion);
        $mensaje = 'Producto eliminado correctamente.';
    }
}

// query(): Lanza una consulta directa a la base de datos integrando productos y categorías
$productos = $conexion->query(
    "SELECT p.id_producto, p.nombre, c.nombre AS categoria, p.nivel, p.precio, p.stock, p.activo
     FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id_categoria
     ORDER BY p.id_producto"
// fetchAll(): Convierte la respuesta tabular de la base de datos en un array multidimensional
)->fetchAll();

// query() y fetchAll(): Obtiene las categorías activas para el select del formulario de creación
$categorias = $conexion->query("SELECT id_categoria, nombre FROM categorias WHERE activo = 1")->fetchAll();

// count(): Calcula el número total de productos existentes en la tabla
$totalProductos = count($productos);

// array_filter() y fn(): Filtra el array de productos para aislar solo los que tienen el campo 'activo' en 1
// count(): Cuenta el resultado del filtro para obtener los productos activos reales
$productosActivos = count(array_filter($productos, fn($p) => (int) $p['activo'] === 1));

// array_column(): Extrae únicamente la columna 'stock' de todos los productos en un array plano
// array_sum(): Suma todos los números de ese array plano para dar el total de inventario físico
$stockTotal = array_sum(array_column($productos, 'stock'));

function crearProductoAdmin($conexion)
{
    $sql = "INSERT INTO productos (id_categoria, nombre, nivel, precio, descripcion, imagen, stock)
            VALUES (:id_categoria, :nombre, :nivel, :precio, :descripcion, :imagen, :stock)";
    
    // prepare(): Compila la sentencia INSERT en el motor SQL por seguridad
    $stmt = $conexion->prepare($sql);
    
    // execute(): Ejecuta la inserción limpiando y asignando los datos capturados de $_POST
    $stmt->execute([
        'id_categoria' => (int) $_POST['id_categoria'],
        'nombre' => limpiarTexto($_POST['nombre']),
        'nivel' => $_POST['nivel'],
        'precio' => (float) $_POST['precio'],
        'descripcion' => limpiarTexto($_POST['descripcion']),
        'imagen' => limpiarTexto($_POST['imagen']),
        'stock' => (int) $_POST['stock'],
    ]);
}

function actualizarProductoAdmin($conexion)
{
    $sql = "UPDATE productos SET id_categoria=:id_categoria, nombre=:nombre, nivel=:nivel,
            precio=:precio, descripcion=:descripcion, imagen=:imagen, stock=:stock
            WHERE id_producto=:id_producto";
            
    // prepare(): Alista el UPDATE en la base de datos para no inyectar strings directamente
    $stmt = $conexion->prepare($sql);
    
    // execute(): Reemplaza todos los parámetros de la consulta con los valores frescos del formulario
    $stmt->execute([
        'id_categoria' => (int) $_POST['id_categoria'],
        'nombre' => limpiarTexto($_POST['nombre']),
        'nivel' => $_POST['nivel'],
        'precio' => (float) $_POST['precio'],
        'descripcion' => limpiarTexto($_POST['descripcion']),
        'imagen' => limpiarTexto($_POST['imagen']),
        'stock' => (int) $_POST['stock'],
        'id_producto' => (int) $_POST['id_producto'],
    ]);
}

function eliminarProductoAdmin($conexion)
{
    $sql = "UPDATE productos SET activo = 0 WHERE id_producto = :id";
    
    // prepare(): Prepara la sentencia de borrado lógico
    $stmt = $conexion->prepare($sql);
    
    // execute(): Desactiva el producto sin borrarlo físicamente
    $stmt->execute(['id' => (int) $_POST['id_producto']]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Productos | Mundo Tech</title>
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
        <a class="activo" href="productos.php"><i class="bi bi-box-seam"></i> Productos</a>
        <a href="pedidos.php"><i class="bi bi-receipt"></i> Pedidos</a>
        <a href="../index.html"><i class="bi bi-shop"></i> Ver tienda</a>
      </nav>

      <div class="admin-sidebar-footer">
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesion</a>
      </div>
    </aside>

    <main class="admin-main">
      <div class="admin-topbar">
        <div>
          <h1>Productos</h1>
          <p>Gestiona el catalogo, stock y disponibilidad de la tienda.</p>
        </div>
        <span class="admin-user-chip"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador'); ?></span>
      </div>

      <section class="admin-stats">
        <article class="admin-stat-card"><strong><?php echo $totalProductos; ?></strong><span>Productos registrados</span></article>
        <article class="admin-stat-card"><strong><?php echo $productosActivos; ?></strong><span>Activos en catalogo</span></article>
        <article class="admin-stat-card"><strong><?php echo $stockTotal; ?></strong><span>Unidades en stock</span></article>
      </section>

      <section class="admin-panel mb-4">
        <div class="admin-panel-header">
          <h2>Nuevo producto</h2>
          <span class="text-muted small">Alta rapida desde el panel</span>
        </div>
        <div class="admin-panel-body">
          <form method="POST" class="row g-3">
            <input type="hidden" name="accion" value="crear">
            <div class="col-md-4">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Categoria</label>
              <select name="id_categoria" class="form-select" required>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?php echo $cat['id_categoria']; ?>"><?php echo $cat['nombre']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Nivel</label>
              <select name="nivel" class="form-select">
                <option>Basico</option>
                <option>Intermedio</option>
                <option>Avanzado</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Precio (S/)</label>
              <input type="number" name="precio" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Stock</label>
              <input type="number" name="stock" class="form-control" value="10" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Descripcion</label>
              <input type="text" name="descripcion" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Ruta imagen</label>
              <input type="text" name="imagen" class="form-control" value="assets/categoria-componente.webp">
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-admin-primary w-100">Crear producto</button>
            </div>
          </form>
        </div>
      </section>

      <section class="admin-panel">
        <div class="admin-panel-header">
          <h2>Inventario actual</h2>
          <span class="text-muted small"><?php echo $totalProductos; ?> registros</span>
        </div>
        <div class="admin-panel-body table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoria</th>
                <th>Nivel</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productos as $p): ?>
              <tr>
                <td><?php echo $p['id_producto']; ?></td>
                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                <td><?php echo htmlspecialchars($p['categoria']); ?></td>
                <td><?php echo $p['nivel']; ?></td>
                <td>S/ <?php echo number_format($p['precio'], 2); ?></td>
                <td><?php echo $p['stock']; ?></td>
                <td>
                  <span class="admin-badge <?php echo $p['activo'] ? 'activo' : 'inactivo'; ?>">
                    <?php echo $p['activo'] ? 'Activo' : 'Inactivo'; ?>
                  </span>
                </td>
                <td>
                  <button
                    type="button"
                    class="btn btn-sm btn-admin-danger"
                    data-eliminar-id="<?php echo $p['id_producto']; ?>"
                    data-eliminar-nombre="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES); ?>"
                  >
                    Eliminar
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <div class="modal fade" id="modalEliminarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          Se desactivara <strong id="nombreProductoEliminar"></strong> del catalogo publico.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <form method="POST" id="formEliminarProducto" class="d-inline">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id_producto" value="">
            <button type="submit" class="btn btn-danger">Si, eliminar</button>
          </form>
        </div>
      </div>
    </div>
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