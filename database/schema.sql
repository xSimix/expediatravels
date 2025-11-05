-- Esquema inicial para Expediatravels.
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'editor', 'cliente') DEFAULT 'cliente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT,
    lat DECIMAL(10, 7),
    lon DECIMAL(10, 7),
    imagen VARCHAR(255),
    region VARCHAR(120)
);

CREATE TABLE paquetes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destino_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    resumen TEXT,
    itinerario MEDIUMTEXT,
    duracion VARCHAR(50),
    precio DECIMAL(10, 2) NOT NULL,
    estado ENUM('borrador', 'publicado') DEFAULT 'borrador',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destino_id) REFERENCES destinos(id)
);

CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    paquete_id INT NOT NULL,
    fecha_reserva DATE NOT NULL,
    cantidad_personas INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (paquete_id) REFERENCES paquetes(id)
);

CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    metodo ENUM('izipay', 'paypal', 'culqi') NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    estado ENUM('pendiente', 'aprobado', 'fallido') DEFAULT 'pendiente',
    fecha_pago TIMESTAMP NULL,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id)
);

CREATE TABLE resenas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    paquete_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comentario TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (paquete_id) REFERENCES paquetes(id)
);
