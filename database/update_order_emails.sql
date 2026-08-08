-- Correo de confirmación y expiración real de enlaces de descarga.
ALTER TABLE orders
 ADD COLUMN download_expires_at DATETIME NULL AFTER paid_at,
 ADD COLUMN confirmation_email_sent_at DATETIME NULL AFTER download_expires_at;

UPDATE orders SET download_expires_at=DATE_ADD(paid_at,INTERVAL 15 DAY)
WHERE status='paid' AND paid_at IS NOT NULL AND download_expires_at IS NULL;
