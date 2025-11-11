-- Migración para agregar el campo de ubicación de Google Maps al itinerario
-- y retirar la tabla de marcadores individuales.
ALTER TABLE circuito_itinerarios
    ADD COLUMN IF NOT EXISTS ubicacion_maps VARCHAR(255) DEFAULT NULL AFTER descripcion;

DROP TABLE IF EXISTS circuito_marcadores;
