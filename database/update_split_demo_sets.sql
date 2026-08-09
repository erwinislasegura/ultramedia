-- Convierte los tres lotes de demostración antiguos en un set independiente
-- por fotografía. No afecta sets creados desde el panel ni fotografías reales.
DELIMITER $$

DROP PROCEDURE IF EXISTS split_ultra_demo_sets$$
CREATE PROCEDURE split_ultra_demo_sets()
BEGIN
    DECLARE finished INT DEFAULT 0;
    DECLARE photo_id BIGINT UNSIGNED;
    DECLARE source_set_id BIGINT UNSIGNED;
    DECLARE event_id INT UNSIGNED;
    DECLARE photo_title VARCHAR(180);
    DECLARE photo_bib VARCHAR(30);
    DECLARE individual_enabled TINYINT;
    DECLARE set_enabled TINYINT;
    DECLARE pack_enabled TINYINT;
    DECLARE pack_quantity SMALLINT UNSIGNED;
    DECLARE pack_price INT UNSIGNED;
    DECLARE set_price INT UNSIGNED;
    DECLARE new_set_id BIGINT UNSIGNED;

    DECLARE demo_photos CURSOR FOR
        SELECT p.id,p.set_id,p.event_id,p.title,p.bib_number,
               ps.individual_enabled,ps.set_enabled,ps.pack_enabled,
               ps.pack_quantity,ps.pack_price,ps.set_price
        FROM photos p
        JOIN photo_sets ps ON ps.id=p.set_id
        WHERE ps.name IN (
            'Set Trail · Competidor 184',
            'Set Liga Urbana · Jornada Final',
            'Set Desafío Cordillerano'
        );
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET finished=1;

    OPEN demo_photos;
    photo_loop: LOOP
        FETCH demo_photos INTO photo_id,source_set_id,event_id,photo_title,photo_bib,
            individual_enabled,set_enabled,pack_enabled,pack_quantity,pack_price,set_price;
        IF finished=1 THEN LEAVE photo_loop; END IF;

        INSERT INTO photo_sets(
            event_id,name,bib_number,individual_enabled,set_enabled,
            pack_enabled,pack_quantity,pack_price,set_price,status
        ) VALUES (
            event_id,CONCAT('Set · ',photo_title),photo_bib,individual_enabled,set_enabled,
            pack_enabled,pack_quantity,pack_price,set_price,'active'
        );
        SET new_set_id=LAST_INSERT_ID();

        INSERT IGNORE INTO photo_pack_options(set_id,slot,quantity,price,active)
        SELECT new_set_id,slot,quantity,price,active
        FROM photo_pack_options WHERE set_id=source_set_id;

        UPDATE photos SET set_id=new_set_id WHERE id=photo_id;
    END LOOP;
    CLOSE demo_photos;

    UPDATE photo_sets ps
    SET ps.status='hidden'
    WHERE ps.name IN (
        'Set Trail · Competidor 184',
        'Set Liga Urbana · Jornada Final',
        'Set Desafío Cordillerano'
    ) AND NOT EXISTS(SELECT 1 FROM photos p WHERE p.set_id=ps.id);
END$$

CALL split_ultra_demo_sets()$$
DROP PROCEDURE split_ultra_demo_sets$$
DELIMITER ;
