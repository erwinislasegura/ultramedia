-- Módulo de eventos: portada pública y selección explícita de sets.
CREATE TABLE IF NOT EXISTS event_catalog(
 event_id INT UNSIGNED PRIMARY KEY,
 cover_path VARCHAR(500) NULL,
 description VARCHAR(700) NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_event_catalog_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS event_sets(
 event_id INT UNSIGNED NOT NULL,
 set_id BIGINT UNSIGNED NOT NULL,
 position SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(event_id,set_id),
 UNIQUE KEY uq_event_set(set_id),
 INDEX idx_event_sets_position(event_id,position),
 CONSTRAINT fk_event_sets_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE CASCADE,
 CONSTRAINT fk_event_sets_set FOREIGN KEY(set_id) REFERENCES photo_sets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Conserva las asociaciones que ya existían antes de instalar el módulo.
INSERT IGNORE INTO event_sets(event_id,set_id,position)
SELECT event_id,id,id FROM photo_sets;
