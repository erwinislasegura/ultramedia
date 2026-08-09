-- ULTRA MEDIA DIGITAL · BASE COMPLETA
-- Instalación nueva. Este archivo elimina las tablas de Ultra si ya existen.
-- No importar sobre una base con pedidos o fotografías reales sin respaldo.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS email_settings;
DROP TABLE IF EXISTS payment_settings;
DROP TABLE IF EXISTS homepage_cta;
DROP TABLE IF EXISTS homepage_hero;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS photos;
DROP TABLE IF EXISTS photo_pack_options;
DROP TABLE IF EXISTS photo_sets;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE roles(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80) NOT NULL,slug VARCHAR(90) NOT NULL UNIQUE,permissions JSON NOT NULL,is_system TINYINT(1) NOT NULL DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE users(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,role_id INT UNSIGNED NOT NULL,name VARCHAR(160) NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,status ENUM('active','inactive') NOT NULL DEFAULT 'active',last_login_at DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,INDEX(role_id),CONSTRAINT fk_user_role FOREIGN KEY(role_id) REFERENCES roles(id)) ENGINE=InnoDB;
CREATE TABLE events(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(160) NOT NULL,slug VARCHAR(180) NOT NULL UNIQUE,sport VARCHAR(80) NOT NULL,event_date DATE NOT NULL,location VARCHAR(160),status ENUM('draft','published','archived') NOT NULL DEFAULT 'published',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE customers(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(160) NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,password_hash VARCHAR(255) NOT NULL,phone VARCHAR(40),rut VARCHAR(20),status ENUM('active','inactive') NOT NULL DEFAULT 'active',last_login_at DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE photo_sets(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,event_id INT UNSIGNED NOT NULL,name VARCHAR(180) NOT NULL,bib_number VARCHAR(30),individual_enabled TINYINT(1) NOT NULL DEFAULT 1,set_enabled TINYINT(1) NOT NULL DEFAULT 1,pack_enabled TINYINT(1) NOT NULL DEFAULT 0,pack_quantity SMALLINT UNSIGNED NOT NULL DEFAULT 5,pack_price INT UNSIGNED NOT NULL DEFAULT 14990,set_price INT UNSIGNED NOT NULL DEFAULT 19990,status ENUM('active','hidden') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(event_id),CONSTRAINT fk_set_event FOREIGN KEY(event_id) REFERENCES events(id)) ENGINE=InnoDB;
CREATE TABLE photo_pack_options(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,set_id BIGINT UNSIGNED NOT NULL,slot TINYINT UNSIGNED NOT NULL,quantity SMALLINT UNSIGNED NOT NULL,price INT UNSIGNED NOT NULL,active TINYINT(1) NOT NULL DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,UNIQUE KEY uq_pack_slot(set_id,slot),UNIQUE KEY uq_pack_quantity(set_id,quantity),CONSTRAINT fk_pack_set FOREIGN KEY(set_id) REFERENCES photo_sets(id) ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE photos(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,event_id INT UNSIGNED NOT NULL,set_id BIGINT UNSIGNED NULL,title VARCHAR(180) NOT NULL,bib_number VARCHAR(30),price INT UNSIGNED NOT NULL DEFAULT 4990,original_path VARCHAR(500) NOT NULL,preview_path VARCHAR(500) NOT NULL,file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,watermark_enabled TINYINT(1) NOT NULL DEFAULT 1,watermark_type ENUM('text','image') NOT NULL DEFAULT 'text',watermark_text VARCHAR(80) NULL,download_enabled TINYINT(1) NOT NULL DEFAULT 1,status ENUM('processing','active','hidden') NOT NULL DEFAULT 'active',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(event_id),INDEX(set_id),INDEX(bib_number),CONSTRAINT fk_photo_event FOREIGN KEY(event_id) REFERENCES events(id),CONSTRAINT fk_photo_set FOREIGN KEY(set_id) REFERENCES photo_sets(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE orders(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,customer_id BIGINT UNSIGNED NULL,customer_name VARCHAR(160) NOT NULL,customer_email VARCHAR(190) NOT NULL,phone VARCHAR(40) NULL,rut VARCHAR(20) NULL,total INT UNSIGNED NOT NULL,status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',payment_provider VARCHAR(50) DEFAULT 'flow',payment_reference VARCHAR(160),download_token CHAR(48) NOT NULL UNIQUE,paid_at DATETIME NULL,download_expires_at DATETIME NULL,confirmation_email_sent_at DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(customer_id),INDEX(customer_email),INDEX(status),INDEX(payment_reference),CONSTRAINT fk_order_customer FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE order_items(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id BIGINT UNSIGNED NOT NULL,photo_id BIGINT UNSIGNED NULL,set_id BIGINT UNSIGNED NULL,item_type ENUM('photo','set','pack') NOT NULL DEFAULT 'photo',item_title VARCHAR(180),selected_photo_ids TEXT NULL,unit_price INT UNSIGNED NOT NULL,CONSTRAINT fk_item_order FOREIGN KEY(order_id) REFERENCES orders(id),CONSTRAINT fk_item_photo FOREIGN KEY(photo_id) REFERENCES photos(id),CONSTRAINT fk_order_item_set FOREIGN KEY(set_id) REFERENCES photo_sets(id),UNIQUE(order_id,photo_id)) ENGINE=InnoDB;
CREATE TABLE homepage_cta(id TINYINT UNSIGNED PRIMARY KEY,event_id INT UNSIGNED NULL,eyebrow VARCHAR(100) NOT NULL DEFAULT 'EVENTO DESTACADO',title VARCHAR(180) NOT NULL,description VARCHAR(500) NULL,button_text VARCHAR(80) NOT NULL DEFAULT 'VER FOTOGRAFÍAS',button_url VARCHAR(255) NOT NULL DEFAULT '#fotos',image_url VARCHAR(500) NULL,active TINYINT(1) NOT NULL DEFAULT 1,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,CONSTRAINT fk_cta_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE SET NULL) ENGINE=InnoDB;
CREATE TABLE payment_settings(id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,provider VARCHAR(40) NOT NULL UNIQUE,active TINYINT(1) NOT NULL DEFAULT 0,environment ENUM('sandbox','production') NOT NULL DEFAULT 'sandbox',api_key VARCHAR(255) NOT NULL,secret_key_encrypted TEXT NOT NULL,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE email_settings(id TINYINT UNSIGNED PRIMARY KEY,active TINYINT(1) NOT NULL DEFAULT 0,host VARCHAR(190) NOT NULL,port SMALLINT UNSIGNED NOT NULL DEFAULT 587,encryption ENUM('tls','ssl','none') NOT NULL DEFAULT 'tls',username VARCHAR(190) NOT NULL,password_encrypted TEXT NOT NULL,from_email VARCHAR(190) NOT NULL,from_name VARCHAR(160) NOT NULL DEFAULT 'Ultra Media Digital',reply_to VARCHAR(190) NOT NULL,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
CREATE TABLE homepage_hero(id TINYINT UNSIGNED PRIMARY KEY,eyebrow VARCHAR(120) NOT NULL,title VARCHAR(180) NOT NULL,highlight VARCHAR(120) NOT NULL,description VARCHAR(500) NULL,search_placeholder VARCHAR(160) NOT NULL,button_text VARCHAR(80) NOT NULL,background_url VARCHAR(500) NOT NULL,background_position VARCHAR(40) NOT NULL DEFAULT 'center center',overlay_opacity TINYINT UNSIGNED NOT NULL DEFAULT 75,trust_one VARCHAR(100) NULL,trust_two VARCHAR(100) NULL,trust_three VARCHAR(100) NULL,updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB;
INSERT INTO homepage_hero(id,eyebrow,title,highlight,description,search_placeholder,button_text,background_url,background_position,overlay_opacity,trust_one,trust_two,trust_three) VALUES(1,'TU ESFUERZO. TU MOMENTO.','ENCUENTRA TU','MEJOR FOTO.','Compra una fotografía específica o descarga el set completo de tu participación.','Número de competidor o evento…','BUSCAR MIS FOTOS','https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1900&q=90','center center',75,'▧ Calidad profesional','↓ Foto o set completo','◇ Pago seguro Flow');
INSERT INTO roles(name,slug,permissions,is_system) VALUES
('Administrador','administrador',JSON_ARRAY('dashboard.view','photos.manage','orders.manage','users.manage','roles.manage'),1),
('Editor fotográfico','editor-fotografico',JSON_ARRAY('dashboard.view','photos.manage'),1),
('Ventas','ventas',JSON_ARRAY('dashboard.view','orders.manage'),1);
INSERT INTO events(name,slug,sport,event_date,location) VALUES
('Trail Volcán Antuco','trail-volcan-antuco','Running','2026-08-02','Antuco'),
('Liga Urbana','liga-urbana','Fútbol','2026-07-26','Concepción'),
('Desafío Cordillerano','desafio-cordillerano','Ciclismo','2026-07-19','Biobío');
INSERT INTO photo_sets(event_id,name,bib_number,individual_enabled,set_enabled,set_price) VALUES
(1,'Set Trail · Competidor 184','184',1,1,19990),(2,'Set Liga Urbana · Jornada Final',NULL,1,1,24990),(3,'Set Desafío Cordillerano',NULL,1,1,21990);
INSERT INTO photos(event_id,title,bib_number,price,original_path,preview_path,file_size) VALUES
(1,'Sprint en la cumbre','184',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=900&q=85',5242880),
(1,'Equipo en ruta','214',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1540539234-c14a20fb7c7b?auto=format&fit=crop&w=900&q=85',4980736),
(2,'Charla previa','81',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1526232761682-d26e03ac148e?auto=format&fit=crop&w=900&q=85',5767168),
(3,'Ascenso final','302',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1541625602330-2277a4c46182?auto=format&fit=crop&w=900&q=85',6291456),
(1,'Meta maratón','415',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?auto=format&fit=crop&w=900&q=85',5120000),
(2,'Control del balón','128',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=900&q=85',6100000),
(3,'Curva técnica','219',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1511994298241-608e28f14fde?auto=format&fit=crop&w=900&q=85',5900000),
(1,'Corrida nocturna','340',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1517963879433-6ad2b056d712?auto=format&fit=crop&w=900&q=85',4800000),
(2,'Básquet urbano','96',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=900&q=85',5600000),
(3,'Pelotón compacto','511',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1486218119243-13883505764c?auto=format&fit=crop&w=900&q=85',6300000),
(1,'Último kilómetro','73',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1592656094267-764a45160876?auto=format&fit=crop&w=900&q=85',5100000),
(2,'Celebración final','57',4990,'assets/descarga-demo.txt','https://images.unsplash.com/photo-1534158914592-062992fbe900?auto=format&fit=crop&w=900&q=85',5450000);
UPDATE photos SET set_id=1 WHERE event_id=1;
UPDATE photos SET set_id=2 WHERE event_id=2;
UPDATE photos SET set_id=3 WHERE event_id=3;
INSERT INTO orders(customer_name,customer_email,total,status,download_token,paid_at) VALUES
('Camila Soto','camila@example.com',9980,'paid','demo00000000000000000000000000000000000000000001',DATE_SUB(NOW(),INTERVAL 6 DAY)),
('Matías Rojas','matias@example.com',4990,'paid','demo00000000000000000000000000000000000000000002',DATE_SUB(NOW(),INTERVAL 3 DAY)),
('Valentina Díaz','vale@example.com',14970,'paid','demo00000000000000000000000000000000000000000003',NOW());
INSERT INTO order_items(order_id,photo_id,unit_price) VALUES(1,1,4990),(1,2,4990),(2,3,4990),(3,4,4990),(3,5,4990),(3,6,4990);
INSERT INTO homepage_cta(id,event_id,eyebrow,title,description,button_text,button_url,image_url,active) VALUES(1,1,'EVENTO DESTACADO','TRAIL VOLCÁN ANTUCO 2026','Ya están disponibles las fotografías oficiales del evento.','VER FOTOGRAFÍAS','#fotos','https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1800&q=88',1);

