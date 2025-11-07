-- Crea la tabla para gestionar los medios reutilizables.
CREATE TABLE IF NOT EXISTS media_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(160) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    texto_alternativo VARCHAR(255) DEFAULT NULL,
    creditos VARCHAR(180) DEFAULT NULL,
    ruta VARCHAR(255) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    tipo_mime VARCHAR(120) NOT NULL,
    extension VARCHAR(12) DEFAULT NULL,
    tamano_bytes BIGINT UNSIGNED NOT NULL,
    ancho INT DEFAULT NULL,
    alto INT DEFAULT NULL,
    sha1_hash CHAR(40) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_media_hash (sha1_hash),
    KEY idx_media_creado (creado_en)
);
