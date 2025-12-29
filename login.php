<?php
include 'config.php';
include 'session.php';

// Si déjà connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? '/Sys_ges_formation/admin/' : '/Sys_ges_formation/user/'));
    exit;
}

$message = '';
$message_type = '';

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password) {
        $message = 'Veuillez remplir tous les champs';
        $message_type = 'danger';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, username, email, password, role, nom, prenom, actif, approved, rejection_reason FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $message = 'Identifiants invalides';
                $message_type = 'danger';
            } elseif (!$user['actif']) {
                $message = 'Votre compte a été désactivé. Contactez l\'administrateur.';
                $message_type = 'danger';
            } elseif (!password_verify($password, $user['password'])) {
                $message = 'Identifiants invalides';
                $message_type = 'danger';
            } elseif (!$user['approved']) {
                $message = 'Votre compte est en attente d\'approbation par l\'administrateur.';
                if ($user['rejection_reason']) {
                    $message .= '<br><strong>Motif du rejet:</strong> ' . htmlspecialchars($user['rejection_reason']);
                }
                $message_type = 'warning';
            } else {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['last_activity'] = time();
                
                // Rediriger selon le rôle
                $redirect = ($user['role'] === 'admin') ? '/Sys_ges_formation/admin/' : '/Sys_ges_formation/user/';
                header('Location: ' . $redirect);
                exit;
            }
        } catch (Exception $e) {
            $message = 'Erreur: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Traiter les messages d'état
$expired = isset($_GET['expired']) ? true : false;
$logged_out = isset($_GET['logged_out']) ? true : false;
$unauthorized = isset($_GET['unauthorized']) ? true : false;

if ($expired) {
    $message = 'Votre session a expiré. Veuillez vous reconnecter.';
    $message_type = 'warning';
} elseif ($logged_out) {
    $message = 'Vous avez été déconnecté avec succès.';
    $message_type = 'info';
} elseif ($unauthorized) {
    $message = 'Accès non autorisé. Veuillez vous connecter avec les permissions appropriées.';
    $message_type = 'warning';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système de Gestion de Formation</title>
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
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        .card {
            border: none;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            border-radius: 10px;
            background-color: #ffffff;
        }
        .card-header {
            background-color: var(--brand);
            color: var(--brand-contrast);
            border: none;
            border-radius: 10px 10px 0 0;
            padding: 24px;
            text-align: center;
        }
        .card-header h2 {
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .card-header p {
            margin: 0;
            opacity: 0.9;
        }
        .card-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #adb5bd;
            box-shadow: none;
        }
        .btn-login {
            background-color: var(--brand);
            border: none;
            color: var(--brand-contrast);
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
        }
        .btn-login:hover {
            background-color: #adb5bd;
            color: var(--brand-contrast);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
        }
        .demo-credentials {
            background: #f8f9fa;
            border-left: 4px solid #ced4da;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .demo-credentials h6 {
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
        }
        .demo-credentials p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #666;
        }
        .demo-credentials code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <h2></h2>
                <h2>Gestion Formation</h2>
                <p>Système de Gestion de Formations Professionnelles</p>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control form-control-lg" id="username" name="username" 
                               placeholder="Entrez votre nom d'utilisateur" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" 
                               placeholder="Entrez votre mot de passe" required>
                    </div>

                    <button type="submit" class="btn btn-login btn-lg">Se Connecter</button>
                </form>

                <div style="text-align: center; margin-top: 20px;">
                    <p class="text-muted">Vous n'avez pas de compte ?</p>
                    <a href="/Sys_ges_formation/register.php" class="btn btn-outline-secondary">Créer un compte</a>
                </div>

                <!-- <div class="demo-credentials">
                    <h6>Identifiants de démonstration</h6>
                    <p>
                        <strong>Admin :</strong><br>
                        Login: <code>admin</code> | Mot de passe: <code>admin123</code>
                    </p>
                    <hr style="margin: 10px 0;">
                    <p>
                        <strong>Utilisateur :</strong><br>
                        Login: <code>user</code> | Mot de passe: <code>user123</code>
                    </p>
                </div> -->
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; color: #495057;">
            <p>© 2025 Système de Gestion de Formation - Tous droits réservés</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
