<?php
/**
 * Script de migration pour ajouter le système d'approbation des utilisateurs
 * Ajoute les colonnes "approved" et "rejection_reason" à la table users
 * Marque les utilisateurs existants comme approuvés
 */

include 'config.php';

echo "=== Migration: Système d'Approbation des Utilisateurs ===\n\n";

try {
    // Vérifier si la colonne "approved" existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'approved'");
    $approved_exists = $stmt->fetch() !== false;
    
    // Vérifier si la colonne "rejection_reason" existe
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'rejection_reason'");
    $rejection_exists = $stmt->fetch() !== false;
    
    if ($approved_exists && $rejection_exists) {
        echo "✓ Les colonnes d'approbation existent déjà. Migration non nécessaire.\n";
        exit;
    }
    
    // Ajouter la colonne "approved" si elle n'existe pas
    if (!$approved_exists) {
        echo "Ajout de la colonne 'approved'...\n";
        $pdo->exec('ALTER TABLE users ADD COLUMN approved BOOLEAN DEFAULT FALSE AFTER participant_id');
        echo "✓ Colonne 'approved' ajoutée\n";
    }
    
    // Ajouter la colonne "rejection_reason" si elle n'existe pas
    if (!$rejection_exists) {
        echo "Ajout de la colonne 'rejection_reason'...\n";
        $pdo->exec('ALTER TABLE users ADD COLUMN rejection_reason TEXT NULL AFTER approved');
        echo "✓ Colonne 'rejection_reason' ajoutée\n";
    }
    
    // Marquer tous les utilisateurs existants comme approuvés
    echo "\nMise à jour des utilisateurs existants...\n";
    $stmt = $pdo->prepare('UPDATE users SET approved = TRUE WHERE approved = FALSE');
    $stmt->execute();
    $affected = $stmt->rowCount();
    echo "✓ {$affected} utilisateur(s) marqué(s) comme approuvé(s)\n";
    
    // Ajouter un index sur la colonne "approved" pour les performances
    echo "\nAjout d'un index sur la colonne 'approved'...\n";
    try {
        $pdo->exec('ALTER TABLE users ADD INDEX(approved)');
        echo "✓ Index créé\n";
    } catch (Exception $e) {
        echo "ℹ️ Index existant (ou non nécessaire)\n";
    }
    
    echo "\n✓✓✓ Migration terminée avec succès! ✓✓✓\n";
    echo "\nProchaines étapes:\n";
    echo "1. Allez sur: http://localhost/Sys_ges_formation/login.php\n";
    echo "2. Connectez-vous avec admin/admin (ou votre compte existant)\n";
    echo "3. Les nouveaux utilisateurs devront maintenant être approuvés par l'admin\n";
    
} catch (Exception $e) {
    echo "✗ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
