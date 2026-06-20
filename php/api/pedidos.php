<?php
/**
 * ==============================================================================
 * HEADER: GLOSARIO DE MÉTODOS Y PALABRAS CLAVE DEL ARCHIVO
 * ==============================================================================
 * - require_once: Importa un archivo externo asegurando que se incluya solo una vez.
 * - $_SERVER: Variable superglobal con información del entorno del servidor y la petición actual.
 * - try...catch: Estructura de control que intenta ejecutar código y captura errores (excepciones).
 * - PDOException: Clase que representa un error emitido por la base de datos a través de PDO.
 * - Exception: Clase base nativa de PHP para lanzar y capturar errores generales o lógicos.
 * - ?? (Fusión null): Operador que asigna un valor por defecto si la variable a su izquierda es nula o indefinida.
 * - empty(): Determina si una variable está vacía, es cero o no ha sido definida.
 * - is_array(): Comprueba estructuralmente si una variable es de tipo array.
 * - beginTransaction(): Inicia una transacción en la base de datos para ejecutar múltiples consultas como un solo bloque seguro (todo o nada).
 * - foreach: Bucle iterativo diseñado específicamente para recorrer cada elemento de un array o lista.
 * - throw new: Fuerza la interrupción inmediata del flujo del código lanzando un error personalizado.
 * - prepare(): Prepara una sentencia SQL en el servidor usando parámetros, previniendo inyección SQL.
 * - execute(): Ejecuta la consulta SQL previamente preparada inyectando los valores de forma segura.
 * - fetch(): Extrae una única fila del conjunto de resultados de la consulta SQL.
 * - lastInsertId(): Obtiene el ID autogenerado del último registro insertado en la tabla de la base de datos.
 * - commit(): Confirma y guarda permanentemente en la base de datos todas las consultas ejecutadas en la transacción actual.
 * - rollBack(): Deshace y revierte todas las consultas de la transacción actual si ocurrió un error, dejando la BD intacta.
 * ==============================================================================
 */

/**
 * API de pedidos - Registro de compras desde el carrito
 * Endpoint: POST con JSON { nombre_cliente, correo_cliente, telefono, direccion, items[] }
 */

// require_once: Carga dependencias esenciales y bloquea su re-inclusión accidental
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../includes/respuesta.php';
require_once __DIR__ . '/../includes/validaciones.php';

configurarCors();

// Solo acepta peticiones POST para crear pedidos
// $_SERVER: Verifica que el cliente haya usado estrictamente el método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarError('Metodo no permitido. Use POST.', 405);
}

// try...catch: Abre el bloque principal para intentar la conexión y la lógica del pedido
try {
    $conexion = obtenerConexion();
    registrarPedido($conexion);
// PDOException: Captura errores críticos a nivel de infraestructura de base de datos
} catch (PDOException $e) {
    enviarError('Error al registrar pedido: ' . $e->getMessage(), 500);
// Exception: Captura errores de validación y lógica de negocio (como falta de stock)
} catch (Exception $e) {
    enviarError($e->getMessage(), 400);
}

/**
 * Registra un pedido completo con su detalle en la base de datos.
 */
function registrarPedido($conexion)
{
    // Lee los datos del pedido enviados como JSON
    $datos = leerJsonEntrada();

    // Extrae y limpia los datos del cliente
    // ?? (Fusión null): Asigna un string vacío si la clave no existe en el JSON de entrada
    $nombre = limpiarTexto($datos['nombre_cliente'] ?? '');
    $correo = limpiarTexto($datos['correo_cliente'] ?? '');
    $telefono = limpiarTexto($datos['telefono'] ?? '');
    $direccion = limpiarTexto($datos['direccion'] ?? '');
    $items = $datos['items'] ?? [];

    // Valida que los campos obligatorios no esten vacios
    // empty(): Verifica que los datos críticos del cliente tengan contenido real
    if (empty($nombre) || empty($correo)) {
        enviarError('Nombre y correo del cliente son obligatorios.');
    }

    if (!esCorreoValido($correo)) {
        enviarError('El correo electronico no es valido.');
    }

    // is_array(): Asegura que el listado de items tenga la estructura de datos correcta para poder iterarla
    if (empty($items) || !is_array($items)) {
        enviarError('El pedido debe incluir al menos un producto.');
    }

    // Inicia transaccion para garantizar integridad (todo o nada)
    // beginTransaction(): Pausa el autoguardado de MySQL para ejecutar los inserts y updates en bloque
    $conexion->beginTransaction();

    // try...catch: Sub-bloque para monitorear las consultas internas del pedido
    try {
        $total = 0;
        $detalles = [];

        // Recorre cada item del carrito y valida stock y precio
        // foreach: Itera sobre el array de items enviados por el frontend
        foreach ($items as $item) {
        $idProducto = (int) ($item['id'] ?? 0);
        $cantidad = (int) ($item['cantidad'] ?? 0);

        if ($idProducto <= 0 || $cantidad <= 0) {
            // throw new: Aborta el proceso si el producto o cantidad están corruptos
            throw new Exception('Item de pedido no valido.');
        }

        // Consulta el precio y stock actual del producto en la BD
        $sqlProducto = "SELECT precio, stock, nombre FROM productos WHERE id_producto = :id AND activo = 1";
        
        // prepare(): Compila la consulta del producto individual en el servidor BD
        $stmtProducto = $conexion->prepare($sqlProducto);
        
        // execute(): Pasa el ID del producto limpiado como parámetro
        $stmtProducto->execute(['id' => $idProducto]);
        
        // fetch(): Extrae únicamente el registro del producto solicitado
        $producto = $stmtProducto->fetch();

        if (!$producto) {
            // throw new: Lanza error si el ID enviado no existe en la BD
            throw new Exception('Producto ID ' . $idProducto . ' no encontrado.');
        }

        if ($producto['stock'] < $cantidad) {
            // throw new: Lanza error si el cliente pide más cantidad de la que hay disponible
            throw new Exception('Stock insuficiente para: ' . $producto['nombre']);
        }

        // Calcula subtotal de la linea del pedido
        $precioUnitario = (float) $producto['precio'];
        $subtotal = $precioUnitario * $cantidad;
        $total += $subtotal;

        $detalles[] = [
            'id_producto' => $idProducto,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal' => $subtotal,
        ];
    }

    // Inserta el registro principal del pedido
    $sqlPedido = "INSERT INTO pedidos (nombre_cliente, correo_cliente, telefono, direccion, total, estado)
                  VALUES (:nombre, :correo, :telefono, :direccion, :total, 'nuevo')";

    // prepare(): Alista el insert del documento principal del pedido
    $stmtPedido = $conexion->prepare($sqlPedido);
    
    // execute(): Guarda los datos generales de la compra en la tabla pedidos
    $stmtPedido->execute([
        'nombre' => $nombre,
        'correo' => $correo,
        'telefono' => $telefono,
        'direccion' => $direccion,
        'total' => $total,
    ]);

    // Obtiene el ID del pedido recien creado
    // lastInsertId(): Rescata el ID autoincremental que MySQL le acaba de dar a este pedido
    $idPedido = (int) $conexion->lastInsertId();

    // Inserta cada linea del detalle y descuenta stock
    $sqlDetalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
                   VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario, :subtotal)";

    $sqlStock = "UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto";

    // foreach: Recorre el array de detalles previamente validado y calculado
    foreach ($detalles as $detalle) {
        // prepare(): Compila el insert para cada fila del recibo
        $stmtDetalle = $conexion->prepare($sqlDetalle);
        
        // execute(): Registra el producto específico atándolo al ID principal del pedido
        $stmtDetalle->execute([
            'id_pedido' => $idPedido,
            'id_producto' => $detalle['id_producto'],
            'cantidad' => $detalle['cantidad'],
            'precio_unitario' => $detalle['precio_unitario'],
            'subtotal' => $detalle['subtotal'],
        ]);

        // Reduce el stock disponible del producto vendido
        // prepare(): Prepara la orden de descuento en el inventario
        $stmtStock = $conexion->prepare($sqlStock);
        
        // execute(): Aplica la resta matemática en el stock del producto
        $stmtStock->execute([
            'cantidad' => $detalle['cantidad'],
            'id_producto' => $detalle['id_producto'],
        ]);
    }

        // Confirma todos los cambios en la base de datos
        // commit(): Cierra la transacción fijando permanentemente todos los INSERTS y el UPDATE
        $conexion->commit();

        enviarExito('Pedido registrado correctamente.', [
            'id_pedido' => $idPedido,
            'total' => $total,
        ]);
    } catch (Exception $e) {
        // Revierte cambios si ocurre un error durante la transaccion
        // rollBack(): Si faltó stock o falló algo en medio proceso, revierte todas las inserciones evitando datos fantasma
        $conexion->rollBack();
        
        // throw: Relanza la excepción hacia el bloque catch principal para ser enviada al frontend
        throw $e;
    }
}