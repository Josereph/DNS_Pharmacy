CREATE DATABASE IF NOT EXISTS dns_pharmacy
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE dns_pharmacy;

-- =========================================
-- TABLA 1: roles
-- =========================================
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(150) NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================
-- TABLA 2: usuarios
-- =========================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    ultimo_acceso DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_roles
        FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================
-- TABLA 3: turnos
-- =========================================
CREATE TABLE turnos (
    id_turno INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha DATE NOT NULL,
    hora_entrada TIME NOT NULL,
    hora_salida TIME NULL,
    estado ENUM('abierto','cerrado','cancelado') NOT NULL DEFAULT 'abierto',
    total_ventas DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_tickets INT NOT NULL DEFAULT 0,
    observaciones VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_turnos_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================
-- TABLA 4: categorias
-- =========================================
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================
-- TABLA 5: productos
-- =========================================
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    codigo_barras VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(255) NULL,
    presentacion VARCHAR(80) NULL,
    marca VARCHAR(80) NULL,
    laboratorio VARCHAR(100) NULL,
    precio_compra DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 0,
    unidad_medida VARCHAR(30) NOT NULL DEFAULT 'unidad',
    requiere_receta BOOLEAN NOT NULL DEFAULT FALSE,
    imagen_url VARCHAR(255) NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_productos_categorias
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_productos_precio_compra CHECK (precio_compra >= 0),
    CONSTRAINT chk_productos_precio_venta CHECK (precio_venta >= 0),
    CONSTRAINT chk_productos_stock_actual CHECK (stock_actual >= 0),
    CONSTRAINT chk_productos_stock_minimo CHECK (stock_minimo >= 0)
) ENGINE=InnoDB;

-- =========================================
-- TABLA 6: proveedores
-- =========================================
CREATE TABLE proveedores (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    nombre_contacto VARCHAR(120) NULL,
    telefono VARCHAR(20) NULL,
    correo VARCHAR(120) NULL,
    direccion VARCHAR(255) NULL,
    nit VARCHAR(20) NULL UNIQUE,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================
-- TABLA 7: compras
-- =========================================
CREATE TABLE compras (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT NOT NULL,
    id_usuario INT NOT NULL,
    numero_factura VARCHAR(50) NOT NULL,
    fecha_compra DATE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    impuesto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    observaciones VARCHAR(255) NULL,
    estado ENUM('registrada','anulada','pendiente') NOT NULL DEFAULT 'registrada',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_compras_proveedores
        FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_compras_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_compras_subtotal CHECK (subtotal >= 0),
    CONSTRAINT chk_compras_impuesto CHECK (impuesto >= 0),
    CONSTRAINT chk_compras_total CHECK (total >= 0)
) ENGINE=InnoDB;

-- =========================================
-- TABLA 8: detalle_compra
-- =========================================
CREATE TABLE detalle_compra (
    id_detalle_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    costo_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    numero_lote VARCHAR(50) NULL,
    fecha_vencimiento DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_detalle_compra_compras
        FOREIGN KEY (id_compra) REFERENCES compras(id_compra)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_detalle_compra_productos
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_detalle_compra_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_detalle_compra_costo CHECK (costo_unitario >= 0),
    CONSTRAINT chk_detalle_compra_subtotal CHECK (subtotal >= 0)
) ENGINE=InnoDB;

-- =========================================
-- TABLA 9: ventas
-- =========================================
CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_turno INT NOT NULL,
    numero_ticket VARCHAR(20) NOT NULL UNIQUE,
    fecha_venta DATETIME NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    impuesto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    monto_recibido DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cambio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    metodo_pago ENUM('efectivo','tarjeta','transferencia') NOT NULL DEFAULT 'efectivo',
    estado ENUM('completada','anulada','pendiente') NOT NULL DEFAULT 'completada',
    observaciones VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ventas_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_ventas_turnos
        FOREIGN KEY (id_turno) REFERENCES turnos(id_turno)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_ventas_subtotal CHECK (subtotal >= 0),
    CONSTRAINT chk_ventas_impuesto CHECK (impuesto >= 0),
    CONSTRAINT chk_ventas_total CHECK (total >= 0),
    CONSTRAINT chk_ventas_monto_recibido CHECK (monto_recibido >= 0),
    CONSTRAINT chk_ventas_cambio CHECK (cambio >= 0)
) ENGINE=InnoDB;

-- =========================================
-- TABLA 10: detalle_venta
-- =========================================
CREATE TABLE detalle_venta (
    id_detalle_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_detalle_venta_ventas
        FOREIGN KEY (id_venta) REFERENCES ventas(id_venta)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_detalle_venta_productos
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_detalle_venta_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_detalle_venta_precio CHECK (precio_unitario >= 0),
    CONSTRAINT chk_detalle_venta_subtotal CHECK (subtotal >= 0)
) ENGINE=InnoDB;

-- =========================================
-- TABLA 11: movimientos_inventario
-- =========================================
CREATE TABLE movimientos_inventario (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    id_usuario INT NOT NULL,
    tipo_movimiento ENUM('entrada','salida','ajuste') NOT NULL,
    tabla_origen ENUM('detalle_compra','detalle_venta','ajuste_manual') NOT NULL,
    id_referencia INT NOT NULL,
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    motivo VARCHAR(255) NULL,
    fecha_movimiento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_movimientos_productos
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_movimientos_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_movimientos_cantidad CHECK (cantidad > 0),
    CONSTRAINT chk_movimientos_stock_anterior CHECK (stock_anterior >= 0),
    CONSTRAINT chk_movimientos_stock_nuevo CHECK (stock_nuevo >= 0)
) ENGINE=InnoDB;

-- =========================================
-- INSERTS OBLIGATORIOS
-- =========================================

INSERT INTO roles (nombre, descripcion, estado)
VALUES
('Administrador', 'Control total del sistema', TRUE),
('Empleado', 'Usuario operativo del POS', TRUE);

-- Usuario administrador inicial
-- La contraseña aquí es un hash de ejemplo.
-- Debes reemplazarlo por el hash real generado desde PHP con password_hash().
INSERT INTO usuarios (
    id_rol,
    nombre,
    apellido,
    correo,
    password_hash,
    telefono,
    estado
) VALUES (
    1,
    'Administrador',
    'General',
    'admin@dnspharmacy.com',
    '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOpqrstuvwxyz123456',
    '0000-0000',
    TRUE
);

-- =========================================
-- CATEGORÍAS BASE RECOMENDADAS
-- =========================================
INSERT INTO categorias (nombre, descripcion, estado)
VALUES
('Analgésicos', 'Medicamentos para aliviar dolor', TRUE),
('Antibióticos', 'Medicamentos para infecciones bacterianas', TRUE),
('Vitaminas', 'Suplementos vitamínicos', TRUE),
('Jarabes', 'Medicamentos líquidos orales', TRUE),
('Higiene personal', 'Productos de aseo e higiene', TRUE);

-- =========================================
-- ÍNDICES ÚTILES
-- =========================================
CREATE INDEX idx_productos_nombre ON productos(nombre);
CREATE INDEX idx_productos_marca ON productos(marca);
CREATE INDEX idx_compras_fecha ON compras(fecha_compra);
CREATE INDEX idx_ventas_fecha ON ventas(fecha_venta);
CREATE INDEX idx_turnos_fecha ON turnos(fecha);
CREATE INDEX idx_movimientos_fecha ON movimientos_inventario(fecha_movimiento);