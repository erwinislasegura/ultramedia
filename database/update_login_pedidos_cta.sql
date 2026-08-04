-- Login administrativo, datos completos de pedidos y CTA de portada
ALTER TABLE orders ADD COLUMN phone VARCHAR(40) NULL AFTER customer_email;
ALTER TABLE orders ADD COLUMN rut VARCHAR(20) NULL AFTER phone;
CREATE TABLE IF NOT EXISTS homepage_cta(
 id TINYINT UNSIGNED PRIMARY KEY,
 event_id INT UNSIGNED NULL,
 eyebrow VARCHAR(100) NOT NULL DEFAULT 'EVENTO DESTACADO',
 title VARCHAR(180) NOT NULL,
 description VARCHAR(500) NULL,
 button_text VARCHAR(80) NOT NULL DEFAULT 'VER FOTOGRAFÍAS',
 button_url VARCHAR(255) NOT NULL DEFAULT '#fotos',
 image_url VARCHAR(500) NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_cta_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE SET NULL
) ENGINE=InnoDB;
INSERT INTO homepage_cta(id,event_id,eyebrow,title,description,button_text,button_url,image_url,active)
SELECT 1,id,'EVENTO DESTACADO',name,'Ya están disponibles las fotografías oficiales del evento.','VER FOTOGRAFÍAS','#fotos','https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1800&q=88',1 FROM events ORDER BY event_date DESC LIMIT 1
ON DUPLICATE KEY UPDATE id=id;

