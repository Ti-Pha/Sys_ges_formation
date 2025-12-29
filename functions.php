<?php
/**
 * Fonctions utilitaires
 * Système de Gestion de Formation Professionnelle
 */

function startTransaction($pdo) {
    try {
        $pdo->beginTransaction();
        return true;
    } catch (Exception $e) {
        error_log('Erreur transaction: ' . $e->getMessage());
        return false;
    }
}

function commit($pdo) {
    try {
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        error_log('Erreur commit: ' . $e->getMessage());
        return false;
    }
}

function rollback($pdo) {
    try {
        $pdo->rollBack();
        return true;
    } catch (Exception $e) {
        error_log('Erreur rollback: ' . $e->getMessage());
        return false;
    }
}

function getSuccessMessage($message) {
    return '<div class="alert alert-success" role="alert">' . htmlspecialchars($message) . '</div>';
}

function getErrorMessage($message) {
    return '<div class="alert alert-danger" role="alert">' . htmlspecialchars($message) . '</div>';
}

function getInfoMessage($message) {
    return '<div class="alert alert-info" role="alert">' . htmlspecialchars($message) . '</div>';
}

function redirect($page) {
    header('Location: ' . $page);
    exit;
}

?>
