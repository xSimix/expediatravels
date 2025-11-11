-- Actualiza las tablas de destinos, circuitos y paquetes para gestionar el contenido desde la base de datos.

ALTER TABLE destinos
    ADD COLUMN tagline VARCHAR(180) DEFAULT NULL AFTER descripcion,
    ADD COLUMN imagen_destacada VARCHAR(255) DEFAULT NULL AFTER imagen,
    ADD COLUMN galeria JSON DEFAULT NULL AFTER imagen_destacada,
    ADD COLUMN video_destacado_url VARCHAR(255) DEFAULT NULL AFTER galeria,
    ADD COLUMN tags JSON DEFAULT NULL AFTER video_destacado_url,
    ADD COLUMN estado ENUM('activo', 'oculto', 'borrador') NOT NULL DEFAULT 'activo' AFTER tags,
    ADD COLUMN creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER estado,
    ADD COLUMN actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER creado_en;

ALTER TABLE destinos
    MODIFY COLUMN imagen VARCHAR(255) DEFAULT NULL,
    MODIFY COLUMN lat DECIMAL(10, 7) NULL,
    MODIFY COLUMN lon DECIMAL(10, 7) NULL;

CREATE TABLE IF NOT EXISTS circuitos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destino_id INT DEFAULT NULL,
    destino_personalizado VARCHAR(150) DEFAULT NULL,
    nombre VARCHAR(150) NOT NULL,
    duracion VARCHAR(80) NOT NULL,
    categoria ENUM('naturaleza', 'cultural', 'aventura', 'gastronomico', 'bienestar') NOT NULL DEFAULT 'naturaleza',
    dificultad ENUM('relajado', 'moderado', 'intenso') NOT NULL DEFAULT 'relajado',
    frecuencia VARCHAR(120) DEFAULT NULL,
    estado ENUM('borrador', 'activo', 'inactivo') NOT NULL DEFAULT 'borrador',
    descripcion TEXT,
    puntos_interes JSON DEFAULT NULL,
    servicios JSON DEFAULT NULL,
    imagen_portada VARCHAR(255) DEFAULT NULL,
    imagen_destacada VARCHAR(255) DEFAULT NULL,
    galeria JSON DEFAULT NULL,
    video_destacado_url VARCHAR(255) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destino_id) REFERENCES destinos(id) ON DELETE SET NULL
);

ALTER TABLE paquetes
    MODIFY COLUMN destino_id INT NULL,
    MODIFY COLUMN duracion VARCHAR(80) DEFAULT NULL,
    MODIFY COLUMN precio DECIMAL(10, 2) NULL,
    MODIFY COLUMN estado ENUM('borrador', 'publicado', 'agotado', 'inactivo') DEFAULT 'borrador',
    ADD COLUMN moneda CHAR(3) NOT NULL DEFAULT 'PEN' AFTER precio,
    ADD COLUMN imagen_portada VARCHAR(255) DEFAULT NULL AFTER estado,
    ADD COLUMN imagen_destacada VARCHAR(255) DEFAULT NULL AFTER imagen_portada,
    ADD COLUMN galeria JSON DEFAULT NULL AFTER imagen_destacada,
    ADD COLUMN video_destacado_url VARCHAR(255) DEFAULT NULL AFTER galeria,
    ADD COLUMN beneficios JSON DEFAULT NULL AFTER video_destacado_url,
    ADD COLUMN incluye JSON DEFAULT NULL AFTER beneficios,
    ADD COLUMN no_incluye JSON DEFAULT NULL AFTER incluye,
    ADD COLUMN salidas JSON DEFAULT NULL AFTER no_incluye,
    ADD COLUMN cupos_min INT DEFAULT NULL AFTER salidas,
    ADD COLUMN cupos_max INT DEFAULT NULL AFTER cupos_min,
    ADD COLUMN actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER creado_en;

UPDATE paquetes SET moneda = 'PEN' WHERE moneda IS NULL;

CREATE TABLE IF NOT EXISTS paquete_destinos (
    paquete_id INT NOT NULL,
    destino_id INT NOT NULL,
    PRIMARY KEY (paquete_id, destino_id),
    FOREIGN KEY (paquete_id) REFERENCES paquetes(id) ON DELETE CASCADE,
    FOREIGN KEY (destino_id) REFERENCES destinos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS paquete_circuitos (
    paquete_id INT NOT NULL,
    circuito_id INT NOT NULL,
    PRIMARY KEY (paquete_id, circuito_id),
    FOREIGN KEY (paquete_id) REFERENCES paquetes(id) ON DELETE CASCADE,
    FOREIGN KEY (circuito_id) REFERENCES circuitos(id) ON DELETE CASCADE
);

INSERT INTO paquete_destinos (paquete_id, destino_id)
SELECT p.id, p.destino_id
FROM paquetes p
WHERE p.destino_id IS NOT NULL
ON DUPLICATE KEY UPDATE destino_id = VALUES(destino_id);
