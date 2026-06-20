<?php
/**
 * API REST de productos - CRUD basico
 * Endpoints:
 *   GET    ?accion=listar          -> Lista productos activos
 *   GET    ?accion=obtener&id=1    -> Obtiene un producto
 *   GET    ?accion=recomendar      -> Recomienda productos segun criterios del asesor
 *   POST   accion=crear            -> Crea producto (admin)
 *   PUT    accion=actualizar       -> Actualiza producto (admin)
 *   DELETE ?accion=eliminar&id=1   -> Elimina producto (admin, borrado logico)
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/respuesta.php';
require_once __DIR__ . '/../includes/validaciones.php';

configurarCors();

// Lee la accion solicitada desde la URL o el cuerpo JSON
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    // Obtiene la conexion PDO a MySQL
    $conexion = obtenerConexion();

    if ($metodo === 'GET' && $accion === 'listar') {
        listarProductos($conexion);
    } elseif ($metodo === 'GET' && $accion === 'recomendar') {
        recomendarProductos($conexion);
    } elseif ($metodo === 'GET' && $accion === 'obtener') {
        obtenerProducto($conexion);
    } elseif ($metodo === 'POST' && $accion === 'crear') {
        crearProducto($conexion);
    } elseif ($metodo === 'POST' && $accion === 'actualizar') {
        actualizarProducto($conexion);
    } elseif ($metodo === 'POST' && $accion === 'eliminar') {
        eliminarProducto($conexion);
    } else {
        enviarError('Accion no valida o metodo HTTP incorrecto.', 404);
    }
} catch (PDOException $e) {
    // Captura errores de base de datos y los reporta al cliente
    enviarError('Error de base de datos: ' . $e->getMessage(), 500);
}

/**
 * Lista todos los productos activos con categoria, objetivos y preferencias.
 */
function listarProductos($conexion)
{
    // Consulta SQL con JOIN para traer el nombre de la categoria
    $sql = "SELECT p.id_producto AS id, p.nombre, c.nombre AS categoria, p.nivel,
                   p.precio, p.descripcion, p.imagen, p.stock
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE p.activo = 1
            ORDER BY p.id_producto";

    // Ejecuta la consulta y obtiene todas las filas
    $stmt = $conexion->query($sql);
    $productos = $stmt->fetchAll();

    // Agrega objetivos y preferencias a cada producto
    foreach ($productos as &$producto) {
        $producto['nivel'] = formatearNivel($producto['nivel']);
        $producto['precio'] = (float) $producto['precio'];
        $producto['objetivo'] = obtenerObjetivosProducto($conexion, $producto['id']);
        $producto['preferencia'] = obtenerPreferenciasProducto($conexion, $producto['id']);
    }

    // Devuelve el arreglo de productos en formato JSON
    enviarExito('Productos obtenidos correctamente.', ['productos' => $productos]);
}

/**
 * Obtiene un producto especifico por su ID.
 */
function obtenerProducto($conexion)
{
    // Lee el ID desde los parametros GET
    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        enviarError('ID de producto no valido.');
    }

    // Prepara consulta con placeholder para evitar inyeccion SQL
    $sql = "SELECT p.id_producto AS id, p.nombre, c.nombre AS categoria, p.nivel,
                   p.precio, p.descripcion, p.imagen, p.stock
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE p.id_producto = :id AND p.activo = 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id' => $id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        enviarError('Producto no encontrado.', 404);
    }

    $producto['nivel'] = formatearNivel($producto['nivel']);
    $producto['precio'] = (float) $producto['precio'];
    $producto['objetivo'] = obtenerObjetivosProducto($conexion, $id);
    $producto['preferencia'] = obtenerPreferenciasProducto($conexion, $id);

    enviarExito('Producto obtenido correctamente.', ['producto' => $producto]);
}

/**
 * Recomienda productos segun presupuesto, objetivo y preferencia del asesor.
 * Uso: GET ?accion=recomendar&presupuesto=2500&objetivo=Gaming&preferencia=Rendimiento
 * Filtra en MySQL con JOINs; devuelve hasta 4 productos ordenados por precio.
 */
function recomendarProductos($conexion)
{
    // Parametros enviados desde asesor.html via fetch (query string)
    $presupuesto = (float) ($_GET['presupuesto'] ?? 0);
    $objetivo = trim($_GET['objetivo'] ?? '');
    $preferencia = trim($_GET['preferencia'] ?? '');

    $error = validarCriteriosAsesor($presupuesto, $objetivo, $preferencia);
    if ($error) {
        enviarError($error);
    }

    $objetivoBd = normalizarObjetivoBd($objetivo);
    $niveles = obtenerNivelesConsultaAsesor($presupuesto);
    $placeholdersNivel = implode(', ', array_fill(0, count($niveles), '?'));
    // Precio: mas barato primero; resto de preferencias: mayor precio primero
    $orden = $preferencia === 'Precio' ? 'ASC' : 'DESC';

    $sql = "SELECT DISTINCT p.id_producto AS id, p.nombre, c.nombre AS categoria, p.nivel,
                   p.precio, p.descripcion, p.imagen, p.stock
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            INNER JOIN producto_objetivo po ON po.id_producto = p.id_producto
            INNER JOIN objetivos o ON o.id_objetivo = po.id_objetivo AND o.nombre = ?
            ";

    $parametros = [$objetivoBd];

    if ($preferencia !== 'Equilibrio') {
        $sql .= "INNER JOIN producto_preferencia pp ON pp.id_producto = p.id_producto
                 INNER JOIN preferencias pr ON pr.id_preferencia = pp.id_preferencia AND pr.nombre = ?
                 ";
        $parametros[] = $preferencia;
    }

    $sql .= "WHERE p.activo = 1
             AND p.precio <= ?
             AND p.nivel IN ($placeholdersNivel)
             ORDER BY p.precio $orden
             LIMIT 4";

    $parametros[] = $presupuesto;
    foreach ($niveles as $nivel) {
        $parametros[] = $nivel;
    }

    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    $productos = $stmt->fetchAll();

    foreach ($productos as &$producto) {
        $producto['nivel'] = formatearNivel($producto['nivel']);
        $producto['precio'] = (float) $producto['precio'];
        $producto['objetivo'] = obtenerObjetivosProducto($conexion, $producto['id']);
        $producto['preferencia'] = obtenerPreferenciasProducto($conexion, $producto['id']);
    }

    enviarExito('Recomendacion generada correctamente.', [
        'productos' => $productos,
        'nivel' => formatearNivel(obtenerNivelPorPresupuestoBd($presupuesto)),
        'total' => count($productos),
    ]);
}

/**
 * Crea un nuevo producto en la base de datos.
 */
function crearProducto($conexion)
{
    // Lee los datos enviados como JSON en el cuerpo de la peticion
    $datos = leerJsonEntrada();

    // Valida campos obligatorios del producto
    if (empty($datos['nombre']) || empty($datos['id_categoria']) || empty($datos['precio'])) {
        enviarError('Faltan datos obligatorios: nombre, categoria y precio.');
    }

    // Inserta el registro del producto en la tabla productos
    $sql = "INSERT INTO productos (id_categoria, nombre, nivel, precio, descripcion, imagen, stock)
            VALUES (:id_categoria, :nombre, :nivel, :precio, :descripcion, :imagen, :stock)";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        'id_categoria' => (int) $datos['id_categoria'],
        'nombre' => limpiarTexto($datos['nombre']),
        'nivel' => $datos['nivel'] ?? 'Basico',
        'precio' => (float) $datos['precio'],
        'descripcion' => limpiarTexto($datos['descripcion'] ?? ''),
        'imagen' => limpiarTexto($datos['imagen'] ?? 'assets/categoria-componente.webp'),
        'stock' => (int) ($datos['stock'] ?? 10),
    ]);

    // Obtiene el ID autogenerado del nuevo producto
    $idNuevo = (int) $conexion->lastInsertId();

    enviarExito('Producto creado correctamente.', ['id_producto' => $idNuevo]);
}

/**
 * Actualiza los datos de un producto existente.
 */
function actualizarProducto($conexion)
{
    $datos = leerJsonEntrada();
    $id = (int) ($datos['id_producto'] ?? 0);

    if ($id <= 0) {
        enviarError('ID de producto no valido.');
    }

    // Actualiza los campos del producto usando consulta preparada
    $sql = "UPDATE productos SET
                id_categoria = :id_categoria,
                nombre = :nombre,
                nivel = :nivel,
                precio = :precio,
                descripcion = :descripcion,
                imagen = :imagen,
                stock = :stock
            WHERE id_producto = :id_producto";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        'id_categoria' => (int) $datos['id_categoria'],
        'nombre' => limpiarTexto($datos['nombre']),
        'nivel' => $datos['nivel'] ?? 'Basico',
        'precio' => (float) $datos['precio'],
        'descripcion' => limpiarTexto($datos['descripcion'] ?? ''),
        'imagen' => limpiarTexto($datos['imagen'] ?? ''),
        'stock' => (int) ($datos['stock'] ?? 0),
        'id_producto' => $id,
    ]);

    enviarExito('Producto actualizado correctamente.');
}

/**
 * Realiza borrado logico de un producto (activo = 0).
 */
function eliminarProducto($conexion)
{
    $datos = leerJsonEntrada();
    $id = (int) ($datos['id_producto'] ?? $_GET['id'] ?? 0);

    if ($id <= 0) {
        enviarError('ID de producto no valido.');
    }

    // Marca el producto como inactivo en lugar de borrarlo fisicamente
    $sql = "UPDATE productos SET activo = 0 WHERE id_producto = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id' => $id]);

    enviarExito('Producto eliminado correctamente.');
}

/**
 * Obtiene la lista de objetivos asociados a un producto.
 */
function obtenerObjetivosProducto($conexion, $idProducto)
{
    $sql = "SELECT o.nombre FROM objetivos o
            INNER JOIN producto_objetivo po ON o.id_objetivo = po.id_objetivo
            WHERE po.id_producto = :id";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id' => $idProducto]);

    // array_column extrae solo la columna 'nombre' del resultado
    return array_map('formatearObjetivo', array_column($stmt->fetchAll(), 'nombre'));
}

/**
 * Obtiene la lista de preferencias asociadas a un producto.
 */
function obtenerPreferenciasProducto($conexion, $idProducto)
{
    $sql = "SELECT pr.nombre FROM preferencias pr
            INNER JOIN producto_preferencia pp ON pr.id_preferencia = pp.id_preferencia
            WHERE pp.id_producto = :id";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(['id' => $idProducto]);

    return array_column($stmt->fetchAll(), 'nombre');
}
