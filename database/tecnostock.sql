-- ============================================
-- TecnoStock - Script de base de datos (Auditado)
-- Ejecutar en phpMyAdmin o por terminal MySQL
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS tecnostock;
CREATE DATABASE tecnostock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tecnostock;

-- --------------------------------------------
-- Tabla: usuario
-- --------------------------------------------
CREATE TABLE usuario (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    correo     VARCHAR(150) NOT NULL UNIQUE,
    clave      VARCHAR(255) NOT NULL,
    estado     TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabla: categoria
-- --------------------------------------------
CREATE TABLE categoria (
    id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL UNIQUE,
    descripcion  VARCHAR(255) NULL,
    estado       TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabla: producto
-- --------------------------------------------
CREATE TABLE producto (
    id_producto   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo        VARCHAR(50)  NOT NULL UNIQUE,
    nombre        VARCHAR(150) NOT NULL,
    descripcion   VARCHAR(255) NULL,
    precio        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_actual  INT UNSIGNED NOT NULL DEFAULT 0,
    stock_minimo  INT UNSIGNED NOT NULL DEFAULT 0,
    estado        TINYINT(1)   NOT NULL DEFAULT 1,
    id_categoria  INT UNSIGNED NOT NULL,
    CONSTRAINT chk_producto_precio CHECK (precio >= 0),
    CONSTRAINT fk_producto_categoria FOREIGN KEY (id_categoria)
        REFERENCES categoria(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Tabla: movimiento
-- --------------------------------------------
CREATE TABLE movimiento (
    id_movimiento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha_hora    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo          ENUM('entrada','salida') NOT NULL,
    cantidad      INT UNSIGNED NOT NULL,
    observacion   VARCHAR(255) NULL,
    id_producto   INT UNSIGNED NOT NULL,
    id_usuario    INT UNSIGNED NOT NULL,
    CONSTRAINT chk_movimiento_cantidad CHECK (cantidad > 0),
    CONSTRAINT fk_movimiento_producto FOREIGN KEY (id_producto)
        REFERENCES producto(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_movimiento_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_movimiento_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- Datos de prueba (DATOS LIMPIOS Y REALISTAS)
-- --------------------------------------------

-- Usuario: encargado@tecnostock.com / contraseña: password
INSERT INTO usuario (nombre, correo, clave, estado) VALUES
('Encargado de Inventario', 'encargado@tecnostock.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

INSERT INTO categoria (nombre, descripcion, estado) VALUES
('Cables',     'Cables USB, HDMI, etc.',                    1),
('Cargadores', 'Cargadores y adaptadores de corriente',     1),
('Fundas',     'Fundas y protectores para dispositivos',    1),
('Audífonos',  'Audífonos y auriculares',                   1);

-- Productos tecnológicos reales. CAB-002 y CAR-002 tienen stock bajo a propósito.
INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria) VALUES
('CAB-001', 'Cable USB-C 1m',        'Cable USB-C a USB-A de 1 metro',       3500.00, 25,  5, 1, 1),
('CAB-002', 'Cable HDMI 2m',         'Cable HDMI 2.0 de 2 metros',            5500.00,  3,  5, 1, 1),
('CAR-001', 'Cargador 20W',          'Cargador USB-C de 20W',                 8500.00, 15,  3, 1, 2),
('CAR-002', 'Cargador inalámbrico',  'Base de carga inalámbrica 15W',        12000.00,  2,  5, 1, 2),
('FUN-001', 'Funda universal 6"',    'Funda protectora para celular',         2500.00, 40, 10, 1, 3),
('AUD-001', 'Audífonos Bluetooth',   'Audífonos inalámbricos con estuche',   18000.00,  8,  3, 1, 4);

INSERT INTO movimiento (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario) VALUES
(NOW(), 'entrada', 25, 'Carga inicial de stock', 1, 1),
(NOW(), 'entrada', 15, 'Carga inicial de stock', 3, 1),
(NOW(), 'salida',   2, 'Venta mostrador',        1, 1);

SET FOREIGN_KEY_CHECKS = 1;
