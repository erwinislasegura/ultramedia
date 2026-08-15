INSERT INTO roles(name,slug,permissions,is_system) VALUES
('Administrador','administrador',JSON_ARRAY('dashboard.view','events.manage','photos.manage','orders.manage','homepage.manage','payments.manage','email.manage','users.manage','roles.manage'),1),
('Editor fotográfico','editor-fotografico',JSON_ARRAY('dashboard.view','events.manage','photos.manage'),1),
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
UPDATE photo_sets ps SET cover_photo_id=(SELECT MIN(p.id) FROM photos p WHERE p.set_id=ps.id);
INSERT INTO orders(customer_name,customer_email,total,status,download_token,paid_at) VALUES
('Camila Soto','camila@example.com',9980,'paid','demo00000000000000000000000000000000000000000001',DATE_SUB(NOW(),INTERVAL 6 DAY)),
('Matías Rojas','matias@example.com',4990,'paid','demo00000000000000000000000000000000000000000002',DATE_SUB(NOW(),INTERVAL 3 DAY)),
('Valentina Díaz','vale@example.com',14970,'paid','demo00000000000000000000000000000000000000000003',NOW());
INSERT INTO order_items(order_id,photo_id,unit_price) VALUES(1,1,4990),(1,2,4990),(2,3,4990),(3,4,4990),(3,5,4990),(3,6,4990);
INSERT INTO homepage_cta(id,event_id,eyebrow,title,description,button_text,button_url,image_url,active) VALUES(1,1,'EVENTO DESTACADO','TRAIL VOLCÁN ANTUCO 2026','Ya están disponibles las fotografías oficiales del evento.','VER FOTOGRAFÍAS','#fotos','https://images.unsplash.com/photo-1552674605-db6ffd4facb5?auto=format&fit=crop&w=1800&q=88',1);
