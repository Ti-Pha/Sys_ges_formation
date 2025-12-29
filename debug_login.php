<?php
/**
 * Page de débogage pour tester la connexion
 * URL: http://localhost/Sys_ges_formation/debug_login.php
 */

include 'config.php';

$debug_info = '';
$test_username = 'admin';
$test_password = 'admin123';

try {
    // Vérifier la connexion à la base de données
    $debug_info .= "✓ Connexion à la base de données: OK\n\n";
    
    // Vérifier si la table users existe
    $check_table = $pdo->query("
        SELECT COUNT(*) as count 
        FROM information_schema.tables 
        WHERE table_schema = 'gestion_formation' 
        AND table_name = 'users'
    ");
    $table_exists = $check_table->fetch()['count'] > 0;
    
    if ($table_exists) {
        $debug_info .= "✓ Table 'users' existe\n";
    } else {
        $debug_info .= "✗ Table 'users' N'EXISTE PAS\n";
    }
    
    // Compter les utilisateurs
    $count_users = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $debug_info .= "  - Nombre d'utilisateurs: $count_users\n\n";
    
    // Lister les utilisateurs
    if ($count_users > 0) {
        $debug_info .= "Utilisateurs en base de données:\n";
        $users = $pdo->query("SELECT id, username, email, role, actif FROM users")->fetchAll();
        foreach ($users as $user) {
            $debug_info .= "  - {$user['username']} ({$user['email']}) - Rôle: {$user['role']} - Actif: {$user['actif']}\n";
        }
    } else {
        $debug_info .= "✗ AUCUN utilisateur en base de données!\n";
    }
    
    $debug_info .= "\n---\n\n";
    
    // Tester la connexion avec admin/admin123
    $debug_info .= "Test de connexion: admin / admin123\n";
    $stmt = $pdo->prepare("SELECT id, username, email, password, role, nom, prenom, actif FROM users WHERE username = ?");
    $stmt->execute([$test_username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $debug_info .= "✗ Utilisateur '$test_username' non trouvé en base de données\n";
    } else {
        $debug_info .= "✓ Utilisateur trouvé\n";
        $debug_info .= "  - ID: {$user['id']}\n";
        $debug_info .= "  - Username: {$user['username']}\n";
        $debug_info .= "  - Email: {$user['email']}\n";
        $debug_info .= "  - Role: {$user['role']}\n";
        $debug_info .= "  - Actif: {$user['actif']}\n";
        $debug_info .= "  - Password hash: " . substr($user['password'], 0, 20) . "...\n";
        
        // Vérifier le mot de passe
        if (password_verify($test_password, $user['password'])) {
            $debug_info .= "✓ Mot de passe CORRECT (password_verify OK)\n";
        } else {
            $debug_info .= "✗ Mot de passe INCORRECT (password_verify ÉCHOUÉE)\n";
            
            // Essayer de régénérer le hash
            $new_hash = password_hash($test_password, PASSWORD_BCRYPT, ['cost' => 10]);
            $debug_info .= "\n  Hash généré pour '$test_password':\n";
            $debug_info .= "  $new_hash\n";
            $debug_info .= "\n  Le hash en base de données est peut-être incorrect!\n";
        }
    }
    
} catch (Exception $e) {
    $debug_info .= "✗ Erreur: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Authentification</title>
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
            max-width: 700px;
            background: white;
            border-radius: 10px;
            padding: 28px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        }
        pre {
            background: #f4f4f4;
            padding: 16px;
            border-radius: 5px;
            border-left: 4px solid #ced4da;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🔍 Debug - Authentification</h1>
        
        <div class="alert alert-warning">
            <strong>⚠️ Page de débogage</strong> - À supprimer en production
        </div>
        
        <h5>Informations de débogage:</h5>
        <pre><?php echo htmlspecialchars($debug_info); ?></pre>
        
        <hr>
        
        <h5>Actions recommandées:</h5>
        <div class="list-group">
            <a href="/Sys_ges_formation/setup.php" class="list-group-item list-group-item-action">
                ⚙️ Aller à la page de setup (pour initialiser la base de données)
            </a>
            <a href="/Sys_ges_formation/login.php" class="list-group-item list-group-item-action">
                🔐 Aller à la page de connexion
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
