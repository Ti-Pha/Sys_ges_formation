-- ===================================================
-- SCRIPT DE CRÉATION DE LA BASE DE DONNÉES
-- Système de Gestion de Formation Professionnelle
-- ===================================================

-- Supprimer la base si elle existe
DROP DATABASE IF EXISTS gestion_formation;
CREATE DATABASE gestion_formation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_formation;

-- ===================================================
-- TABLE 1: FORMATIONS
-- ===================================================
CREATE TABLE formations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    instructeur VARCHAR(100) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    duree_heures INT NOT NULL,
    nombre_participants INT DEFAULT 0,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    statut ENUM('planifiée', 'en_cours', 'terminée', 'annulée') DEFAULT 'planifiée',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(statut),
    INDEX(date_debut)
);

-- ===================================================
-- TABLE 2: PARTICIPANTS
-- ===================================================
CREATE TABLE participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telephone VARCHAR(20),
    date_inscription DATE NOT NULL,
    statut ENUM('inscrit', 'en_cours', 'terminé', 'abandonné') DEFAULT 'inscrit',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(email),
    INDEX(statut)
);

-- ===================================================
-- TABLE 0: UTILISATEURS (AUTHENTIFICATION)
-- ===================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    nom VARCHAR(100),
    prenom VARCHAR(100),
    participant_id INT,
    approved BOOLEAN DEFAULT FALSE,
    rejection_reason TEXT,
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(username),
    INDEX(email),
    INDEX(role),
    INDEX(approved),
    FOREIGN KEY(participant_id) REFERENCES participants(id) ON DELETE SET NULL
);

-- ===================================================
-- TABLE 3: INSCRIPTIONS (Relation Formation-Participant)
-- ===================================================
CREATE TABLE inscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    participant_id INT NOT NULL,
    formation_id INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('inscrit', 'actif', 'complété', 'abandonne') DEFAULT 'inscrit',
    FOREIGN KEY(participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    FOREIGN KEY(formation_id) REFERENCES formations(id) ON DELETE CASCADE,
    UNIQUE KEY(participant_id, formation_id),
    INDEX(formation_id),
    INDEX(statut)
);

-- ===================================================
-- TABLE 4: ÉVALUATIONS
-- ===================================================
CREATE TABLE evaluations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inscription_id INT NOT NULL,
    note_devoir DECIMAL(5, 2),
    note_test DECIMAL(5, 2),
    note_participation DECIMAL(5, 2),
    note_finale DECIMAL(5, 2) GENERATED ALWAYS AS (
        (COALESCE(note_devoir, 0) * 0.3 + 
         COALESCE(note_test, 0) * 0.5 + 
         COALESCE(note_participation, 0) * 0.2)
    ) STORED,
    resultat ENUM('réussi', 'échoué', 'en_attente') DEFAULT 'en_attente',
    certificat_delivre BOOLEAN DEFAULT FALSE,
    date_evaluation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(inscription_id) REFERENCES inscriptions(id) ON DELETE CASCADE,
    INDEX(resultat),
    INDEX(inscription_id)
);

-- ===================================================
-- INSERTION DE DONNÉES DE TEST
-- ===================================================

INSERT INTO formations (titre, description, instructeur, date_debut, date_fin, duree_heures, prix_unitaire, statut) VALUES
('PHP Avancé', 'Formation complète sur PHP orienté objet', 'Jean Dupont', '2025-01-15', '2025-02-15', 40, 500.00, 'planifiée'),
('MySQL & Bases de Données', 'Maîtriser les bases de données relationnelles', 'Marie Martin', '2025-02-01', '2025-03-01', 35, 450.00, 'planifiée'),
('Web Development Fullstack', 'HTML, CSS, JavaScript, PHP et MySQL', 'Pierre Bernard', '2025-03-10', '2025-05-10', 80, 800.00, 'planifiée');

INSERT INTO participants (nom, prenom, email, telephone, date_inscription, statut) VALUES
('Dupont', 'Alice', 'alice.dupont@email.com', '0612345678', '2025-01-01', 'inscrit'),
('Martin', 'Bob', 'bob.martin@email.com', '0687654321', '2025-01-02', 'inscrit'),
('Bernard', 'Charlie', 'charlie.bernard@email.com', '0698765432', '2025-01-03', 'inscrit'),
('Durand', 'Diana', 'diana.durand@email.com', '0612348765', '2025-01-04', 'inscrit');

INSERT INTO inscriptions (participant_id, formation_id, statut) VALUES
(1, 1, 'inscrit'),
(1, 2, 'inscrit'),
(2, 1, 'inscrit'),
(3, 2, 'inscrit'),
(4, 3, 'inscrit');

INSERT INTO evaluations (inscription_id, note_devoir, note_test, note_participation) VALUES
(1, 15.50, 16.00, 17.00),
(2, 14.00, 13.50, 15.00),
(3, 16.50, 17.00, 18.50),
(4, 12.00, 11.50, 13.00);

-- ===================================================
-- INSERTION DE DONNÉES DE TEST - UTILISATEURS
-- ===================================================
-- Admin: admin / admin123
-- User (Alice Dupont): user / user123

INSERT INTO users (username, email, password, role, nom, prenom, participant_id, approved, actif) VALUES
('admin', 'admin@formation.com', '$2y$10$YBgPxPrXa4n1B8HkX3wGIeQpxjLxVcNvLmMBQGSCNW0FBMKZLd.I2', 'admin', 'Admin', 'Système', NULL, TRUE, TRUE),
('user', 'alice.dupont@email.com', '$2y$10$lMFHM9AJ0KQ8lj3bH0nCfOc3vf1I4Q9KqV7wC0Q5oD3YqK2xE9mB6', 'user', 'Dupont', 'Alice', 1, TRUE, TRUE);

COMMIT;
