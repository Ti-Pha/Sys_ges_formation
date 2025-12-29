<?php
/**
 * Script pour générer les hashes de mots de passe corrects
 * À exécuter UNE FOIS pour obtenir les bons hashes
 */

// Mots de passe à hasher
$passwords = [
    'admin' => 'admin123',
    'user' => 'user123'
];

echo "=== Hashes de mots de passe générés ===\n\n";

foreach ($passwords as $username => $password) {
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    echo "Utilisateur: $username\n";
    echo "Mot de passe: $password\n";
    echo "Hash: $hash\n";
    echo "---\n";
    
    // Vérifier que le hash fonctionne
    if (password_verify($password, $hash)) {
        echo "✓ Vérification: OK\n";
    } else {
        echo "✗ Vérification: ERREUR\n";
    }
    echo "\n";
}

echo "\n=== Utilisez ces hashes dans la migration SQL ===\n";
?>
