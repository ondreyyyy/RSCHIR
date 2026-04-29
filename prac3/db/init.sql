-- Create schema for game profiles and auth (MariaDB)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  -- Store bcrypt hashes (Apache understands $2y$)
  password VARCHAR(255) NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nickname VARCHAR(64) NOT NULL,
  game VARCHAR(64) NOT NULL,
  level INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (username, password, active) VALUES
  -- username: admin, password: admin123 (bcrypt)
  ('admin', '$2y$10$v6X8oS8lO4z3xJt5yX0Q7u7pPZQe1z8h0vV9g5xkH2l2m0t7Yk6cW', 1)
  ON DUPLICATE KEY UPDATE username=VALUES(username);

INSERT INTO profiles (nickname, game, level) VALUES
  ('PlayerOne', 'Skyrim', 42),
  ('ProGamer', 'StarCraft', 7),
  ('NoobMaster', 'Dota2', 3);
