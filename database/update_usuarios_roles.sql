-- Actualización: módulo de usuarios y roles
-- Ejecutar una sola vez sobre una base instalada.
SET NAMES utf8mb4;
CREATE TABLE IF NOT EXISTS roles(
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(80) NOT NULL,
 slug VARCHAR(90) NOT NULL UNIQUE,
 permissions JSON NOT NULL,
 is_system TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS users(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 role_id INT UNSIGNED NOT NULL,
 name VARCHAR(160) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 last_login_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX(role_id),INDEX(status),
 CONSTRAINT fk_user_role FOREIGN KEY(role_id) REFERENCES roles(id)
) ENGINE=InnoDB;
INSERT INTO roles(name,slug,permissions,is_system) VALUES
('Administrador','administrador',JSON_ARRAY('dashboard.view','photos.manage','orders.manage','users.manage','roles.manage'),1),
('Editor fotográfico','editor-fotografico',JSON_ARRAY('dashboard.view','photos.manage'),1),
('Ventas','ventas',JSON_ARRAY('dashboard.view','orders.manage'),1)
ON DUPLICATE KEY UPDATE name=VALUES(name),permissions=VALUES(permissions);
