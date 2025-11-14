-- Agrega metadatos de capacidad y idiomas a los circuitos existentes.
ALTER TABLE circuitos
    ADD COLUMN IF NOT EXISTS tamano_grupo VARCHAR(120) DEFAULT NULL AFTER frecuencia,
    ADD COLUMN IF NOT EXISTS idiomas JSON DEFAULT NULL AFTER tamano_grupo;
