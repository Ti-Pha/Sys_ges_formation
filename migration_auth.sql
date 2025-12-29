-- ===================================================
-- MIGRATION: Ajout du système d'authentification
-- À EXÉCUTER APRÈS avoir créé la base de données
-- ===================================================

USE gestion_formation;

-- Ajouter la table users si elle n'existe pas
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    nom VARCHAR(100),
    prenom VARCHAR(100),
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(username),
    INDEX(email),
    INDEX(role)
);

-- Insérer les utilisateurs de test
-- Admin: admin / admin123
-- User: user / user123
INSERT IGNORE INTO users (username, email, password, role, nom, prenom, actif) VALUES
('admin', 'admin@formation.com', '$2y$10$YBgPxPrXa4n1B8HkX3wGIeQpxjLxVcNvLmMBQGSCNW0FBMKZLd.I2', 'admin', 'Admin', 'Système', TRUE),
('user', 'user@formation.com', '$2y$10$lMFHM9AJ0KQ8lj3bH0nCfOc3vf1I4Q9KqV7wC0Q5oD3YqK2xE9mB6', 'user', 'User', 'Standard', TRUE);

COMMIT;
