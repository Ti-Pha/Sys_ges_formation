<?php
/**
 * Script de migration pour ajouter la colonne participant_id à la table users
 * URL: http://localhost/Sys_ges_formation/migrate.php
 */

include 'config.php';

$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['migrate_key']) && $_POST['migrate_key'] === 'migrate_2025') {
    try {
        // Vérifier si la colonne participant_id existe déjà
        $check = $pdo->query("
            SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = 'gestion_formation' 
            AND TABLE_NAME = 'users' 
            AND COLUMN_NAME = 'participant_id'
        ");
        
        if ($check->rowCount() === 0) {
            // Colonne n'existe pas, l'ajouter
            $pdo->exec("
                ALTER TABLE users 
                ADD COLUMN participant_id INT NULL AFTER prenom
            ");
            $message .= "✓ Colonne participant_id ajoutée à la table users\n";
            
            // Ajouter la clé étrangère si elle n'existe pas
            $fk_check = $pdo->query("
                SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'users' 
                AND COLUMN_NAME = 'participant_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if ($fk_check->rowCount() === 0) {
                $pdo->exec("
                    ALTER TABLE users 
                    ADD FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE SET NULL
                ");
                $message .= "✓ Clé étrangère participant_id ajoutée\n";
            }
            
            $success = true;
        } else {
            $message = "✓ Colonne participant_id existe déjà\n";
            $success = true;
        }
        
    } catch (Exception $e) {
        $message = "✗ Erreur: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration - Base de Données</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Sys_ges_formation/assets/css/muted.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f7f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #495057;
        }
        .container {
            max-width: 600px;
            background: white;
            border-radius: 10px;
            padding: 32px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">⚙️ Migration Base de Données</h1>
        
        <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <h4>✓ Migration Réussie!</h4>
            <pre><?php echo htmlspecialchars($message); ?></pre>
            <hr>
            <p>La base de données a été mise à jour. Vous pouvez maintenant:</p>
            <ul>
                <li>Exécuter <a href="/Sys_ges_formation/setup.php">setup.php</a> pour initialiser les utilisateurs</li>
                <li>Aller à <a href="/Sys_ges_formation/login.php">login.php</a> pour vous connecter</li>
            </ul>
        </div>
        <?php elseif ($message): ?>
        <div class="alert alert-danger" role="alert">
            <h4>Erreur lors de la migration</h4>
            <pre><?php echo htmlspecialchars($message); ?></pre>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="mt-4">
            <div class="alert alert-info">
                <p><strong>ℹ️ Cette migration ajoute la colonne manquante:</strong></p>
                <ul class="mb-0">
                    <li>Ajoute la colonne <code>participant_id</code> à la table users</li>
                    <li>Ajoute une clé étrangère vers la table participants</li>
                    <li>Permet de lier chaque utilisateur à un participant</li>
                </ul>
            </div>
            
            <div class="mb-3">
                <label for="migrate_key" class="form-label">Clé de Migration</label>
                <input type="password" class="form-control" id="migrate_key" name="migrate_key" 
                       placeholder="Entrez la clé de migration" required>
                <small class="text-muted">Clé: <code>migrate_2025</code></small>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg w-100">Exécuter Migration</button>
        </form>
        
        <hr class="my-4">
        
        <h5>📋 Instructions</h5>
        <ol>
            <li>Entrez la clé: <code>migrate_2025</code></li>
            <li>Cliquez sur "Exécuter Migration"</li>
            <li>Attendez le message de succès</li>
            <li>Allez sur <a href="/Sys_ges_formation/setup.php">setup.php</a></li>
            <li>Exécutez le setup pour créer les utilisateurs</li>
            <li>Connectez-vous avec <code>user</code> / <code>user123</code></li>
        </ol>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
