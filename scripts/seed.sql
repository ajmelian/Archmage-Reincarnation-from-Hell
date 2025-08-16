-- Usuarios base (ajusta a tu esquema real)
CREATE TABLE IF NOT EXISTS users (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) UNIQUE,
  password_hash VARCHAR(255),
  role VARCHAR(32) DEFAULT 'user',
  email_verified_at INT NULL,
  created_at INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (email, password_hash, role, created_at)
VALUES ('admin@example.com', '$2y$10$0Vti5sZk1nS1x8j3F0gW/OoJ5W7bCzM85v3WcS5rQhO4a5r7UoXl6', 'admin', UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE email=email;

-- Realms base (mínimo para batallas/guerras)
CREATE TABLE IF NOT EXISTS realms (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT NULL,
  name VARCHAR(128) DEFAULT 'Realm',
  net_power INT DEFAULT 10000,
  gold INT DEFAULT 10000,
  land INT DEFAULT 100,
  turns INT DEFAULT 50,
  mana INT DEFAULT 100,
  alliance_id BIGINT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO realms (user_id, name, net_power, gold, land, turns, mana)
VALUES (1, 'Adminia', 20000, 50000, 200, 100, 500)
ON DUPLICATE KEY UPDATE name=name;
