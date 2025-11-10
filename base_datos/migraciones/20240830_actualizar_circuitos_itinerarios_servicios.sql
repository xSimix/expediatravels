-- Migración para agregar tablas de itinerarios, servicios y marcadores de circuitos.
CREATE TABLE IF NOT EXISTS servicios_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    tipo ENUM('incluido', 'excluido') NOT NULL DEFAULT 'incluido',
    descripcion VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS circuito_servicios (
    circuito_id INT NOT NULL,
    servicio_id INT NOT NULL,
    tipo ENUM('incluido', 'excluido') NOT NULL DEFAULT 'incluido',
    PRIMARY KEY (circuito_id, servicio_id, tipo),
    FOREIGN KEY (circuito_id) REFERENCES circuitos(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios_catalogo(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS circuito_itinerarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    circuito_id INT NOT NULL,
    orden INT NOT NULL,
    dia VARCHAR(80) DEFAULT NULL,
    hora VARCHAR(40) DEFAULT NULL,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (circuito_id) REFERENCES circuitos(id) ON DELETE CASCADE,
    INDEX idx_circuito_itinerarios_circuito (circuito_id, orden)
);

CREATE TABLE IF NOT EXISTS circuito_marcadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    circuito_id INT NOT NULL,
    orden INT NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    latitud DECIMAL(10, 7) NOT NULL,
    longitud DECIMAL(10, 7) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (circuito_id) REFERENCES circuitos(id) ON DELETE CASCADE,
    INDEX idx_circuito_marcadores_circuito (circuito_id, orden)
);
