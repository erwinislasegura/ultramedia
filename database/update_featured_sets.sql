-- Permite seleccionar qué sets completos se muestran en la página de inicio.
ALTER TABLE photo_sets
    ADD COLUMN IF NOT EXISTS featured_home TINYINT(1) NOT NULL DEFAULT 0 AFTER set_enabled;

CREATE INDEX IF NOT EXISTS idx_photo_sets_featured_home
    ON photo_sets(featured_home, status);
