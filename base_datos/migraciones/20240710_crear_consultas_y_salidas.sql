-- Migration: create consultas_contacto and salidas_programadas tables for admin dashboard metrics
CREATE TABLE IF NOT EXISTS consultas_contacto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    asunto VARCHAR(150) DEFAULT NULL,
    mensaje TEXT NOT NULL,
    estado ENUM('abierta', 'en_progreso', 'cerrada') DEFAULT 'abierta',
    canal ENUM('web', 'telefono', 'whatsapp', 'presencial') DEFAULT 'web',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS salidas_programadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paquete_id INT NOT NULL,
    fecha_salida DATE NOT NULL,
    estado ENUM('programada', 'confirmada', 'reprogramada', 'cancelada') DEFAULT 'programada',
    aforo INT DEFAULT NULL,
    reservas_confirmadas INT DEFAULT 0,
    notas VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (paquete_id) REFERENCES paquetes(id)
);
