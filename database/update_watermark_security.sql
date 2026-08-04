-- Configuración de marca de agua por lote
ALTER TABLE photos
 ADD COLUMN watermark_type ENUM('text','image') NOT NULL DEFAULT 'text' AFTER watermark_enabled,
 ADD COLUMN watermark_text VARCHAR(80) NULL AFTER watermark_type;

