-- Datos de muestra para Expediatravels.
INSERT INTO usuarios (nombre, apellidos, celular, correo, contrasena_hash, verificado_en, rol)
VALUES (
    'Admin',
    'Principal',
    '+51 900 000 001',
    'admin@expediatravels.pe',
    '$2y$12$hzehrAtvj2Smn.qAn00yQ.VecHri4JjTJeUoj5u8BVUVqefWWIIxe',
    '2024-01-01 09:00:00',
    'administrador'
);

INSERT INTO usuario_fotos_perfil (usuario_id, ruta, es_actual)
VALUES (1, 'https://images.unsplash.com/photo-1544725176-7c40e5a2c9f9?q=80&w=400&auto=format&fit=crop', 1);

INSERT INTO usuario_fotos_portada (usuario_id, ruta, es_actual)
VALUES (1, 'https://images.unsplash.com/photo-1529923188384-5e545b81d48d?auto=format&fit=crop&w=1600&q=80', 1);

INSERT INTO destinos (nombre, descripcion, lat, lon, imagen, region) VALUES
('Oxapampa', 'Capital cafetalera y puerta de entrada a la Reserva de Biosfera Oxapampa-Ashaninka-Yanesha.', -10.5833333, -75.4, 'oxapampa.jpg', 'Pasco'),
('Villa Rica', 'Tierra del café de altura y de la Laguna El Oconal.', -10.7333333, -75.2666667, 'villa-rica.jpg', 'Pasco'),
('Pozuzo', 'Colonia austro-alemana rodeada de paisajes naturales únicos.', -10.0666667, -75.55, 'pozuzo.jpg', 'Pasco'),
('Perené', 'Cataratas, mariposarios y experiencias culturales amazónicas.', -10.95, -75.25, 'perene.jpg', 'Chanchamayo'),
('Yanachaga', 'Reserva que resguarda bosques de neblina y fauna endémica.', -10.3167, -75.2667, 'yanachaga.jpg', 'Pasco');

INSERT INTO paquetes (destino_id, nombre, resumen, itinerario, duracion, precio, estado) VALUES
(1, 'Tour Oxapampa', 'Tunqui Cueva, El Wharapo y Catarata Río Tigre en una experiencia full day.', 'Visita Tunqui Cueva, degustación en El Wharapo, caminata a la Catarata Río Tigre y recorrido por el Parque Temático.', '1 día', 120.00, 'publicado'),
(2, 'Tour Villa Rica', 'Laguna El Oconal, catación de café y mirador La Cumbre.', 'Ingreso al Portal de Villa Rica, navegación en la laguna, ictioterapia, catación de café y puesta de sol en el mirador.', '1 día', 110.00, 'publicado'),
(3, 'Tour Pozuzo', 'Descubre la colonia austro-alemana y sus cascadas.', 'Recorrido histórico, visita a cervecería artesanal, caminata a cascadas y cruce por el puente colgante.', '1 día', 150.00, 'publicado'),
(4, 'Tour Perené', 'Catarata Bayoz, Velo de la Novia y paseo en bote.', 'Tour por Mariposario, caminata a las cataratas y navegación por el río Perené.', '1 día', 95.00, 'publicado'),
(5, 'Tour Yanachaga', 'Avistamiento de aves en Lluvias Eternas.', 'Senderismo interpretativo, observación de flora y fauna, visita al centro de interpretación.', '1 día', 130.00, 'publicado');

INSERT INTO site_settings (id, site_title, site_tagline, contact_emails, contact_phones, contact_addresses, contact_locations, social_links)
VALUES (
    1,
    'Expediatravels',
    'Explora la Selva Central',
    'hola@expediatravels.pe\nreservas@expediatravels.pe',
    '+51 984 635 885\n+51 901 224 678',
    'Jr. San Martín 245, Oxapampa\nCentro empresarial Aurora, Lima',
    'Oxapampa, Pasco — Perú\nMiraflores, Lima — Perú',
    'Instagram|https://instagram.com/expediatravels\nFacebook|https://facebook.com/expediatravels\nYouTube|https://youtube.com/@expediatravels'
);

INSERT INTO hero_slides (image_url, label, alt_text, description, sort_order, is_visible) VALUES
('https://images.unsplash.com/photo-1529923188384-5e545b81d48d?auto=format&fit=crop&w=1600&q=80', 'Bosques de Oxapampa', 'Bosque de neblina en Oxapampa con luz dorada al amanecer', 'Paisaje representativo de la Reserva de Biosfera Oxapampa-Ashaninka-Yanesha al amanecer.', 1, 1),
('https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80', 'Laguna El Oconal', 'Reflejos en la Laguna El Oconal en Villa Rica', 'La laguna El Oconal de Villa Rica al atardecer, ideal para avistamiento de aves.', 2, 1),
('https://images.unsplash.com/photo-1505761671935-60b3a7427bad?auto=format&fit=crop&w=1600&q=80', 'Cascadas de Pozuzo', 'Cascada rodeada de selva en Pozuzo, Selva Central del Perú', 'Cascadas cristalinas de Pozuzo rodeadas de vegetación amazónica.', 3, 1);

INSERT INTO usuarios (nombre, apellidos, celular, correo, contrasena_hash, verificado_en, rol)
VALUES
('María', 'Quispe Ramos', '+51 900 000 102', 'maria.quispe@expediatravels.pe', '$2y$12$hzehrAtvj2Smn.qAn00yQ.VecHri4JjTJeUoj5u8BVUVqefWWIIxe', '2024-02-10 08:30:00', 'suscriptor'),
('Jorge', 'Huamán Ortiz', '+51 900 000 103', 'jorge.huaman@expediatravels.pe', '$2y$12$hzehrAtvj2Smn.qAn00yQ.VecHri4JjTJeUoj5u8BVUVqefWWIIxe', '2024-03-05 12:15:00', 'suscriptor'),
('Lucía', 'Salazar Vega', '+51 900 000 104', 'lucia.salazar@expediatravels.pe', '$2y$12$hzehrAtvj2Smn.qAn00yQ.VecHri4JjTJeUoj5u8BVUVqefWWIIxe', '2024-04-18 09:45:00', 'suscriptor');

INSERT INTO reservas (usuario_id, paquete_id, fecha_reserva, cantidad_personas, total, estado, creado_en)
VALUES
(2, 1, '2024-07-08', 3, 360.00, 'confirmada', '2024-07-01 09:20:00'),
(3, 4, '2024-07-09', 2, 190.00, 'pendiente', '2024-07-02 17:45:00'),
(4, 3, '2024-07-15', 5, 750.00, 'confirmada', '2024-07-03 15:10:00'),
(3, 2, '2024-07-12', 2, 220.00, 'cancelada', '2024-07-04 11:55:00'),
(2, 5, '2024-07-20', 4, 520.00, 'confirmada', '2024-07-05 08:40:00');

INSERT INTO pagos (reserva_id, metodo, monto, estado, fecha_pago)
VALUES
(1, 'paypal', 360.00, 'aprobado', '2024-07-01 10:05:00'),
(3, 'culqi', 750.00, 'aprobado', '2024-07-03 16:00:00'),
(5, 'izipay', 520.00, 'pendiente', NULL);

INSERT INTO consultas_contacto (nombre, correo, asunto, mensaje, estado, canal, creado_en)
VALUES
('Andrea Valdez', 'andrea.valdez@example.com', 'Consulta sobre disponibilidad', 'Hola, quisiera saber si hay salidas para agosto.', 'abierta', 'web', '2024-07-02 09:15:00'),
('Carlos Ramos', 'carlos.ramos@example.com', 'Cotización corporativa', 'Requiero una cotización para un viaje corporativo de 12 personas.', 'en_progreso', 'telefono', '2024-06-30 16:40:00'),
('Fiorella Díaz', 'fiorella.diaz@example.com', 'Métodos de pago', '¿Puedo pagar en cuotas el tour Pozuzo?', 'abierta', 'whatsapp', '2024-07-05 13:25:00'),
('Luis Castillo', 'luis.castillo@example.com', 'Felicitaciones', 'Excelente servicio en nuestra última visita.', 'cerrada', 'web', '2024-06-20 10:00:00');

INSERT INTO salidas_programadas (paquete_id, fecha_salida, estado, aforo, reservas_confirmadas, notas)
VALUES
(1, '2024-07-08', 'confirmada', 25, 18, 'Salida garantizada'),
(2, '2024-07-10', 'programada', 20, 9, 'Cierre de ventas 48h antes'),
(3, '2024-07-15', 'confirmada', 30, 26, 'Grupo completo con guía bilingüe'),
(4, '2024-07-18', 'reprogramada', 18, 12, 'Reprogramada por mantenimiento en catarata'),
(5, '2024-07-22', 'programada', 16, 8, NULL);
