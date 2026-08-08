-- Tercera modalidad: pack configurable de X fotografías por un precio fijo.
ALTER TABLE photo_sets
 ADD COLUMN pack_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER set_enabled,
 ADD COLUMN pack_quantity SMALLINT UNSIGNED NOT NULL DEFAULT 5 AFTER pack_enabled,
 ADD COLUMN pack_price INT UNSIGNED NOT NULL DEFAULT 14990 AFTER pack_quantity;

ALTER TABLE order_items
 MODIFY COLUMN item_type ENUM('photo','set','pack') NOT NULL DEFAULT 'photo',
 ADD COLUMN selected_photo_ids TEXT NULL AFTER item_title;
