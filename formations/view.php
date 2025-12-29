<?php
include '../config.php';
include '../functions.php';
include '../session.php';
include '../header.php';

requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo getErrorMessage('Formation non trouvée');
    include '../footer.php';
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM formations WHERE id = ?');
    $stmt->execute([$id]);
    $formation = $stmt->fetch();
    
    if (!$formation) {
        echo getErrorMessage('Formation non trouvée');
        include '../footer.php';
        exit;
    }
    
    // Récupérer les inscriptions
    $stmt = $pdo->prepare('
        SELECT i.*, p.nom, p.prenom, e.note_finale, e.resultat
        FROM inscriptions i
        JOIN participants p ON i.participant_id = p.id
        LEFT JOIN evaluations e ON i.id = e.inscription_id
        WHERE i.formation_id = ?
    ');
    $stmt->execute([$id]);
    $inscriptions = $stmt->fetchAll();
    
} catch (Exception $e) {
    echo getErrorMessage('Erreur: ' . $e->getMessage());
    $formation = null;
    $inscriptions = [];
}
?>

<div class="mb-3">
    <a href="list.php" class="btn btn-secondary">← Retour</a>
</div>

<?php if ($formation): ?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3 class="mb-0"><?php echo htmlspecialchars($formation['titre']); ?></h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Instructeur:</strong> <?php echo htmlspecialchars($formation['instructeur']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($formation['description'] ?: 'N/A'); ?></p>
                <p><strong>Durée:</strong> <?php echo $formation['duree_heures']; ?> heures</p>
            </div>
            <div class="col-md-6">
                <p><strong>Début:</strong> <?php echo date('d/m/Y', strtotime($formation['date_debut'])); ?></p>
                <p><strong>Fin:</strong> <?php echo date('d/m/Y', strtotime($formation['date_fin'])); ?></p>
                <p><strong>Prix unitaire:</strong> <?php echo number_format($formation['prix_unitaire'], 2, ',', ' '); ?> €</p>
                <p><strong>Statut:</strong> <span class="badge bg-primary"><?php echo ucfirst($formation['statut']); ?></span></p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Participants inscrits (<?php echo count($inscriptions); ?>)</h4>
    <?php if (isAdmin()): ?>
    <a href="add_participant.php?formation_id=<?php echo $id; ?>" class="btn btn-success">+ Ajouter un participant</a>
    <?php endif; ?>
</div>

<?php if (count($inscriptions) > 0): ?>
<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Participant</th>
                <th>Date inscription</th>
                <th>Statut</th>
                <th>Note finale</th>
                <th>Résultat</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inscriptions as $inscription): ?>
            <tr>
                <td><?php echo htmlspecialchars($inscription['prenom'] . ' ' . $inscription['nom']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($inscription['date_inscription'])); ?></td>
                <td><span class="badge bg-info"><?php echo ucfirst($inscription['statut']); ?></span></td>
                <td><?php echo $inscription['note_finale'] ? number_format($inscription['note_finale'], 2, ',', ' ') : '-'; ?></td>
                <td>
                    <?php if ($inscription['resultat']): ?>
                        <span class="badge bg-<?php echo $inscription['resultat'] == 'réussi' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($inscription['resultat']); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Non évalué</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p class="text-muted">Aucun participant inscrit pour cette formation.</p>
<?php endif; ?>

<?php else: ?>
<p class="text-danger">Formation non trouvée.</p>
<?php endif; ?>

<?php include '../footer.php'; ?>
