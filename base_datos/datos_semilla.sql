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

INSERT INTO destinos (
    nombre,
    slug,
    descripcion,
    tagline,
    lat,
    lon,
    imagen,
    imagen_destacada,
    region,
    galeria,
    video_destacado_url,
    tags,
    estado,
    mostrar_en_buscador,
    mostrar_en_explorador,
    creado_en,
    actualizado_en
) VALUES
('Amazonas', 'amazonas', 'Selva alta, cataratas imponentes y legado chachapoya.', 'Aventuras en la selva alta del norte.', NULL, NULL, NULL, NULL, 'Amazonas', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Áncash', 'ancash', 'Cordillera Blanca, lagunas turquesas y arqueología preinca.', 'Montañas nevadas y cultura viva.', NULL, NULL, NULL, NULL, 'Áncash', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Apurímac', 'apurimac', 'Cañones profundos, puentes colgantes y comunidades quechuas.', 'Puertas de los Andes Centrales.', NULL, NULL, NULL, NULL, 'Apurímac', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Arequipa', 'arequipa', 'Ciudad blanca, volcanes tutelares y gastronomía emblemática.', 'Contrastes entre sillar y volcanes.', NULL, NULL, NULL, NULL, 'Arequipa', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Ayacucho', 'ayacucho', 'Arte religioso, Semana Santa y retablos tradicionales.', 'Capital del arte popular peruano.', NULL, NULL, NULL, NULL, 'Ayacucho', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Cajamarca', 'cajamarca', 'Campos verdes, historia inca y aguas termales revitalizantes.', 'Encuentro de historia y naturaleza.', NULL, NULL, NULL, NULL, 'Cajamarca', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Cusco', 'cusco', 'Capital del Tahuantinsuyo y acceso al Valle Sagrado.', 'Puerta de entrada a Machu Picchu.', NULL, NULL, NULL, NULL, 'Cusco', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Huancavelica', 'huancavelica', 'Paisajes altoandinos, ferias patronales y arquitectura virreinal.', 'Tradición minera y cultura viva.', NULL, NULL, NULL, NULL, 'Huancavelica', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Huánuco', 'huanuco', 'Puerta de la Amazonía, bosques nubosos y legado kotosh.', 'Clima primaveral todo el año.', NULL, NULL, NULL, NULL, 'Huánuco', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Ica', 'ica', 'Viñedos, oasis desérticos y líneas de Nazca a pocos kilómetros.', 'Sabores costeños y dunas infinitas.', NULL, NULL, NULL, NULL, 'Ica', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Junín', 'junin', 'Selva central, cataratas y tradición cafetalera en expansión.', 'Capital de la aventura en la selva central.', NULL, NULL, NULL, NULL, 'Junín', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('La Libertad', 'la-libertad', 'Chan Chan, balnearios y festivales primaverales.', 'Historia chimú frente al Pacífico.', NULL, NULL, NULL, NULL, 'La Libertad', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Lambayeque', 'lambayeque', 'Museos de élite, playas cálidas y tradición mochica.', 'Cuna del Señor de Sipán.', NULL, NULL, NULL, NULL, 'Lambayeque', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Lima', 'lima', 'Capital cosmopolita con barrios bohemios y gastronomía de clase mundial.', 'Punto de partida del Perú contemporáneo.', NULL, NULL, NULL, NULL, 'Lima', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Loreto', 'loreto', 'Selva amazónica, ríos imponentes y reservas biodiversas.', 'Aventura fluvial en la Amazonía peruana.', NULL, NULL, NULL, NULL, 'Loreto', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Madre de Dios', 'madre-de-dios', 'Reserva de biosfera, biodiversidad y lodges inmersos en la selva.', 'Capital de la biodiversidad peruana.', NULL, NULL, NULL, NULL, 'Madre de Dios', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Moquegua', 'moquegua', 'Viñedos, campiñas soleadas y arquitectura colonial preservada.', 'Sabores del sur en pleno desierto.', NULL, NULL, NULL, NULL, 'Moquegua', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Pasco', 'pasco', 'Bosques nubosos, tradición austroalemana y reservas de biosfera.', 'Pasajes verdes de la selva central.', NULL, NULL, NULL, NULL, 'Pasco', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Piura', 'piura', 'Playas cálidas, artesanía y tradición gastronómica norteña.', 'Verano eterno y ritmo norteño.', NULL, NULL, NULL, NULL, 'Piura', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Puno', 'puno', 'Lago Titicaca, islas flotantes y festivales altiplánicos.', 'El altiplano místico del Perú.', NULL, NULL, NULL, NULL, 'Puno', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('San Martín', 'san-martin', 'Selva alta, cataratas y producción de cacao de calidad.', 'Puente entre Andes y Amazonía.', NULL, NULL, NULL, NULL, 'San Martín', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Tacna', 'tacna', 'Historia republicana, campiñas y circuitos termales.', 'Hospitalidad e identidad patriótica.', NULL, NULL, NULL, NULL, 'Tacna', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Tumbes', 'tumbes', 'Manglares, playas cálidas y ecosistemas marinos únicos.', 'Sol perpetuo en el extremo norte.', NULL, NULL, NULL, NULL, 'Tumbes', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00'),
('Ucayali', 'ucayali', 'Comunidades shipibo-konibo, lagunas y selva vibrante.', 'Cultura amazónica en cada travesía.', NULL, NULL, NULL, NULL, 'Ucayali', NULL, NULL, NULL, 'activo', 1, 1, '2024-01-04 12:00:00', '2024-01-04 12:00:00');
INSERT INTO circuitos (
    destino_id,
    destino_personalizado,
    nombre,
    duracion,
    precio,
    categoria,
    dificultad,
    frecuencia,
    tamano_grupo,
    idiomas,
    estado,
    estado_publicacion,
    vigencia_desde,
    vigencia_hasta,
    visibilidad,
    descripcion,
    puntos_interes,
    servicios,
    imagen_portada,
    imagen_destacada,
    galeria,
    video_destacado_url,
    creado_en,
    actualizado_en
) VALUES
((SELECT id FROM destinos WHERE nombre = 'Junín' LIMIT 1),
 'Perené',
 'Tour Perené',
 'Full day',
  180.00,
 'aventura',
 'moderado',
 'Diario',
 'Hasta 16 viajeros',
 JSON_ARRAY('Español', 'Inglés'),
 'activo',
 'publicado',
 '2024-01-01 00:00:00',
 NULL,
 'publico',
 'Inicio: 08:00 a. m. · Finaliza: 07:30 p. m. (aprox.). Circuito amazónico con cataratas, comunidades nativas y deportes de aventura opcionales.',
 JSON_ARRAY('Deportes de aventura (opcional)', 'Mariposario / Zoológico', 'Catarata de Bayoz (trekking de 15 minutos)', 'Catarata Velo de la Novia', 'Paseo en bote en el río Perené', 'Comunidad nativa'),
 JSON_ARRAY('Transporte turístico', 'Guía especializado', 'Entradas a atractivos principales'),
 'almacenamiento/medios/tour-perene-portada.jpg',
 'almacenamiento/medios/tour-perene-destacado.jpg',
 JSON_ARRAY('almacenamiento/medios/tour-perene-1.jpg'),
 NULL,
 '2024-01-04 12:00:00',
 '2024-01-04 12:00:00'),
((SELECT id FROM destinos WHERE nombre = 'Pasco' LIMIT 1),
 'Oxapampa',
 'Tour Oxapampa',
 'Full day',
  150.00,
 'naturaleza',
 'relajado',
 'Lunes a domingo',
 'Hasta 18 viajeros',
 JSON_ARRAY('Español'),
 'activo',
 'publicado',
 '2024-01-01 00:00:00',
 NULL,
 'publico',
 'Inicio: 10:30 a. m. · Finaliza: 06:30 p. m. (aprox.). Experiencia cultural y natural por los principales atractivos de Oxapampa.',
 JSON_ARRAY('Danzas austroalemanas', 'Mirador de Oharampa', 'Tunqui Cueva', 'El Wharapo', 'Artesanías', 'Apicultura / Degustación', 'Parque temático de Chontabamba', 'Catarata del Río Tigre', 'Manantial La Virgen', 'Portal El Abra', 'Casa Museo Schlaefli'),
 JSON_ARRAY('Transporte turístico', 'Guía local', 'Degustaciones programadas'),
 'almacenamiento/medios/tour-oxapampa-portada.jpg',
 'almacenamiento/medios/tour-oxapampa-destacado.jpg',
 JSON_ARRAY('almacenamiento/medios/tour-oxapampa-1.jpg'),
 NULL,
 '2024-01-04 12:00:00',
 '2024-01-04 12:00:00'),
((SELECT id FROM destinos WHERE nombre = 'Pasco' LIMIT 1),
 'Pozuzo',
 'Tour Pozuzo',
 'Full day',
  210.00,
 'aventura',
 'moderado',
 'Martes y sábado',
 'Hasta 12 viajeros',
 JSON_ARRAY('Español', 'Inglés'),
 'activo',
 'publicado',
 '2024-01-01 00:00:00',
 NULL,
 'publico',
 'Inicio: 08:30 a. m. · Finaliza: 06:30 p. m. (aprox.). Ruta histórica por la colonia austro-alemana con cascadas y experiencias vivenciales.',
 JSON_ARRAY('Catarata Rayantambo', 'Cascada Yulitunqui', 'Portal de Pozuzo', 'Parque Temático', 'Barco Norton', 'Puente Colgante Emperador Guillermo I', 'Cerveza artesanal', 'Pozas de Agua y Sal', 'Barrio Prusia'),
 JSON_ARRAY('Transporte 4x4', 'Guía bilingüe', 'Almuerzo típico'),
 'almacenamiento/medios/tour-pozuzo-portada.jpg',
 'almacenamiento/medios/tour-pozuzo-destacado.jpg',
 JSON_ARRAY('almacenamiento/medios/tour-pozuzo-1.jpg'),
 NULL,
 '2024-01-04 12:00:00',
 '2024-01-04 12:00:00'),
((SELECT id FROM destinos WHERE nombre = 'Pasco' LIMIT 1),
 'Villa Rica',
 'Tour Villa Rica',
 'Full day',
  165.00,
 'cultural',
 'relajado',
 'Viernes a lunes',
 'Hasta 14 viajeros',
 JSON_ARRAY('Español'),
 'activo',
 'publicado',
 '2024-01-01 00:00:00',
 NULL,
 'publico',
 'Inicio: 08:30 a. m. · Finaliza: 06:30 p. m. (aprox.). Circuito cafetalero y de bienestar por Villa Rica.',
 JSON_ARRAY('Deportes de aventura (opcional)', 'Portal de Villa Rica', 'Laguna El Oconal', 'Paseo en bote', 'Ictioterapia de pies', 'Catación de café', 'Cascadas El León', 'Mirador La Cumbre', 'Plaza de Villa Rica', 'Chocolatería'),
 JSON_ARRAY('Transporte turístico', 'Guía barista', 'Degustación de café'),
 'almacenamiento/medios/tour-villarica-portada.jpg',
 'almacenamiento/medios/tour-villarica-destacado.jpg',
 JSON_ARRAY('almacenamiento/medios/tour-villarica-1.jpg'),
 NULL,
 '2024-01-04 12:00:00',
 '2024-01-04 12:00:00'),
((SELECT id FROM destinos WHERE nombre = 'Pasco' LIMIT 1),
 'Yanachaga',
 'Tour Yanachaga',
 'Full day',
  190.00,
 'naturaleza',
 'moderado',
 'Programación especial',
 'Hasta 10 viajeros',
 JSON_ARRAY('Español'),
 'activo',
 'publicado',
 '2024-01-01 00:00:00',
 NULL,
 'publico',
 'Inicio: 07:00 a. m. · Finaliza: 06:00 p. m. (aprox.). Exploración de la Reserva Yanachaga-Chemillén con guías especialistas.',
 JSON_ARRAY('Sendero San Alberto', 'Centro de interpretación', 'Observación de aves', 'Mirador El Cedro'),
 JSON_ARRAY('Transporte turístico', 'Guía especializado', 'Entradas a la reserva'),
 'almacenamiento/medios/tour-yanachaga-portada.jpg',
 'almacenamiento/medios/tour-yanachaga-destacado.jpg',
 JSON_ARRAY('almacenamiento/medios/tour-yanachaga-1.jpg'),
 NULL,
 '2024-01-04 12:00:00',
 '2024-01-04 12:00:00');

INSERT INTO circuito_destinos (circuito_id, destino_id)
SELECT c.id, c.destino_id
FROM circuitos c
WHERE c.destino_id IS NOT NULL;
INSERT INTO paquetes (
    destino_id,
    nombre,
    resumen,
    itinerario,
    duracion,
    precio,
    moneda,
    estado,
    estado_publicacion,
    vigencia_desde,
    vigencia_hasta,
    visibilidad,
    imagen_portada,
    imagen_destacada,
    galeria,
    video_destacado_url,
    beneficios,
    incluye,
    no_incluye,
    salidas,
    cupos_min,
    cupos_max,
    creado_en,
    actualizado_en
) VALUES
((SELECT id FROM destinos WHERE nombre = 'Pasco' LIMIT 1),
 'Tour Oxapampa',
 'Tunqui Cueva, El Wharapo y Catarata Río Tigre en una experiencia full day.',
 'Visita Tunqui Cueva, degustación en El Wharapo, caminata a la Catarata Río Tigre y recorrido por el Parque Temático Chontabamba.',
 '1 día',
  120.00,
 'PEN',
 'publicado',
 'publicado',
 '2024-01-01 00:00:00',
 NULL,
 'publico',
 'almacenamiento/medios/paquete-oxapampa-portada.jpg',
 'almacenamiento/medios/paquete-oxapampa-destacado.jpg',
 JSON_ARRAY('almacenamiento/medios/paquete-oxapampa-1.jpg'),
 NULL,
 JSON_ARRAY('Salidas garantizadas todo el año', 'Degustación de productos locales'),
 JSON_ARRAY('Transporte turístico', 'Guía oficial de turismo', 'Entradas a atractivos'),
 JSON_ARRAY('Almuerzos', 'Gastos personales'),
 JSON_ARRAY('Diarias', 'Especiales feriados'),
 8,
 24,
 '2024-01-04 12:00:00',
 '2024-01-04 12:00:00'),
((SELECT id FROM destinos WHERE nombre = 'Pasco' LIMIT 1),
 'Tour Villa Rica',
 'Laguna El Oconal, catación de café y mirador La Cumbre.',
 'Ingreso al Portal de Villa Rica, navegación en la laguna, ictioterapia, catación de café y puesta de sol en el mirador.',
 '1 día',
  110.00,
 'PEN',
 'publicado',
 'publicado',
 '2024-01-01 00:00:00',
 NULL,
 'publico',
 'almacenamiento/medios/paquete-villarica-portada.jpg',
 'almacenamiento/medios/paquete-villarica-destacado.jpg',
 JSON_ARRAY('almacenamiento/medios/paquete-villarica-1.jpg'),
 NULL,
 JSON_ARRAY('Experiencia cafetalera guiada', 'Avistamiento de aves en El Oconal'),
 JSON_ARRAY('Transporte turístico', 'Guía barista', 'Catación de café'),
 JSON_ARRAY('Alimentación no mencionada', 'Propinas'),
 JSON_ARRAY('Viernes', 'Sábado', 'Domingo'),
 6,
 20,
 '2024-01-04 12:00:00',
 '2024-01-04 12:00:00'),
((SELECT id FROM destinos WHERE nombre = 'Pasco' LIMIT 1),
 'Tour Pozuzo',
 'Descubre la colonia austro-alemana y sus cascadas.',
 'Recorrido histórico, visita a cervecería artesanal, caminata a cascadas y cruce por el puente colgante.',
 '1 día',
  150.00,
 'PEN',
 'publicado',
 'publicado',
 '2024-01-01 00:00:00',
 NULL,
 'publico',
 'almacenamiento/medios/paquete-pozuzo-portada.jpg',
 'almacenamiento/medios/paquete-pozuzo-destacado.jpg',
 JSON_ARRAY('almacenamiento/medios/paquete-pozuzo-1.jpg'),
 NULL,
 JSON_ARRAY('Contacto directo con colonos austro-alemanes', 'Caminatas interpretativas'),
 JSON_ARRAY('Transporte 4x4', 'Guía local', 'Entradas a atractivos'),
 JSON_ARRAY('Alimentación no mencionada', 'Bebidas alcohólicas'),
 JSON_ARRAY('Martes', 'Sábado'),
 8,
 18,
 '2024-01-04 12:00:00',
 '2024-01-04 12:00:00'),
((SELECT id FROM destinos WHERE nombre = 'Junín' LIMIT 1),
 'Tour Perené',
 'Catarata Bayoz, Velo de la Novia y paseo en bote.',
 'Tour por mariposario, caminata a las cataratas y navegación por el río Perené.',
 '1 día',
  95.00,
 'PEN',
 'publicado',
 'publicado',
 '2024-01-01 00:00:00',
 NULL,
 'publico',
 'almacenamiento/medios/paquete-perene-portada.jpg',
 'almacenamiento/medios/paquete-perene-destacado.jpg',
 JSON_ARRAY('almacenamiento/medios/paquete-perene-1.jpg'),
 NULL,
 JSON_ARRAY('Contacto con comunidades nativas', 'Actividades de aventura opcionales'),
 JSON_ARRAY('Transporte turístico', 'Guía especializado', 'Entradas a atractivos'),
 JSON_ARRAY('Actividades opcionales', 'Alimentación no mencionada'),
 JSON_ARRAY('Diario'),
 6,
 22,
 '2024-01-04 12:00:00',
 '2024-01-04 12:00:00'),
((SELECT id FROM destinos WHERE nombre = 'Pasco' LIMIT 1),
 'Tour Yanachaga',
 'Avistamiento de aves en Lluvias Eternas.',
 'Senderismo interpretativo, observación de flora y fauna, visita al centro de interpretación.',
 '1 día',
  130.00,
 'PEN',
 'publicado',
 'publicado',
 '2024-01-01 00:00:00',
 NULL,
 'publico',
 'almacenamiento/medios/paquete-yanachaga-portada.jpg',
 'almacenamiento/medios/paquete-yanachaga-destacado.jpg',
 JSON_ARRAY('almacenamiento/medios/paquete-yanachaga-1.jpg'),
 NULL,
 JSON_ARRAY('Avistamiento de fauna endémica', 'Guía especialista en naturaleza'),
 JSON_ARRAY('Transporte turístico', 'Guía especializado', 'Equipo de observación'),
 JSON_ARRAY('Alimentación no mencionada', 'Gastos personales'),
 JSON_ARRAY('Programadas según temporada'),
 6,
 16,
 '2024-01-04 12:00:00',
 '2024-01-04 12:00:00');
INSERT INTO paquete_destinos (paquete_id, destino_id)
SELECT p.id, p.destino_id
FROM paquetes p
WHERE p.destino_id IS NOT NULL;

INSERT INTO paquete_circuitos (paquete_id, circuito_id) VALUES
(1, (SELECT id FROM circuitos WHERE nombre = 'Tour Oxapampa' LIMIT 1)),
(2, (SELECT id FROM circuitos WHERE nombre = 'Tour Villa Rica' LIMIT 1)),
(3, (SELECT id FROM circuitos WHERE nombre = 'Tour Pozuzo' LIMIT 1)),
(4, (SELECT id FROM circuitos WHERE nombre = 'Tour Perené' LIMIT 1)),
(5, (SELECT id FROM circuitos WHERE nombre = 'Tour Yanachaga' LIMIT 1));

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

INSERT INTO media_items (titulo, descripcion, texto_alternativo, creditos, ruta, nombre_archivo, nombre_original, tipo_mime, extension, tamano_bytes, ancho, alto, sha1_hash, creado_en)
VALUES
('Bosque neblinoso', 'Amanecer en los bosques de Oxapampa con neblina dorada.', 'Bosque iluminado por el amanecer en Oxapampa', 'Foto: Lucía Salazar', 'almacenamiento/medios/oxapampa-bosque.jpg', 'oxapampa-bosque.jpg', 'oxapampa-bosque.jpg', 'image/jpeg', 'jpg', 284512, 1600, 1067, '0f2c5f98c9ea7f1d233a7a52f7d022d2a3e2b1d3', '2024-07-01 09:00:00'),
('Laguna el Oconal', 'Reflejos perfectos en la Laguna El Oconal durante el atardecer.', 'Laguna con reflejo de montañas y cielo dorado', 'Foto: Jorge Huamán', 'almacenamiento/medios/laguna-oconal.jpg', 'laguna-oconal.jpg', 'laguna-oconal.jpg', 'image/jpeg', 'jpg', 315440, 1600, 1067, '9e320fb2d4b79e3c47691d9961c3c2de3c8a6d4f', '2024-07-02 18:20:00');

-- Servicios disponibles para circuitos.
INSERT INTO servicios_catalogo (nombre, icono, descripcion, activo)
VALUES
    ('Transporte turístico', 'fa-solid fa-bus', 'Traslado terrestre para todo el circuito.', 1),
    ('Guía especializado', 'fa-solid fa-person-hiking', 'Guías bilingües certificados.', 1),
    ('Guía local', 'fa-solid fa-person-hiking', 'Acompañamiento de guías locales en destino.', 1),
    ('Entradas a atractivos', 'fa-solid fa-ticket', 'Tickets de ingreso a sitios turísticos.', 1),
    ('Alimentación durante el circuito', 'fa-solid fa-utensils', 'Almuerzos o snacks indicados en el itinerario.', 1),
    ('Degustaciones programadas', 'fa-solid fa-wine-glass', 'Degustaciones de productos locales incluidas en el programa.', 1),
    ('Alojamiento en hotel', 'fa-solid fa-hotel', 'Hospedaje según categoría indicada.', 1),
    ('Desayunos incluidos', 'fa-solid fa-mug-hot', 'Desayunos diarios durante el circuito.', 1),
    ('Asistencia médica durante el viaje', 'fa-solid fa-briefcase-medical', 'Equipo de primeros auxilios y apoyo básico.', 1),
    ('Seguro de viaje', 'fa-solid fa-shield', 'Coberturas personales adicionales.', 1),
    ('Traslados aeropuerto–hotel–aeropuerto', 'fa-solid fa-plane-arrival', 'Traslados coordinados según itinerario.', 1),
    ('Actividades guiadas', 'fa-solid fa-route', 'Actividades acompañadas por guías autorizados.', 1),
    ('Equipos de seguridad o aventura', 'fa-solid fa-helmet-safety', 'Equipamiento requerido para actividades programadas.', 1),
    ('Fotografías o video del recorrido', 'fa-solid fa-camera', 'Registro audiovisual básico del circuito.', 1),
    ('Impuestos y tasas locales', 'fa-solid fa-file-invoice-dollar', 'Tributos aplicables incluidos en la tarifa.', 1),
    ('Propinas', 'fa-solid fa-hand-holding-dollar', 'Propinas a guías y conductores.', 1),
    ('Bebidas alcohólicas', 'fa-solid fa-wine-glass', 'Consumo de bebidas alcohólicas.', 1),
    ('Gastos personales', 'fa-solid fa-wallet', 'Compras o servicios fuera del programa.', 1),
    ('Souvenirs o compras', 'fa-solid fa-bag-shopping', 'Artículos personales y recuerdos.', 1),
    ('Vuelos nacionales', 'fa-solid fa-plane', 'Vuelos internos no contemplados en el paquete.', 1),
    ('Vuelos internacionales', 'fa-solid fa-earth-americas', 'Pasajes internacionales hacia/desde el destino.', 1),
    ('Actividades opcionales fuera del itinerario', 'fa-solid fa-puzzle-piece', 'Actividades no mencionadas en el programa.', 1),
    ('Servicios de lavandería', 'fa-solid fa-shirt', 'Lavandería o planchado en el alojamiento.', 1),
    ('Consumo en minibar', 'fa-solid fa-wine-bottle', 'Consumo adicional en la habitación.', 1),
    ('Almuerzos o cenas no especificadas', 'fa-solid fa-burger', 'Comidas no detalladas en el itinerario.', 1),
    ('Acceso VIP o preferencial', 'fa-solid fa-crown', 'Servicios con prioridad o acceso especial.', 1),
    ('Transporte marítimo o fluvial', 'fa-solid fa-ship', 'Traslados por vía marítima o fluvial programados.', 1);

-- Relaciones de servicios por circuito de ejemplo.
INSERT INTO circuito_servicios (circuito_id, servicio_id, tipo)
SELECT c.id, s.id, 'incluido'
FROM circuitos c
JOIN servicios_catalogo s ON s.nombre IN ('Transporte turístico', 'Guía especializado', 'Entradas a atractivos')
WHERE c.nombre = 'Tour Perené';

INSERT INTO circuito_servicios (circuito_id, servicio_id, tipo)
SELECT c.id, s.id, 'incluido'
FROM circuitos c
JOIN servicios_catalogo s ON s.nombre IN ('Transporte turístico', 'Guía local', 'Degustaciones programadas')
WHERE c.nombre = 'Tour Oxapampa';

-- Itinerarios de referencia.
INSERT INTO circuito_itinerarios (circuito_id, orden, dia, hora, titulo, descripcion, ubicacion_maps)
SELECT c.id, 1, 'Mañana', '08:00', 'Salida de Oxapampa', 'Recojo desde hotel y charla de seguridad.', ''
FROM circuitos c WHERE c.nombre = 'Tour Oxapampa';
INSERT INTO circuito_itinerarios (circuito_id, orden, dia, hora, titulo, descripcion, ubicacion_maps)
SELECT c.id, 2, 'Mañana', '10:30', 'Tunqui Cueva', 'Recorrido guiado por formaciones rocosas.', ''
FROM circuitos c WHERE c.nombre = 'Tour Oxapampa';
INSERT INTO circuito_itinerarios (circuito_id, orden, dia, hora, titulo, descripcion, ubicacion_maps)
SELECT c.id, 3, 'Tarde', '15:00', 'Catarata Río Tigre', 'Caminata corta para disfrutar del paisaje.', ''
FROM circuitos c WHERE c.nombre = 'Tour Oxapampa';

