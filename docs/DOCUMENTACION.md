# Documentacion Tecnica - Mundo Tech

**Curso:** Taller de Programacion Web  
**Universidad:** UTP - Facultad de Ingenieria  
**Periodo:** I-2026  
**Integrantes:** Allpoc Vargas Martin Jhonel, Castillo de la Pena Elvis Eugenio, Veliz Atoche Deyvis, Meza Calderon Jheferson Guido, Gonzales Flores Leonardo Martin

---

## 1. Descripcion del proyecto

Mundo Tech es una aplicacion web de comercio electronico para una tienda ficticia de tecnologia en Lima, Peru. El sistema permite:

- Explorar un catalogo de productos con filtros
- Agregar productos a un carrito de compras
- Registrar pedidos en base de datos MySQL
- Enviar consultas mediante formulario de contacto
- Recibir recomendaciones de compra segun presupuesto y uso
- Administrar productos y pedidos desde un panel PHP

### Stack tecnologico

| Capa        | Tecnologia                          |
|-------------|-------------------------------------|
| Frontend    | HTML5, CSS3, JavaScript, Bootstrap 5|
| Backend     | PHP 8.x (PDO)                       |
| Base datos  | MySQL 8.x / MariaDB                 |
| Servidor    | XAMPP, WAMP o Laragon               |

---

## 2. Estructura del proyecto

```
proyecto-web-main/
|-- index.html              Pagina principal y catalogo
|-- asesor.html             Asesor de compra
|-- contacto.html           Formulario de contacto y mapa
|-- multimedia.html         Video de presentacion
|-- Ofertas.html            Seccion de promociones (en desarrollo)
|-- styles.css              Estilos personalizados
|-- products.js             Respaldo local de productos (fallback)
|-- app.js                  Logica del frontend
|-- assets/                 Imagenes, video y audio
|-- database/
|   |-- mundo_tech.sql      Script de creacion e inserts
|-- php/
|   |-- config/conexion.php Conexion PDO a MySQL
|   |-- includes/           Funciones auxiliares
|   |-- api/                Endpoints REST JSON
|   |-- instalar.php        Configura contrasenas demo
|-- admin/
    |-- login.php           Acceso administrador
    |-- productos.php       CRUD de productos
    |-- pedidos.php         Gestion de pedidos
    |-- logout.php          Cerrar sesion
```

---

## 3. Base de datos MySQL

### 3.1 Diagrama entidad-relacion

Archivo PlantUML: [`diagramas/er-mundo-tech.puml`](diagramas/er-mundo-tech.puml)

Relaciones principales:

- `categorias` (1) → (N) `productos`
- `productos` (N) ↔ (N) `objetivos` mediante `producto_objetivo`
- `productos` (N) ↔ (N) `preferencias` mediante `producto_preferencia`
- `usuarios` (1) → (N) `pedidos`
- `pedidos` (1) → (N) `detalle_pedido` → (N) `productos`
- `contactos` (tabla independiente)

### 3.2 Tablas principales

| Tabla              | Descripcion                                      |
|--------------------|--------------------------------------------------|
| categorias         | Laptops, PC, Celulares, Monitores, etc.          |
| productos          | Catalogo con precio, stock, nivel y descripcion  |
| objetivos          | Oficina, Estudio, Gaming, Diseno, Movilidad      |
| preferencias       | Precio, Portabilidad, Equilibrio, Rendimiento    |
| producto_objetivo  | Relacion producto-objetivo (N:N)                 |
| producto_preferencia | Relacion producto-preferencia (N:N)            |
| usuarios           | Administradores y clientes                       |
| pedidos            | Cabecera del pedido con datos del cliente        |
| detalle_pedido     | Lineas del pedido (producto, cantidad, precio)   |
| contactos          | Mensajes del formulario de contacto              |

### 3.3 Instalacion de la base de datos

1. Iniciar Apache y MySQL en XAMPP/WAMP/Laragon
2. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
3. Importar el archivo `database/mundo_tech.sql`
4. Verificar que exista la base de datos `mundo_tech` con 16 productos
5. Ejecutar una vez: `http://localhost/proyecto-web-main/php/instalar.php`

### 3.4 Credenciales demo

| Rol           | Correo               | Contrasena |
|---------------|----------------------|------------|
| Administrador | admin@mundotech.pe   | admin123   |
| Cliente       | cliente@mundotech.pe | admin123   |

---

## 4. Backend PHP - API REST

### 4.1 Endpoints disponibles

| Metodo | URL                              | Descripcion                    |
|--------|----------------------------------|--------------------------------|
| GET    | php/api/productos.php?accion=listar | Lista productos activos     |
| GET    | php/api/productos.php?accion=recomendar | Recomienda productos segun asesor |
| GET    | php/api/productos.php?accion=obtener&id=1 | Obtiene un producto   |
| POST   | php/api/productos.php (accion=crear)      | Crea producto (admin) |
| POST   | php/api/productos.php (accion=actualizar)   | Actualiza producto  |
| POST   | php/api/productos.php (accion=eliminar)     | Borrado logico      |
| POST   | php/api/pedidos.php              | Registra pedido desde carrito  |
| POST   | php/api/contacto.php             | Guarda mensaje de contacto     |
| POST   | php/api/login.php                  | Inicia sesion JSON           |
| GET    | php/api/categorias.php?accion=listar | Lista categorias           |

### 4.2 Formato de respuesta JSON

Exito:
```json
{
  "exito": true,
  "mensaje": "Productos obtenidos correctamente.",
  "productos": [...]
}
```

Error:
```json
{
  "exito": false,
  "mensaje": "Descripcion del error"
}
```

### 4.3 Ejemplo: registrar pedido

Request POST `php/api/pedidos.php`:

```json
{
  "nombre_cliente": "Juan Perez",
  "correo_cliente": "juan@correo.com",
  "telefono": "999888777",
  "direccion": "Av. Wilson 845, Lima",
  "items": [
    { "id": 1, "cantidad": 2 },
    { "id": 10, "cantidad": 1 }
  ]
}
```

### 4.4 Ejemplo: asesor de compra

Request GET al pulsar **Ver recomendacion** en `asesor.html`:

```
php/api/productos.php?accion=recomendar&presupuesto=2500&objetivo=Gaming&preferencia=Rendimiento
```

Respuesta exitosa:

```json
{
  "exito": true,
  "mensaje": "Recomendacion generada correctamente.",
  "productos": [ ... ],
  "nivel": "Intermedio",
  "total": 4
}
```

---

## 4.5 Frontend JavaScript (`app.js`)

Comunicacion con PHP mediante `fetch()` y `async/await`:

| Funcion | Cuando se ejecuta | Endpoint |
|---------|-------------------|----------|
| `cargarProductosDesdeApi()` | Al cargar paginas con catalogo | GET `productos.php?accion=listar` |
| `generarRecomendacion()` | Boton Ver recomendacion en asesor | GET `productos.php?accion=recomendar` |
| `procesarCompra()` | Confirmar pedido en carrito | POST `pedidos.php` |
| `enviarFormularioContacto()` | Enviar mensaje en contacto | POST `contacto.php` |

Patron usado en cada llamada:

```javascript
const respuesta = await fetch(url, opciones); // peticion HTTP asincrona
const datos = await respuesta.json();         // convierte respuesta a objeto
if (datos.exito) { /* usar datos */ }
```

Modales Bootstrap (`mostrarMensaje`, `mostrarConfirmacion`, `mostrarCheckout`) reemplazan `alert()`, `confirm()` y `prompt()`.

---

## 5. Diagramas del sistema (PlantUML)

Todos los diagramas estan en [`diagramas/`](diagramas/). Cada archivo usa **participantes funcionales** (Cliente, Servicio de pedidos, Base de datos, etc.) y una nota **Referencia tecnica** con los archivos o tablas involucradas.

Para visualizarlos: extension PlantUML en el editor, [plantuml.com](https://www.plantuml.com/plantuml/uml/) o `java -jar plantuml.jar docs/diagramas/*.puml`.

### 5.1 Modelo de datos y arquitectura

| Diagrama | Archivo | Enfoque |
|----------|---------|---------|
| Entidad-relacion | [`er-mundo-tech.puml`](diagramas/er-mundo-tech.puml) | Modelo relacional de tablas y cardinalidades |
| Flujo general | [`flujo-general.puml`](diagramas/flujo-general.puml) | Arquitectura funcional cliente/admin + notas tecnicas |

### 5.2 Flujos del cliente (tienda)

| Diagrama | Archivo | Proceso de negocio |
|----------|---------|-------------------|
| Consulta de catalogo | [`secuencia-catalogo.puml`](diagramas/secuencia-catalogo.puml) | Cliente consulta productos disponibles |
| Filtros y busqueda | [`secuencia-filtros.puml`](diagramas/secuencia-filtros.puml) | Cliente acota resultados del catalogo |
| Carrito de compras | [`secuencia-carrito.puml`](diagramas/secuencia-carrito.puml) | Cliente agrega, quita o vacia productos |
| Asesor de compra | [`secuencia-asesor.puml`](diagramas/secuencia-asesor.puml) | Cliente recibe recomendacion personalizada |
| Registro de pedido | [`secuencia-pedido.puml`](diagramas/secuencia-pedido.puml) | Cliente confirma compra y se registra pedido |
| Contacto | [`secuencia-contacto.puml`](diagramas/secuencia-contacto.puml) | Cliente envia consulta a la tienda |

### 5.3 Flujos del administrador

| Diagrama | Archivo | Proceso de negocio |
|----------|---------|-------------------|
| Autenticacion | [`secuencia-login-admin.puml`](diagramas/secuencia-login-admin.puml) | Administrador accede al panel |
| Gestion de productos | [`secuencia-crud-productos.puml`](diagramas/secuencia-crud-productos.puml) | Alta y baja logica de productos |
| Gestion de pedidos | [`secuencia-pedidos-admin.puml`](diagramas/secuencia-pedidos-admin.puml) | Seguimiento y cambio de estado |

---

## 6. Configuracion del servidor local

### 6.1 XAMPP (recomendado)

1. Copiar la carpeta `proyecto-web-main` a `C:\xampp\htdocs\`
2. Iniciar Apache y MySQL desde el panel de control
3. Importar `database/mundo_tech.sql` en phpMyAdmin
4. Ejecutar `http://localhost/proyecto-web-main/php/instalar.php`
5. Acceder a la tienda: `http://localhost/proyecto-web-main/index.html`
6. Panel admin: `http://localhost/proyecto-web-main/admin/login.php`

### 6.2 Configuracion de conexion

Editar `php/config/conexion.php` si tus credenciales difieren:

```php
define('DB_HOST', 'localhost');
define('DB_NOMBRE', 'mundo_tech');
define('DB_USUARIO', 'root');
define('DB_CONTRASENA', '');  // vacio por defecto en XAMPP
```

---

## 7. Cumplimiento de la rubrica (Avance 03)

| Criterio rubrica              | Implementacion en el proyecto                        |
|-------------------------------|------------------------------------------------------|
| HTML5 semantico               | header, nav, main, section, article, footer, aside   |
| Formularios con validacion    | contacto.html, asesor.html, admin/login.php          |
| CSS con clases e IDs          | styles.css con variables y componentes reutilizables |
| Bootstrap                     | CDN en todas las paginas + panel admin completo      |
| Diseno responsive             | CSS media queries + grid Bootstrap en mapa/contacto  |
| JavaScript dinamico           | app.js: filtros, carrito, fetch API, recomendador    |
| PHP procesamiento             | api/*.php + admin/*.php                              |
| Conexion MySQL y CRUD         | PDO + tabla productos (crear, listar, eliminar)      |
| Multimedia                    | Imagenes, video (multimedia.html), mapa (contacto)   |
| Servidor local                | Compatible con XAMPP/WAMP/Laragon                    |

---

## 8. Flujo de datos general

Diagrama de arquitectura: [`diagramas/flujo-general.puml`](diagramas/flujo-general.puml)

Resumen:

- El navegador carga HTML/CSS/Bootstrap y ejecuta `app.js`.
- El carrito se persiste en `localStorage` hasta confirmar el pedido.
- Las operaciones de tienda usan `fetch()` hacia `php/api/*.php` y estas consultan MySQL con PDO.
- El panel admin (`admin/*.php`) gestiona sesiones, productos y pedidos directamente contra la BD.

---

## 9. Seguridad implementada (nivel academico)

- Consultas preparadas (PDO) para prevenir inyeccion SQL
- `htmlspecialchars()` para sanitizar salida (XSS basico)
- `password_hash()` / `password_verify()` para contrasenas
- Validacion de correo con `filter_var()`
- Control de sesion por rol en panel admin
- Transacciones SQL en registro de pedidos
- Borrado logico de productos (activo = 0)

---

## 10. Pruebas sugeridas

1. Abrir `index.html` y verificar que cargan 16 productos desde la API
2. Filtrar por categoria "Laptops" y nivel "Intermedio"
3. Agregar productos al carrito y confirmar pedido
4. Verificar en phpMyAdmin que el pedido aparece en tablas `pedidos` y `detalle_pedido`
5. Enviar formulario de contacto y verificar registro en tabla `contactos`
6. Ingresar al panel admin y crear/eliminar un producto
7. Cambiar estado de un pedido a "en_proceso"
8. Probar en modo responsive (F12 > dispositivo movil)

---

## 11. Trabajo futuro (avance final)

- Integrar pasarela de pago simulada
- Modulo "Arma tu PC" con compatibilidad de componentes
- Autenticacion de clientes en el checkout
- API REST completa con JWT
- Paginacion del catalogo
- Seccion de ofertas con descuentos dinamicos

---

## 12. Referencias

- OWASP Top 10 - Seguridad en aplicaciones web
- WCAG 2.2 - Accesibilidad web
- Bootstrap 5 Documentation - https://getbootstrap.com/docs/5.3/
- PHP PDO - https://www.php.net/manual/es/book.pdo.php
- PlantUML - https://plantuml.com/sequence-diagram

---

*Documento generado para el Avance 03 del proyecto integrador Mundo Tech.*
