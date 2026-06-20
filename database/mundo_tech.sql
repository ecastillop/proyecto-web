-- ============================================================
-- MUNDO TECH - Script de creacion de base de datos MySQL
-- ============================================================

DROP DATABASE IF EXISTS mundo_tech;
CREATE DATABASE mundo_tech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mundo_tech;

-- Tabla de categorias de productos
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(60) NOT NULL UNIQUE,
    descripcion VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- Tabla de usuarios del sistema (administrador y cliente)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'cliente') NOT NULL DEFAULT 'cliente',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla principal de productos del catalogo
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    nivel ENUM('Basico', 'Intermedio', 'Avanzado') NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    descripcion TEXT NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    stock INT NOT NULL DEFAULT 10,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB;

-- Catalogo de objetivos de uso (oficina, gaming, etc.)
CREATE TABLE objetivos (
    id_objetivo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Catalogo de preferencias de compra (precio, rendimiento, etc.)
CREATE TABLE preferencias (
    id_preferencia INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Relacion muchos a muchos: producto - objetivo
CREATE TABLE producto_objetivo (
    id_producto INT NOT NULL,
    id_objetivo INT NOT NULL,
    PRIMARY KEY (id_producto, id_objetivo),
    CONSTRAINT fk_po_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    CONSTRAINT fk_po_objetivo FOREIGN KEY (id_objetivo) REFERENCES objetivos(id_objetivo) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Relacion muchos a muchos: producto - preferencia
CREATE TABLE producto_preferencia (
    id_producto INT NOT NULL,
    id_preferencia INT NOT NULL,
    PRIMARY KEY (id_producto, id_preferencia),
    CONSTRAINT fk_pp_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    CONSTRAINT fk_pp_preferencia FOREIGN KEY (id_preferencia) REFERENCES preferencias(id_preferencia) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla de pedidos realizados por clientes
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT DEFAULT NULL,
    nombre_cliente VARCHAR(100) NOT NULL,
    correo_cliente VARCHAR(120) NOT NULL,
    telefono VARCHAR(20) DEFAULT NULL,
    direccion TEXT DEFAULT NULL,
    total DECIMAL(10, 2) NOT NULL,
    estado ENUM('nuevo', 'en_proceso', 'entregado', 'cancelado') NOT NULL DEFAULT 'nuevo',
    fecha_pedido TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedido_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- Detalle de cada producto incluido en un pedido
CREATE TABLE detalle_pedido (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_detalle_pedido FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
) ENGINE=InnoDB;

-- Mensajes enviados desde el formulario de contacto
CREATE TABLE contactos (
    id_contacto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(120) NOT NULL,
    tipo_consulta VARCHAR(50) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- DATOS INICIALES
-- ============================================================

INSERT INTO categorias (nombre, descripcion) VALUES
('Laptops', 'Equipos portatiles para trabajo, estudio y gaming'),
('PC', 'Computadoras de escritorio y workstations'),
('Celulares', 'Smartphones y telefonos moviles'),
('Monitores', 'Pantallas para escritorio y productividad'),
('Componentes', 'Partes internas y mejoras de hardware'),
('Accesorios', 'Perifericos y complementos');

INSERT INTO objetivos (nombre) VALUES
('Oficina'), ('Estudio'), ('Movilidad'), ('Diseno'), ('Gaming');

INSERT INTO preferencias (nombre) VALUES
('Precio'), ('Portabilidad'), ('Equilibrio'), ('Rendimiento');

-- Contrasena por defecto: ejecutar php/instalar.php para generar hash de admin123
INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES
('Administrador Mundo Tech', 'admin@mundotech.pe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador'),
('Cliente Demo', 'cliente@mundotech.pe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente');

INSERT INTO productos (id_categoria, nombre, nivel, precio, descripcion, imagen, stock) VALUES
(1, 'Notebook Pulse 14', 'Basico', 1699.00, 'Ryzen 5, 8 GB RAM y SSD de 512 GB. Buena base para clases, oficina y uso diario.', 'assets/categoria-pulse14.webp', 15),
(1, 'Notebook Frame 15', 'Intermedio', 2699.00, 'Core i5, 16 GB RAM y SSD de 512 GB. Pensada para multitarea, trabajo y diseno ligero.', 'assets/categoria-frame15.webp', 12),
(1, 'Notebook Creator 16', 'Avanzado', 4199.00, 'Ryzen 7, 16 GB RAM y grafica dedicada. Ideal para edicion, render y gaming exigente.', 'assets/categoria-creator16.webp', 8),
(2, 'PC WorkStation A5', 'Intermedio', 2899.00, 'Torre con Ryzen 5, 16 GB RAM y SSD NVMe. Rinde muy bien para trabajo continuo y multitarea.', 'assets/categoria-a5.webp', 10),
(2, 'PC Studio RTX', 'Avanzado', 4899.00, 'Equipo con grafica dedicada, 32 GB RAM y almacenamiento rapido para produccion y gaming.', 'assets/categoria-rtx.webp', 6),
(3, 'Celular Orbit 128', 'Basico', 899.00, 'Pantalla amplia, bateria duradera y 128 GB de almacenamiento para uso diario sin complicaciones.', 'assets/categoria-celular.webp', 20),
(3, 'Celular Orbit Pro 256', 'Intermedio', 1599.00, 'Mas memoria, mejor camara y mejor respuesta general para productividad, contenido y movilidad.', 'assets/categoria-celular.webp', 14),
(4, 'Monitor View 24 IPS', 'Basico', 599.00, 'Panel IPS Full HD de 24 pulgadas para escritorio, clases, oficina y consumo multimedia.', 'assets/categoria-monitor.webp', 18),
(4, 'Monitor View 27 QHD', 'Intermedio', 1299.00, 'Mas area de trabajo y mejor definicion para edicion, productividad y entretenimiento.', 'assets/categoria-monitor.webp', 11),
(5, 'SSD NVMe 500 GB', 'Basico', 229.00, 'Una mejora simple y efectiva para acelerar el inicio del sistema y la carga de programas.', 'assets/categoria-componente.webp', 30),
(5, 'SSD NVMe 1 TB', 'Intermedio', 419.00, 'Mas capacidad y mejor margen para proyectos, juegos, archivos pesados y trabajo continuo.', 'assets/categoria-componente.webp', 25),
(5, 'RAM DDR4 16 GB Kit', 'Intermedio', 329.00, 'Kit 2x8 GB para mejorar multitarea y dar mayor soltura a programas de uso intensivo.', 'assets/categoria-ramm.webp', 22),
(5, 'GPU Orbit 12 GB', 'Avanzado', 2799.00, 'Grafica dedicada para render, creacion de contenido y juegos con mayor exigencia grafica.', 'assets/categoria-componente.webp', 7),
(6, 'Teclado Slim Pro', 'Intermedio', 249.00, 'Perfil delgado, buena respuesta al tacto y estetica limpia para escritorio de trabajo.', 'assets/categoria-teclado.webp', 35),
(6, 'Mouse Ergo Wireless', 'Basico', 119.00, 'Mouse inalambrico comodo, practico y facil de llevar para trabajo y clases.', 'assets/categoria-mouse.webp', 40),
(6, 'Combo Gamer Shadow', 'Intermedio', 499.00, 'Set con teclado, mouse y audifonos para completar una base gamer sin comprar por separado.', 'assets/categoria-combo.webp', 16);

-- Relaciones producto - objetivo
INSERT INTO producto_objetivo (id_producto, id_objetivo) VALUES
(1, 1), (1, 2), (1, 3),
(2, 1), (2, 2), (2, 4),
(3, 4), (3, 5),
(4, 1), (4, 2),
(5, 4), (5, 5),
(6, 3), (6, 2),
(7, 3), (7, 2), (7, 1),
(8, 1), (8, 2),
(9, 4), (9, 1), (9, 5),
(10, 1), (10, 2), (10, 5),
(11, 1), (11, 4), (11, 5),
(12, 1), (12, 4), (12, 5),
(13, 5), (13, 4),
(14, 1), (14, 5), (14, 2),
(15, 1), (15, 2), (15, 3),
(16, 5);

-- Relaciones producto - preferencia
INSERT INTO producto_preferencia (id_producto, id_preferencia) VALUES
(1, 1), (1, 2), (1, 3),
(2, 3), (2, 2), (2, 4),
(3, 4),
(4, 3), (4, 4),
(5, 4),
(6, 1), (6, 2),
(7, 3), (7, 2),
(8, 1), (8, 3),
(9, 3), (9, 4),
(10, 1), (10, 3),
(11, 3), (11, 4),
(12, 3), (12, 4),
(13, 4),
(14, 3), (14, 2),
(15, 1), (15, 2),
(16, 3), (16, 4);
