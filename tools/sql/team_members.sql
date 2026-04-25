CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    role VARCHAR(50) NOT NULL DEFAULT 'Support',
    status ENUM('active','pending') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO team_members (name, email, role, status)
VALUES
('3legant Admin', 'admin@3legant.com', 'Owner', 'active'),
('Store Manager', 'manager@3legant.com', 'Manager', 'active'),
('Support Staff', 'support@3legant.com', 'Support', 'pending')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    role = VALUES(role),
    status = VALUES(status);