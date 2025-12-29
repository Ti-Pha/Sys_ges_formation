<?php
/**
 * Gestion des sessions et authentification
 * Système de Gestion de Formation Professionnelle
 */

session_start();

// Durée de session en secondes (5 minutes)
$SESSION_TIMEOUT = 5 * 60;

// Vérifier si la session est expirée
if (isset($_SESSION['user_id'])) {
    if (time() - $_SESSION['last_activity'] > $SESSION_TIMEOUT) {
        // Session expirée
        session_destroy();
        header('Location: /Sys_ges_formation/login.php?expired=1');
        exit;
    }
}

// Mettre à jour le timestamp d'activité
$_SESSION['last_activity'] = time();

/**
 * Vérifier si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur est admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * Obtenir l'utilisateur connecté
 * @return array|null
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'nom' => $_SESSION['user_nom'],
            'prenom' => $_SESSION['user_prenom']
        ];
    }
    return null;
}

/**
 * Rediriger vers la connexion si non authentifié
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /Sys_ges_formation/login.php');
        exit;
    }
}

/**
 * Rediriger vers la connexion si non admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /Sys_ges_formation/login.php?unauthorized=1');
        exit;
    }
}

/**
 * Déconnexion
 */
function logout() {
    $_SESSION = [];
    session_destroy();
    header('Location: /Sys_ges_formation/login.php?logged_out=1');
    exit;
}

?>
