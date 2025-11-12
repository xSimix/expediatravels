-- Registra las 9 provincias del departamento de Junín como destinos individuales.
INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Huancayo', 'huancayo', 'Junín',
       'Capital económica de la sierra central, conocida por su feria dominical y el convento de Santa Rosa de Ocopa.',
       'Tradición wanka y sabores andinos en movimiento.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'huancayo' OR nombre = 'Huancayo'
);

INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Concepción', 'concepcion', 'Junín',
       'Valle fértil al pie del Mantaro con iglesias coloniales, quesos artesanales y rutas agroturísticas.',
       'Sabores y paisajes en el corazón del Mantaro.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'concepcion' OR nombre = 'Concepción'
);

INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Chanchamayo', 'chanchamayo', 'Junín',
       'Puerta de entrada a la selva central con cataratas, cafetales y comunidades asháninkas.',
       'Selva central vibrante y cafetera.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'chanchamayo' OR nombre = 'Chanchamayo'
);

INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Jauja', 'jauja', 'Junín',
       'Primera capital del Perú virreinal, famosa por su Semana Santa, la Tunantada y la laguna de Paca.',
       'Historia viva entre fiestas y paisajes altoandinos.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'jauja' OR nombre = 'Jauja'
);

INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Junín', 'junin-provincia', 'Junín',
       'Altiplano ganadero que bordea el lago Junín, hábitat de parihuanas y sitio de la histórica batalla de 1824.',
       'Altiplano de tradiciones y biodiversidad lacustre.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'junin-provincia' OR (nombre = 'Junín' AND region = 'Junín')
);

INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Satipo', 'satipo', 'Junín',
       'Provincia amazónica con reservas comunitarias, cascadas accesibles y circuitos de aventura.',
       'Aventura amazónica entre ríos y comunidades.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'satipo' OR nombre = 'Satipo'
);

INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Tarma', 'tarma', 'Junín',
       'Conocida como la Perla de los Andes, destaca por sus balcones coloniales y rutas floricultoras.',
       'Perla de los Andes con jardines eternos.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'tarma' OR nombre = 'Tarma'
);

INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Yauli', 'yauli', 'Junín',
       'Provincia minera que alberga paisajes altoandinos, nevados y la ciudad metalúrgica de La Oroya.',
       'Andes industriales y paisajes de altura.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'yauli' OR nombre = 'Yauli'
);

INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Chupaca', 'chupaca', 'Junín',
       'Cuna de danzas tradicionales como la Chonguinada y punto de partida a la laguna Ñahuimpuquio.',
       'Capital de danzas ancestrales y paisajes lagunares.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'chupaca' OR nombre = 'Chupaca'
);
