-- Crear tabla para gestionar integrantes del equipo de Expediatravels.
CREATE TABLE IF NOT EXISTS equipo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    cargo VARCHAR(150) DEFAULT NULL,
    telefono VARCHAR(60) DEFAULT NULL,
    correo VARCHAR(150) DEFAULT NULL,
    categoria ENUM('asesor_ventas', 'guia', 'operaciones', 'otro') NOT NULL DEFAULT 'otro',
    descripcion TEXT DEFAULT NULL,
    prioridad INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_equipo_categoria_activo (categoria, activo, prioridad),
    UNIQUE KEY uniq_equipo_nombre_categoria (nombre, categoria)
);

INSERT INTO equipo (nombre, cargo, telefono, correo, categoria, prioridad, activo)
VALUES
('María López', 'Especialista en circuitos', '+51 987 654 321', 'maria.lopez@expediatravels.pe', 'asesor_ventas', 10, 1),
('Jorge Ramírez', 'Atención personalizada', '+51 945 123 456', 'jorge.ramirez@expediatravels.pe', 'asesor_ventas', 8, 1),
('Lucía Quispe', 'Guía senior', '+51 912 345 678', 'lucia.quispe@expediatravels.pe', 'guia', 5, 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    cargo = VALUES(cargo),
    telefono = VALUES(telefono),
    correo = VALUES(correo),
    categoria = VALUES(categoria),
    prioridad = VALUES(prioridad),
    activo = VALUES(activo);
