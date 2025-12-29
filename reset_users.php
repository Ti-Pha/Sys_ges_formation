<?php
/**
 * Page pour réinitialiser les utilisateurs avec les bons hashes de mots de passe
 * URL: http://localhost/Sys_ges_formation/reset_users.php
 */

include 'config.php';

$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_key']) && $_POST['reset_key'] === 'reset_2025_password') {
    try {
        // Générer les bons hashes
        $admin_hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 10]);
        $user_hash = password_hash('user123', PASSWORD_BCRYPT, ['cost' => 10]);
        
        // Supprimer les anciens utilisateurs
        $pdo->exec("DELETE FROM users");
        $message .= "✓ Anciens utilisateurs supprimés\n";
        
        // Insérer les nouveaux utilisateurs avec les bons hashes
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, role, nom, prenom, participant_id, actif)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Admin
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
        $message .= "✓ Utilisateur ADMIN créé avec le mot de passe: admin123\n";
        
        // User lié à Alice Dupont
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
        $message .= "✓ Utilisateur USER (Alice) créé avec le mot de passe: user123\n";
        
        // Tester la connexion
        $test_stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $test_stmt->execute(['admin']);
        $test_user = $test_stmt->fetch();
        
        if ($test_user && password_verify('admin123', $test_user['password'])) {
            $message .= "✓ Vérification: Mot de passe CORRECT (password_verify OK)\n";
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
    <title>Réinitialiser Utilisateurs</title>
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
        <h1 class="mb-4">🔑 Réinitialiser les Utilisateurs</h1>
        
        <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <h4>✓ Réinitialisation Réussie!</h4>
            <pre><?php echo htmlspecialchars($message); ?></pre>
            <hr>
            <h5>Identifiants de connexion:</h5>
            <table class="table table-sm">
                <tr>
                    <td><strong>Admin:</strong></td>
                    <td>Login: <code>admin</code> | Mot de passe: <code>admin123</code></td>
                </tr>
                <tr>
                    <td><strong>Participant (Alice):</strong></td>
                    <td>Login: <code>user</code> | Mot de passe: <code>user123</code></td>
                </tr>
            </table>
            <hr>
            <a href="/Sys_ges_formation/login.php" class="btn btn-primary">🔐 Aller à la connexion</a>
        </div>
        <?php elseif ($message): ?>
        <div class="alert alert-danger" role="alert">
            <h4>Erreur lors de la réinitialisation</h4>
            <pre><?php echo htmlspecialchars($message); ?></pre>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="mt-4">
            <div class="alert alert-warning">
                <p><strong>⚠️ Cette page réinitialise les utilisateurs:</strong></p>
                <ul class="mb-0">
                    <li>Supprime tous les utilisateurs existants</li>
                    <li>Crée l'admin avec <code>admin123</code></li>
                    <li>Crée l'utilisateur participant (Alice) avec <code>user123</code></li>
                    <li>Vérifie que les mots de passe sont corrects</li>
                </ul>
            </div>
            
            <div class="mb-3">
                <label for="reset_key" class="form-label">Clé de Réinitialisation</label>
                <input type="password" class="form-control" id="reset_key" name="reset_key" 
                       placeholder="Entrez la clé" required>
                <small class="text-muted">Clé: <code>reset_2025_password</code></small>
            </div>
            
            <button type="submit" class="btn btn-warning btn-lg w-100">🔄 Réinitialiser les Utilisateurs</button>
        </form>
        
        <hr class="my-4">
        
        <h5>📋 Instructions</h5>
        <ol>
            <li>Assurez-vous que la base de données a été importée correctement</li>
            <li>Entrez la clé: <code>reset_2025_password</code></li>
            <li>Cliquez sur "Réinitialiser les Utilisateurs"</li>
            <li>Attendez le message de succès</li>
            <li>Allez sur <a href="/Sys_ges_formation/login.php">login.php</a></li>
            <li>Connectez-vous avec <code>admin</code> / <code>admin123</code></li>
        </ol>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
