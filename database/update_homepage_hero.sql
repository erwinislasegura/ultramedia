CREATE TABLE IF NOT EXISTS homepage_hero(
 id TINYINT UNSIGNED PRIMARY KEY,
 eyebrow VARCHAR(120) NOT NULL,
 title VARCHAR(180) NOT NULL,
 highlight VARCHAR(120) NOT NULL,
 description VARCHAR(500) NULL,
 search_placeholder VARCHAR(160) NOT NULL,
 button_text VARCHAR(80) NOT NULL,
 background_url VARCHAR(500) NOT NULL,
 background_position VARCHAR(40) NOT NULL DEFAULT 'center center',
 overlay_opacity TINYINT UNSIGNED NOT NULL DEFAULT 75,
 trust_one VARCHAR(100) NULL,
 trust_two VARCHAR(100) NULL,
 trust_three VARCHAR(100) NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO homepage_hero(id,eyebrow,title,highlight,description,search_placeholder,button_text,background_url,background_position,overlay_opacity,trust_one,trust_two,trust_three)
VALUES(1,'TU ESFUERZO. TU MOMENTO.','ENCUENTRA TU','MEJOR FOTO.','Compra una fotografía específica o descarga el set completo de tu participación.','Número de competidor o evento…','BUSCAR MIS FOTOS','https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1900&q=90','center center',75,'▧ Calidad profesional','↓ Foto o set completo','◇ Pago seguro Flow')
ON DUPLICATE KEY UPDATE id=id;
