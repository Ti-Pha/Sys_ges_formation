<?php
/**
 * Script d'initialisation du système d'authentification
 * À exécuter si la table users n'existe pas ou si les données sont incorrectes
 * 
 * Accédez à: http://localhost/Sys_ges_formation/setup.php
 */

include 'config.php';

// ========== ADMIN ONLY ==========
// Pour sécurité, vérifiez que vous êtes admin
// (En production, utiliser une clé secrète ou IP whitelist)

$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_key']) && $_POST['setup_key'] === 'setup_2025_admin') {
    try {
        // Étape 1: Créer la table users si elle n'existe pas
        $sql_create_table = "
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user') DEFAULT 'user',
                nom VARCHAR(100),
                prenom VARCHAR(100),
                participant_id INT,
                actif BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX(username),
                INDEX(email),
                INDEX(role),
                FOREIGN KEY(participant_id) REFERENCES participants(id) ON DELETE SET NULL
            )
        ";
        
        $pdo->exec($sql_create_table);
        $message .= "✓ Table users créée/vérifiée avec colonne participant_id\n";
        
        // Étape 2: Générer les hashes corrects
        $admin_hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 10]);
        $user_hash = password_hash('user123', PASSWORD_BCRYPT, ['cost' => 10]);
        
        // Étape 3: Supprimer les anciens utilisateurs (optionnel)
        $pdo->exec("DELETE FROM users WHERE username IN ('admin', 'user')");
        $message .= "✓ Anciens utilisateurs supprimés\n";
        
        // Étape 4: Insérer les nouveaux utilisateurs avec les bons hashes
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, nom, prenom, participant_id, actif)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Admin (pas de participant_id)
        $stmt->execute([
            'admin',
            'admin@formation.com',
            $admin_hash,
            'admin',
            'Admin',
            'Système',
            NULL,
            true
        ]);
        $message .= "✓ Utilisateur ADMIN créé\n";
        
        // User lié à Alice Dupont (participant_id = 1)
        $stmt->execute([
            'user',
            'alice.dupont@email.com',
            $user_hash,
            'user',
            'Dupont',
            'Alice',
            1,
            true
        ]);
        $message .= "✓ Utilisateur USER (Alice Dupont) créé et lié au participant\n";
        
        // Étape 5: Tester la connexion
        $test_stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $test_stmt->execute(['admin']);
        $test_user = $test_stmt->fetch();
        
        if ($test_user && password_verify('admin123', $test_user['password'])) {
            $message .= "✓ Vérification: Mot de passe correct\n";
            $success = true;
        } else {
            $message .= "✗ Erreur: Vérification du mot de passe échouée\n";
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
    <title>Setup - Authentification</title>
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
        <h1 class="mb-4">Initialisation du Système d'Authentification</h1>
        
        <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <h4>✓ Setup Réussi!</h4>
            <p class="mb-3">Les utilisateurs ont été créés avec succès.</p>
            <hr>
            <h5>Identifiants de connexion:</h5>
            <table class="table table-sm">
                <tr>
                    <td><strong>Admin:</strong></td>
                    <td>Login: <code>admin</code> | Mot de passe: <code>admin123</code></td>
                </tr>
                <tr>
                    <td><strong>Participant (Alice Dupont):</strong></td>
                    <td>Login: <code>user</code> | Mot de passe: <code>user123</code></td>
                </tr>
            </table>
            <hr>
            <a href="/Sys_ges_formation/login.php" class="btn btn-primary">→ Aller à la connexion</a>
        </div>
        <?php elseif ($message): ?>
        <div class="alert alert-danger" role="alert">
            <h4>Erreur lors du setup</h4>
            <pre><?php echo htmlspecialchars($message); ?></pre>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="mt-4">
            <div class="alert alert-info">
                <p><strong>Cette page initialise le système d'authentification:</strong></p>
                <ul class="mb-0">
                    <li>Crée la table <code>users</code></li>
                    <li>Insère les utilisateurs ADMIN et USER</li>
                    <li>Génère les hashes de mots de passe corrects</li>
                    <li>Teste la connexion</li>
                </ul>
            </div>
            
            <div class="mb-3">
                <label for="setup_key" class="form-label">Clé de Setup</label>
                <input type="password" class="form-control" id="setup_key" name="setup_key" 
                       placeholder="Entrez la clé de setup" required>
                <small class="text-muted">Clé: <code>setup_2025_admin</code></small>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg w-100">Initialiser</button>
        </form>
        
        <hr class="my-4">
        
        <h5>Instructions</h5>
        <ol>
            <li>Entrez la clé de setup: <code>setup_2025_admin</code></li>
            <li>Cliquez sur "Initialiser"</li>
            <li>Attendez le message de succès</li>
            <li>Allez sur <a href="/Sys_ges_formation/login.php">/login.php</a></li>
            <li>Connectez-vous comme participant avec <code>user</code> / <code>user123</code></li>
            <li>Ou comme admin avec <code>admin</code> / <code>admin123</code></li>
        </ol>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
