-- DNS Pharmacy - Backup 2026-06-02 21:13:12

SET FOREIGN_KEY_CHECKS=0;

-- Tabla: roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` VALUES ('1','Administrador','Control total del sistema','1','2026-03-18 19:12:33',NULL);
INSERT INTO `roles` VALUES ('2','Empleado','Usuario operativo del POS','1','2026-03-18 19:12:33',NULL);

-- Tabla: categorias
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categorias` VALUES ('1','Analgésicos','Medicamentos para aliviar dolor','1','2026-03-18 19:12:33',NULL);
INSERT INTO `categorias` VALUES ('2','Antibióticos','Medicamentos para infecciones bacterianas','1','2026-03-18 19:12:33',NULL);
INSERT INTO `categorias` VALUES ('3','Vitaminas','Suplementos vitamínicos','1','2026-03-18 19:12:33',NULL);
INSERT INTO `categorias` VALUES ('4','Jarabes','Medicamentos líquidos orales','1','2026-03-18 19:12:33',NULL);
INSERT INTO `categorias` VALUES ('5','Higiene personal','Productos de aseo e higiene','1','2026-03-18 19:12:33',NULL);

-- Tabla: proveedores
DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE `proveedores` (
  `id_proveedor` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_contacto` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_proveedor`),
  UNIQUE KEY `nit` (`nit`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `proveedores` VALUES ('1','Distribuidora Medica S.A.','Messi Ronaldo','+50372334450','ms@gmail.com','Col.Escalon.San Salvador','9847-7263548-826-2','1','2026-03-26 20:31:15',NULL);
INSERT INTO `proveedores` VALUES ('2','Vijosa','Pablo escobar','7323-7463','Pe@gmail.com','Medellin, Colombia','1231-231231-243-2','1','2026-04-23 21:26:22',NULL);
INSERT INTO `proveedores` VALUES ('3','Sanofi','vendedor tres','7823-7646','sanofi@gmail.com','Col.Escalon.San Salvador','8488-467564-373-3','1','2026-04-23 21:27:40',NULL);

-- Tabla: usuarios
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `ultimo_acceso` datetime DEFAULT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`),
  KEY `fk_usuarios_roles` (`id_rol`),
  CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` VALUES ('2','1','Arturo','Pocasangre','arturo@gmail.com','$2y$10$9hI1Lg9LO88kg/rqjoKaYOz2y1efGaSzWi9nx4b85xAmk0TNivH2G','72121222','1','2026-06-02 13:00:49',NULL,'2026-03-19 15:14:48','2026-06-02 13:00:49');
INSERT INTO `usuarios` VALUES ('3','1','Admin','Arturo','admin@gmail.com','$2y$10$2GGAEb7jyHZ5cw9CFMF5feXAaJ7jdjhmYp.ykN.7t1w.ug5EA02ka','+503 76224659','1','2026-03-25 08:08:12',NULL,'2026-03-25 08:07:26','2026-03-25 08:08:12');
INSERT INTO `usuarios` VALUES ('4','2','Arturo','Pocasangre','arturo1@gmail.com','$2y$10$Bfnz8U3K4IhU3bQ36jTK/O8DUMqoLiU9.JXyOPP1eCZVuFQqW9kfe','7546-9118','1','2026-04-24 10:33:52',NULL,'2026-04-21 17:31:30','2026-04-24 10:33:52');

-- Tabla: productos
DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `id_categoria` int NOT NULL,
  `codigo_barras` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presentacion` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marca` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laboratorio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `precio_venta` decimal(10,2) NOT NULL,
  `stock_actual` int NOT NULL DEFAULT '0',
  `stock_minimo` int NOT NULL DEFAULT '0',
  `unidad_medida` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unidad',
  `requiere_receta` tinyint(1) NOT NULL DEFAULT '0',
  `imagen_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_producto`),
  UNIQUE KEY `codigo_barras` (`codigo_barras`),
  KEY `fk_productos_categorias` (`id_categoria`),
  KEY `idx_productos_nombre` (`nombre`),
  KEY `idx_productos_marca` (`marca`),
  CONSTRAINT `fk_productos_categorias` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_productos_precio_compra` CHECK ((`precio_compra` >= 0)),
  CONSTRAINT `chk_productos_precio_venta` CHECK ((`precio_venta` >= 0)),
  CONSTRAINT `chk_productos_stock_actual` CHECK ((`stock_actual` >= 0)),
  CONSTRAINT `chk_productos_stock_minimo` CHECK ((`stock_minimo` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `productos` VALUES ('1','1','76735673526','Acetaminofen-caja','No tomar si tiene higado graso de ahi todo bien','tabletas','MK','MK','8.00','10.25','194','2','caja','1','assets/img/productos/prod_1777000886_69eae1b655200.webp','1','2026-03-19 15:43:37','2026-04-24 10:45:43');
INSERT INTO `productos` VALUES ('4','1','7415100205379','Viro-Grip am-caja','esta no produce sueño','capsulas','Vijosa','Vijosa','9.50','12.25','198','10','sobre','1','assets/img/productos/prod_1777001827_69eae563a6b43.webp','1','2026-04-23 21:37:07','2026-04-24 10:47:49');
INSERT INTO `productos` VALUES ('5','1','74105403','Viro-Grip pm','esta si produce sueño','capsulas','Vijosa','Vijosa','0.30','0.50','198','10','sobre','1','assets/img/productos/prod_1777002053_69eae64593cf2.webp','1','2026-04-23 21:40:53','2026-04-24 10:47:49');
INSERT INTO `productos` VALUES ('6','1','7891058008598','Novalgina - caja','via oral','tabletas','Novalgina','Sanofi','11.91','13.50','199','19','caja','1','assets/img/productos/prod_1777002277_69eae7256317c.webp','1','2026-04-23 21:44:37','2026-04-24 10:45:43');
INSERT INTO `productos` VALUES ('7','1','7410003400081','Palagri-p','es buena pa la gripe','tabletas','Palagri-p','ngm+','0.20','0.25','294','25','sobre','1','assets/img/productos/prod_1777002467_69eae7e32bdd4.webp','1','2026-04-23 21:47:47','2026-04-24 10:47:49');
INSERT INTO `productos` VALUES ('8','2','74151002053354','Viro-Grip','esta no produce sueño','capsulas','Vijosa','Vijosa','9.50','12.25','100','10','unidad','1','','1','2026-04-24 10:44:39','2026-06-02 13:09:15');

-- Tabla: turnos
DROP TABLE IF EXISTS `turnos`;
CREATE TABLE `turnos` (
  `id_turno` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time DEFAULT NULL,
  `estado` enum('abierto','cerrado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierto',
  `total_ventas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_tickets` int NOT NULL DEFAULT '0',
  `observaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_turno`),
  KEY `fk_turnos_usuarios` (`id_usuario`),
  KEY `idx_turnos_fecha` (`fecha`),
  CONSTRAINT `fk_turnos_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `turnos` VALUES ('1','2','2026-03-21','19:24:52',NULL,'abierto','726.55','12',NULL,'2026-03-21 13:24:52','2026-06-02 13:09:15');

-- Tabla: compras
DROP TABLE IF EXISTS `compras`;
CREATE TABLE `compras` (
  `id_compra` int NOT NULL AUTO_INCREMENT,
  `id_proveedor` int NOT NULL,
  `id_usuario` int NOT NULL,
  `numero_factura` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_compra` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `impuesto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `observaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('registrada','anulada','pendiente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registrada',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_compra`),
  KEY `fk_compras_proveedores` (`id_proveedor`),
  KEY `fk_compras_usuarios` (`id_usuario`),
  KEY `idx_compras_fecha` (`fecha_compra`),
  CONSTRAINT `fk_compras_proveedores` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_compras_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_compras_impuesto` CHECK ((`impuesto` >= 0)),
  CONSTRAINT `chk_compras_subtotal` CHECK ((`subtotal` >= 0)),
  CONSTRAINT `chk_compras_total` CHECK ((`total` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `compras` VALUES ('1','1','2','FAC-2025-10','2026-03-27','160.00','20.80','180.80','pedido completo','registrada','2026-03-26 20:35:22',NULL);
INSERT INTO `compras` VALUES ('2','1','2','FAC-2025-10','2026-03-27','80.00','10.40','90.40','pedido completo','registrada','2026-03-27 07:36:03',NULL);
INSERT INTO `compras` VALUES ('3','1','2','FAC-20260421-E35AE','2026-04-21','122.00','0.00','122.00','pedido completo','registrada','2026-04-21 17:48:28',NULL);
INSERT INTO `compras` VALUES ('4','1','2','FAC-20260422-63DA6','2026-04-22','800.00','0.00','800.00','','registrada','2026-04-21 22:39:41',NULL);
INSERT INTO `compras` VALUES ('5','1','2','FAC-20260424-DFD82','2026-04-24','2991.00','0.00','2991.00','pedido completo','registrada','2026-04-24 10:41:48',NULL);

-- Tabla: detalle_compra
DROP TABLE IF EXISTS `detalle_compra`;
CREATE TABLE `detalle_compra` (
  `id_detalle_compra` int NOT NULL AUTO_INCREMENT,
  `id_compra` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `numero_lote` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_detalle_compra`),
  KEY `fk_detalle_compra_compras` (`id_compra`),
  KEY `fk_detalle_compra_productos` (`id_producto`),
  CONSTRAINT `fk_detalle_compra_compras` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_compra_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_detalle_compra_cantidad` CHECK ((`cantidad` > 0)),
  CONSTRAINT `chk_detalle_compra_costo` CHECK ((`costo_unitario` >= 0)),
  CONSTRAINT `chk_detalle_compra_subtotal` CHECK ((`subtotal` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `detalle_compra` VALUES ('1','1','1','20','8.00','160.00','111','2037-03-27','2026-03-26 20:35:22',NULL);
INSERT INTO `detalle_compra` VALUES ('2','2','1','10','8.00','80.00','111','2037-03-27','2026-03-27 07:36:03',NULL);
INSERT INTO `detalle_compra` VALUES ('3','3','1','20','6.10','122.00','000913',NULL,'2026-04-21 17:48:28',NULL);
INSERT INTO `detalle_compra` VALUES ('4','4','1','100','8.00','800.00','092672',NULL,'2026-04-21 22:39:41',NULL);
INSERT INTO `detalle_compra` VALUES ('5','5','1','100','8.00','800.00','34534','2026-04-24','2026-04-24 10:41:48',NULL);
INSERT INTO `detalle_compra` VALUES ('6','5','6','100','11.91','1191.00','34534','2026-04-24','2026-04-24 10:41:48',NULL);
INSERT INTO `detalle_compra` VALUES ('7','5','7','100','0.20','20.00','34534','2026-04-24','2026-04-24 10:41:48',NULL);
INSERT INTO `detalle_compra` VALUES ('8','5','4','100','9.50','950.00','34534','2026-04-24','2026-04-24 10:41:48',NULL);
INSERT INTO `detalle_compra` VALUES ('9','5','5','100','0.30','30.00','34534','2026-04-24','2026-04-24 10:41:48',NULL);

-- Tabla: ventas
DROP TABLE IF EXISTS `ventas`;
CREATE TABLE `ventas` (
  `id_venta` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_turno` int NOT NULL,
  `numero_ticket` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_venta` datetime NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `impuesto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monto_recibido` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cambio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `metodo_pago` enum('efectivo','tarjeta','transferencia') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `estado` enum('completada','anulada','pendiente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completada',
  `observaciones` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_venta`),
  UNIQUE KEY `numero_ticket` (`numero_ticket`),
  KEY `fk_ventas_usuarios` (`id_usuario`),
  KEY `fk_ventas_turnos` (`id_turno`),
  KEY `idx_ventas_fecha` (`fecha_venta`),
  CONSTRAINT `fk_ventas_turnos` FOREIGN KEY (`id_turno`) REFERENCES `turnos` (`id_turno`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ventas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_ventas_cambio` CHECK ((`cambio` >= 0)),
  CONSTRAINT `chk_ventas_impuesto` CHECK ((`impuesto` >= 0)),
  CONSTRAINT `chk_ventas_monto_recibido` CHECK ((`monto_recibido` >= 0)),
  CONSTRAINT `chk_ventas_subtotal` CHECK ((`subtotal` >= 0)),
  CONSTRAINT `chk_ventas_total` CHECK ((`total` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ventas` VALUES ('1','2','1','TKT-20260321-5773','2026-03-21 19:24:52','99.00','12.87','111.87','120.00','8.13','efectivo','completada',NULL,'2026-03-21 13:24:52',NULL);
INSERT INTO `ventas` VALUES ('2','2','1','TKT-20260323-4158','2026-03-23 15:46:23','27.00','3.51','30.51','40.00','9.49','efectivo','completada',NULL,'2026-03-23 09:46:23',NULL);
INSERT INTO `ventas` VALUES ('3','2','1','TKT-20260325-3879','2026-03-25 13:39:54','9.00','1.17','10.17','11.00','0.83','efectivo','completada',NULL,'2026-03-25 07:39:54',NULL);
INSERT INTO `ventas` VALUES ('4','2','1','TKT-20260327-6438','2026-03-27 03:13:58','18.00','2.34','20.34','0.00','0.00','tarjeta','completada',NULL,'2026-03-26 21:13:58',NULL);
INSERT INTO `ventas` VALUES ('5','2','1','TKT-20260327-7075','2026-03-27 03:14:16','18.00','2.34','20.34','0.00','0.00','tarjeta','completada',NULL,'2026-03-26 21:14:16',NULL);
INSERT INTO `ventas` VALUES ('6','2','1','TKT-20260413-5277','2026-04-13 02:01:00','54.00','0.00','54.00','54.00','0.00','efectivo','completada',NULL,'2026-04-12 20:01:00',NULL);
INSERT INTO `ventas` VALUES ('7','2','1','TKT-20260421-9745','2026-04-21 23:22:08','81.00','10.53','91.53','100.00','8.47','efectivo','completada',NULL,'2026-04-21 17:22:08',NULL);
INSERT INTO `ventas` VALUES ('8','2','1','TKT-20260421-8554','2026-04-21 23:44:41','45.00','0.00','45.00','50.00','5.00','efectivo','completada',NULL,'2026-04-21 17:44:41',NULL);
INSERT INTO `ventas` VALUES ('9','2','1','TKT-20260422-7450','2026-04-22 03:15:04','252.50','0.00','252.50','300.00','47.50','efectivo','completada',NULL,'2026-04-21 21:15:04',NULL);
INSERT INTO `ventas` VALUES ('10','2','1','TKT-20260423-0987','2026-04-23 17:43:18','51.25','6.66','57.91','60.00','2.09','efectivo','completada',NULL,'2026-04-23 11:43:18',NULL);
INSERT INTO `ventas` VALUES ('11','2','1','TKT-20260424-2110','2026-04-24 16:45:43','18.38','0.00','18.38','20.00','1.63','efectivo','completada',NULL,'2026-04-24 10:45:43',NULL);
INSERT INTO `ventas` VALUES ('12','2','1','TKT-20260424-5471','2026-04-24 16:47:49','14.00','0.00','14.00','15.00','1.00','efectivo','completada',NULL,'2026-04-24 10:47:49',NULL);
INSERT INTO `ventas` VALUES ('13','2','1','TKT-20260602-3035','2026-06-02 19:09:03','122.50','0.00','122.50','125.00','2.50','efectivo','anulada',NULL,'2026-06-02 13:09:03','2026-06-02 13:09:15');

-- Tabla: detalle_venta
DROP TABLE IF EXISTS `detalle_venta`;
CREATE TABLE `detalle_venta` (
  `id_detalle_venta` int NOT NULL AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_detalle_venta`),
  KEY `fk_detalle_venta_ventas` (`id_venta`),
  KEY `fk_detalle_venta_productos` (`id_producto`),
  CONSTRAINT `fk_detalle_venta_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_venta_ventas` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_detalle_venta_cantidad` CHECK ((`cantidad` > 0)),
  CONSTRAINT `chk_detalle_venta_precio` CHECK ((`precio_unitario` >= 0)),
  CONSTRAINT `chk_detalle_venta_subtotal` CHECK ((`subtotal` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `detalle_venta` VALUES ('1','1','1','11','9.00','99.00','2026-03-21 13:24:52',NULL);
INSERT INTO `detalle_venta` VALUES ('2','2','1','3','9.00','27.00','2026-03-23 09:46:23',NULL);
INSERT INTO `detalle_venta` VALUES ('3','3','1','1','9.00','9.00','2026-03-25 07:39:54',NULL);
INSERT INTO `detalle_venta` VALUES ('4','4','1','2','9.00','18.00','2026-03-26 21:13:58',NULL);
INSERT INTO `detalle_venta` VALUES ('5','5','1','2','9.00','18.00','2026-03-26 21:14:16',NULL);
INSERT INTO `detalle_venta` VALUES ('6','6','1','6','9.00','54.00','2026-04-12 20:01:00',NULL);
INSERT INTO `detalle_venta` VALUES ('7','7','1','10','9.00','90.00','2026-04-21 17:22:08',NULL);
INSERT INTO `detalle_venta` VALUES ('8','8','1','5','9.00','45.00','2026-04-21 17:44:41',NULL);
INSERT INTO `detalle_venta` VALUES ('9','9','1','25','10.10','252.50','2026-04-21 21:15:04',NULL);
INSERT INTO `detalle_venta` VALUES ('10','10','1','5','10.25','51.25','2026-04-23 11:43:18',NULL);
INSERT INTO `detalle_venta` VALUES ('11','11','1','1','10.25','10.25','2026-04-24 10:45:43',NULL);
INSERT INTO `detalle_venta` VALUES ('12','11','4','1','12.25','12.25','2026-04-24 10:45:43',NULL);
INSERT INTO `detalle_venta` VALUES ('13','11','5','1','0.50','0.50','2026-04-24 10:45:43',NULL);
INSERT INTO `detalle_venta` VALUES ('14','11','6','1','13.50','13.50','2026-04-24 10:45:43',NULL);
INSERT INTO `detalle_venta` VALUES ('15','11','7','1','0.25','0.25','2026-04-24 10:45:43',NULL);
INSERT INTO `detalle_venta` VALUES ('16','12','4','1','12.25','12.25','2026-04-24 10:47:49',NULL);
INSERT INTO `detalle_venta` VALUES ('17','12','5','1','0.50','0.50','2026-04-24 10:47:49',NULL);
INSERT INTO `detalle_venta` VALUES ('18','12','7','5','0.25','1.25','2026-04-24 10:47:49',NULL);
INSERT INTO `detalle_venta` VALUES ('19','13','8','10','12.25','122.50','2026-06-02 13:09:03',NULL);

-- Tabla: asistencia
DROP TABLE IF EXISTS `asistencia`;
CREATE TABLE `asistencia` (
  `id_asistencia` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time DEFAULT NULL,
  `estado` enum('entrada','salida') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'entrada',
  `origen` enum('qr','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'qr',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `unique_asistencia_dia` (`id_usuario`,`fecha`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `asistencia` VALUES ('1','2','2026-04-23','17:31:59','17:32:15','salida','qr','2026-04-23 11:31:59','2026-04-23 11:32:15');
INSERT INTO `asistencia` VALUES ('2','2','2026-04-24','14:39:59','14:40:04','salida','qr','2026-04-24 08:39:59','2026-04-24 08:40:04');

-- Tabla: movimientos_inventario
DROP TABLE IF EXISTS `movimientos_inventario`;
CREATE TABLE `movimientos_inventario` (
  `id_movimiento` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `id_usuario` int NOT NULL,
  `tipo_movimiento` enum('entrada','salida','ajuste') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tabla_origen` enum('detalle_compra','detalle_venta','ajuste_manual') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_referencia` int NOT NULL,
  `cantidad` int NOT NULL,
  `stock_anterior` int NOT NULL,
  `stock_nuevo` int NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimiento`),
  KEY `fk_movimientos_productos` (`id_producto`),
  KEY `fk_movimientos_usuarios` (`id_usuario`),
  KEY `idx_movimientos_fecha` (`fecha_movimiento`),
  CONSTRAINT `fk_movimientos_productos` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_movimientos_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_movimientos_cantidad` CHECK ((`cantidad` > 0)),
  CONSTRAINT `chk_movimientos_stock_anterior` CHECK ((`stock_anterior` >= 0)),
  CONSTRAINT `chk_movimientos_stock_nuevo` CHECK ((`stock_nuevo` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
