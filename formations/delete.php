<?php
include '../config.php';
include '../functions.php';
include '../session.php';


requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: list.php');
    exit;
}

try {
    // ===== DÉBUT TRANSACTION =====
    if (!startTransaction($pdo)) {
        throw new Exception('Impossible de démarrer la transaction');
    }
    
    // Supprimer les inscriptions associées
    $stmt = $pdo->prepare('DELETE FROM evaluations WHERE inscription_id IN (SELECT id FROM inscriptions WHERE formation_id = ?)');
    $stmt->execute([$id]);
    
    $stmt = $pdo->prepare('DELETE FROM inscriptions WHERE formation_id = ?');
    $stmt->execute([$id]);
    
    // Supprimer la formation
    $stmt = $pdo->prepare('DELETE FROM formations WHERE id = ?');
    $success = $stmt->execute([$id]);
    
    if ($success && $stmt->rowCount() > 0) {
        // ===== COMMIT =====
        if (!commit($pdo)) {
            throw new Exception('Impossible de valider la transaction');
        }
        header('Location: list.php?success=deleted');
    } else {
        // ===== ROLLBACK =====
        rollback($pdo);
        header('Location: list.php?error=not_found');
    }
} catch (Exception $e) {
    rollback($pdo);
    header('Location: list.php?error=' . urlencode($e->getMessage()));
}
exit;
?>
