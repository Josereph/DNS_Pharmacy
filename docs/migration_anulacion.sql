-- =============================================================
-- DNS Pharmacy — Migración: Auditoría de Anulación de Ventas
-- Versión: 1.0
-- Fecha:   2026-06-01
-- Descripción:
--   Añade tres columnas de auditoría a la tabla `ventas` para
--   soportar la funcionalidad de anulación con trazabilidad
--   completa. No modifica ni elimina ningún dato existente.
-- =============================================================

-- Ejecutar en la base de datos: dns_pharmacy
-- Compatible con MySQL 5.7+ y MariaDB 10.2+

-- -------------------------------------------------------------
-- PASO 1: Añadir columnas de auditoría
-- Se usa IF NOT EXISTS (MySQL 8+ / MariaDB 10.2+)
-- Si tu versión es anterior, ejecuta cada ALTER TABLE por separado
-- después de verificar con SHOW COLUMNS FROM ventas;
-- -------------------------------------------------------------

ALTER TABLE ventas
    ADD COLUMN IF NOT EXISTS fecha_anulacion  DATETIME     NULL DEFAULT NULL
        COMMENT 'Timestamp del momento exacto en que se procesó la anulación'
        AFTER estado,

    ADD COLUMN IF NOT EXISTS anulada_por      INT          NULL DEFAULT NULL
        COMMENT 'FK al id_usuario del administrador que ejecutó la anulación'
        AFTER fecha_anulacion,

    ADD COLUMN IF NOT EXISTS motivo_anulacion VARCHAR(255) NULL DEFAULT NULL
        COMMENT 'Motivo opcional ingresado por el administrador al anular'
        AFTER anulada_por;

-- -------------------------------------------------------------
-- PASO 2: Índice en columna estado (mejora rendimiento en filtros)
-- -------------------------------------------------------------

-- Verificar si el índice ya existe antes de crearlo:
-- SELECT COUNT(*) FROM information_schema.statistics
-- WHERE table_schema = 'dns_pharmacy'
--   AND table_name   = 'ventas'
--   AND index_name   = 'idx_ventas_estado';

-- Crear índice solo si no existe (ejecutar manualmente si la sintaxis falla):
ALTER TABLE ventas
    ADD INDEX IF NOT EXISTS idx_ventas_estado (estado);

-- -------------------------------------------------------------
-- PASO 3: Restricción de integridad referencial (OPCIONAL)
-- Conecta anulada_por con la tabla usuarios para auditoria
-- Omitir si la tabla usuarios no tiene ON DELETE SET NULL permitido
-- -------------------------------------------------------------

-- ALTER TABLE ventas
--     ADD CONSTRAINT fk_ventas_anulada_por
--     FOREIGN KEY (anulada_por) REFERENCES usuarios(id_usuario)
--     ON DELETE SET NULL
--     ON UPDATE CASCADE;

-- -------------------------------------------------------------
-- VERIFICACIÓN POST-MIGRACIÓN
-- Ejecuta esto para confirmar que las columnas fueron creadas:
-- -------------------------------------------------------------

-- SHOW COLUMNS FROM ventas LIKE 'fecha_anulacion';
-- SHOW COLUMNS FROM ventas LIKE 'anulada_por';
-- SHOW COLUMNS FROM ventas LIKE 'motivo_anulacion';

-- =============================================================
-- FIN DE MIGRACIÓN
-- =============================================================
