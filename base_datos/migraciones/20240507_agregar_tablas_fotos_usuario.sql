-- Agrega tablas para las fotos de perfil y portada de usuarios.
CREATE TABLE IF NOT EXISTS usuario_fotos_perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    es_actual TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_fotos_perfil_usuario_actual (usuario_id, es_actual)
);

CREATE TABLE IF NOT EXISTS usuario_fotos_portada (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    es_actual TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_fotos_portada_usuario_actual (usuario_id, es_actual)
);
