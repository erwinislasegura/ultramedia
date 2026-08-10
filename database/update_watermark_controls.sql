SET @watermark_scale_exists = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'photos'
    AND COLUMN_NAME = 'watermark_scale'
);

SET @watermark_scale_sql = IF(
  @watermark_scale_exists = 0,
  'ALTER TABLE photos ADD COLUMN watermark_scale TINYINT UNSIGNED NOT NULL DEFAULT 90 AFTER watermark_text',
  'SELECT ''watermark_scale ya existe'' AS resultado'
);

PREPARE watermark_scale_statement FROM @watermark_scale_sql;
EXECUTE watermark_scale_statement;
DEALLOCATE PREPARE watermark_scale_statement;

SET @watermark_opacity_exists = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'photos'
    AND COLUMN_NAME = 'watermark_opacity'
);

SET @watermark_opacity_sql = IF(
  @watermark_opacity_exists = 0,
  'ALTER TABLE photos ADD COLUMN watermark_opacity TINYINT UNSIGNED NOT NULL DEFAULT 65 AFTER watermark_scale',
  'SELECT ''watermark_opacity ya existe'' AS resultado'
);

PREPARE watermark_opacity_statement FROM @watermark_opacity_sql;
EXECUTE watermark_opacity_statement;
DEALLOCATE PREPARE watermark_opacity_statement;
