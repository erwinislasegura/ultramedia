ALTER TABLE photo_sets
 ADD COLUMN cover_photo_id BIGINT UNSIGNED NULL AFTER bib_number,
 ADD INDEX idx_set_cover_photo(cover_photo_id),
 ADD CONSTRAINT fk_set_cover_photo FOREIGN KEY(cover_photo_id) REFERENCES photos(id) ON DELETE SET NULL;

UPDATE photo_sets ps
SET cover_photo_id=(SELECT MIN(p.id) FROM photos p WHERE p.set_id=ps.id);
