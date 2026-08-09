CREATE TABLE IF NOT EXISTS photo_pack_options(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 set_id BIGINT UNSIGNED NOT NULL,
 slot TINYINT UNSIGNED NOT NULL,
 quantity SMALLINT UNSIGNED NOT NULL,
 price INT UNSIGNED NOT NULL,
 active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_pack_slot(set_id,slot),
 UNIQUE KEY uq_pack_quantity(set_id,quantity),
 CONSTRAINT fk_pack_set FOREIGN KEY(set_id) REFERENCES photo_sets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Conserva como Pack 1 la configuración única creada por la versión anterior.
INSERT IGNORE INTO photo_pack_options(set_id,slot,quantity,price,active)
SELECT id,1,pack_quantity,pack_price,pack_enabled FROM photo_sets;
