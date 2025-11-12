-- Ajusta destinos, circuitos y paquetes para el flujo de publicación y múltiples destinos.

-- 1. Slug único y estado binario para destinos.
ALTER TABLE destinos
    ADD COLUMN IF NOT EXISTS slug VARCHAR(150) NULL AFTER nombre;

-- Genera slugs básicos a partir del nombre.
UPDATE destinos
SET slug = LOWER(TRIM(nombre))
WHERE slug IS NULL OR slug = '';

-- Normaliza caracteres acentuados y espacios.
UPDATE destinos
SET slug = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(slug,
            ' ', '-'),
            'á', 'a'),
            'é', 'e'),
            'í', 'i'),
            'ó', 'o'),
            'ú', 'u'),
            'ñ', 'n'),
            'ü', 'u');
UPDATE destinos
SET slug = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(slug,
            'Á', 'a'),
            'É', 'e'),
            'Í', 'i'),
            'Ó', 'o'),
            'Ú', 'u'),
            'Ñ', 'n'),
            'Ü', 'u');

-- Elimina caracteres no permitidos y colapsa guiones consecutivos.
UPDATE destinos
SET slug = REGEXP_REPLACE(slug, '[^a-z0-9-]', '');
UPDATE destinos
SET slug = REGEXP_REPLACE(slug, '-+', '-');
UPDATE destinos
SET slug = REGEXP_REPLACE(slug, '(^-+)|(-+$)', '');

-- Asegura unicidad añadiendo el id cuando exista duplicado.
UPDATE destinos d
JOIN (
    SELECT slug, COUNT(*) AS repeticiones
    FROM destinos
    WHERE slug IS NOT NULL AND slug <> ''
    GROUP BY slug
    HAVING COUNT(*) > 1
) repetidos ON repetidos.slug = d.slug
SET d.slug = CONCAT(d.slug, '-', d.id);

-- Garantiza que ningún slug quede vacío.
UPDATE destinos
SET slug = CONCAT('destino-', id)
WHERE slug IS NULL OR slug = '';

ALTER TABLE destinos
    MODIFY COLUMN slug VARCHAR(150) NOT NULL;

ALTER TABLE destinos
    ADD UNIQUE INDEX IF NOT EXISTS idx_destinos_slug (slug);

-- Normaliza estados previos "oculto"/"borrador" a "inactivo" y acota el catálogo.
UPDATE destinos
SET estado = 'inactivo'
WHERE estado IN ('oculto', 'borrador');

ALTER TABLE destinos
    MODIFY COLUMN estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo';

-- 2. Campos de publicación para circuitos y tabla puente N:M.
ALTER TABLE circuitos
    ADD COLUMN IF NOT EXISTS estado_publicacion ENUM('borrador', 'publicado') NOT NULL DEFAULT 'borrador' AFTER estado;
ALTER TABLE circuitos
    ADD COLUMN IF NOT EXISTS vigencia_desde DATETIME NULL AFTER estado_publicacion;
ALTER TABLE circuitos
    ADD COLUMN IF NOT EXISTS vigencia_hasta DATETIME NULL AFTER vigencia_desde;
ALTER TABLE circuitos
    ADD COLUMN IF NOT EXISTS visibilidad ENUM('publico', 'privado') NOT NULL DEFAULT 'publico' AFTER vigencia_hasta;

CREATE TABLE IF NOT EXISTS circuito_destinos (
    circuito_id INT NOT NULL,
    destino_id INT NOT NULL,
    PRIMARY KEY (circuito_id, destino_id),
    FOREIGN KEY (circuito_id) REFERENCES circuitos(id) ON DELETE CASCADE,
    FOREIGN KEY (destino_id) REFERENCES destinos(id) ON DELETE CASCADE,
    INDEX idx_circuito_destinos_destino (destino_id)
);

-- Migra asociaciones existentes 1:N a la tabla puente.
INSERT INTO circuito_destinos (circuito_id, destino_id)
SELECT id, destino_id
FROM circuitos
WHERE destino_id IS NOT NULL
ON DUPLICATE KEY UPDATE destino_id = VALUES(destino_id);

-- Ajusta estados de publicación según estado operativo.
UPDATE circuitos
SET estado_publicacion = CASE WHEN estado = 'activo' THEN 'publicado' ELSE 'borrador' END
WHERE estado_publicacion IS NULL OR estado_publicacion = 'borrador';

ALTER TABLE circuitos
    ADD INDEX IF NOT EXISTS idx_circuitos_publicacion (estado_publicacion, visibilidad, vigencia_desde, vigencia_hasta);

-- 3. Campos de publicación para paquetes y refuerzo de relaciones.
ALTER TABLE paquetes
    ADD COLUMN IF NOT EXISTS estado_publicacion ENUM('borrador', 'publicado') NOT NULL DEFAULT 'borrador' AFTER estado;
ALTER TABLE paquetes
    ADD COLUMN IF NOT EXISTS vigencia_desde DATETIME NULL AFTER estado_publicacion;
ALTER TABLE paquetes
    ADD COLUMN IF NOT EXISTS vigencia_hasta DATETIME NULL AFTER vigencia_desde;
ALTER TABLE paquetes
    ADD COLUMN IF NOT EXISTS visibilidad ENUM('publico', 'privado') NOT NULL DEFAULT 'publico' AFTER vigencia_hasta;

-- Alinea estado_publicacion con el estado de negocio existente.
UPDATE paquetes
SET estado_publicacion = CASE WHEN estado IN ('publicado', 'agotado') THEN 'publicado' ELSE 'borrador' END
WHERE estado_publicacion IS NULL OR estado_publicacion = 'borrador';

ALTER TABLE paquetes
    ADD INDEX IF NOT EXISTS idx_paquetes_publicacion (estado_publicacion, visibilidad, vigencia_desde, vigencia_hasta);

-- Refuerza vínculos destino-paquete existentes.
INSERT INTO paquete_destinos (paquete_id, destino_id)
SELECT id, destino_id
FROM paquetes
WHERE destino_id IS NOT NULL
ON DUPLICATE KEY UPDATE destino_id = VALUES(destino_id);

-- Índice auxiliar para consultas por destino.
ALTER TABLE paquete_destinos
    ADD INDEX IF NOT EXISTS idx_paquete_destinos_destino (destino_id);

-- 4. Placeholder para destinos sin imagen.
UPDATE destinos
SET imagen = 'almacenamiento/medios/placeholder-destino.jpg'
WHERE imagen IS NULL OR imagen = '';

