<?php
include '../config.php';
include '../functions.php';
include '../session.php';
include '../header.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo getErrorMessage('Évaluation non trouvée');
    include '../footer.php';
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT 
            e.*,
            p.prenom,
            p.nom,
            p.email,
            f.titre as formation_titre,
            f.instructeur
        FROM evaluations e
        JOIN inscriptions i ON e.inscription_id = i.id
        JOIN participants p ON i.participant_id = p.id
        JOIN formations f ON i.formation_id = f.id
        WHERE e.id = ?
    ');
    $stmt->execute([$id]);
    $evaluation = $stmt->fetch();
    
    if (!$evaluation) {
        echo getErrorMessage('Évaluation non trouvée');
        include '../footer.php';
        exit;
    }
} catch (Exception $e) {
    echo getErrorMessage('Erreur: ' . $e->getMessage());
    $evaluation = null;
}
?>

<div class="mb-3">
    <a href="list.php" class="btn btn-secondary">← Retour</a>
</div>

<?php if ($evaluation): ?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="mb-0">Évaluation - <?php echo htmlspecialchars($evaluation['prenom'] . ' ' . $evaluation['nom']); ?></h3>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Participant</h5>
                <p><strong>Nom complet:</strong> <?php echo htmlspecialchars($evaluation['prenom'] . ' ' . $evaluation['nom']); ?></p>
                <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($evaluation['email']); ?>"><?php echo htmlspecialchars($evaluation['email']); ?></a></p>
            </div>
            <div class="col-md-6">
                <h5>Formation</h5>
                <p><strong>Titre:</strong> <?php echo htmlspecialchars($evaluation['formation_titre']); ?></p>
                <p><strong>Instructeur:</strong> <?php echo htmlspecialchars($evaluation['instructeur']); ?></p>
            </div>
        </div>
        
        <hr>
        
        <h5>Notes</h5>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Devoir (30%)</h6>
                        <p class="card-text fs-5">
                            <?php echo $evaluation['note_devoir'] ? number_format($evaluation['note_devoir'], 2, ',', ' ') : '-'; ?>/20
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Test (50%)</h6>
                        <p class="card-text fs-5">
                            <?php echo $evaluation['note_test'] ? number_format($evaluation['note_test'], 2, ',', ' ') : '-'; ?>/20
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Participation (20%)</h6>
                        <p class="card-text fs-5">
                            <?php echo $evaluation['note_participation'] ? number_format($evaluation['note_participation'], 2, ',', ' ') : '-'; ?>/20
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6 class="card-title">Note Finale</h6>
                        <p class="card-text fs-5 fw-bold">
                            <?php echo number_format($evaluation['note_finale'], 2, ',', ' '); ?>/20
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <hr>
        
        <h5>Formule de calcul de la note finale</h5>
        <div class="alert alert-info">
            <p class="mb-0">
                <strong>Note finale = </strong>
                (Devoir × 30%) + (Test × 50%) + (Participation × 20%)
            </p>
            <small class="text-muted">
                Basé sur vos saisies: (<?php echo $evaluation['note_devoir'] ?? '0'; ?> × 0,3) + (<?php echo $evaluation['note_test'] ?? '0'; ?> × 0,5) + (<?php echo $evaluation['note_participation'] ?? '0'; ?> × 0,2) = <?php echo number_format($evaluation['note_finale'], 2, ',', ' '); ?>
            </small>
        </div>
        
        <hr>
        
        <div class="row">
            <div class="col-md-6">
                <h5>Résultat: 
                    <span class="badge bg-<?php echo $evaluation['resultat'] == 'réussi' ? 'success' : ($evaluation['resultat'] == 'échoué' ? 'danger' : 'secondary'); ?>">
                        <?php echo ucfirst($evaluation['resultat']); ?>
                    </span>
                </h5>
                <p class="text-muted">
                    <small>
                        <?php 
                        if ($evaluation['resultat'] === 'réussi') {
                            echo "Note finale ≥ 12 → RÉUSSI";
                        } elseif ($evaluation['resultat'] === 'échoué') {
                            echo "Note finale < 12 → ÉCHOUÉ";
                        } else {
                            echo "? Aucune note saisie → EN ATTENTE";
                        }
                        ?>
                    </small>
                </p>
            </div>
            <div class="col-md-6">
                <h5>Certificat: 
                    <span class="badge bg-<?php echo $evaluation['certificat_delivre'] ? 'success' : 'warning'; ?>">
                        <?php echo $evaluation['certificat_delivre'] ? '✓ Délivré' : '✗ Non délivré'; ?>
                    </span>
                </h5>
                <p class="text-muted">
                    <small><?php echo $evaluation['certificat_delivre'] ? 'Certificat remis au participant' : 'En attente de remise'; ?></small>
                </p>
            </div>
        </div>
        
        <?php if (isAdmin()): ?>
        <div class="mt-4">
            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">✏️ Modifier</a>
            <a href="delete.php?id=<?php echo $id; ?>" class="btn btn-danger" onclick="return confirm('Confirmer la suppression?')">🗑️ Supprimer</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<p class="text-danger">Évaluation non trouvée.</p>
<?php endif; ?>

<?php include '../footer.php'; ?>
