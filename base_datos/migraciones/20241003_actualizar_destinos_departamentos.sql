-- Añade banderas de visibilidad para destinos y registra los 24 departamentos del Perú.
ALTER TABLE destinos
    ADD COLUMN IF NOT EXISTS mostrar_en_buscador TINYINT(1) NOT NULL DEFAULT 1 AFTER estado,
    ADD COLUMN IF NOT EXISTS mostrar_en_explorador TINYINT(1) NOT NULL DEFAULT 1 AFTER mostrar_en_buscador;

-- Registra los 24 departamentos del Perú como destinos base.
INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Amazonas', 'Amazonas', 'Selva alta, cataratas imponentes y legado chachapoya.', 'Aventuras en la selva alta del norte.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Amazonas');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Áncash', 'Áncash', 'Cordillera Blanca, lagunas turquesas y arqueología preinca.', 'Montañas nevadas y cultura viva.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Áncash');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Apurímac', 'Apurímac', 'Cañones profundos, puentes colgantes y comunidades quechuas.', 'Puertas de los Andes Centrales.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Apurímac');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Arequipa', 'Arequipa', 'Ciudad blanca, volcanes tutelares y gastronomía emblemática.', 'Contrastes entre sillar y volcanes.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Arequipa');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Ayacucho', 'Ayacucho', 'Arte religioso, Semana Santa y retablos tradicionales.', 'Capital del arte popular peruano.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Ayacucho');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Cajamarca', 'Cajamarca', 'Campos verdes, historia inca y aguas termales revitalizantes.', 'Encuentro de historia y naturaleza.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Cajamarca');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Cusco', 'Cusco', 'Capital del Tahuantinsuyo y acceso al Valle Sagrado.', 'Puerta de entrada a Machu Picchu.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Cusco');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Huancavelica', 'Huancavelica', 'Paisajes altoandinos, ferias patronales y arquitectura virreinal.', 'Tradición minera y cultura viva.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Huancavelica');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Huánuco', 'Huánuco', 'Puerta de la Amazonía, bosques nubosos y legado kotosh.', 'Clima primaveral todo el año.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Huánuco');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Ica', 'Ica', 'Viñedos, oasis desérticos y líneas de Nazca a pocos kilómetros.', 'Sabores costeños y dunas infinitas.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Ica');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Junín', 'Junín', 'Selva central, cataratas y tradición cafetalera en expansión.', 'Capital de la aventura en la selva central.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Junín');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'La Libertad', 'La Libertad', 'Chan Chan, balnearios y festivales primaverales.', 'Historia chimú frente al Pacífico.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'La Libertad');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Lambayeque', 'Lambayeque', 'Museos de élite, playas cálidas y tradición mochica.', 'Cuna del Señor de Sipán.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Lambayeque');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Lima', 'Lima', 'Capital cosmopolita con barrios bohemios y gastronomía de clase mundial.', 'Punto de partida del Perú contemporáneo.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Lima');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Loreto', 'Loreto', 'Selva amazónica, ríos imponentes y reservas biodiversas.', 'Aventura fluvial en la Amazonía peruana.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Loreto');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Madre de Dios', 'Madre de Dios', 'Reserva de biosfera, biodiversidad y lodges inmersos en la selva.', 'Capital de la biodiversidad peruana.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Madre de Dios');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Moquegua', 'Moquegua', 'Viñedos, campiñas soleadas y arquitectura colonial preservada.', 'Sabores del sur en pleno desierto.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Moquegua');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Pasco', 'Pasco', 'Bosques nubosos, tradición austroalemana y reservas de biosfera.', 'Pasajes verdes de la selva central.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Pasco');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Piura', 'Piura', 'Playas cálidas, artesanía y tradición gastronómica norteña.', 'Verano eterno y ritmo norteño.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Piura');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Puno', 'Puno', 'Lago Titicaca, islas flotantes y festivales altiplánicos.', 'El altiplano místico del Perú.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Puno');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'San Martín', 'San Martín', 'Selva alta, cataratas y producción de cacao de calidad.', 'Puente entre Andes y Amazonía.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'San Martín');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Tacna', 'Tacna', 'Historia republicana, campiñas y circuitos termales.', 'Hospitalidad e identidad patriótica.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Tacna');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Tumbes', 'Tumbes', 'Manglares, playas cálidas y ecosistemas marinos únicos.', 'Sol perpetuo en el extremo norte.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Tumbes');

INSERT INTO destinos (nombre, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Ucayali', 'Ucayali', 'Comunidades shipibo-konibo, lagunas y selva vibrante.', 'Cultura amazónica en cada travesía.', 'activo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM destinos WHERE nombre = 'Ucayali');
