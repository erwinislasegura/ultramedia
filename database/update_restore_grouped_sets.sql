-- Revierte exclusivamente la separación incorrecta de las fotografías demo.
-- Cada carga real realizada desde /admin/fotos permanece como un único set.
START TRANSACTION;

UPDATE photo_sets SET status='active'
WHERE name IN (
 'Set Trail · Competidor 184',
 'Set Liga Urbana · Jornada Final',
 'Set Desafío Cordillerano'
);

UPDATE photos p
JOIN photo_sets ps ON ps.name='Set Trail · Competidor 184'
SET p.set_id=ps.id
WHERE p.original_path='assets/descarga-demo.txt' AND p.event_id=ps.event_id;

UPDATE photos p
JOIN photo_sets ps ON ps.name='Set Liga Urbana · Jornada Final'
SET p.set_id=ps.id
WHERE p.original_path='assets/descarga-demo.txt' AND p.event_id=ps.event_id;

UPDATE photos p
JOIN photo_sets ps ON ps.name='Set Desafío Cordillerano'
SET p.set_id=ps.id
WHERE p.original_path='assets/descarga-demo.txt' AND p.event_id=ps.event_id;

DELETE ps FROM photo_sets ps
LEFT JOIN photos p ON p.set_id=ps.id
LEFT JOIN order_items oi ON oi.set_id=ps.id
WHERE p.id IS NULL AND oi.id IS NULL AND ps.name IN (
 'Set · Sprint en la cumbre','Set · Equipo en ruta','Set · Charla previa',
 'Set · Ascenso final','Set · Meta maratón','Set · Control del balón',
 'Set · Curva técnica','Set · Corrida nocturna','Set · Básquet urbano',
 'Set · Pelotón compacto','Set · Último kilómetro','Set · Celebración final'
);

COMMIT;
