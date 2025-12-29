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
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    
    if (!$email || !$password || !$password_confirm || !$nom || !$prenom) {
        $message = 'Veuillez remplir tous les champs';
        $message_type = 'danger';
    } elseif ($password !== $password_confirm) {
        $message = 'Les mots de passe ne correspondent pas';
        $message_type = 'danger';
    } elseif (strlen($password) < 6) {
        $message = 'Le mot de passe doit contenir au moins 6 caractères';
        $message_type = 'danger';
    } else {
        try {
            // Vérifier si un compte existe déjà avec cet email
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $message = 'Un compte avec cet email existe déjà.';
                $message_type = 'warning';
            } else {
                // Créer le compte user (non approuvé)
                $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
                
                $stmt = $pdo->prepare('
                    INSERT INTO users (username, email, password, role, nom, prenom, participant_id, approved, actif)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ');
                
                $stmt->execute([
                    $email, // username = email
                    $email,
                    $password_hash,
                    'user',
                    $nom,
                    $prenom,
                    NULL, // pas encore lié à un participant
                    FALSE, // NON APPROUVÉ PAR DÉFAUT
                    true
                ]);
                
                $message = '✓ Compte créé avec succès! En attente d\'approbation de l\'administrateur.';
                $message_type = 'success';
            }
        } catch (Exception $e) {
            $message = 'Erreur: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Système de Gestion de Formation</title>
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
        }
        .register-container {
            max-width: 500px;
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
            font-size: 1.8rem;
        }
        .card-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
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
        .btn-register {
            background-color: var(--brand);
            border: none;
            color: var(--brand-contrast);
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
        }
        .btn-register:hover {
            background-color: #adb5bd;
            color: var(--brand-contrast);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: var(--brand);
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card">
            <div class="card-header">
                <h2></h2>
                <h2>S'inscrire</h2>
                <p>Gestion Formation</p>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control form-control-lg" id="prenom" name="prenom" 
                                   placeholder="Votre prénom" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control form-control-lg" id="nom" name="nom" 
                                   placeholder="Votre nom" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" 
                               placeholder="votre.email@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" 
                               placeholder="Minimum 6 caractères" required>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control form-control-lg" id="password_confirm" name="password_confirm" 
                               placeholder="Confirmez votre mot de passe" required>
                    </div>

                    <button type="submit" class="btn btn-register btn-lg">Créer mon Compte</button>
                </form>

                <div class="login-link">
                    Vous avez déjà un compte ? <a href="/Sys_ges_formation/login.php">Se connecter</a>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; color: #495057;">
            <p>© 2025 Système de Gestion de Formation - Tous droits réservés</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

