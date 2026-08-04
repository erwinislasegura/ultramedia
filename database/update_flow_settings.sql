-- Configuración administrable de la pasarela Flow.
CREATE TABLE IF NOT EXISTS payment_settings(
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider VARCHAR(40) NOT NULL UNIQUE,
  active TINYINT(1) NOT NULL DEFAULT 0,
  environment ENUM('sandbox','production') NOT NULL DEFAULT 'sandbox',
  api_key VARCHAR(255) NOT NULL,
  secret_key_encrypted TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
