-- Esquema inicial para Expediatravels.
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(120) NOT NULL,
    celular VARCHAR(30) DEFAULT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(255) NOT NULL,
    verificacion_pin VARCHAR(6) DEFAULT NULL,
    pin_expira_en DATETIME DEFAULT NULL,
    verificado_en DATETIME DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    remember_token_expira_en DATETIME DEFAULT NULL,
    rol ENUM('administrador', 'moderador', 'suscriptor') DEFAULT 'suscriptor',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuario_fotos_perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    es_actual TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_fotos_perfil_usuario_actual (usuario_id, es_actual)
);

CREATE TABLE usuario_fotos_portada (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    es_actual TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_fotos_portada_usuario_actual (usuario_id, es_actual)
);

CREATE TABLE destinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT,
    tagline VARCHAR(180) DEFAULT NULL,
    lat DECIMAL(10, 7),
    lon DECIMAL(10, 7),
    imagen VARCHAR(255),
    imagen_destacada VARCHAR(255) DEFAULT NULL,
    region VARCHAR(120),
    galeria JSON DEFAULT NULL,
    video_destacado_url VARCHAR(255) DEFAULT NULL,
    tags JSON DEFAULT NULL,
    estado ENUM('activo', 'oculto', 'borrador') NOT NULL DEFAULT 'activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE circuitos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destino_id INT DEFAULT NULL,
    destino_personalizado VARCHAR(150) DEFAULT NULL,
    nombre VARCHAR(150) NOT NULL,
    duracion VARCHAR(80) NOT NULL,
    precio DECIMAL(10, 2) DEFAULT NULL,
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


CREATE TABLE servicios_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    icono VARCHAR(120) DEFAULT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE circuito_servicios (
    circuito_id INT NOT NULL,
    servicio_id INT NOT NULL,
    tipo ENUM('incluido', 'excluido') NOT NULL DEFAULT 'incluido',
    PRIMARY KEY (circuito_id, servicio_id, tipo),
    FOREIGN KEY (circuito_id) REFERENCES circuitos(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios_catalogo(id) ON DELETE CASCADE
);

CREATE TABLE circuito_itinerarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    circuito_id INT NOT NULL,
    orden INT NOT NULL,
    dia VARCHAR(80) DEFAULT NULL,
    hora VARCHAR(40) DEFAULT NULL,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    ubicacion_maps VARCHAR(255) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (circuito_id) REFERENCES circuitos(id) ON DELETE CASCADE,
    INDEX idx_circuito_itinerarios_circuito (circuito_id, orden)
);

CREATE TABLE paquetes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destino_id INT DEFAULT NULL,
    nombre VARCHAR(150) NOT NULL,
    resumen TEXT,
    itinerario MEDIUMTEXT,
    duracion VARCHAR(80),
    precio DECIMAL(10, 2) DEFAULT NULL,
    moneda CHAR(3) NOT NULL DEFAULT 'PEN',
    estado ENUM('borrador', 'publicado', 'agotado', 'inactivo') DEFAULT 'borrador',
    imagen_portada VARCHAR(255) DEFAULT NULL,
    imagen_destacada VARCHAR(255) DEFAULT NULL,
    galeria JSON DEFAULT NULL,
    video_destacado_url VARCHAR(255) DEFAULT NULL,
    beneficios JSON DEFAULT NULL,
    incluye JSON DEFAULT NULL,
    no_incluye JSON DEFAULT NULL,
    salidas JSON DEFAULT NULL,
    cupos_min INT DEFAULT NULL,
    cupos_max INT DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destino_id) REFERENCES destinos(id)
);

CREATE TABLE paquete_destinos (
    paquete_id INT NOT NULL,
    destino_id INT NOT NULL,
    PRIMARY KEY (paquete_id, destino_id),
    FOREIGN KEY (paquete_id) REFERENCES paquetes(id) ON DELETE CASCADE,
    FOREIGN KEY (destino_id) REFERENCES destinos(id) ON DELETE CASCADE
);

CREATE TABLE paquete_circuitos (
    paquete_id INT NOT NULL,
    circuito_id INT NOT NULL,
    PRIMARY KEY (paquete_id, circuito_id),
    FOREIGN KEY (paquete_id) REFERENCES paquetes(id) ON DELETE CASCADE,
    FOREIGN KEY (circuito_id) REFERENCES circuitos(id) ON DELETE CASCADE
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

CREATE TABLE site_settings (
    id INT PRIMARY KEY,
    site_title VARCHAR(150) NOT NULL,
    site_tagline VARCHAR(150) DEFAULT NULL,
    contact_emails TEXT DEFAULT NULL,
    contact_phones TEXT DEFAULT NULL,
    contact_addresses TEXT DEFAULT NULL,
    contact_locations TEXT DEFAULT NULL,
    social_links TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE hero_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(255) NOT NULL,
    label VARCHAR(120) DEFAULT NULL,
    alt_text VARCHAR(160) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE consultas_contacto (
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

CREATE TABLE salidas_programadas (
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

CREATE TABLE media_items (
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
