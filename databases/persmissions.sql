CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);
INSERT INTO permissions (name) VALUES
('view_users'),
('create_user'),
('edit_user'),
('delete_user');
