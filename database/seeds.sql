-- Datos de muestra para Expediatravels.
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

INSERT INTO hero_slides (image_url, label, sort_order, is_visible) VALUES
('https://images.unsplash.com/photo-1529923188384-5e545b81d48d?auto=format&fit=crop&w=1600&q=80', 'Bosques de Oxapampa', 1, 1),
('https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80', 'Laguna El Oconal', 2, 1),
('https://images.unsplash.com/photo-1505761671935-60b3a7427bad?auto=format&fit=crop&w=1600&q=80', 'Cascadas de Pozuzo', 3, 1);
