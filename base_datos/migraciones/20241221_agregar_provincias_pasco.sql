-- Registra las provincias del departamento de Pasco como destinos individuales.
INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Pasco', 'pasco-provincia', 'Pasco',
       'Provincia minera y cultural que tiene como capital a Cerro de Pasco, la ciudad más alta del país.',
       'Capital minera en las alturas andinas.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'pasco-provincia' OR (nombre = 'Pasco' AND region = 'Pasco')
);

INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Daniel Alcides Carrión', 'daniel-alcides-carrion', 'Pasco',
       'Provincia serrana cuya capital Yanahuanca resguarda tradiciones campesinas y paisajes de montaña.',
       'Tradición yanahuanquina en el corazón de Pasco.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'daniel-alcides-carrion' OR nombre = 'Daniel Alcides Carrión'
);

INSERT INTO destinos (nombre, slug, region, descripcion, tagline, estado, mostrar_en_buscador, mostrar_en_explorador)
SELECT 'Oxapampa', 'oxapampa-provincia', 'Pasco',
       'Provincia de selva alta con capital en Oxapampa, famosa por su herencia austroalemana y reservas de biosfera.',
       'Selva alta con identidad austroalemana.',
       'activo', 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM destinos WHERE slug = 'oxapampa-provincia' OR (nombre = 'Oxapampa' AND region = 'Pasco')
);
