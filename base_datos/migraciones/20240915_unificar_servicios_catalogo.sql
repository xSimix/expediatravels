-- Migración para unificar el catálogo de servicios en una sola lista.
ALTER TABLE servicios_catalogo
    DROP COLUMN tipo;
